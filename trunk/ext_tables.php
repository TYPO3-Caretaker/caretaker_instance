<?php 

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (t3lib_extMgm::isLoaded('caretaker') ){
		// load Service Helper
	include_once(t3lib_extMgm::extPath('caretaker').'classes/class.tx_caretaker_ServiceHelper.php');
		// register Tests
	tx_caretaker_ServiceHelper::registerCaretakerService ($_EXTKEY , 'services' , 'tx_caretakerinstance_Extension',  'TYPO3 -> Extension' , 'Check for a specific Extension' );
}

?>