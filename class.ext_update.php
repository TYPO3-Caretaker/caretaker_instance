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

if (t3lib_extMgm::isLoaded('caretaker_instance')) {
	require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_ServiceFactory.php'));
}

/**
 * Extension manager update class to generate public / private key pairs.
 *
 * @author		Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class ext_update {

	/**
	 * @var tx_caretakerinstance_ServiceFactory
	 */
	protected $factory;

	/**
	 * @return boolean Whether the update should be shown / allowed
	 */
	public function access() {

		$extConf = $this->getExtConf();

		$show = !strlen($extConf['crypto.']['instance.']['publicKey']) ||
			!strlen($extConf['crypto.']['instance.']['privateKey']);
		return $show;
	}

	/**
	 * Return the update process HTML content
	 *
	 * @return string
	 */
	public function main() {
		$extConf = $this->getExtConf();

		$this->factory = tx_caretakerinstance_ServiceFactory::getInstance();
		try {
			list($publicKey, $privateKey) = $this->factory->getCryptoManager()->generateKeyPair();
			$extConf['crypto.']['instance.']['publicKey'] = $publicKey;
			$extConf['crypto.']['instance.']['privateKey'] = $privateKey;
			$typo3Version = explode('.', TYPO3_version);
			$majorVersion = intval($typo3Version[0]);
			if ($majorVersion >= 6) {
				$this->writeExtensionConfiguration($extConf);
			} else {
				$this->writeExtConf($extConf);
			}
			$content = "Success: Generated public / private key";
		} catch(Exception $exception) {
			$content = 'Error: ' . $exception->getMessage();
		}

		return $content;
	}

	/**
	 * Write back configuration
	 *
	 * @param array $extConf
	 * @return void
	 */
	protected function writeExtConf($extConf) {
		$install = new t3lib_install();
		$install->allowUpdateLocalConf = 1;
		$install->updateIdentity = 'Caretaker Instance installation';

		$lines = $install->writeToLocalconf_control();
		$install->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extConf\'][\'caretaker_instance\']', serialize($extConf));
		$install->writeToLocalconf_control($lines);

		t3lib_extMgm::removeCacheFiles();
	}

	/**
	 * Writes the extension's configuration (version for TYPO3 CMS 6.0+)
	 *
	 * @param $extensionConfiguration
	 * @return void
	 */
	protected function writeExtensionConfiguration($extensionConfiguration) {
		/** @var $configurationManager \TYPO3\CMS\Core\Configuration\ConfigurationManager */
		$configurationManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
		$configurationManager->setLocalConfigurationValueByPath('EXT/extConf/caretaker_instance', serialize($extensionConfiguration));
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::removeCacheFiles();
	}

	/**
	 * Get the extension configuration
	 *
	 * @return array
	 */
	protected function getExtConf() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker_instance']);
		if (!$extConf) {
			$extConf = array();
		}
		return $extConf;
	}

}
?>