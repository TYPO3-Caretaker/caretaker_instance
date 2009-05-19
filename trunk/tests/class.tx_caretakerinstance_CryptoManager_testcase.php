<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_CryptoManager.php'));

/**
 * Testcase for the CryptoManager
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_CryptoManager_testcase extends tx_phpunit_testcase {
	
	/**
	 * @var tx_caretakerinstance_CryptoManager
	 */
	protected $cryptoManager;
	
	/**
	 * @var string
	 */
	protected $privateKey;
	
	/**
	 * @var string
	 */
	protected $publicKey;
	
	
	function setUp() {		
		$this->cryptoManager = new tx_caretakerinstance_CryptoManager();
		
		$this->privateKey = 'YTozOntpOjA7czoxMjg6IuMO6w7voRTIkooxGDtQe8W+Yv59YYHyoA4TLYCszfmyH0oPyhh/2oYhlWZqIGeaxewVMMGteAU0H0uU4El3K/sWqt3tAPtTLK27ksNCjPi9bv0PW0SySL52FFqavmjETVuHof2CzKltAJTOlMet3zrVfcWEo5/PGGIpxRoRHqWLIjtpOjE7czoxMjg6IlEXhW1Ue6+YYynk8YSaiDfxua/u8KilxlTHiZWcXV4VkgOg1WPIfeOFKj6FyNLu0kifCP44touR/4X11MToQQP/13IWy16ZQKHPeL50a5ItrjskuJFthW/8HMgmrFj8ZEZQyAkndv1LosPTtMT3xNOHqdF5+lkj8MBHZw3jFRUeIjtpOjI7czo3OiJwcml2YXRlIjt9';
		
		$this->publicKey = 'YTozOntpOjA7czoxMjg6IuMO6w7voRTIkooxGDtQe8W+Yv59YYHyoA4TLYCszfmyH0oPyhh/2oYhlWZqIGeaxewVMMGteAU0H0uU4El3K/sWqt3tAPtTLK27ksNCjPi9bv0PW0SySL52FFqavmjETVuHof2CzKltAJTOlMet3zrVfcWEo5/PGGIpxRoRHqWLIjtpOjE7czozOiIBAAEiO2k6MjtzOjY6InB1YmxpYyI7fQ==';
	}
	
	function testGenerateSessionTokenAndverifySignature() {
		$data = time();
		
		$token = $this->cryptoManager->createSessionToken($data, $this->privateKey);

		$this->assertEquals($data, $this->cryptoManager->verifySessionToken($token, $this->privateKey));
	}
	
	function testSessionTokensWithSalt() {
		$data = time();
		
		$tokens = array();
		for($i = 0; $i < 3; $i++) {
			$token = $this->cryptoManager->createSessionToken($data, $this->privateKey);
			$this->assertArrayNotHasKey($token, $tokens);
			$tokens[$token] = 1;
		}
	}
	
	function testSessionTokenVerifySignature() {
		$data = time();
		
		$token = $this->cryptoManager->createSessionToken($data, $this->privateKey);
		list($timestamp, $hash) = explode(':', $token, 2);
		// change data
		$timestamp += 100;
		$token = $timestamp . ':' . $hash;
		$this->assertFalse($this->cryptoManager->verifySessionToken($token, $this->privateKey));
	}

	function testSignAndVerifySignature() {
		$this->markTestSkipped('Skip RSA test for performance reasons');
		
		$data = "this has to be signed";
		$signature = $this->cryptoManager->createSignature($data, $this->privateKey);
		$this->assertTrue($this->cryptoManager->verifySignature($data, $signature, $this->publicKey));
	}

	function testModifiedDocumentDoesntVerifySignature() {
		$this->markTestSkipped('Skip RSA test for performance reasons');
		
		$data = "this has to be signed";
		$signature = $this->cryptoManager->createSignature($data, $this->privateKey);
		$data = "this has been modified";
		$this->assertFalse($this->cryptoManager->verifySignature($data, $signature, $this->publicKey));
	}
	
	function testEncryptDecrypt() {
		$this->markTestSkipped('Skip RSA test for performance reasons');
		
		$data = "top-secret";
		$crypt = $this->cryptoManager->encrypt($data, $this->publicKey);
		$decrypt = $this->cryptoManager->decrypt($crypt, $this->privateKey);
		$this->assertEquals($data, $decrypt);
	}
	
	function testEncryptAJson() {
		$crypt = $this->cryptoManager->encrypt(
			'{"operations":[["foo",{"bar":"fop"}],["lorem",{"ip":"sum"}]]}', 
			$this->publicKey
		);
		$this->assertEquals(
			'UKrckK7W6mUFuQOJbjvYays0k1i70envRfJOAx3SYcyFvnURuSpuC1aeat8gc2/rRi+GN0BEWUJQ8687FPpB3prkE6BjekMN7hMO1UVNalzj48vL9BCbQ2ZTuQX6GC9dhglWgADq3CjNjWWV4gEh5HsBMM+tPkdTz9cgEzmACwI=', 
			$crypt
		);
		// t3lib_div::debug($crypt);
	}
	
	function testDecryptAString() {
		$plain = $this->cryptoManager->decrypt(
			'UKrckK7W6mUFuQOJbjvYays0k1i70envRfJOAx3SYcyFvnURuSpuC1aeat8gc2/rRi+GN0BEWUJQ8687FPpB3prkE6BjekMN7hMO1UVNalzj48vL9BCbQ2ZTuQX6GC9dhglWgADq3CjNjWWV4gEh5HsBMM+tPkdTz9cgEzmACwI=', 
			$this->privateKey
		);
	
		$this->assertEquals(
			'{"operations":[["foo",{"bar":"fop"}],["lorem",{"ip":"sum"}]]}', 
			$plain
		);
	}
	
}
?>