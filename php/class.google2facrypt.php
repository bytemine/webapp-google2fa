<?php

/**
 * PHP Class for handling Encryption an decryption in database
 *
 * @class Google2FACrypt
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */
class Google2FACrypt {

	private static $encr = null;
	private static $td = null;
	private static $iv = null;
	private static $key = null;

	/**
	 * Initialization type of cryption
	 *
	 * @return string
	 */
	protected static function initCryption() {
		if (self::$encr === null) {
			self::$encr = Google2FAData::getCryption(); // PLUGIN_GOOGLE2FA_CRYPT
			if (self::$encr === "") {
				if (extension_loaded("mcrypt") && function_exists ("mcrypt_encrypt") && PLUGIN_GOOGLE2FA_CRYPT !== 'base64' && PLUGIN_GOOGLE2FA_CRYPT !== 'no')
					self::$encr = "mcrypt";		
				else if (function_exists ("base64_encode") && PLUGIN_GOOGLE2FA_CRYPT !== 'no')
					self::$encr = "base64";
				else
					self::$encr = "no";
				Google2FAData::setCryption(self::$encr);
			}
			if (self::$encr === "mcrypt") {
				self::$td = mcrypt_module_open(PLUGIN_GOOGLE2FA_MCRYPTALGORITHM, '', PLUGIN_GOOGLE2FA_MCRYPTMODE, '');
				self::$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size(self::$td), PLUGIN_GOOGLE2FA_MCRYPTRAND);
				$ks = mcrypt_enc_get_key_size(self::$td);
				self::$key = substr(md5(PLUGIN_GOOGLE2FA_MCRYPTKEY), 0, $ks);
			}
		}
	}

        /**
         * Encrypt a string
         *
	 * @param string $a text to encrypt
         * @return string
         */
	public static function encrypt($a) {
		self::initCryption();
		if ($a === "") {
			return $a;
		} else if (self::$encr === "base64") {
			return base64_encode($a);
		} else if (self::$encr === "mcrypt") {
			mcrypt_generic_init(self::$td, self::$key, self::$iv);
			$a = mcrypt_generic(self::$td, $a);
			mcrypt_generic_deinit(self::$td);
			return trim(base64_encode($a));
		}
		return $a;
	}

        /**
         * Decrypt a string
         *
         * @param string $a text to decrypt
         * @return string
         */
	public static function decrypt($a) {
                self::initCryption();
                if ($a === "") {
			return $a;
		} else if (self::$encr === "base64") {
                        return base64_decode($a);
                } else if (self::$encr === "mcrypt") {
			mcrypt_generic_init(self::$td, self::$key, self::$iv);
			$a = mdecrypt_generic(self::$td, base64_decode($a));
			mcrypt_generic_deinit(self::$td);
			return trim($a);
		}
                return $a;
	}
}

?>
