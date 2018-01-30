<?php

require "class.google2facrypt.php";
require "class.google2fadata." . PLUGIN_GOOGLE2FA_DATABASE . ".php";
require_once "external/http-foundation/IpUtils.php";

/**
 * PHP Class plugin Google2FA for two-factor authentication
 *
 * @class PluginGoogle2FA
 * @extends Plugin
 * @author Norman Thimm, Daniel Rauer
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */
class PluginGoogle2FA extends Plugin {

	/**
	 * Function initializes the Plugin and registers all hooks
	 */
	function init() {
		$this->registerHook('server.core.settings.init.before');
		$this->registerHook('server.index.load.main.before');
	}

	/**
	 * Function is executed when a hook is triggered by the PluginManager
	 *
	 * @param string $eventID the id of the triggered hook
	 * @param mixed $data object(s) related to the hook
	 */
	function execute($eventID, &$data) {
		switch($eventID) {
                        case 'server.core.settings.init.before' :

                                $this->injectPluginSettings($data);
                                break;

			case 'server.index.load.main.before' : // don't use the logon trigger because we need the settings

				try {

					if (PLUGIN_GOOGLE2FA_ALWAYS_ENABLED) {
						$GLOBALS["settings"]->set('zarafa/v1/plugins/google2fa/enable', true);
						$GLOBALS["settings"]->saveSettings();
					}

					if (PLUGIN_GOOGLE2FA_ALWAYS_ACTIVATED)
						Google2FAData::setActivate(true);

					// Check, if user has enabled plugin and has activated 2FA
					if (!$GLOBALS["settings"]->get('zarafa/v1/plugins/google2fa/enable')
						|| !Google2FAData::isActivated())
						break;

					// Check, if Client-IP is in Whitelist
					if (PLUGIN_GOOGLE2FA_WHITELIST !== "") {
						try {
							if (Symfony\Component\HttpFoundation\IpUtils::checkIp($_SERVER['REMOTE_ADDR'], explode (",", PLUGIN_GOOGLE2FA_WHITELIST)))
								break;
						} catch (Exception $e) { // show error, if we have to check an IPv6 connection and PHP was compiled with option "disable-ipv6"
							die ("Google2FA: " . $e->getMessage());
						}
					}

					// Check, if token authorisation is already done (example: attachment-upload)
					if (array_key_exists('google2FALoggedOn', $_SESSION) && $_SESSION['google2FALoggedOn']) {

						// Login successful - save or remove code
						if (isset($_SESSION['google2FACode'])) {
							if (isset($_SESSION['google2FACodeTimeless'])) {
								Google2FAData::rmTimelessCode($_SESSION['google2FACode']);
								unset($_SESSION['google2FACodeTimeless']);
							} else {
								Google2FAData::addUsedCode($_SESSION['google2FACode']);
							}
							unset($_SESSION['google2FACode']);
						}
						break;
					}

					// Token needed - logoff, remember credentials for later logon with logon.php/login.php and load token-page
					$encryptionStore = EncryptionStore::getInstance();

					// store credentials in temporary session, and remove from encryptionStore
					$username = $encryptionStore->get('username');
					$password = $encryptionStore->get('password');

					$encryptionStore->add('google2FAUsername', $username);
					$encryptionStore->add('google2FAPassword', $password);

					$encryptionStore->add('username', '');
					$encryptionStore->add('password', '');

					$_SESSION['google2FASecret'] = Google2FAData::getSecret();
					$_SESSION['google2FAUsedCodes'] = Google2FAData::getUsedCodes();
					$_SESSION['google2FATimelessCodes'] = Google2FAData::getTimelessCodes();
					$_SESSION['google2FAEcho']['boxTitle'] = dgettext('plugin_google2fa', 'Please enter code');
					$_SESSION['google2FAEcho']['txtCodePlaceholder'] = dgettext('plugin_google2fa', 'Code');
					$_SESSION['google2FAEcho']['msgInvalidCode'] = dgettext('plugin_google2fa', 'Invalid code. Please check code.');
					$_SESSION['google2FAEcho']['butLogin'] = dgettext('plugin_google2fa', 'Login');

					header('Location: plugins/google2fa/php/login.php', true, 303); // delete GLOBALS, go to token page
					exit; // don't execute header-function in index.php

				} catch (Exception $e) {
					$mess = $e->getFile() . ":" . $e->getLine() . "<br />" . $e->getMessage();
					error_log("[google2fa]: " . $mess);
                                        die($mess);
				}
                }
	}

	/**
	 * Inject default plugin settings
	 *
	 * @param Array $data Reference to the data of the triggered hook
	 */
	function injectPluginSettings(&$data) {
		$data['settingsObj']->addSysAdminDefaults(Array(
			'zarafa' => Array(
				'v1' => Array(
					'plugins' => Array(
						'google2fa' => Array(
							'enable' => PLUGIN_GOOGLE2FA_ENABLE,
							'user_disable_allowed' => !PLUGIN_GOOGLE2FA_ALWAYS_ENABLED,
							'enable_but_conf' => PLUGIN_GOOGLE2FA_ENBUTCONF,
							'enable_but_activ' => PLUGIN_GOOGLE2FA_ENBUTACTIV,
							'enable_but_tcodes' => PLUGIN_GOOGLE2FA_ENBUTTCODES,
							'enable_but_reset' => PLUGIN_GOOGLE2FA_ENBUTTRESET,
							'secret_key' => '',
							'activate' => PLUGIN_GOOGLE2FA_ACTIVATE,
							'encryption' => '',
							'used_codes' => '',
							'timeless_codes' => ''
						)
					)
				)
			)
		));
	}
}
?>
