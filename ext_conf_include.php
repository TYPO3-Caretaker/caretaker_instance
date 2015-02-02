<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
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

if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

// Register Caretaker Services
if (t3lib_extMgm::isLoaded('caretaker') ){
	include_once(t3lib_extMgm::extPath('caretaker') . 'classes/helpers/class.tx_caretaker_ServiceHelper.php');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_Extension',                 'TYPO3 -> Extension', 'Check for a specific Extension');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_TYPO3Version',              'TYPO3 -> Version', 'Check for the TYPO3 version');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_FindInsecureExtension',     'TYPO3 -> Find insecure Extensions', 'Find Extensions wich are marked insecure in TER');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_FindExtensionUpdates',      'TYPO3 -> Find Extension Updates', 'Find available Updates for installed Extensions');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_BackendUser',               'TYPO3 -> Check backend user accounts', 'Find unwanted backend user accounts');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_FindBlacklistedBePassword', 'TYPO3 -> Check be-password blacklist', 'Find backend user accounts with blacklisted passwords.');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_CheckConfVars',             'TYPO3 -> Check TYPO3_CONF_VARS', 'Check Settings of TYPO3_CONF_VARS');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_CheckPath',                 'FILE -> Check path', 'Checks for some path stats');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_VersionControl',            'FILE -> Check versioning system', 'Check is version control system is up to date.');
	tx_caretaker_ServiceHelper::registerCaretakerService($_EXTKEY, 'services', 'tx_caretakerinstance_DiskSpace',                 'DISK -> Check disk space', 'Check if there is avaiable disk space.');

}

?>
