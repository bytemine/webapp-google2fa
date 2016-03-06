<?
/**
 * PHP file that presents itself as a QR Code with the 2FA information of the user
 *
 * @author Norman Thimm
 * @copyright 2016 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */

	require "../../../config.php";

        session_name(COOKIE_NAME);
        session_start();

	require "../config.php";
	require "class.google2facrypt.php";
	require "class.google2fadata." . PLUGIN_GOOGLE2FA_DATABASE . ".php";

	require "external/phpqrcode/qrlib.php";

	$uri = "otpauth://totp/" . PLUGIN_GOOGLE2FA_APPNAME . ":" . $_SESSION['username'] . "@" . PLUGIN_GOOGLE2FA_APPNAME .
		"?secret=" . Google2FAData::getSecret() . "&issuer=" . PLUGIN_GOOGLE2FA_APPNAME;

	QRcode::png($uri, false, QR_ECLEVEL_L, 5, 0);

?>
