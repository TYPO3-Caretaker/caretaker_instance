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
 * Check for TYPO3 version
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class tx_caretakerinstance_TYPO3VersionTestService extends tx_caretakerinstance_RemoteTestServiceBase
{
    /**
     * @return tx_caretaker_TestResult
     */
    public function runTest()
    {
        $minVersion = $this->checkForLatestVersion($this->getConfigValue('min_version'), $this->getConfigValue('allow_unstable'));
        $maxVersion = $this->checkForLatestVersion($this->getConfigValue('max_version'), $this->getConfigValue('allow_unstable'));

        if (!$minVersion && !$maxVersion) {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_undefined, 0, 'Cannot execute TYPO3 version test without min and max version');
        }

        if ($maxVersion === false) {
            return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_undefined, 0, 'No TYPO3 version information available. Please add "TYPO3 Versionnumbers Update" to your scheduler queue.');
        }

        $operation = array('GetTYPO3Version');
        $operations = array($operation);

        $commandResult = $this->executeRemoteOperations($operations);

        if (!$this->isCommandResultSuccessful($commandResult)) {
            return $this->getFailedCommandResultTestResult($commandResult);
        }

        $results = $commandResult->getOperationResults();
        $operationResult = $results[0];
        if ($operationResult->isSuccessful()) {
            $version = $operationResult->getValue();
        } else {
            return $this->getFailedOperationResultTestResult($operationResult);
        }

        $checkResult = $this->checkVersionRange(
            $version,
            $minVersion,
            $maxVersion);

        if ($checkResult) {
            $message = 'TYPO3 version ' . $version . ' is installed';
            $testResult = tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, $message);
        } else {
            $message = 'TYPO3 version ' . $version . ' is installed, but';
            if ($minVersion) {
                $message .= ' >= ' . $minVersion;
            }
            if ($maxVersion) {
                $message .= ' <= ' . $maxVersion;
            }
            $message .= ' expected.';
            $testResult = tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, $message);
        }

        return $testResult;
    }

    /**
     * @param string $versionString
     * @param bool $allowUnstable
     * @return string|bool
     */
	protected function checkForLatestVersion($versionString, $allowUnstable = false) {
		if (strpos($versionString, '.latest') !== false || strpos($versionString, '.secure') !== false ) {
            $versionDigits = explode('.', $versionString, 3);
			$versionSource = 'TYPO3versionsSecurity';
			if (strpos($versionString, '.latest') !== false) {
			    $versionSource = $allowUnstable ? 'TYPO3versions' : 'TYPO3versionsStable';
            }
			$latestVersions = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry')->get('tx_caretaker', $versionSource);
            $newVersionString = $latestVersions[$versionDigits[0] . '.' . $versionDigits[1]];
            if (!$newVersionString) {
                // try with single version number, used since TYPO3 CMS 7
                $newVersionString = $latestVersions[$versionDigits[0]];
            }

            if (!empty($newVersionString)) {
                $versionString = $newVersionString;
            } else {
                // if we reach this point, no "current version was "latest" was found. This can be caused by a not running TYPO3 Version update task.
                return false;
            }
        }

        return $versionString;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_TYPO3VersionTestService.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_TYPO3VersionTestService.php']);
}
