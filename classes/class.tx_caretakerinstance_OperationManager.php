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
 * Operation manager for operation registration and execution.
 *
 * This implementation registers Operations in an array.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_OperationManager implements tx_caretakerinstance_IOperationManager
{

    /**
     * @var array of tx_caretakerinstance_IOperation
     */
    protected $operations;

    /**
     * Register a new operation
     *
     * @param string $operationKey The key of the operation (All lowercase, underscores)
     * @param string|object $operation Operation instance or class
     */
    function registerOperation($operationKey, $operation)
    {
        $this->operations[$operationKey] = $operation;
    }

    /**
     * Get a registered operation as instance
     *
     * @param string $operationKey
     * @return tx_caretakerinstance_IOperation|boolean The operation instance or FALSE if not registered
     */
    function getOperation($operationKey)
    {
        if (is_string($this->operations[$operationKey])) {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->operations[$operationKey]);
        } elseif (is_object($this->operations[$operationKey])) {
            return $this->operations[$operationKey];
        } else {
            return false;
        }
    }

    /**
     * Execute an Operation by key with optional parameters
     *
     * @param string $operationKey
     * @param array $parameter
     * @return tx_caretakerinstance_OperationResult
     */
    function executeOperation($operationKey, $parameter = [])
    {
        $operation = $this->getOperation($operationKey);
        if ($operation) {
            return $operation->execute($parameter);
        } else {
            return new tx_caretakerinstance_OperationResult(false, 'Operation [' . $operationKey . '] unknown');
        }
    }

}
