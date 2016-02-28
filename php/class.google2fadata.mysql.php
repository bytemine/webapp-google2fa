<?php

/**
 * PHP Class for handling database communication (mysql)
 *
 * @class Google2FAData
 * @author Norman Thimm
 * @copyright 2016 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */
class Google2FAData {

	private static $conn = null;
	private static $user_id = null;

	/**
	 * Initialization db connection
	 *
	 * @return dbconnection
	 */
	protected static function initConn() {
		
		if (!extension_loaded("mysqli"))
			die("PHP Module MYSQLI not loaded!");

		if (self::$conn == null) {
			
			// Create connection
			self::$conn = new mysqli(PLUGIN_GOOGLE2FA_DATABASE_SERVERNAME, PLUGIN_GOOGLE2FA_DATABASE_USERNAME,
				PLUGIN_GOOGLE2FA_DATABASE_PASSWORD, PLUGIN_GOOGLE2FA_DATABASE_DBNAME);

			// Check connection
			if (mysqli_connect_error()) { // no object-oriented check because of compatibility with PHP 5.2.9 and 5.3.0
				die("MySQL connection failed: " . mysqli_connect_error());
			}

			// Create tables
			if (PLUGIN_GOOGLE2FA_DATABASE_CREATETABLES) {
				if (!self::$conn->query("CREATE TABLE IF NOT EXISTS `user` (" .
					"`id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(512) COLLATE utf8_unicode_ci NOT NULL, " .
					"`secret_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '', `activate` tinyint(1) NOT NULL DEFAULT '0', " .
					"`encryption` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '', `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, " .
					"PRIMARY KEY (`id`), KEY `username` (`username`(333)), KEY `id` (`id`)) ENGINE=MyISAM " .
					"DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;"))
					die ("MySQL error: table user can't created");
				if (!self::$conn->query("CREATE TABLE IF NOT EXISTS `used_codes` (" .
					"`user_id` int(11) NOT NULL, `code` varchar(18) COLLATE utf8_unicode_ci NOT NULL, " .
					"`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY `user_id` (`user_id`), " .
					"KEY `code` (`code`), KEY `created` (`created`)" .
					") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"))
					die ("MySQL error: table used_codes can't created");
				if (!self::$conn->query("CREATE TABLE IF NOT EXISTS `timeless_codes` (" .
					"`user_id` int(11) NOT NULL, `code` varchar(18) COLLATE utf8_unicode_ci NOT NULL, " .
					"`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY `user_id` (`user_id`), " .
					"KEY `code` (`code`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"))
					die ("MySQL error: table timeless_codes can't created");
			}

			// Get user_id - insert user if he/she doesn't exists
			if ($result = self::$conn->query("SELECT `id` FROM `user` WHERE `username` LIKE '" . $_SESSION['username'] . "' LIMIT 0 , 1;")) {
				if ($result->num_rows == 1) {
					self::$user_id = $result->fetch_object()->id;
				} else if ($result->num_rows == 0) {
					if (!self::$conn->query("INSERT INTO `user` (`username`) VALUES ('" . $_SESSION['username'] . "');"))
						die ("MySQL error: can't create user");
					self::$user_id = self::$conn->insert_id;
				}
				$result->close();
			} else {
				die ("MySQL error: can't get id from user");	
			}
		}
		return self::$conn;
	}

	/**
	 * Get secret key
	 *
	 * @return string or false
	 */
	public static function getSecret() {
		if ($result = self::initConn()->query("SELECT `secret_key` FROM `user` WHERE `id` =" . self::$user_id . " LIMIT 1;")) {
			$secret_key = $result->fetch_object()->secret_key;
			$result->close();
			return Google2FACrypt::decrypt($secret_key);
		}
		return false;
	}

	/**
	 * Set secret key
	 *
	 * @param string $a key
	 * @return boolean
	 */
	public static function setSecret($secret) {
		return self::initConn()->query("UPDATE `user` SET `secret_key` = '" . Google2FACrypt::encrypt($secret) . "' WHERE `id` =" . self::$user_id . ";");
	}

