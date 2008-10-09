<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_CommandResult.php'));

/**
 * Testcase for the CommandResult
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_CommandResult_testcase extends tx_phpunit_testcase {
	function testCommandResultToJsonCreatesJson() {
		$result = new tx_caretakerinstance_CommandResult(true, array(
			new tx_caretakerinstance_OperationResult(true, 'foo'),
			new tx_caretakerinstance_OperationResult(true, false),
			new tx_caretakerinstance_OperationResult(true, array('foo', 'bar'))
		), 'Test message');
		
		$json = $result->toJson();
		
		$this->assertEquals('{"status":true,"results":[{"status":true,"value":"foo"},{"status":true,"value":false},{"status":true,"value":["foo","bar"]}],"message":"Test message"}', $json);
	}
}
?>