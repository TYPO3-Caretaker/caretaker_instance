<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2021 by Oliver Eglseder <oliver.eglseder@in2code.de>
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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This operation select all recent failures from the Scheduler Task table.
 * This feature can not be implemented using GetRecord since there is no TCA for the table tx_scheduler_task
 *
 * @author Oliver Eglseder <oliver.eglseder@in2code.de>
 */
class tx_caretakerinstance_Operation_GetSchedulerFailures implements tx_caretakerinstance_IOperation, SingletonInterface
{
    public function execute($parameter = array()): tx_caretakerinstance_OperationResult
    {
        $results = array();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class);
        $query = $connection->getQueryBuilderForTable('tx_scheduler_task');
        $query->select('*')->from('tx_scheduler_task');
        $statement = $query->execute();
        foreach ($statement as $row) {
            $taskUid = $row['uid'];
            if (empty($row['lastexecution_failure'])) {
                continue;
            }
            try {
                $exception = unserialize($row['lastexecution_failure'], array('allowed_classes' => false));
            } catch (Throwable $exception) {
                $results[$taskUid] = 'unserialize exception: ' . (string)$exception;
                continue;
            }
            $keepKeys = array('code', 'file', 'line', 'message');
            foreach (array_keys($exception) as $key) {
                if (!in_array($key, $keepKeys, true)) {
                    unset($exception[$key]);
                }
            }
            $results[$taskUid] = $exception;
        }
        return new tx_caretakerinstance_OperationResult(empty($results), $results);
    }
}
