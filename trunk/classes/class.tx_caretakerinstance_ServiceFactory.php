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
	
	/**
	 * @return tx_caretakerinstance_CommandService
	 */
	public function getCommandService() {
		if($this->commandService == null) {
			$this->commandService = new tx_caretakerinstance_CommandService(
				$this->getOperationManager(),
				$this->getSecurityManager()
			);
		}
		return $this->commandService;
	}

	/**
	 * @return tx_caretakerinstance_SecurityManager
	 */
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
	
	/**
	 * @return tx_caretakerinstance_OperationManager
	 */
	public function getOperationManager() {
		if($this->operationManager == null) {
			$this->operationManager = new tx_caretakerinstance_OperationManager();
			
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'] as $key => $operationRef) {
					if(is_string($operationRef)) {
						$operation = t3lib_div::getUserObj($operationRef);
					} elseif ($operationRef instanceof tx_caretakerinstance_IOperation) {
						$operation = $operationRef;
					} else {
						// TODO log error if some strange value is registered
					}
					$this->operationManager->registerOperation($key, $operation);
				}
			}
		}
		return $this->operationManager;
	}

	/**
	 * @return tx_caretakerinstance_CryptoManager
	 */
	public function getCryptoManager() {
		if($this->cryptoManager == null) {
			$this->cryptoManager = new tx_caretakerinstance_CryptoManager();
		}
		return $this->cryptoManager;
	}
	
	public function destroy() {
		self::$instance = null;
	}
}
?>