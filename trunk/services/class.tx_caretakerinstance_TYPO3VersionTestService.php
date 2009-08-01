<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Christopher Hlubek <hlubek@networkteam.com>
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

class tx_caretakerinstance_TYPO3VersionTestService extends tx_caretakerinstance_RemoteTestServiceBase{
	
	public function runTest() {
		$minVersion = $this->getConfigValue('min_version');
		$maxVersion = $this->getConfigValue('max_version');
		
		if (!$minVersion && !$maxVersion) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_UNDEFINED, 0, 'Cannot execute TYPO3 version test without min and max version');
		}
		
		$operation = array('GetTYPO3Version');
		$operations = array($operation);
		
		$commandResult = $this->executeRemoteOperations($operations);

		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}
		
		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];
		if ($operationResult->isSuccessful()) {
			$version = $operationResult->getValue();
		} else {
			return $this->getFailedOperationResultTestResult($operationResult);
		}
		
		$checkResult = $this->checkVersionRange(
			$version,
			$minVersion,
			$maxVersion);
		if ($checkResult) {
			$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0);
		} else {
			$message = 'TYPO3 version ' . $version . ' installed, but';
			if ($minVersion) {
				$message .= ' >= ' . $minVersion; 
			}
			if ($maxVersion) {
				$message .= ' <= ' . $maxVersion; 
			}
			$message .= ' expected.';
			$testResult = tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, $message);
		}

		return $testResult;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_TYPO3VersionTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_TYPO3VersionTestService.php']);
}
?>