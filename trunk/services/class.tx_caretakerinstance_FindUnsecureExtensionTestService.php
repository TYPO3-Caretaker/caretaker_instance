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

class tx_caretakerinstance_FindUnsecureExtensionTestService extends tx_caretakerinstance_RemoteTestServiceBase{
	
	public function runTest() {
		/*
		$extensionKey = $this->getConfigValue('extension_key');
		$requirementMode = $this->getConfigValue('requirement_mode');
		$minVersion = $this->getConfigValue('min_version');
		$maxVersion = $this->getConfigValue('max_version');

		if (!$extensionKey){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, 'Cannot execute extension test without extension key');
		}
		*/
		$operation = array('GetExtensionList', array());
		$operations = array($operation);

		$commandResult = $this->executeRemoteOperations($operations);

		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}
		
		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];
		debug($operationResult->getValue());
		if ($operationResult->isSuccessful()) {
			$extensionVersion = $operationResult->getValue();
		} else {
			$extensionVersion = FALSE;
		}
		
		$checkResult = $this->checkVersionForRequirementAndVersionRange(
			$extensionVersion,
			$requirementMode,
			$minVersion,
			$maxVersion);
		if ($checkResult) {
			$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0);
		} else {
			$message = 'Extension check for [' . $extensionKey . '] failed';
			$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, $message);
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
		} elseif ($requirement == 'required') {
			if (!$actualValue) {
				return FALSE;
			} else {
				return $this->checkVersionRange($actualValue, $minVersion, $maxVersion);
			}
		} elseif ($requirement == 'forbidden') {
			return !$actualValue;
		} elseif ($requirement == 'evil') {
			// TODO implement check for installed but not loaded extension
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}
?>