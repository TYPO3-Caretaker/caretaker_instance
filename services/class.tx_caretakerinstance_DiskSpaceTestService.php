<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2015 by Tobias Liebig <tobias.liebig@typo3.org>
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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

/**
 * Check for TYPO3 version
 *
 * @author Tobias Liebig <tobias.liebig@typo3.org>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_DiskSpaceTestService extends tx_caretakerinstance_RemoteTestServiceBase{

	public function runTest() {
		$path = $this->getConfigValue('path');

		$operation = array('GetDiskSpace', array('path' => $path));
		$operations = array($operation);
		$commandResult = $this->executeRemoteOperations($operations);

		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}

		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];
		if ($operationResult->isSuccessful()) {
			$diskSpace = $operationResult->getValue();
		} else {
			return $this->getFailedOperationResultTestResult($operationResult);
		}

		$minFreeAbsolute = $this->getMinFreeAbsolute(
				$this->getConfigValue('min_free'),
				$this->getConfigValue('min_free_unit'),
				$diskSpace
		);

		$info = '(' .
			'free: ' . $this->humanFilesize($diskSpace['free']) .
			' ; total: ' . $this->humanFilesize($diskSpace['total']) .
			(
				$minFreeAbsolute > 0 ? (
					' ; expected free: ' . $this->getConfigValue('min_free') .
					$this->getConfigValue('min_free_unit')
				) : ''
			) . ')';

		if (!empty($minFreeAbsolute)) {
			if ($diskSpace['free'] <= $minFreeAbsolute) {
				$message = 'Not enough free disk space ' . $info;
				return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, $message);
			}
		}

		$message = 'Disk space test successful ' . $info;

		return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, $message);
	}

	/**
	 * @param int $bytes
	 * @param int $dec
	 * @return string
	 */
	protected function humanFilesize($bytes, $dec = 2) {
		$size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$factor = (int)floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}

	/**
	 * @param int $value
	 * @param string $unit
	 * @return int
	 */
	protected function getMinFreeAbsolute($value, $unit, $diskSpace) {
		if (empty($value)) {
			return 0;
		}
		if ($unit === '%') {
			return (double)(ceil($diskSpace['total'] / 100 * $value));
		}
		$factor = array_search($unit, array('b', 'kB', 'MB', 'GB', 'TB'));
		return $value * (pow(1024, $factor));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_DiskSpaceTestService.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_DiskSpaceTestService.php']);
}
