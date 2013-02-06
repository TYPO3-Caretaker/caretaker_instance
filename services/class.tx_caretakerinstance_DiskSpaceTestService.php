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
 * Check if the versioning control system is up to date.
 *
 * @author Tomas Norre Mikkelsen <tnm@netimage.dk>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_DiskSpaceTestService extends tx_caretakerinstance_RemoteTestServiceBase {

	protected $stateWarning = '80' // In procent
	protected $stateError	= '90' // In procent

 
    public function runTest() {

		// Configuration for diskusage for warning and error
		$warningState['WARNING']	= isset($this->getConfigValue('state_warning') ? isset($this->getConfigValue('state_warning') : $this->stateWarning;
		$warningState['ERROR'] 		= isset($this->getConfigValue('state_error') ? isset($this->getConfigValue('state_error') : $this->stateError;

		$diskSpaceProcent = $this->diskSpaceInProcent();
        
        if($diskSpaceProcent < $warningState['WARNING']){
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, 'Everything is ok!'.$diskSpaceProcent.'% of the disk is used');
		} elseif($diskSpaceProcent < $warningState['ERROR']){
			return 	tx_caretaker_TestResult::create(tx_caretaker_Constants::state_warning, 0, $diskSpaceProcent'% of the disk is used, maybe you should clean it up.');
		} else {
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'Not enough diskspace, '.$diskSpaceProcent.'% is used. Please cleanup the server or by additional space.');
		}
        
        
    }

    
    /**
     * Get used diskspace.
     * 
     * @return int of used diskspace in procentage
     */
    private function diskSpaceInProcent() {
		
		$diskSize = disk_total_space(getcwd());
		$diskUsed = disk_free_space(getcwd());

		$remain = 100 - ($diskUsed / $diskSize * 100);

		return (int) $remain;
        
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_VersionControlTestService.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_VersionControlTestService.php']);
}
?>
