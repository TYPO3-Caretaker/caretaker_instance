<?php 

if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
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