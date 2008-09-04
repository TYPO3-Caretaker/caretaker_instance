<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationManager.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_CommandService.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_SecurityManager.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_CryptoManager.php'));

class tx_caretakerinstance_ServiceFactory {
	
	protected static $instance;
	
	public static function getInstance() {
		if(tx_caretakerinstance_ServiceFactory::$instance == null) {
			tx_caretakerinstance_ServiceFactory::$instance = new tx_caretakerinstance_ServiceFactory();

		}
		return tx_caretakerinstance_ServiceFactory::$instance;
	}
	
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker_instance']);
	}
	
	public function getCommandService() {
		if($this->commandService == null) {
			$this->commandService = new tx_caretakerinstance_CommandService(
				$this->getOperationManager(),
				$this->getSecurityManager()
			);
		}
		return $this->commandService;
	}
	
	public function getSecurityManager() {
		if($this->securityManager == null) {
			$this->securityManager = new tx_caretakerinstance_SecurityManager(
				$this->getCryptoManager()
			);
			$this->securityManager->setPublicKey($this->extConf['crypto.']['instance.']['publicKey']);
			$this->securityManager->setPrivateKey($this->extConf['crypto.']['instance.']['privateKey']);
			$this->securityManager->setClientPublicKey($this->extConf['crypto.']['client.']['publicKey']);
			$this->securityManager->setClientHostAddressRestriction($this->extConf['security.']['clientHostAddressRestriction']);
		}
		return $this->securityManager;
	}
	
	public function getOperationManager() {
		if($this->operationManager == null) {
			$this->operationManager = new tx_caretakerinstance_OperationManager();
			// TODO register additional operations
		}
		return $this->operationManager;
	}
	
	public function getCryptoManager() {
		if($this->cryptoManager == null) {
			$this->cryptoManager = new tx_caretakerinstance_CryptoManager();
		}
		return $this->cryptoManager;
	}
}
?>