<?php

/***************************************************************
* Copyright notice
*
* (c) 2009-2010 by n@work GmbH and networkteam GmbH
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


require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'));

/**
 * Check if given BE-Users exists
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_FindBlacklistedBePasswordTestService extends tx_caretakerinstance_RemoteTestServiceBase{
	
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

		$operations = array();
		foreach ($blacklistedPasswords as $password) {
			$password = trim( $password );
			if ( strlen( $password) ) {
				$operations[] = array('GetRecord', array('table' => 'be_users', 'field' => 'password', 'value' => md5( $password ), 'checkEnableFields' => TRUE));
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

				$user = $operationResult->getValue();
				print_r ($user);
				if ($user !== FALSE) {
					$careless_users[] = $user;
				}
			} else {
				return $this->getFailedOperationResultTestResult($operationResult);
			}
		}

		if ( count($careless_users) > 0){
			$submessages = array();
			foreach ( $careless_users as $user){
				print_r ($user['username'] );
				$submessages[] = new tx_caretaker_ResultMessage( $user['username'] );
			}
			return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_error, 0, 'The following accounts have blacklisted passwords:' , $submessages );
		}

		return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, '');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_BackendUserTestService.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker_instance/services/class.tx_caretaker_BackendUserTestService.php']);
}
?>