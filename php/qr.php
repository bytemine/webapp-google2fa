<?php
/**
 * PHP file that presents itself as a QR Code with the 2FA information of the user
 *
 * @author Norman Thimm
 * @copyright 2016 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */

	require "../../../init.php"; // BASE_PATH
	
	require "external/phpqrcode/qrlib.php";
	require BASE_PATH . "server/includes/core/class.encryptionstore.php";

	$encryptionStore = EncryptionStore::getInstance();
	$username = $encryptionStore->get('username');
	$secret = base64_decode($_SESSION['PLUGIN_GOOGLE2FA_SECRET']);
	
	$url = "otpauth://totp/" . PLUGIN_GOOGLE2FA_APPNAME . ":" . $username . "@" . PLUGIN_GOOGLE2FA_APPNAME .
				"?secret=" . $secret . "&issuer=" . PLUGIN_GOOGLE2FA_APPNAME;

	QRcode::png($url, false, QR_ECLEVEL_L, 5, 0);

?>
