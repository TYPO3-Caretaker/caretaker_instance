<?php

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
	function setUp() {
		$this->operationManager = $this->getMock('tx_caretakerinstance_OperationManager',
			array('executeOperation'));
		
		$this->securityManager = $this->getMock('tx_caretakerinstance_SecurityManager',
			array('checkRequest', 'decryptRequest'));
		
		$this->commandService = new tx_caretakerinstance_CommandService(
			$this->operationManager, $this->securityManager);
			
		$this->commandRequest = new tx_caretakerinstance_CommandRequest(
			array('client_info' => array(
				'host_address' => '192.168.10.100',
				'client_key' => 'abcdefg'
			),
			'operations' => array(
				array('mock', array('foo' => 'bar')),
				array('mock', array('foo' => 'bar'))
			),
			// Crypted JSON
			'encrypted' => 'xxer4rt34x'
		));
	}
	
	function testExecuteCommandWithSecurity() {
		$this->securityManager->expects($this->once())
			->method('checkRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(true));

		$this->securityManager->expects($this->once())
			->method('decryptRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue($this->commandRequest));
			
		$this->operationManager->expects($this->exactly(2))
			->method('executeOperation')
			->with($this->equalTo('mock'), $this->equalTo(array('foo' => 'bar')))
			->will($this->returnValue(new tx_caretakerinstance_OperationResult(true, 'bar')));
		
		$result = $this->commandService->executeCommand($this->commandRequest);
		
		$this->assertType('tx_caretakerinstance_CommandResult', $result);
		
		$this->assertTrue($result->isSuccessful());
		
		foreach($result->getOperationResults() as $operationResult) {
			$this->assertType('tx_caretakerinstance_OperationResult', $operationResult);
			$this->assertTrue($operationResult->isSuccessful());
			$this->assertEquals('bar', $operationResult->getValue());
		}
	}
	
	function testExecuteCommandSecurityCheckFailed() {
		$this->securityManager->expects($this->once())
			->method('checkRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(false));

		$this->securityManager->expects($this->never())
			->method('decryptRequest');
		
		$result = $this->commandService->executeCommand($this->commandRequest);
		
		$this->assertFalse($result->isSuccessful());
		
		$this->assertEquals('The request could not be certified', $result->getMessage());
	}
	
	function testExecuteCommandDecryptionFailed() {
		$this->securityManager->expects($this->once())
			->method('checkRequest')
			->with($this->equalTo($this->commandRequest))
			->will($this->returnValue(true));

		$this->securityManager->expects($this->once())
			->method('decryptRequest');
		
		$this->operationManager->expects($this->never())
			->method('executeOperation');
			
		$result = $this->commandService->executeCommand($this->commandRequest);
		
		$this->assertFalse($result->isSuccessful());
		
		$this->assertEquals('The request could not be decrypted', $result->getMessage());
	}
}
?>