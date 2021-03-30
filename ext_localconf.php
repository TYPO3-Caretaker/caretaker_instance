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
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add eID script for caretaker instance frontend service
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_caretakerinstance'] = \Caretaker\CaretakerInstance\Controller\EidController::class . '::execute';

// Register default caretaker Operations
$operations = array(
    'GetPHPVersion',
    'GetTYPO3Version',
    'GetExtensionVersion',
    'GetExtensionList',
    'GetRecord',
    'GetSchedulerFailures',
    'GetFilesystemChecksum',
    'MatchPredefinedVariable',
    'CheckPathExists',
    'GetDiskSpace',
);
foreach ($operations as $operationKey) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'][$operationKey] =
        'tx_caretakerinstance_Operation_' . $operationKey;
}

require(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('caretaker_instance') . 'ext_conf_include.php');
