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


require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));

/**
 * An Operation that returns the first record matched by a field name and value as an array (excluding protected record details like be_user password).
 * This operation should be SQL injection safe. The table has to be mapped in the TCA.
 * 
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_Operation_GetRecord implements tx_caretakerinstance_IOperation {

	/**
	 * An array of tables and table fields that should be cleared before
	 * sending.
	 * @var array
	 */
	protected $protectedFieldsByTable = array(
		'be_users' => array('password', 'uc'),
		'fe_users' => array('password')
	);

	protected $implicitFields = array('uid', 'pid', 'deleted', 'hidden');
	
	/**
	 * 
	 * @param array $parameter A table 'table', field name 'field' and the value 'value' to find the record 
	 * @return The first found record as an array or FALSE if no record was found
	 */
	public function execute($parameter = array()) {
		$table = $parameter['table'];
		$field = $parameter['field'];
		$value = $parameter['value'];
		$checkEnableFields = $parameter['checkEnableFields'] == TRUE;
		
		$this->includeTCA();
		
		if (!isset($GLOBALS['TCA'][$table])) {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Table [' . $table . '] not found in the TCA');
		}
		t3lib_div::loadTCA($table);
		if (!isset($GLOBALS['TCA'][$table]['columns'][$field]) && !in_array($field, $this->implicitFields)) {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Field [' . $field . '] of table [' . $table . '] not found in the TCA');
		}
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$table,
			$field . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $table) . ($checkEnableFields ? $this->enableFields($table) : ''));
		
		if ($result) {
			$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			if ($record !== FALSE) {
				if (isset($this->protectedFieldsByTable[$table])) {
					$protectedFields = $this->protectedFieldsByTable[$table];
					foreach ($protectedFields as $protectedField) {
						unset($record[$protectedField]);
					}
				}
				return new tx_caretakerinstance_OperationResult(TRUE, $record);
			} else {
				return new tx_caretakerinstance_OperationResult(TRUE, FALSE);
			}
		} else {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Error when executing SQL: [' . $GLOBALS['TYPO3_DB']->sql_error() . ']');
		}
	}

	protected function includeTCA() {
		if (!$GLOBALS['TSFE']) {
			require_once(PATH_tslib.'class.tslib_fe.php');

				// require some additional stuff in TYPO3 4.1
			require_once(PATH_t3lib.'class.t3lib_cs.php');
			require_once(PATH_t3lib.'class.t3lib_userauth.php');
			require_once(PATH_tslib.'class.tslib_feuserauth.php');

				// Make new instance of TSFE object for initializing user:
			$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
			$GLOBALS['TSFE'] = new $temp_TSFEclassName($GLOBALS['TYPO3_CONF_VARS'], 0, 0);
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
	function enableFields($table) {
		$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
		$query = '';
		if (is_array($ctrl)) {
				// Delete field check:
			if ($ctrl['delete']) {
				$query .= ' AND ' . $table . '.' . $ctrl['delete'] . ' = 0';
			}

				// Filter out new place-holder records in case we are NOT in a versioning preview (that means we are online!)
			if ($ctrl['versioningWS']) {
				$query .=' AND ' . $table . '.t3ver_state <= 0'; // Shadow state for new items MUST be ignored!
			}

				// Enable fields:
			if (is_array($ctrl['enablecolumns']))	{
				if ($ctrl['enablecolumns']['disabled']) {
					$field = $table . '.' . $ctrl['enablecolumns']['disabled'];
					$query .= ' AND ' . $field . ' = 0';
				}
				if ($ctrl['enablecolumns']['starttime']) {
					$field = $table.'.'.$ctrl['enablecolumns']['starttime'];
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
?>