<?php

/* * *************************************************************
 * Copyright notice
 *
 * (c) 2013 by @netimage and @tomasnorre
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
 * ************************************************************* */

/**
 * This is a file of the caretaker project.
 * http://forge.typo3.org/projects/show/extension-caretaker
 *
 * Project sponsored by:
 * n@work GmbH - http://www.work.de
 * networkteam GmbH - http://www.networkteam.com/
 * netimage - http://www.netimage.dk
 *
 * $Id$
 */
require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

/**
 * Check if the disk space is sufficient.
 *
 * @author Tomas Norre Mikkelsen <tnm@netimage.dk>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_DiskSpaceTestService extends tx_caretakerinstance_RemoteTestServiceBase {

	// default warning and error percent
	protected $stateDefaultWarning = '80'; // In percent
	protected $stateDefaultError = '90'; // In percent

	public function runTest() {
		
		$warningState = array(
			'WARNING' => $this->getConfigValue('warning_percent'),
			'ERROR' => $this->getConfigValue('error_percent')
		);

		// Configuration for diskusage for warning and error
		$warningState['WARNING'] = !empty($warningState['WARNING']) ? $warningState['WARNING'] : $this->stateDefaultWarning;
		$warningState['ERROR'] = !empty($warningState['ERROR']) ? $warningState['ERROR'] : $this->stateDefaultError;

		$operations[] = array('GetDiskUsage', array());
		$commandResult = $this->executeRemoteOperations($operations);
		
		// catch errors
		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}
		// process resultset
		$resultset = $commandResult->getOperationResults();
		$result = reset($resultset);
		$diskSpacePercent = $result->getValue();
		
        if ($diskSpacePercent < $warningState['WARNING']) {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, 'Everything is ok! ' . $diskSpacePercent . '% of the disk is used, a warning will be triggered at ' . $warningState['WARNING'] . '% disk usage');
        } elseif ($diskSpacePercent < $warningState['ERROR']) {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, 0, $diskSpacePercent . '% of the disk is used, maybe you should clean it up. An error will be triggered at ' . $warningState['ERROR'] . '% disk usage');
        } else {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'Not enough diskspace, ' . $diskSpacePercent . '% is used. Please cleanup the server or by additional space. This error is triggered because the webhotel use ' . $warningState['ERROR'] . '% or more disk space');
        }
		
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_DiskSpaceTestService.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_DiskSpaceTestService.php']);
}
?>
