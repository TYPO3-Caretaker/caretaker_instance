<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'lib/Crypt/RSA.php'));

class tx_caretakerinstance_RSA_testcase extends tx_phpunit_testcase {

	protected $publicKey = 'YTozOntpOjA7czoxMjg6IuMO6w7voRTIkooxGDtQe8W+Yv59YYHyoA4TLYCszfmyH0oPyhh/2oYhlWZqIGeaxewVMMGteAU0H0uU4El3K/sWqt3tAPtTLK27ksNCjPi9bv0PW0SySL52FFqavmjETVuHof2CzKltAJTOlMet3zrVfcWEo5/PGGIpxRoRHqWLIjtpOjE7czozOiIBAAEiO2k6MjtzOjY6InB1YmxpYyI7fQ==';
	
	protected $privateKey = 'YTozOntpOjA7czoxMjg6IuMO6w7voRTIkooxGDtQe8W+Yv59YYHyoA4TLYCszfmyH0oPyhh/2oYhlWZqIGeaxewVMMGteAU0H0uU4El3K/sWqt3tAPtTLK27ksNCjPi9bv0PW0SySL52FFqavmjETVuHof2CzKltAJTOlMet3zrVfcWEo5/PGGIpxRoRHqWLIjtpOjE7czoxMjg6IlEXhW1Ue6+YYynk8YSaiDfxua/u8KilxlTHiZWcXV4VkgOg1WPIfeOFKj6FyNLu0kifCP44touR/4X11MToQQP/13IWy16ZQKHPeL50a5ItrjskuJFthW/8HMgmrFj8ZEZQyAkndv1LosPTtMT3xNOHqdF5+lkj8MBHZw3jFRUeIjtpOjI7czo3OiJwcml2YXRlIjt9';
	
	function testRSAEncryption() {
		// $keyPair = new Crypt_RSA_KeyPair(1024);
		// $publicKeyString = $keyPair->getPublic()->toString();
		
		$publicKey = Crypt_RSA_Key::fromString($this->publicKey);		
		$privateKey = Crypt_RSA_Key::fromString($this->privateKey);

		
		$rsa = new Crypt_RSA();
		$enc = $rsa->encrypt('top-secret', $publicKey);
		
		$dec = $rsa->decrypt($enc, $privateKey);
		
		$this->assertEquals('top-secret', $dec);
		
	}
}
?>