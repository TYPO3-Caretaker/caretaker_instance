<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
 *
 * All rights reserved
 *
 * This script is part of the Caretaker project. The Caretaker project
 * is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This is a file of the caretaker project.
 * http://forge.typo3.org/projects/show/extension-caretaker
 *
 * Project sponsored by:
 * n@work GmbH - http://www.work.de
 * networkteam GmbH - http://www.networkteam.com/
 *
 * $Id$
 */

/**
 * Singleton factory as a dependency injection container
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_ServiceFactory {

	/**
	 * @var tx_caretakerinstance_ServiceFactory
	 */
	protected static $instance;

	/**
	 * @var tx_caretakerinstance_CommandService
	 */
	protected $commandService;


	/**
	 * @var tx_caretakerinstance_SecurityManager
	 */
	protected $securityManager;

	/**
	 * @var tx_caretakerinstance_OperationManager
	 */
	protected $operationManager;

	/**
	 * @var tx_caretakerinstance_OpenSSLCryptoManager
	 */
	protected $cryptoManager;

	/**
	 * @var tx_caretakerinstance_RemoteCommandConnector
	 */
	protected $remoteCommandConnector;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker_instance']);
	}

	/**
	 * @static
	 * @return tx_caretakerinstance_ServiceFactory
	 */
	public static function getInstance() {
		if (tx_caretakerinstance_ServiceFactory::$instance == null) {
			tx_caretakerinstance_ServiceFactory::$instance = new tx_caretakerinstance_ServiceFactory();

		}
		return tx_caretakerinstance_ServiceFactory::$instance;
	}

	/**
	 * @return tx_caretakerinstance_CommandService
	 */
	public function getCommandService() {
		if ($this->commandService == null) {
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
		if ($this->securityManager == null) {
			$this->securityManager = new tx_caretakerinstance_SecurityManager($this->getCryptoManager());
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
		if ($this->operationManager == null) {
			$this->operationManager = new tx_caretakerinstance_OperationManager();

			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'] as $key => $operationRef) {
					if (is_string($operationRef)) {
						$operation = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($operationRef);
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
	 * @return tx_caretakerinstance_OpenSSLCryptoManager
	 */
	public function getCryptoManager() {
		if ($this->cryptoManager == null) {
			$this->cryptoManager = new tx_caretakerinstance_OpenSSLCryptoManager();
		}
		return $this->cryptoManager;
	}

	/**
	 * @return tx_caretakerinstance_RemoteCommandConnector
	 */
	public function getRemoteCommandConnector() {
		if ($this->remoteCommandConnector == null) {
			$this->remoteCommandConnector = new tx_caretakerinstance_RemoteCommandConnector(
					$this->getCryptoManager(),
					$this->getSecurityManager()
			);
		}
		return $this->remoteCommandConnector;
	}

	/**
	 * Destroy the factory instance
	 */
	public function destroy() {
		self::$instance = null;
	}

}