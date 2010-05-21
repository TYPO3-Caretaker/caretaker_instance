<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'lib/Crypt/RSA.php'));

/**
 * Testcase for the CommandResult
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_libRSA_testcase extends tx_phpunit_testcase {
	function skiptestGenerateKeyPair() {
		$length = 64;
		
		// 1024-bit key pair generation
		$key_pair = new Crypt_RSA_KeyPair($length);

		$this->assertEquals($length, $key_pair->getPublicKey()->getKeyLength());
		$this->assertEquals($length, $key_pair->getPrivateKey()->getKeyLength());

		echo 'PrivateKey: ', $key_pair->getPrivateKey()->toString(), '<br/>';
		echo 'PublicKey:  ', $key_pair->getPublicKey()->toString(),  '<br/>';
		// PrivateKey: YTozOntpOjA7czo4OiLphXB4HJXTjyI7aToxO3M6ODoidb0GlUnBGmgiO2k6MjtzOjc6InByaXZhdGUiO30=
		// PublicKey: YTozOntpOjA7czo4OiLphXB4HJXTjyI7aToxO3M6MzoiAQABIjtpOjI7czo2OiJwdWJsaWMiO30=
	}
	
	function testSomething() {
	}	
	
}
?>