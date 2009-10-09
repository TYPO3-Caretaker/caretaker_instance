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
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, 0, 'Remote operation failed: ' . $operationResult->getValue());
		} 

		$extensionList = $operationResult->getValue();

		$errors =  array();
		$warnings = array();
		foreach ($extensionList as $extension) {
			$this->checkExtension($extension, $errors, $warnings);
		}

		// Return error if insecure extensions are installed
		$seperator = chr(10). ' - ';

		if (count($errors) == 0 && count($warnings) == 0){
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_OK, 0, 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_ok');
		}

		$num_errors   = count($errors);
		$num_warnings = count($warnings);

		if ( $num_errors > 0 )    array_unshift($errors,   'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_errors');
		if ( $num_warnings > 0 )  array_unshift($warnings, 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_warnings');

		$info_array = array(
			'values' => array('num_errors' => $num_errors, 'num_warnings' => $num_warnings ),
			'details' => array_merge($errors,$warnings)
		);
		
		if ( $num_errors > 0 ) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_ERROR, (count($errors) + count($warnings)), 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_problems' , $info_array );
		}

		if ( $num_warnings > 0 ) {
			return tx_caretaker_TestResult::create(TX_CARETAKER_STATE_WARNING, count($warnings), 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_test_problems' , $info_array );
		}
		
	}
	
	
	public function getLocationList(){
		$locationCode = (int)$this->getConfigValue('check_extension_locations');
		$locationList = array();
		if ($locationCode & 1) $locationList[] = 'system';
		if ($locationCode & 2) $locationList[] = 'global';
		if ($locationCode & 4) $locationList[] = 'local';
		return $locationList;
	}
	 
	public function checkExtension($extension, &$errors, &$warnings) {
		$ext_key = $extension['ext_key'];
		$ext_version = $extension['version'];
		$ext_installed = $extension['installed']; 

		// Check whitelist
		$ext_whitelist = $this->getCustomExtensionWhitelist();
		if (in_array($ext_key, $ext_whitelist)) {
			return;
		}

		// Find extension in TER
		$ter_info = $this->getExtensionTerInfos($ext_key, $ext_version);
		
		// Ext is in TER
		if ($ter_info) {
			// Ext is reviewed as secure or not reviewed at all
			if ($ter_info['reviewstate'] > -1) {
				return array(0, '');
			}
			
			// Ext is installed	
			if ($ext_installed) {
				$handling = $this->getInstalledExtensionErrorHandling();
				$message  =   'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_detail_installed';
				switch ($handling) {
					// Warning
					case 1: 
						$warnings[] = array( 'message'=>$message , 'values'=>$extension );
						return;
					// Error
					case 2: 
						$errors[] = array( 'message'=>$message , 'values'=>$extension );
						return;
					// Ignore
					default: return;
				}
			}
			// Ext is not installed
			else {
				$handling = $this->getUninstalledExtensionErrorHandling();
				$message  =   'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_detail_present';
				switch ($handling) {
					// Warning
					case 1: 
						$warnings[] = array( 'message'=>$message , 'values'=>$extension );
						return;
					// Error
					case 2: 
						$errors[] = array( 'message'=>$message , 'values'=>$extension );
						return;
					// Ignore
					default: return;
				}
			}
		}
		// Ext is not in TER
		else {

			$handling = $this->getCustomExtensionErrorHandling();
			$message  = 'LLL:EXT:caretaker_instance/locallang.xml:insecure_extension_detail_unknown';

			switch ($handling) {
				// Warning	
				case 1: 
					$warnings[] = array( 'message'=>$message , 'values'=>$extension );
					return;
				// Error
				case 2: 
					$errors[] = array( 'message'=>$message , 'values'=>$extension );
					return;
				// Ignore
				default: return;
			}
		}
	}
	
	public function getExtensionTerInfos( $ext_key, $ext_version ){
		$ext_infos = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('extkey, version, reviewstate','cache_extensions','extkey = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($ext_key,'cache_extensions' ).' AND version = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($ext_version,'cache_extensions'), '', '' , 1 );
		if (count($ext_infos)==1){
			return $ext_infos[0];
		} else {
			return false;
		}
	}
	
	public function getInstalledExtensionErrorHandling(){
		return (int)$this->getConfigValue('status_of_installed_insecure_extensions');
	}
	
	public function getUninstalledExtensionErrorHandling(){
		return (int)$this->getConfigValue('status_of_uninstalled_insecure_extensions');
	}
	
	public function getCustomExtensionErrorHandling(){
		return (int)$this->getConfigValue('status_of_custom_extensions');
	}
	
	public function getCustomExtensionWhitelist(){
		return explode(chr(10), $this->getConfigValue('custom_extkey_whitlelist'));
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_ExtensionTestService.php']);
}
?>