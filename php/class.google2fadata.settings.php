<?php

/**
 * PHP Class for handling database communication (settings)
 *
 * @class Google2FAData
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */
class Google2FAData {

	/**
	 * Get secret key
	 *
	 * @return string
	 */
	public static function getSecret() {
		return Google2FACrypt::decrypt($GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/secret_key"));
	}

	/**
	 * Set secret key
	 *
	 * @param string $a key
	 */
	public static function setSecret($secret) {
		$GLOBALS["settings"]->set("zarafa/v1/plugins/google2fa/secret_key", Google2FACrypt::encrypt($secret));
		$GLOBALS["settings"]->saveSettings();
	}

	/**
	 * Two-factor authentication activated
	 *
	 * @return boolean
	 */
	public static function isActivated() {
		return $GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/activate");
	}

	/**
	 * Activate or deactivate two-factor authentication
	 *
	 * @param boolean $activate activation true/false
	 */
	public static function setActivate($activate) {
		$GLOBALS["settings"]->set("zarafa/v1/plugins/google2fa/activate", $activate);
		$GLOBALS["settings"]->saveSettings();
	}

        /**
         * Get type of cryption
         *
         * @return string
         */
	public static function getCryption() {
		return $GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/encryption");
	}

	/**
	 * Set type of cryption
	 *
	 * @param string $crypt type of cryption
	 */
	public static function setCryption($crypt) {
                $GLOBALS["settings"]->set("zarafa/v1/plugins/google2fa/encryption", $crypt);
                $GLOBALS["settings"]->saveSettings();
	}

	/**
	 * Get used codes
	 *
	 * @return arrayi
	 */
	public static function getUsedCodes() {
		return str_split(Google2FACrypt::decrypt($GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/used_codes")), 6);
	}

	/**
	 * Add a used code
	 *
	 * @param string $code used code
	 */
	public static function addUsedCode($code) {
		$usedCodes = Google2FACrypt::decrypt($GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/used_codes"));
		$a = str_split($usedCodes, 6);
		$GLOBALS["settings"]->set("zarafa/v1/plugins/google2fa/used_codes", Google2FACrypt::encrypt($a[count($a)-1] . $code));
                $GLOBALS["settings"]->saveSettings();
	}

	/**
	 * Delete used codes
	 */
	public static function delUsedCodes() {
                $GLOBALS["settings"]->set("zarafa/v1/plugins/google2fa/used_codes", "");
                $GLOBALS["settings"]->saveSettings();
        }

	/**
	 * Get timeless codes
	 *
	 * @return array
	 */
        public static function getTimelessCodes() {
                return str_split(Google2FACrypt::decrypt($GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/timeless_codes")), 6);
        }

	/**
	 * Remove a used timeless code
	 *
	 * @param string $code code
	 */
	public static function rmTimelessCode($code) {
		$timelessCodes = Google2FACrypt::decrypt($GLOBALS["settings"]->get("zarafa/v1/plugins/google2fa/timeless_codes"));
		$a = str_split($timelessCodes, 6);
		unset($a[array_search($code, $a)]);
		self::setTimelessCodes($a);
	}

	/**
	 * Set timeless codes
	 *
	 * @param array $codes timeless codes
	 */
	public static function setTimelessCodes($codes) {
		$GLOBALS["settings"]->set("zarafa/v1/plugins/google2fa/timeless_codes", Google2FACrypt::encrypt(implode($codes)));
		$GLOBALS["settings"]->saveSettings();
	}

}

?>
