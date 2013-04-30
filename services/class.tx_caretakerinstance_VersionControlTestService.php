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
class tx_caretakerinstance_VersionControlTestService extends tx_caretakerinstance_RemoteTestServiceBase {

 
    public function runTest() {

        switch($this->getConfigValue('versionControlSystem')){
            
            case 0: // Subversion (svn)
                $results = $this->getSubversionStatus();
                break;
            default: // Subversion (svn)
                $results = $this->getSubversionStatus();
                break;
        }
        
        
        if($results === FALSE) {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'Version control system - svn is not compiled into php');
        } elseif ($results) {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'There is ' . $results . ' File(s) which is/are not up2date');
        } else {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, 'Everything is ok!');
        }
    }

    
    /**
     * Get subversion status of a DOCUMENT_ROOT.
     * 
     * @return array if svn_status()-function is available otherwise FALSE; 
     */
    private function getSubversionStatus() {

        $path = $_SERVER['DOCUMENT_ROOT'];

        // check if php is compiled with subversion support. 
        if (function_exists('svn_status')) {
            return sizeof(svn_status($path));
        }
       
        return FALSE;
    }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_VersionControlTestService.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_VersionControlTestService.php']);
}
?>
