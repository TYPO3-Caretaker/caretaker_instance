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
		
		$this->assertEquals('{"status":0,"results":[{"status":true,"value":"foo"},{"status":true,"value":false},{"status":true,"value":["foo","bar"]}],"message":"Test message"}', $json);
	}
	
	function testCommandResultFromJson() {
		$json = '{"status":0,"results":[{"status":true,"value":"foo"},{"status":true,"value":false},{"status":true,"value":["foo","bar"]}],"message":"Test message"}';
		$result = tx_caretakerinstance_CommandResult::fromJson($json);
		
		$this->assertType('tx_caretakerinstance_CommandResult', $result);
		$this->assertEquals('Test message', $result->getMessage());
		$this->assertTrue($result->isSuccessful());
		$this->assertEquals(array(
			new tx_caretakerinstance_OperationResult(true, 'foo'),
			new tx_caretakerinstance_OperationResult(true, false),
			new tx_caretakerinstance_OperationResult(true, array('foo', 'bar'))
		), $result->getOperationResults());
	}
}
?>