	/**
	 * Two-factor authentication activated
	 *
	 * @return boolean
	 */
	public static function isActivated() {
		if ($result = self::initConn()->query("SELECT `activate` FROM `user` WHERE `id` =" . self::$user_id . " LIMIT 1;")) {
                        $activate = $result->fetch_object()->activate;
                        $result->close();
                        return ($activate == 1);
                }
                return false;
	}

	/**
	 * Activate or deactivate two-factor authentication
	 *
	 * @param boolean $activate activation true/false
	 * @return boolean
	 */
	public static function setActivate($activate) {
		return self::initConn()->query("UPDATE `user` SET `activate` = " . ($activate ? "1" : "0") . " WHERE `id` =" . self::$user_id . ";");
	}

        /**
         * Get type of cryption
         *
         * @return string or false
         */
	public static function getCryption() {
		if ($result = self::initConn()->query("SELECT `encryption` FROM `user` WHERE `id` =" . self::$user_id . " LIMIT 1;")) {
                        $encryption = $result->fetch_object()->encryption;
                        $result->close();
                        return $encryption;
                }
                return false;
	}

	/**
	 * Set type of cryption
	 *
	 * @param string $crypt type of cryption
	 * @return boolean
	 */
	public static function setCryption($crypt) {
                return self::initConn()->query("UPDATE `user` SET `encryption` = '" . $crypt . "' WHERE `id` =" . self::$user_id . ";");
	}

	/**
	 * Get used codes
	 *
	 * @return array or false
	 */
	public static function getUsedCodes() {
		$codes = array();
		if ($result = self::initConn()->query("SELECT `code` FROM `used_codes` WHERE `user_id` =" . self::$user_id . ";")) {
                        while($code = $result->fetch_object()) {
				$codes[] = Google2FACrypt::decrypt($code->code);
			}
                        $result->close();
			return $codes;
                }
		return false;
	}

	/**
	 * Add a used code
	 *
	 * @param string $code used code
	 * @return boolean
	 */
	public static function addUsedCode($code) {
		if (self::initConn()->query("DELETE FROM `used_codes` WHERE `user_id` =" . self::$user_id . " AND `created` < DATE_SUB(now(), interval 15 Minute);"))
			return self::$conn->query("INSERT INTO `used_codes` (`user_id` ,`code`) VALUES (" . self::$user_id . ", '" . Google2FACrypt::encrypt($code)  . "');");
		return false;
	}

	/**
	 * Delete used codes
	 *
	 * @return boolean
	 */
	public static function delUsedCodes() {
		return self::initConn()->query("DELETE FROM `used_codes` WHERE `user_id` =" . self::$user_id . ";");
        }

	/**
	 * Get timeless codes
	 *
	 * @return array or false
	 */
        public static function getTimelessCodes() {

		$codes = array();
                if ($result = self::initConn()->query("SELECT `code` FROM `timeless_codes` WHERE `user_id` =" . self::$user_id . ";")) {
			while($obj = $result->fetch_object()) {
				$codes[] = Google2FACrypt::decrypt($obj->code);
                        }
                        $result->close();
			return $codes;
                }
                return false;
        }

	/**
	 * Remove a used timeless code
	 *
	 * @param string $code code
	 * @return boolean
	 */
	public static function rmTimelessCode($code) {
		return self::initConn()->query("DELETE FROM `timeless_codes` WHERE `user_id` =" . self::$user_id . " AND `code` = '" . Google2FACrypt::encrypt($code) . "' LIMIT 1;");
	}

	/**
	 * Set timeless codes
	 *
	 * @param array $codes timeless codes
	 * @return boolean
	 */
	public static function setTimelessCodes($codes) {
		if ($result = self::initConn()->query("DELETE FROM `timeless_codes` WHERE `user_id` =" . self::$user_id . ";")) {
			foreach ($codes as $code) {
				if (!self::$conn->query("INSERT INTO `timeless_codes` (`user_id` ,`code`) VALUES (" . self::$user_id . ", '" . Google2FACrypt::encrypt($code)  . "');"))
					return false;
			}
		}
		return $result;
	}
}

?>
