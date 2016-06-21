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
class tx_caretakerinstance_FindExtensionUpdatesTestService extends tx_caretakerinstance_RemoteTestServiceBase {

	/**
	 * Value Description
	 * @var string
	 */
	protected $valueDescription = '';

	/**
	 * Service type description in human readable form.
	 * @var string
	 */
	protected $typeDescription = 'LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_description';

	/**
	 * Template to display the test Configuration in human readable form.
	 * @var string
	 */
	protected $configurationInfoTemplate = 'LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_configuration';

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
		$oks = array();
		foreach ($extensionList as $extension) {
			$this->checkExtension($extension, $errors, $warnings, $oks);
		}

		// Return error if insecure extensions are installed

		$num_errors = count($errors);
		$num_warnings = count($warnings);
		$num_oks = count($oks);

		$submessages = array();
		$values = array('num_errors' => $num_errors, 'num_warnings' => $num_warnings);

		// add error submessages
		if ($num_errors > 0) {
			$submessages[] = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_detail_error');
			foreach ($errors as $error) {
				$submessages[] = new tx_caretaker_ResultMessage($error['message'], $error['values']);
			}
		}

		// add warning submessages
		if ($num_warnings > 0) {
			$submessages[] = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_detail_warning');
			foreach ($warnings as $warning) {
				$submessages[] = new tx_caretaker_ResultMessage($warning['message'], $warning['values']);
			}
		}

		// add ok submessages
		if ($num_oks > 0) {
			$submessages[] = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_detail_ok');
			foreach ($oks as $ok) {
				$submessages[] = new tx_caretaker_ResultMessage($ok['message'], $ok['values']);
			}
		}

		// return error
		if ($num_errors > 0) {
			$value = (count($errors) + count($warnings));
			$message = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_problems', $values);
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, $value, $message, $submessages);
		}

		// return warning
		if ($num_warnings > 0) {
			$value = count($warnings);
			$message = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_problems', $values);
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, $value, $message, $submessages);
		}

		// return ok
		$value = 0;
		$message = new tx_caretaker_ResultMessage('LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_ok', $values);
		return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, $value, $message, $submessages);
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
	 * @param array $oks
	 */
	public function checkExtension($extension, &$errors, &$warnings, &$oks) {
		$ext_key = $extension['ext_key'];
		$ext_version = $extension['version'];
		$ext_installed = $extension['installed'];

		if (!$ext_installed) return;

		if ($this->isExtensionVersionSuffixIgnored()) {
			$ext_version = $this->clearExtensionVersionSuffix($ext_version);
		}

		// Find extension in TER
		$ter_info = $this->getLatestExtensionTerInfos($ext_key, $ext_version);

		// Ext is in TER
		if ($ter_info) {

			$message = 'LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_detailinfo';
			$value = array(
					'ext_key' => $extension['ext_key'],
					'ext_version' => $extension['version'],
					'ter_version' => $ter_info['version']
			);

			if ($this->checkVersionRange($ext_version, $ter_info['version'], '')) {
				$oks[] = array('message' => $message, 'values' => $value);
				return;
			} else {
				// Check whitelist
				$ext_whitelist = $this->getCustomExtensionWhitelist();
				if (in_array($ext_key, $ext_whitelist)) {
					$oks[] = array('message' => $message, 'values' => $value);
					return;
				}
				// handle error
				$handling = $this->getStatusOfUpdatableExtensions();
				switch ($handling) {
					// Warning
					case 1:
						$warnings[] = array('message' => $message, 'values' => $value);
						return;
					// Error
					case 2:
						$errors[] = array('message' => $message, 'values' => $value);
						return;
					// OK
					default:
						$oks[] = array('message' => $message, 'values' => $value);
						return;
				}
			};

		} else {
			$value = array(
					'ext_key' => $extension['ext_key'],
					'ext_version' => $extension['version'],
					'ter_version' => 'unknown'
			);
			$message = 'LLL:EXT:caretaker_instance/locallang.xml:find_extension_updates_test_detailinfo';
			$oks[] = array('message' => $message, 'values' => $value);
		}
	}

	/**
	 * @param string $ext_key
	 * @param string $ext_version
	 * @return bool
	 */
	public function getLatestExtensionTerInfos($ext_key, $ext_version) {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		/** @var TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository $repo */
		$repo = $objectManager->get("TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\ExtensionRepository");
		$repo->initializeObject();

		if ($this->isTYPO3VersionIgnored()) {
			// get last version
			$extension = $repo->findHighestAvailableVersion($ext_key);
		} else {
			// get all versions of the extension
			$extensionAllVersions = $repo->findByExtensionKeyOrderedByVersion($ext_key)->toArray();

			// find last highest version for running TYPO3 version
			/** @var TYPO3\CMS\Extensionmanager\Domain\Model\Extension $extension */
			foreach ($extensionAllVersions as $extensionVersion) {
				/** @var TYPO3\CMS\Extensionmanager\Domain\Model\Dependency $dependency */
				foreach ($extensionVersion->getDependencies() as $dependency) {
					if ($dependency->getIdentifier() == 'typo3'
						&& $this->checkVersionRange(TYPO3_version, $dependency->getLowestVersion(), $dependency->getHighestVersion())
					) {
						$extension = $extensionVersion;
						break 2;
					}
				}
			}
		}

		if ($extension === null || !$extension instanceof \TYPO3\CMS\Extensionmanager\Domain\Model\Extension) {
			return false;
		}

		$ext_infos = array(array(
				'extkey' => $extension->getExtensionKey(),
				'version' => $extension->getVersion(),
		));

		if (!is_array($ext_infos)) {
			return false;
		}

		$result = false;
		$latestVersion = null;
		foreach ($ext_infos as $ext_info) {
			if ($latestVersion === null
					|| version_compare($ext_info['version'], $latestVersion, '>')
			) {
				$latestVersion = $ext_info['version'];
				$result = $ext_info;
			}
		}
		return $result;
	}

	/***
	 * @return int
	 */
	public function getStatusOfUpdatableExtensions() {
		return (int)$this->getConfigValue('status_of_updateable_extensions');
	}

	/**
	 * @return array
	 */
	public function getCustomExtensionWhitelist() {
		return explode(chr(10), $this->getConfigValue('custom_extkey_whitlelist'));
	}

	/**
	 * @return bool
	 */
	protected function isExtensionVersionSuffixIgnored() {
		return $this->getConfigValue('ignore_extension_version_suffix') == 1;
	}

	/**
	 * @return bool
	 */
	protected function isTYPO3VersionIgnored() {
		return $this->getConfigValue('only_for_running_typo3_version') != 1;
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

	/**
	 * Check if the given version is within the minimum and maximum version
	 *
	 * @param string $actualVersion Version to compare to min and max
	 * @param string $minVersion Minimum version that is required.
	 *                              May be empty.
	 * @param string $maxVersion Maximum version that is required.
	 *                              May be empty.
	 *
	 * @return boolean TRUE if the actual version is within min and max.
	 */
	public function checkVersionRange($actualVersion, $minVersion, $maxVersion) {
		if ($minVersion != '') {
			if (!version_compare($actualVersion, $minVersion, '>=')) {
				return FALSE;
			}
		}
		if ($maxVersion != '') {
			if (!version_compare($actualVersion, $maxVersion, '<=')) {
				return FALSE;
			}
		}

		return TRUE;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}
