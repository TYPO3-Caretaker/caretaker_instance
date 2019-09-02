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

use Doctrine\DBAL\Driver\Statement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Utility\EidUtility;

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
class tx_caretakerinstance_Operation_GetRecord implements tx_caretakerinstance_IOperation, SingletonInterface
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
        EidUtility::initTCA();
        if (!isset($GLOBALS['TCA'][$table])) {
            return new tx_caretakerinstance_OperationResult(false, 'Table [' . $table . '] not found in the TCA');
        }
        if (!isset($GLOBALS['TCA'][$table]['columns'][$field]) && !in_array($field, $this->implicitFields)) {
            return new tx_caretakerinstance_OperationResult(false, 'Field [' . $field . '] of table [' . $table . '] not found in the TCA');
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        if (!$checkEnableFields) {
            $queryBuilder->getRestrictions()->removeAll();
        }

        /** @var Statement $statement */
        $statement = $queryBuilder->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter($value)))
            ->execute();

        if (!$statement->errorInfo()) {
            $record = $statement->fetch();
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
            'Error when executing SQL: [' . $statement->errorInfo() . ']');
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
            $GLOBALS['TSFE'] = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
            $GLOBALS['TSFE']->includeTCA();
        }
    }
}
