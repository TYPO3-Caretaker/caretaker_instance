<?php

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
	public function testAddOperationAsClass() {
		$operationManager = new tx_caretakerinstance_OperationManager();
		$operationManager->addOperation('get_php_version',
			'tx_caretakerinstance_Operation_GetPHPVersion');
		$operation = $operationManager->getOperation('get_php_version');
		$this->assertType('tx_caretakerinstance_Operation_GetPHPVersion', $operation);
	}

	public function testAddOperationAsInstance() {
		$operationManager = new tx_caretakerinstance_OperationManager();
		$operationManager->addOperation('get_php_version',
			new tx_caretakerinstance_Operation_GetPHPVersion());
		$operation = $operationManager->getOperation('get_php_version');
		$this->assertType('tx_caretakerinstance_Operation_GetPHPVersion', $operation);
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
		
		$operationManager->addOperation('mock',	$operation);
		
		$result = $operationManager->executeOperation('mock', array('foo' => 'bar'));
		$this->assertTrue($result->isSuccessful());
		$this->assertEquals('bar', $result->getValue());
	}
}
?>