<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_SecurityManager.php'));

/**
 * Testcase for the SecurityManager
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_SecurityManager_testcase extends tx_phpunit_testcase {
	function setUp() {		
		$this->securityManager = new tx_caretakerinstance_SecurityManager();
			
		$this->commandRequest = new tx_caretakerinstance_CommandRequest(
			array(
				'client_info' => array(
					'host_address' => '192.168.10.100',
					'client_key' => 'abcdefg'
				),
				'operations' => array(
					array('mock', array('foo' => 'bar')),
					array('mock', array('foo' => 'bar'))
				),
				// Crypted JSON (FAKE!)
				'encrypted' => 'xxer4rt34x'
			));
	}
	
	function testDecryptRequest() {
		$request = $this->securityManager->decryptRequest($this->commandRequest);
		$this->assertEquals('top-secret', $request['secret']);
	}

}
?>