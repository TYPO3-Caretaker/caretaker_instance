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
 * Check if there are blacklisted passwords or users which use the same password (likely "test", "test", "1234" or something similar) in the be_users table
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 * @author Tomas Norre Mikkelsen <tomasnorre@gmail.com>
 * @author Ulrik Høyer Kold <kontakt@ulrikkold.dk>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_FindBlacklistedBePasswordTestService extends tx_caretakerinstance_RemoteTestServiceBase {

	/**
	 * Value Description
	 * @var string
	 */
	protected $valueDescription = '';

	/**
	 * Service type description in human readble form.
	 * @var string
	 */
	protected $typeDescription = 'Test that no users with blacklisted passwords exist on the instance.';

	/**
	 * Template to display the test Configuration in human readable form.
	 * @var string
	 */
	protected $configurationInfoTemplate = '';

	public function runTest() {
		$blacklistedPasswords = explode(chr(10), $this->getConfigValue('blacklist'));
		$checkForDuplicatePasswords = $this->getConfigValue('check_duplicate_passwords');

		$operations = array();
		$operations[] = array('GetExtensionVersion', array('extensionKey' => 'saltedpasswords'));
		$commandResult = $this->executeRemoteOperations($operations);

		$results = $commandResult->getOperationResults();
		$operationResult = $results[0];
		if (is_object($operationResult) && $operationResult->isSuccessful()) {
			return tx_caretaker_TestResult::create(
				tx_caretaker_Constants::state_undefined,
				0,
				'FindBlacklistedBePassword is not supported if EXT:saltedpasswords is installed on instance.'
			);
		}

		$operations = array();
		foreach ($blacklistedPasswords as $password) {
			$password = trim($password);
			if (strlen($password)) {
				$operations[] = array('GetRecords', array('table' => 'be_users', 'field' => 'password', 'value' => md5($password), 'checkEnableFields' => TRUE));
			}
		}

		$commandResult = $this->executeRemoteOperations($operations);

		if (!$this->isCommandResultSuccessful($commandResult)) {
			return $this->getFailedCommandResultTestResult($commandResult);
		}

		$careless_users = array();

		$results = $commandResult->getOperationResults();
		foreach ($results as $operationResult) {
			if ($operationResult->isSuccessful()) {
				$users = $operationResult->getValue();
				if ($users !== FALSE) {
					foreach ($users as $user) {
						$careless_users[] = $user;
					}
				}
			} else {
				return $this->getFailedOperationResultTestResult($operationResult);
			}
		}

		if ($checkForDuplicatePasswords) {
			// clean the preceding operations
			unset($operations);
			$operations = array();

			// Will check whether "password" is IN (subselect or comma separated list)
			$sql_fields = array(
				'password' => array(
					'SELECT password FROM be_users WHERE disable = 0 AND deleted = 0 GROUP BY password HAVING COUNT(*) > 1' // subselect or comma separated values
				)
			);

			$operations[] = array('GetRecords', array('table' => 'be_users', 'field' => array_keys($sql_fields), 'value' => $sql_fields, 'checkEnableFields' => TRUE));

			$commandResult = $this->executeRemoteOperations($operations);

			if (!$this->isCommandResultSuccessful($commandResult)) {
				return $this->getFailedCommandResultTestResult($commandResult);
			}


			$results = $commandResult->getOperationResults();
			foreach ($results as $operationResult) {
				if ($operationResult->isSuccessful()) {

					$users = $operationResult->getValue();
					if ($users !== FALSE) {
						foreach ($users as $user) {
							$careless_users[] = $user;
						}
					}
				} else {
					return $this->getFailedOperationResultTestResult($operationResult);
				}
			}
		}

		// Check if multiple users have the same password, if so then add them to $careless_users array.
		if (count($careless_users) > 0) {

			$submessages = array();
			foreach ($careless_users as $user) {
				$submessages[] = new tx_caretaker_ResultMessage($user['username']);
			}
//			// Remove dublets

			$submessages = array_unique($submessages, SORT_REGULAR);
			asort($submessages);

			if($checkForDuplicatePasswords) {
				$text_reply = 'The following accounts have blacklisted or duplicate passwords: ';
			} else {
				$text_reply = 'The following accounts have blacklisted passwords: ';
			}

			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, $text_reply, $submessages);
		}

		return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, '');
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_BackendUserTestService.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_BackendUserTestService.php']);
}
?>
