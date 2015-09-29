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
 * Check insecure extensions
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_FindInsecureExtensionTestService extends tx_caretakerinstance_RemoteTestServiceBase {

	/**
	 * Value Description
	 * @var string
	 */
	protected $valueDescription = '';

	/**
	 * Service type description in human readble form.
	 * @var string
	 */
	protected $typeDescription = 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_description';

	/**
	 * Template to display the test Configuration in human readable form.
	 * @var string
	 */
	protected $configurationInfoTemplate = 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_configuration';

	/**
	 * Execute the find insecure extension test
	 * @return tx_caretaker_TestResult
	 */
	public function runTest() {
		$location_list = $this->getLocationList();

		$operation = array('GetExtensionList', array('locations' => $location_list));
		$operations = array($operation);

		$commandResult = $this->executeRemoteOperations($operations);
		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}

		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];

		if (!$operationResult->isSuccessful()) {
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'Remote operation failed: ' . $operationResult->getValue());
		}

		$extensionList = $operationResult->getValue();

		$errors = array();
		$warnings = array();
		foreach ($extensionList as $extension) {
			$this->checkExtension($extension, $errors, $warnings);
		}

		// Return error if insecure extensions are installed
		$seperator = chr(10) . ' - ';

		if (count($errors) == 0 && count($warnings) == 0) {
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_ok');
		}

		$num_errors = count($errors);
		$num_warnings = count($warnings);

		$submessages = array();
		$values = array('num_errors' => $num_errors, 'num_warnings' => $num_warnings);

		// add error submessages
		if ($num_errors > 0) {
			$submessages[] = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_errors');
			foreach ($errors as $error) {
				$submessages[] = new tx_caretaker_ResultMessage($error['message'], $error['values']);
			}
		}

		// add warning submessages
		if ($num_warnings > 0) {
			$submessages[] = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_warnings');
			foreach ($warnings as $warning) {
				$submessages[] = new tx_caretaker_ResultMessage($warning['message'], $warning['values']);
			}
		}

		// return error
		if ($num_errors > 0) {
			$value = (count($errors) + count($warnings));
			$message = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_problems', $values);
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, $value, $message, $submessages);
		}

		// return results
		if ($num_warnings > 0) {
			$value = count($warnings);
			$message = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_problems', $values);
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, $value, $message, $submessages);
		}

	}

	/**
	 * @return array
	 */
	public function getLocationList() {
		$locationCode = (int)$this->getConfigValue('check_extension_locations');
		$locationList = array();
		if ($locationCode & 1) $locationList[] = 'system';
		if ($locationCode & 2) $locationList[] = 'global';
		if ($locationCode & 4) $locationList[] = 'local';
		return $locationList;
	}

	/**
	 * @param array $extension
	 * @param array $errors
	 * @param array $warnings
	 */
	public function checkExtension($extension, &$errors, &$warnings) {
		$ext_key = $extension['ext_key'];
		$ext_version = $extension['version'];
		$ext_installed = $extension['installed'];

		if ($this->isExtensionVersionSuffixIgnored()) {
			$ext_version = $this->clearExtensionVersionSuffix($ext_version);
		}

		// Check whitelist
		$ext_whitelist = $this->getCustomExtensionWhitelist();
		if (in_array($ext_key, $ext_whitelist)) {
			return;
		}

		// Check blacklist
		$ext_blacklist = $this->getCustomExtensionBlacklist();
		if (in_array($ext_key, $ext_blacklist)) {
			if ($ext_installed) {
				$errors[] = array('message' => 'LLL:EXT:caretaker_instance/locallang.xml:blacklisted_extension_installed', 'values' => $extension);
			} else {
				$errors[] = array('message' => 'LLL:EXT:caretaker_instance/locallang.xml:blacklisted_extension_present', 'values' => $extension);
			}
			return;
		}

		// Find extension in TER
		$ter_info = $this->getExtensionTerInfos($ext_key, $ext_version);

		// Ext is in TER
		if (is_array($ter_info)) {
			// Ext is reviewed as secure or not reviewed at all
			if ($ter_info['reviewstate'] > -1) {
				return array(0, '');
			}

			// Ext is installed
			if ($ext_installed) {
				$handling = $this->getInstalledExtensionErrorHandling();
				$message = 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_detail_installed';
				switch ($handling) {
					// Warning
					case 1:
						$warnings[] = array('message' => $message, 'values' => $extension);
						return;
					// Error
					case 2:
						$errors[] = array('message' => $message, 'values' => $extension);
						return;
					// Ignore
					default:
						return;
				}
			} // Ext is not installed
			else {
				$handling = $this->getUninstalledExtensionErrorHandling();
				$message = 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_detail_present';
				switch ($handling) {
					// Warning
					case 1:
						$warnings[] = array('message' => $message, 'values' => $extension);
						return;
					// Error
					case 2:
						$errors[] = array('message' => $message, 'values' => $extension);
						return;
					// Ignore
					default:
						return;
				}
			}
		} // Ext is not in TER
		else {

			$handling = $this->getCustomExtensionErrorHandling();
			$message = 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_detail_unknown';

			switch ($handling) {
				// Warning
				case 1:
					$warnings[] = array('message' => $message, 'values' => $extension);
					return;
				// Error
				case 2:
					$errors[] = array('message' => $message, 'values' => $extension);
					return;
				// Ignore
				default:
					return;
			}
		}
	}

	/**
	 * @param string $ext_key
	 * @param string $ext_version
	 * @return array|bool
	 */
	public function getExtensionTerInfos($ext_key, $ext_version) {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$repo = $objectManager->get("TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository");
		$repo->initializeObject();

		$extension = $repo->findOneByExtensionKeyAndVersion($ext_key, $ext_version);

		if ($extension === null || !$extension instanceof \TYPO3\CMS\Extensionmanager\Domain\Model\Extension) {
			return false;
		}

		return array(
				'extkey' => $extension->getExtensionKey(),
				'version' => $extension->getVersion(),
				'reviewstate' => $extension->getReviewState(),
		);
	}

	/**
	 * @return int
	 */
	public function getInstalledExtensionErrorHandling() {
		return (int)$this->getConfigValue('status_of_installed_insecure_extensions');
	}

	/**
	 * @return int
	 */
	public function getUninstalledExtensionErrorHandling() {
		return (int)$this->getConfigValue('status_of_uninstalled_insecure_extensions');
	}

	/**
	 * @return int
	 */
	public function getCustomExtensionErrorHandling() {
		return (int)$this->getConfigValue('status_of_custom_extensions');
	}

	/**
	 * @return array
	 */
	public function getCustomExtensionWhitelist() {
		return explode(chr(10), $this->getConfigValue('custom_extkey_whitlelist'));
	}

	/**
	 * @return array
	 */
	public function getCustomExtensionBlacklist() {
		return explode(chr(10), $this->getConfigValue('custom_extkey_blacklist'));
	}

	/**
	 * @return bool
	 */
	protected function isExtensionVersionSuffixIgnored() {
		return $this->getConfigValue('ignore_extension_version_suffix') == 1;
	}

	/**
	 * @param $extensionVersion
	 * @return mixed
	 */
	protected function clearExtensionVersionSuffix($extensionVersion) {
		if (preg_match('/^([0-9]+\.[0-9]+\.[0-9]+)/', $extensionVersion, $matches)) {
			return $matches[1];
		}
		// If not matched, return given version
		return $extensionVersion;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}