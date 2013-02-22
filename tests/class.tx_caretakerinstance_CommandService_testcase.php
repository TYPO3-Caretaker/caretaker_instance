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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_CommandService.php'));

/**
 * Testcase for the CommandService
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_CommandService_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_caretakerinstance_SecurityManager
	 */
	protected $securityManager;

	function setUp() {
		$this->operationManager = $this->getMock('tx_caretakerinstance_OperationManager',
			array('executeOperation'));

		$this->securityManager = $this->getMock('tx_caretakerinstance_ISecurityManager');


		$this->commandService = new tx_caretakerinstance_CommandService(
			$this->operationManager, $this->securityManager);

		$this->commandRequest = new tx_caretakerinstance_CommandRequest(
			array('data' => array(
				'operations' => array(
					array('mock', array('foo' => 'bar')),
					array('mock', array('foo' => 'bar'))
				)
			)
		));
	}

	function testWrapCommandResultEncodesResult() {
		$result = new tx_caretakerinstance_CommandResult(true,
			new tx_caretakerinstance_OperationResult(true, array('foo' => 'bar'))
		);

		$data = $result->toJson();

		$this->securityManager->expects($this->once())
			->method('encodeResult')
			->with($this->equalTo($data))
			->will($this->returnValue('Encoded result data'));

		$wrap = $this->commandService->wrapCommandResult($result);

		$this->assertEquals('Encoded result data', $wrap);
	}

	function testExecuteCommandWithSecurity() {
		$this->securityManager->expects($this->once())
			->method('validateRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(true));

		$this->securityManager->expects($this->once())
			->method('decodeRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(true));

		$this->operationManager->expects($this->exactly(2))
			->method('executeOperation')
			->with($this->equalTo('mock'), $this->equalTo(array('foo' => 'bar')))
			->will($this->returnValue(new tx_caretakerinstance_OperationResult(true, 'bar')));

		$result = $this->commandService->executeCommand($this->commandRequest);

		$this->assertInstanceOf('tx_caretakerinstance_CommandResult', $result);

		$this->assertTrue($result->isSuccessful());

		foreach($result->getOperationResults() as $operationResult) {
			$this->assertInstanceOf('tx_caretakerinstance_OperationResult', $operationResult);
			$this->assertTrue($operationResult->isSuccessful());
			$this->assertEquals('bar', $operationResult->getValue());
		}
	}

	function testExecuteCommandSecurityCheckFailed() {
		$this->securityManager->expects($this->once())
			->method('validateRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(false));

		$this->securityManager->expects($this->never())
			->method('decodeRequest');

		$result = $this->commandService->executeCommand($this->commandRequest);

		$this->assertFalse($result->isSuccessful());

		$this->assertEquals('The request could not be certified', $result->getMessage());
	}

	function testExecuteCommandDecryptionFailed() {
		$this->securityManager->expects($this->once())
			->method('validateRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(true));

		$this->securityManager->expects($this->once())
			->method('decodeRequest');

		$this->operationManager->expects($this->never())
			->method('executeOperation');

		$result = $this->commandService->executeCommand($this->commandRequest);

		$this->assertFalse($result->isSuccessful());

		$this->assertEquals('The request could not be decrypted', $result->getMessage());
	}

	function testRequestSessionToken() {
		$this->securityManager->expects($this->once())
			->method('createSessionToken')
			->with($this->equalTo('10.0.0.1'))
			->will($this->returnValue('me-is-token'));

		$token = $this->commandService->requestSessionToken('10.0.0.1');
		$this->assertEquals('me-is-token', $token);
	}
}
?>