<?php
/**
 * This is a file of the caretaker project.
 * Copyright 2008 by n@work Internet Informationssystem GmbH (www.work.de)
 * 
 * @Author	Thomas Hempel 		<thomas@work.de>
 * @Author	Martin Ficzel		<martin@work.de>
 * @Author	Patrick Kollodzik	<patrick@work.de>
 * 
 * $$Id: class.tx_caretaker_typo3_extensions.php 33 2008-06-13 14:00:38Z thomas $$
 */

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Patrick Kollodzik <patrick@work.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

class tx_caretakerinstance_ExtensionTestService extends tx_caretakerinstance_RemoteTestServiceBase{
	
	public function runTest() {
		
		$extensionKey = $this->getConfigValue('extension_key');
		$requirementMode = $this->getConfigValue('requirement_mode');
		
		if (!$extensionKey){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, 'Cannot execute extension test without extension key');
		}
		
		$operation = array('GetExtensionVersion', array('extensionKey' => $extensionKey));

		$instanceUrl = $this->instance->getUrl();
		$instancePublicKey = $this->instance->getPublicKey();

		// TODO execute operation on instance
		// $commandResult = ...
		
		if (!$commandResult->isSuccessful()) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, 'Command execution failed: ' . $commandResult->getMessage());
		}
		
		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];
		// Status can be true or false
		$status = $operationResult->getStatus();
		// The value is the extension version if status is true
		if ($status === TRUE) {
			$extensionVersion = $operationResult->getValue();
		} else {
			$extensionVersion = '';
		}
		
		$checkResult = $this->checkVersionForRequirementAndVersionRange(
			$extensionVersion,
			$requirementMode,
			$minVersion,
			$maxVersion);
		if ($checkResult === TRUE) {
			$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0);
		} else {
			$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'Extension check for [' . $extensionKey . '] failed: ' . $checkResult);
		}

		return $testResult;
	}
	
	public function checkVersionForRequirementAndVersionRange($actualValue, $requirement, $minVersion, $maxVersion) {
		if ($requirement == 'none') {
			if ($actualValue) {
				return $this->checkVersionRange($actualValue, $minVersion, $maxVersion);
			} else {
				return TRUE;
			}
		}
	}
	
	protected function checkVersionRange($actualVersion, $minVersion, $maxVersion) {
		if ($minVersion == '') {
			$minVersion = '0.0.0';
		}
		if ($maxVersion == '') {
			$maxVersion = '9999.9999.9999';
		}
		list($actualMajor, $actualMinor, $actualRelease) = explode('.', $actualVersion);
		list($minMajor, $minMinor, $minRelease) = explode('.', $minVersion);
		list($maxMajor, $maxMinor, $maxRelease) = explode('.', $maxVersion);
		/*
		$actualMajor = intval($actualMajor);
		$actualMinor = intval($actualMinor);
		$actualRelease = intval($actualRelease);
		$minMajor = intval($minMajor);
		$minMinor = intval($minMinor);
		$minRelease = intval($minRelease);
		$maxMajor = intval($maxMajor);
		$maxMinor = intval($maxMinor);
		$maxRelease = intval($maxRelease);
		*/
		
		$b1 = $actualMajor >= $minMajor && $actualMajor <= $maxMajor;
		$b2 = $actualMinor >= $minMinor && $actualMinor <= $maxMinor;
		$b3 = $actualRelease >= $minRelease && $actualRelease <= $maxRelease;
		var_dump($b1, $b2, $b3);
		return $b1 && $b2 && $b3;
	}	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}
?>