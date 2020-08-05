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

/**
 * An Operation that returns the first record matched by a field name and value as an array (excluding protected record details like be_user password).
 * This operation should be SQL injection safe. The table has to be mapped in the TCA.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class tx_caretakerinstance_Operation_GetRecord implements tx_caretakerinstance_IOperation
{
    /**
     * An array of tables and table fields that should be cleared before sending.
     *
     * @var array
     */
    protected $protectedFieldsByTable = array(
        'be_users' => array('password', 'uc'),
        'fe_users' => array('password'),
    );

    protected $implicitFields = array('uid', 'pid', 'deleted', 'hidden');

    /**
     * Get record data from the given table and uid
     *
     * @param array $parameter A table 'table', field name 'field' and the value 'value' to find the record
     * @return tx_caretakerinstance_OperationResult The first found record as an array or FALSE if no record was found
     */
    public function execute($parameter = array())
    {
        $table = $parameter['table'];
        $field = $parameter['field'];
        $value = $parameter['value'];
        $checkEnableFields = $parameter['checkEnableFields'] == true;
        \TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();
        if (!isset($GLOBALS['TCA'][$table])) {
            return new tx_caretakerinstance_OperationResult(false, 'Table [' . $table . '] not found in the TCA');
        }
        if (!isset($GLOBALS['TCA'][$table]['columns'][$field]) && !in_array($field, $this->implicitFields)) {
            return new tx_caretakerinstance_OperationResult(false, 'Field [' . $field . '] of table [' . $table . '] not found in the TCA');
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            '*',
            $table,
            $field . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $table) . ($checkEnableFields ? $this->enableFields($table) : '')
        );

        if ($result) {
            $record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if ($record !== false) {
                if (isset($this->protectedFieldsByTable[$table])) {
                    $protectedFields = $this->protectedFieldsByTable[$table];
                    foreach ($protectedFields as $protectedField) {
                        unset($record[$protectedField]);
                    }
                }

                return new tx_caretakerinstance_OperationResult(true, $record);
            }
            return new tx_caretakerinstance_OperationResult(true, false);
        }
        return new tx_caretakerinstance_OperationResult(
            false,
            'Error when executing SQL: [' . $GLOBALS['TYPO3_DB']->sql_error() . ']'
        );
    }

    /**
     * Include TCA to load table definitions
     *
     * @return void
     */
    protected function includeTCA()
    {
        if (!$GLOBALS['TSFE']) {
            // Make new instance of TSFE object for initializing user:
            $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
            $GLOBALS['TSFE']->includeTCA();
        }
    }

    /**
     * A simplified enableFields function (partially copied from sys_page) that
     * can be used without a full TSFE environment. It doesn't / can't check
     * fe_group constraints or custom hooks.
     *
     * @param $table
     * @return string The query to append
     */
    public function enableFields($table)
    {
        $ctrl = $GLOBALS['TCA'][$table]['ctrl'];
        $query = '';
        if (is_array($ctrl)) {
            // Delete field check:
            if ($ctrl['delete']) {
                $query .= ' AND ' . $table . '.' . $ctrl['delete'] . ' = 0';
            }

            // Filter out new place-holder records in case we are NOT in a versioning preview (that means we are online!)
            if ($ctrl['versioningWS']) {
                $query .= ' AND ' . $table . '.t3ver_state <= 0'; // Shadow state for new items MUST be ignored!
            }

            // Enable fields:
            if (is_array($ctrl['enablecolumns'])) {
                if ($ctrl['enablecolumns']['disabled']) {
                    $field = $table . '.' . $ctrl['enablecolumns']['disabled'];
                    $query .= ' AND ' . $field . ' = 0';
                }
                if ($ctrl['enablecolumns']['starttime']) {
                    $field = $table . '.' . $ctrl['enablecolumns']['starttime'];
                    $query .= ' AND (' . $field . ' <= ' . time() . ')';
                }
                if ($ctrl['enablecolumns']['endtime']) {
                    $field = $table . '.' . $ctrl['enablecolumns']['endtime'];
                    $query .= ' AND (' . $field . ' = 0 OR ' . $field . ' > ' . time() . ')';
                }
            }
        }

        return $query;
    }
}
