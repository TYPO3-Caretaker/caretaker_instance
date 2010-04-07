<?php

/***************************************************************
* Copyright notice
*
* (c) 2009-2010 by n@work GmbH and networkteam GmbH
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
 * Check content of TYPO3_CONF_VARS
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_CheckConfVarsTestService extends tx_caretakerinstance_RemoteTestServiceBase{
	
	/**
	 * Value Description
	 * @var string
	 */
	protected $valueDescription = '';

	/**
	 * Service type description in human readble form.
	 * @var string
	 */
	protected $typeDescription = 'LLL:EXT:caretaker_instance/locallang.xml:typo3_conf_vars_test_description';

	/**
	 * Template to display the test Configuration in human readable form.
	 * @var string
	 */
	protected $configurationInfoTemplate = 'LLL:EXT:caretaker_instance/locallang.xml:typo3_conf_vars_test_configuration';
	

	public function runTest() {
		
		$checkConfVars = explode(chr(10), $this->getConfigValue('checkConfVars'));

		$operations = array();
		foreach ($checkConfVars as $checkConfVar) {
			
			$checkConfVar = trim($checkConfVar);

				// ignore empty and comment lines
			if ( $checkConfVar == '' || strpos($checkConfVar, '#' ) === 0 || strpos($checkConfVar, '//' ) === 0 ){
				continue;
			}
			
				// compare direct =
			if ( strpos( $checkConfVar , '=' ) > 0 ){
				list($path,$value) = explode('=',$checkConfVar);
				$path = trim($path);
				$value = trim($value);
				$regex = false;

					// numeric comparison
				if ( is_numeric( $value ) && intval($value) == $value ) {
					$value = intval($value);
				}
					// regex comparison
				if (preg_match( '/^\/.*\/[a-z]*$/' , $value) ){
					$regex = true;
				}
				
				if ( $path && $value ) {
					$operations[] = array('MatchPredefinedVariable', array(
						'key' => 'GLOBALS|TYPO3_CONF_VARS|' . $path,
						'usingRegexp' => $regex,
						'match' => $value,
					));
				}
			}  
			
		}

		$commandResult = $this->executeRemoteOperations($operations);

		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}

		$results  = $commandResult->getOperationResults();
		
		$sucess   = array();
		$failures = array();

		
		foreach ($results as $key => $operationResult) {
			if ($operationResult->isSuccessful()) {
				$sucess[]   = 'Variable-Path ' . $operations[$key][1]['key'] . ' matched expectation ' .  $operations[$key][1]['match'];
			} else {
				$failures[] = 'Variable-Path ' . $operations[$key][1]['key'] . ' did not match the expectation ' . $operations[$key][1]['match'];
			}
		}

		$msg_failures = '';
		$msg_success  = '';

		if ( count($sucess) ) {
			$msg_success .= chr(10) . 'Success .' . chr(10) . implode( chr(10) , $sucess );
		}
		
		if ( count($failures) ) {
			$msg_failures .= chr(10) . 'Failures .' . chr(10) . implode( chr(10) , $failures );
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, $msg_failures . chr(10) . $msg_success);
		}

		return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, $msg_success );
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_BackendUserTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_BackendUserTestService.php']);
}
?>