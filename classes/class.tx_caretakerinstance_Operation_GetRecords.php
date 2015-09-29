<?php

/* * *************************************************************
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
 * ************************************************************* */

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
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_Operation_GetRecords implements tx_caretakerinstance_IOperation {

	/**
	 * An array of tables and table fields that should be cleared before sending.
	 *
	 * @var array
	 */
	protected $protectedFieldsByTable = array(
			'be_users' => array('password', 'uc'),
			'fe_users' => array('password')
	);

	/**
	 * @var array
	 */
	protected $implicitFields = array('uid', 'pid', 'deleted', 'hidden');

	/**
	 * Get record data from the given table and uid
	 *
	 * <p>The $parameter array is normally just formed like this:</p>
	 * <code>
	 *    array(
	 *        'table' => 'be_users',
	 *        'field' => 'password',
	 *        'value' => md5(string)
	 *    )
	 * </code>
	 *
	 * <p>However, the values for 'field' and 'value' can also be arrays like this:</p>
	 * <code>
	 *  array(
	 *        'table'        => 'be_users',
	 *        'field'        => array (
	 *                        'password',
	 *                        'deleted',
	 *                        'hidden'
	 *                    ),
	 *        'value'        =>    array (
	 *                        'password'    => 'forbidden_password',
	 *                        'deleted'    => 0,
	 *                        'hidden'    => 0
	 *                    )
	 *  )
	 * </code>
	 * <p>The above parameter array will convert to this SQL query:</p>
	 * <code>SELECT * FROM be_users where password = 'forbidden_password' AND deleted = 0 AND hidden = 0;</code>
	 *
	 * <p>The keys in the "field" array is matched with the keys in the "value" array in order to make sure that all requested columns are present in the TCA for the requested table.</p>
	 *
	 * <p>A more complex example which does not only compare "value" to "field" (e.g. "deleted = 0"):</p>
	 * <code>
	 * array(
	 *        'table'        => 'be_users',
	 *        'field'        => array('password'),
	 *        'value'        => array(
	 *                        'password'    =>    array(
	 *                                        'SELECT password FROM be_users WHERE deleted = 0 and disable = 0 GROUP BY password HAVING COUNT(*) > 1'
	 *                                    )
	 *                    )
	 * )
	 * </code>
	 *
	 * <p>If the values in the "value" array are also arrays (getting in too deep now?) then the tests are not simple 'equal to' queries. They are then converted to "IN (subselect or csv)" SQL queries, which means that the above example will convert to:</p>
	 * <code>
	 * SELECT * FROM be_users WHERE password IN (SELECT password from be_users WHERE deleted = 0 AND disable = 0 GROUP BY password HAVING COUNT(*) >1);
	 * </code>
	 *
	 * @example ../services/class.tx_caretakerinstance_FindBlacklistedBePasswordTestService.php This class tests if there are duplicate passwords, besides checking for the presence of blacklisted passwords.
	 * @param array $parameter A table 'table', field name 'field' and the value 'value' to find the record
	 * @return tx_caretakerinstance_OperationResult A set of records as an array or FALSE if no record was found
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

		if (is_array($field) && is_array($value)) {
			// check that every value in the field array is both in the TCA and the value array
			foreach ($field as $val) {
				if (!isset($GLOBALS['TCA'][$table]['columns'][$val]) && (!in_array($val, $this->implicitFields) || !in_array($val, $value))) {
					return new tx_caretakerinstance_OperationResult(FALSE, 'Field [' . $val . '] of table [' . $table . '] not found in the TCA OR not found in value array.');
				}
			}

			// check that every key in the value array is both in the TCA and the field array
			foreach ($value as $key => $val) {
				if (!isset($GLOBALS['TCA'][$table]['columns'][$key]) && (!in_array($key, $this->implicitFields) || !in_array($key, $field))) {
					return new tx_caretakerinstance_OperationResult(FALSE, 'Field [' . $key . '] of table [' . $table . '] not found in the TCA OR not found in field array.');
				}
			}
		} else { // check that all requested fields are present in the TCA (and the value array.
			if (!isset($GLOBALS['TCA'][$table]['columns'][$field]) && !in_array($field, $this->implicitFields)) {
				return new tx_caretakerinstance_OperationResult(FALSE, 'Field [' . $field . '] of table [' . $table . '] not found in the TCA');
			}
		}

		$result = null;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 0;
		if (!is_array($field)) {
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*', $table, $field . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $table) . ($checkEnableFields ? $this->enableFields($table) : ''));
		} else {

			$arrSql = array();
			$arrSql['SELECT'] = "*";
			$arrSql['FROM'] = $table;
			$arrSql['WHERE'] = "";

			$firstField = true;
			foreach ($value as $key => $val) {

				if (!$firstField) {
					$arrSql['WHERE'] .= " AND ";
				}

				if (is_array($val)) {
					$arrSql['WHERE'] .= "$key IN (" . join(",", $val) . ")"; // @TODO Make sure there are no loop holes in the generated SQL query...
				} else {
					$arrSql['WHERE'] .= "$key = " . $GLOBALS['TYPO3_DB']->fullQuoteStr($val, $table);
				}
				$firstField = false;
			}

			$arrSql['WHERE'] .= ($checkEnableFields ? $this->enableFields($table) : '');

			$arrSql['ORDERBY'] = null;
			$arrSql['GROUPBY'] = null;
			$arrSql['LIMIT'] = null;

			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($arrSql);
		}


		if ($result) {
			$records = array();
			while (FALSE !== ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {
				if ($record !== FALSE) {
					if (isset($this->protectedFieldsByTable[$table])) {
						$protectedFields = $this->protectedFieldsByTable[$table];
						foreach ($protectedFields as $protectedField) {
							unset($record[$protectedField]);
						}

						$records[] = $record;
					}
				} else {
					return new tx_caretakerinstance_OperationResult(TRUE, FALSE);
				}
			}

			return new tx_caretakerinstance_OperationResult(TRUE, $records);
		} else {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Error when executing SQL: [' . $GLOBALS['TYPO3_DB']->sql_error() . ']');
		}
	}

	/**
	 * Include TCA to load table definitions
	 *
	 * @return void
	 */
	protected function includeTCA() {
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