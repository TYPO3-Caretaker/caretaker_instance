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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_IOperation.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));
require_once('fixtures/class.tx_caretakerinstance_DummyOperation.php');
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetPHPVersion.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationManager.php'));

/**
 * Testcase for the OperationManager
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_OperationManager_testcase extends tx_phpunit_testcase {
	public function testRegisterOperationAsClass() {
		$operationManager = new tx_caretakerinstance_OperationManager();
		$operationManager->registerOperation('get_php_version',
			'tx_caretakerinstance_Operation_GetPHPVersion');
		$operation = $operationManager->getOperation('get_php_version');
		$this->assertInstanceOf('tx_caretakerinstance_Operation_GetPHPVersion', $operation);
	}

	public function testRegisterOperationAsInstance() {
		$operationManager = new tx_caretakerinstance_OperationManager();
		$operationManager->registerOperation('get_php_version',
			new tx_caretakerinstance_Operation_GetPHPVersion());
		$operation = $operationManager->getOperation('get_php_version');
		$this->assertInstanceOf('tx_caretakerinstance_Operation_GetPHPVersion', $operation);
	}

	public function testGetOperationForUnknownOperation() {
		$operationManager = new tx_caretakerinstance_OperationManager();
		$operation = $operationManager->getOperation('me_no_operation');
		$this->assertFalse($operation);
	}

	public function testExecuteUnknownOperation() {
		$operationManager = new tx_caretakerinstance_OperationManager();
		$result = $operationManager->executeOperation('me_no_operation');
		$this->assertFalse($result->isSuccessful());
		$this->assertEquals('Operation [me_no_operation] unknown', $result->getValue());
	}

	public function testExecuteOperation() {
		$operationManager = new tx_caretakerinstance_OperationManager();

		$operation = $this->getMock('tx_caretakerinstance_IOperation', array('execute'));
		$operation->expects($this->once())
			->method('execute')
			->with($this->equalTo(array('foo' => 'bar')))
			->will($this->returnValue(new tx_caretakerinstance_OperationResult(true, 'bar')));

		$operationManager->registerOperation('mock', $operation);

		$result = $operationManager->executeOperation('mock', array('foo' => 'bar'));
		$this->assertTrue($result->isSuccessful());
		$this->assertEquals('bar', $result->getValue());
	}
}
?>