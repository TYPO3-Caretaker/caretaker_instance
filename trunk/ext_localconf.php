<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Add eID script for caretaker instance frontend service
$TYPO3_CONF_VARS['FE']['eID_include']['tx_caretakerinstance'] = 'EXT:caretaker_instance/eid/eid.tx_caretakerinstance.php';

// Register default caretaker Operations
foreach (array('GetPHPVersion', 'GetTYPO3Version', 'GetExtensionVersion','GetExtensionList', 'GetRecord', 'GetFilesystemChecksum') as $operationKey) {
	$TYPO3_CONF_VARS['EXTCONF']['caretaker_instance']['operations'][$operationKey] =
		'EXT:caretaker_instance/classes/class.tx_caretakerinstance_Operation_' . $operationKey . '.php:&tx_caretakerinstance_Operation_' . $operationKey;
}

// Register Caretaker Services
if (t3lib_extMgm::isLoaded('caretaker') ){
	include_once(t3lib_extMgm::extPath('caretaker') . 'classes/class.tx_caretaker_ServiceHelper.php');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_Extension',  'TYPO3 -> Extension', 'Check for a specific Extension');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_TYPO3Version',  'TYPO3 -> Version', 'Check for the TYPO3 version');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_FindUnsecureExtension',  'TYPO3 -> Find Unsecure Extenions', 'Find Extensions wich are marked unsecure in TER');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_BackendUser',  'TYPO3 -> Check backend user accounts', 'Find unwanted backend user accounts');
}

?>