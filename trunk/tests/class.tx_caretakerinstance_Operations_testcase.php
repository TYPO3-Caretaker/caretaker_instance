<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_IOperation.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));
require_once('fixtures/class.tx_caretakerinstance_DummyOperation.php');
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetPHPVersion.php'));



/**
 * Testcase for Operations
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_Operations_testcase extends tx_phpunit_testcase {
	public function testOperationInterface() {
		$parameter = array('foo' => 'bar');
		$operation = new tx_caretakerinstance_DummyOperation();
		$result = $operation->execute($parameter);
		$this->assertType("tx_caretakerinstance_OperationResult", $result);
		
		$status = $result->isSuccessful();
		$this->assertTrue($status);
		$value = $result->getValue();
		// Value is always an string or array of strings or array of array of strings
		$this->assertEquals('bar', $value);
	}
	
	public function testOperation_GetPHPVersion() {
		$operation = new tx_caretakerinstance_Operation_GetPHPVersion();
		
		$result = $operation->execute();
		
		$this->assertTrue($result->isSuccessful());
		
		$this->assertEquals(phpversion(), $result->getValue());
	}
}
?>