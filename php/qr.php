<?

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
