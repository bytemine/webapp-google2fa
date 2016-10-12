<?php

require "external/GoogleAuthenticator/GoogleAuthenticator.php";

/**
 * WebApp plugin module for interaction with JS-GUI
 *
 * @class Google2FAModule
 * @extends Module
 * @author Norman Thimm
 * @copyright 2015 Norman Thimm
 * @license http://www.gnu.org/licenses/ GNU Affero General Public License
 * @link http://www.familiethimm.de/
 */
class Google2FAModule extends Module {

	/**
	 * instance of external Google Authenticator class
	 *
	 * @var $ga instance of external Google Authenticator class
	 */
	var $ga;

	/**
	 * @constructor
         * @access public
	 * @param int $id unique id of the class
	 * @param array $data list of all actions, which is received from the client
	 */
	public function __construct($id, $data) {
		# in Kopano WebApp the construct method is used explicitely
		if(is_callable('parent::__construct')) {
			parent::__construct($id, $data);
		} else {
			parent::Module($id, $data);	
		}
		$this->ga = new PHPGangsta_GoogleAuthenticator();
	}

	/**
	 * Executes all the actions in the $data variable.
	 *
         * @access public
	 * @return boolean true on success or false on failure.
	 */
	public function execute() {
		$result = false;
		foreach($this->data as $actionType => $actionData) {
			if(isset($actionType)) {
				try {
					switch($actionType) {
						case "resetconfiguration":
							$result = $this->resetConfiguration();
							break;
						case "getsecret":
							$result = $this->getSecret();
							break;
						case "activate":
							$result = $this->activate();
							break;
						case "isactivated":
							$result = $this->isActivated();
							break;
						case "verifycode":
							$result = $this->verifyCode($actionData);
							break;
						case "gettimelesscodes":
							$result = $this->getTimelessCodes($actionData);
							break;
						default:
							$this->handleUnknownActionType($actionType);
					}
				} catch (Exception $e) {
					$mess = $e->getFile() . ":" . $e->getLine() . "<br />" . $e->getMessage();
					error_log("[google2fa]: " . $mess);
					$this->sendFeedback(false, array(
						'type' => ERROR_GENERAL,
						'info' => array('original_message' => $mess, 'display_message' => $mess)
		              		));
				}
			}
		}
		return $result;
	}

	/**
	 * Reset configuration
	 *
         * @access private
	 * @return boolean
	 */
	private function resetConfiguration() {
		Google2FAData::setActivate(false);
		Google2FAData::setCryption("");
		Google2FAData::setSecret("");
		Google2FAData::setTimelessCodes(array());
		Google2FAData::delUsedCodes();
                $response['isActivated'] = false;
		$this->addActionData("resetconfiguration", $response);
		$GLOBALS["bus"]->addData($this->getResponseData());
                return true;
	}

        /**
         * Toggle activate/deactivate two-factor authentication
         *
         * @access private
         * @return boolean
         */
	private function activate() {
		$isActivated = Google2FAData::isActivated();
		Google2FAData::setActivate(!$isActivated);
		$response = array();
		$response['isActivated'] = !$isActivated;
		$this->addActionData("activate", $response);
                $GLOBALS["bus"]->addData($this->getResponseData());
		return true;
	}

        /**
         * Send if two-factor authentication is activated
         *
         * @access private
         * @return boolean
         */
	private function isActivated() {
		$isActivated = Google2FAData::isActivated();
		$response = array();
		$response['isActivated'] = $isActivated;
		$this->addActionData("isactivated", $response);
		$GLOBALS["bus"]->addData($this->getResponseData());
		return true;
        }

        /**
         * Verify code
         *
         * @access private
         * @return boolean
         */
	private function verifyCode($actionData) {
		$code = $actionData['code'];
		$secret = Google2FAData::getSecret();
		$isCodeOK = !in_array($code, Google2FAData::getUsedCodes()) && $this->ga->verifyCode($secret, $code, 1);
		if($isCodeOK)
			Google2FAData::addUsedCode($code);
		$response['isCodeOK'] = $isCodeOK;
		$this->addActionData("verifycode", $response);
		$GLOBALS["bus"]->addData($this->getResponseData());
		return true;
	}

        /**
         * Send timeless codes
         *
         * @access private
         * @return boolean
         */
	private function getTimelessCodes($actionData) {
		$generate = $actionData['generate'];
		$codes = Google2FAData::getTimelessCodes();
		if ($generate || count($codes) === 0 || $codes[0] === "") {
			$codes = array();
			for ($i=0; $i<PLUGIN_GOOGLE2FA_TCODES; $i++)
				array_push($codes, mt_rand(100000, 999999));
			Google2FAData::setTimelessCodes($codes);
		}
		foreach ($codes as &$code)
			$code = base64_encode($code);
		$response = array();
		$response['codes'] = $codes;
		$this->addActionData("gettimelesscodes", $response);
		$GLOBALS["bus"]->addData($this->getResponseData());
		return true;
	}

	/**
	 * Send secret key
	 *
	 * @access private
	 * @return boolean
	 */
	private function getSecret() {
		$secret = Google2FAData::getSecret();
		$encryptionStore = EncryptionStore::getInstance();
		$user = $encryptionStore->get('username');
		$response = array();
		if ($secret === "")
			$secret = $this->createSecret();
		if (PLUGIN_GOOGLE2FA_GENERATEQR === true) {
			session_start();
			$_SESSION['PLUGIN_GOOGLE2FA_SECRET'] = base64_encode($secret); // for qr.php - no transfer to client
                       $response['qRCodeGoogleUrl'] = base64_encode("plugins/google2fa/php/qr.php");
		} else
			$response['qRCodeGoogleUrl'] = base64_encode($this->ga->getQRCodeGoogleUrl($user . "@" . PLUGIN_GOOGLE2FA_APPNAME, $secret, PLUGIN_GOOGLE2FA_APPNAME));
		$response['secret'] = base64_encode($secret);
		$response['application'] = PLUGIN_GOOGLE2FA_APPNAME;
		$response['username'] = $user;
		$this->addActionData("getsecret", $response);
		$GLOBALS["bus"]->addData($this->getResponseData());
		return true;
	}

        /**
         * Create and save new secret key
         *
	 * @access private
         * @return string
         */
	private function createSecret() {
		$secret = $this->ga->createSecret();
		Google2FAData::setSecret($secret);
		return $secret;
	}
}

?>
