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
 * AbstractClass for remote executed tests
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
abstract class tx_caretakerinstance_RemoteTestServiceBase extends tx_caretaker_TestServiceBase
{
    /**
     * Execute a list of operations on the configured instance.
     *
     * The operations must be of the form
     * <code>
     * array(array("SomeOperationWithParams", array("foo" => "bar")), array("OperationWithoutParams"))
     * </code>
     *
     * @param $operations Array of array of operations
     * @return tx_caretakerinstance_CommandResult|bool
     */
    protected function executeRemoteOperations($operations)
    {
        $factory = tx_caretakerinstance_ServiceFactory::getInstance();
        $connector = $factory->getRemoteCommandConnector();
        $connector->setInstance($this->instance);

        return $connector->executeOperations($operations);
    }

    /**
     * Is the command result successful
     *
     * @param tx_caretakerinstance_CommandResult $commandResult
     * @return bool
     */
    protected function isCommandResultSuccessful($commandResult)
    {
        return $commandResult instanceof tx_caretakerinstance_CommandResult && $commandResult->isSuccessful();
    }

    /**
     * Get the test result for a failed command result
     *
     * @param tx_caretakerinstance_CommandResult $commandResult
     * @return tx_caretaker_TestResult
     */
    protected function getFailedCommandResultTestResult($commandResult)
    {
        return tx_caretaker_TestResult::create(
            ($commandResult instanceof tx_caretakerinstance_CommandResult ? $commandResult->getStatus() : tx_caretaker_Constants::state_error),
            0,
            'Command execution failed: ' . ($commandResult instanceof tx_caretakerinstance_CommandResult ? $commandResult->getMessage() : 'undefined')
        );
    }

    /**
     * Get the test result for a failed operation result
     *
     * @param $operationResult
     * @return tx_caretaker_TestResult
     */
    protected function getFailedOperationResultTestResult($operationResult)
    {
        return tx_caretaker_TestResult::create(
            tx_caretaker_Constants::state_error,
            0,
            'Operation execution failed: ' . $operationResult->getValue()
        );
    }

    /**
     * Check if the given version is within the minimum and maximum version
     *
     * @param string $actualVersion Version to compare to min and max
     * @param string $minVersion Minimum version that is required.
     *                              May be empty.
     * @param string $maxVersion Maximum version that is required.
     *                              May be empty.
     *
     * @return bool TRUE if the actual version is within min and max.
     */
    public function checkVersionRange($actualVersion, $minVersion, $maxVersion)
    {
        if ($minVersion != '') {
            if (!version_compare($actualVersion, $minVersion, '>=')) {
                return false;
            }
        }
        if ($maxVersion != '') {
            if (!version_compare($actualVersion, $maxVersion, '<=')) {
                return false;
            }
        }

        return true;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretakerinstance_RemoteTestServiceBase.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretakerinstance_RemoteTestServiceBase.php']);
}
