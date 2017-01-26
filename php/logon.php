<?php

/**
 * PHP file check code from two-factor authentication login page (login.php)
 *
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm, Daniel Rauer
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */
	require_once("../../../init.php");
	require_once(BASE_PATH . "config.php");
	require_once("external/GoogleAuthenticator/GoogleAuthenticator.php");
	require_once(BASE_PATH . "server/includes/core/class.encryptionstore.php");

	// Make sure the php session is started
	WebAppSession::getInstance();

	$code = ($_POST && array_key_exists('token', $_POST)) ? $_POST['token'] : '';
	$secret = $_SESSION['google2FASecret'];
	$usedCodes = $_SESSION['google2FAUsedCodes'];
	$timelessCodes = $_SESSION['google2FATimelessCodes'];

	$ga = new PHPGangsta_GoogleAuthenticator();
	$verification = false;

	if ($ga->verifyCode($secret, $code, 1) && !in_array($code, $usedCodes)) { // 1 = 1*30sec clock tolerance
		$verification = true;
	} else if (count($timelessCodes) > 0 && $timelessCodes[0] !== "" && in_array($code, $timelessCodes)) { // without generated codes timelessCodes has 1 empty string element
		$verification = true;
		$_SESSION['google2FACodeTimeless'] = true;
	}

	if ($verification) {
		$encryptionStore = EncryptionStore::getInstance();
		$username = $encryptionStore->get('google2FAUsername');
		$password = $encryptionStore->get('google2FAPassword');
		$encryptionStore->add('username', $username);
		$encryptionStore->add('password', $password);
		$_SESSION['google2FACode'] = $code; // to disable code
		$_SESSION['google2FALoggedOn'] = TRUE; // 2FA successful
		header('Location: ../../../index.php', true, 303);

	} else {
		$_SESSION['google2FALoggedOn'] = FALSE; // login not successful
		header('Location: login.php', true, 303);
	}
?>
