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
			throw new Exception("PHP Module MYSQLI not loaded!");

		if (self::$conn == null) {
			
			// Create connection
			if (!defined(PLUGIN_GOOGLE2FA_DATABASE_PORT)) define('PLUGIN_GOOGLE2FA_DATABASE_PORT', ini_get("mysqli.default_port")); // new key in config.php in 0.5.2
			self::$conn = new mysqli(PLUGIN_GOOGLE2FA_DATABASE_SERVERNAME, PLUGIN_GOOGLE2FA_DATABASE_USERNAME,
				PLUGIN_GOOGLE2FA_DATABASE_PASSWORD, PLUGIN_GOOGLE2FA_DATABASE_DBNAME, PLUGIN_GOOGLE2FA_DATABASE_PORT);

			// Check connection
			if (mysqli_connect_error()) { // no object-oriented check because of compatibility with PHP 5.2.9 and 5.3.0
				throw new Exception("MySQL connection failed: " . mysqli_connect_error());
			}

			// Create tables
			if (PLUGIN_GOOGLE2FA_DATABASE_CREATETABLES) {
				if (!self::$conn->query("CREATE TABLE IF NOT EXISTS `user` (" .
					"`id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(512) COLLATE utf8_unicode_ci NOT NULL, " .
					"`secret_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '', `activate` tinyint(1) NOT NULL DEFAULT '0', " .
					"`encryption` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '', `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, " .
					"PRIMARY KEY (`id`), KEY `username` (`username`(333)), KEY `id` (`id`)) ENGINE=MyISAM " .
					"DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;"))
					throw new Exception(self::$conn->error);
				if (!self::$conn->query("CREATE TABLE IF NOT EXISTS `used_codes` (" .
					"`user_id` int(11) NOT NULL, `code` varchar(18) COLLATE utf8_unicode_ci NOT NULL, " .
					"`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY `user_id` (`user_id`), " .
					"KEY `code` (`code`), KEY `created` (`created`)" .
					") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"))
					throw new Exception(self::$conn->error);
				if (!self::$conn->query("CREATE TABLE IF NOT EXISTS `timeless_codes` (" .
					"`user_id` int(11) NOT NULL, `code` varchar(18) COLLATE utf8_unicode_ci NOT NULL, " .
					"`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, KEY `user_id` (`user_id`), " .
					"KEY `code` (`code`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"))
					throw new Exception(self::$conn->error);
			}

			$encryptionStore = EncryptionStore::getInstance();
			$username = $encryptionStore->get('username');

			// Get user_id - insert user if he/she doesn't exists
			if ($result = self::$conn->query("SELECT `id` FROM `user` WHERE `username` LIKE '" . $username . "' LIMIT 0 , 1;")) {
				if ($result->num_rows == 1) {
					self::$user_id = $result->fetch_object()->id;
				} else if ($result->num_rows == 0) {
					if (!self::$conn->query("INSERT INTO `user` (`username`) VALUES ('" . $username . "');"))
						die ("MySQL error: can't create user");
					self::$user_id = self::$conn->insert_id;
				}
				$result->close();
			} else {
				throw new Exception(self::$conn->error);
			}
		}
		return self::$conn;
	}

	/**
	 * Get secret key
	 *
	 * @return string
	 */
	public static function getSecret() {
		if ($result = self::initConn()->query("SELECT `secret_key` FROM `user` WHERE `id` =" . self::$user_id . " LIMIT 1;")) {
			$secret_key = $result->fetch_object()->secret_key;
			$result->close();
			return Google2FACrypt::decrypt($secret_key);
		} else throw new Exception(self::$conn->error);
	}

	/**
	 * Set secret key
	 *
	 * @param string $a key
	 */
	public static function setSecret($secret) {
		if (!self::initConn()->query("UPDATE `user` SET `secret_key` = '" . Google2FACrypt::encrypt($secret) . "' WHERE `id` =" . self::$user_id . ";"))
			throw new Exception(self::$conn->error);
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
                } else throw new Exception(self::$conn->error);
	}

	/**
	 * Activate or deactivate two-factor authentication
	 *
	 * @param boolean $activate true/false
	 */
	public static function setActivate($activate) {
		if (!self::initConn()->query("UPDATE `user` SET `activate` = " . ($activate ? "1" : "0") . " WHERE `id` =" . self::$user_id . ";"))
			throw new Exception(self::$conn->error);
	}

        /**
         * Get type of cryption
         *
         * @return string
         */
	public static function getCryption() {
		if ($result = self::initConn()->query("SELECT `encryption` FROM `user` WHERE `id` =" . self::$user_id . " LIMIT 1;")) {
                        $encryption = $result->fetch_object()->encryption;
                        $result->close();
                        return $encryption;
                } throw new Exception(self::$conn->error);
	}

	/**
	 * Set type of cryption
	 *
	 * @param string $crypt type of cryption
	 */
	public static function setCryption($crypt) {
                if (!self::initConn()->query("UPDATE `user` SET `encryption` = '" . $crypt . "' WHERE `id` =" . self::$user_id . ";"))
			throw new Exception(self::$conn->error);	
	}

	/**
	 * Get used codes
	 *
	 * @return array
	 */
	public static function getUsedCodes() {
		$codes = array();
		if ($result = self::initConn()->query("SELECT `code` FROM `used_codes` WHERE `user_id` =" . self::$user_id . ";")) {
                        while($code = $result->fetch_object()) {
				$codes[] = Google2FACrypt::decrypt($code->code);
			}
                        $result->close();
			return $codes;
                } else throw new Exception(self::$conn->error);
	}

	/**
	 * Add a used code
	 *
	 * @param string $code used code
	 */
	public static function addUsedCode($code) {
		if (!self::initConn()->query("DELETE FROM `used_codes` WHERE `user_id` =" . self::$user_id . " AND `created` < DATE_SUB(now(), interval 15 Minute);"))
			throw new Exception(self::$conn->error);
		if (!self::$conn->query("INSERT INTO `used_codes` (`user_id` ,`code`) VALUES (" . self::$user_id . ", '" . Google2FACrypt::encrypt($code)  . "');"))
			throw new Exception(self::$conn->error);
	}

	/**
	 * Delete used codes
	 */
	public static function delUsedCodes() {
		if (!self::initConn()->query("DELETE FROM `used_codes` WHERE `user_id` =" . self::$user_id . ";"))
			throw new Exception(self::$conn->error);
        }

	/**
	 * Get timeless codes
	 *
	 * @return array
	 */
        public static function getTimelessCodes() {

		$codes = array();
                if ($result = self::initConn()->query("SELECT `code` FROM `timeless_codes` WHERE `user_id` =" . self::$user_id . ";")) {
			while($obj = $result->fetch_object()) {
				$codes[] = Google2FACrypt::decrypt($obj->code);
                        }
                        $result->close();
			return $codes;
                } else throw new Exception(self::$conn->error);
        }

	/**
	 * Remove a used timeless code
	 *
	 * @param string $code code
	 */
	public static function rmTimelessCode($code) {
		if (!self::initConn()->query("DELETE FROM `timeless_codes` WHERE `user_id` =" . self::$user_id . " AND `code` = '" . Google2FACrypt::encrypt($code) . "' LIMIT 1;"))
			throw new Exception(self::$conn->error);
	}

	/**
	 * Set timeless codes
	 *
	 * @param array $codes timeless codes
	 */
	public static function setTimelessCodes($codes) {
		if ($result = self::initConn()->query("DELETE FROM `timeless_codes` WHERE `user_id` =" . self::$user_id . ";")) {
			foreach ($codes as $code) {
				if (!self::$conn->query("INSERT INTO `timeless_codes` (`user_id` ,`code`) VALUES (" . self::$user_id . ", '" . Google2FACrypt::encrypt($code)  . "');"))
					return false;
			}
		} else throw new Exception(self::$conn->error);
	}
}

?>
