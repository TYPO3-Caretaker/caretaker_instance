<?php 

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (t3lib_extMgm::isLoaded('caretaker') ){
		// load Service Helper
	include_once(t3lib_extMgm::extPath('caretaker').'classes/class.tx_caretaker_ServiceHelper.php');
		// register Tests
	tx_caretaker_ServiceHelper::registerCaretakerService ($_EXTKEY , 'services' , 'tx_caretaker_typo3_info'   ,'TYPO3-> Info', 'Retrieves the version of TYPO3' );
	tx_caretaker_ServiceHelper::registerCaretakerService ($_EXTKEY , 'services' , 'tx_caretaker_typo3_extensions',  'TYPO3 -> Extensions' , 'Retrieves a list of all available extensions (includes paths, versions and status)' );
	tx_caretaker_ServiceHelper::registerCaretakerService ($_EXTKEY , 'services' , 'tx_caretaker_system_info',  'System -> Info' , 'Retrieves System Informations' );
	tx_caretaker_ServiceHelper::registerCaretakerService ($_EXTKEY , 'services' , 'tx_caretaker_system_load',  'System -> Load' , 'Retrieves System Status Infos' );
}

?>