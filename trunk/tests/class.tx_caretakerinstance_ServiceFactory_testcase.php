<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_ServiceFactory.php'));

/**
 * Testcase for the ServiceFactory
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_ServiceFactory_testcase extends tx_phpunit_testcase {
	function testCommandServiceFactory() {
		
		// Simulate TYPO3 ext conf
		
		$extConf = array(
			'crypto.' => array(
				'instance.' => array(
					'publicKey' => 'FakePublicKey',
					'privateKey' => 'FakePrivateKey'					
				),
				'client.' => array(
					'publicKey' => 'FakeClientPublicKey'
				)
			),
			'security.' => array(
				'clientHostAddressRestriction' => '10.0.0.1'
			)
		);
	
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker_instance'] =
			serialize($extConf);
		
		$factory = tx_caretakerinstance_ServiceFactory::getInstance();
		$commandService = $factory->getCommandService();

		
		$this->assertType('tx_caretakerinstance_CommandService', $commandService);
		
		$securityManager = $factory->getSecurityManager();
		
		$this->assertType('tx_caretakerinstance_SecurityManager', $securityManager);
		
		// Test that properties have been set from extConf
		$this->assertEquals('FakePublicKey', $securityManager->getPublicKey());
		$this->assertEquals('FakePrivateKey', $securityManager->getPrivateKey());
		$this->assertEquals('FakeClientPublicKey', $securityManager->getClientPublicKey());
		$this->assertEquals('10.0.0.1', $securityManager->getClientHostAddressRestriction());
	}
}
?>