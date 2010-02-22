<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Add eID script for caretaker instance frontend service
$TYPO3_CONF_VARS['FE']['eID_include']['tx_caretakerinstance'] = 'EXT:caretaker_instance/eid/eid.tx_caretakerinstance.php';

// Register default caretaker Operations
$operations = array(
	'GetPHPVersion',
	'GetTYPO3Version',
	'GetExtensionVersion',
	'GetExtensionList',
	'GetRecord',
	'GetFilesystemChecksum',
	'MatchPredefinedVariable',
);
foreach ($operations as $operationKey) {
	$TYPO3_CONF_VARS['EXTCONF']['caretaker_instance']['operations'][$operationKey] =
		'EXT:caretaker_instance/classes/class.tx_caretakerinstance_Operation_' . $operationKey . '.php:&tx_caretakerinstance_Operation_' . $operationKey;
}

require(t3lib_extMgm::extPath('caretaker_instance').'/ext_conf_include.php');

?>