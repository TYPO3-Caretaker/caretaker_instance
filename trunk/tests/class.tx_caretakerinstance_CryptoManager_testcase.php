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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OpenSSLCryptoManager.php'));

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
	 * @var tx_caretakerinstance_OpenSSLCryptoManager
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
		$this->cryptoManager = new tx_caretakerinstance_OpenSSLCryptoManager();

		$this->publicKey = '-----BEGIN PUBLIC KEY-----|MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDIykCszT87/DU5CVZSjW2KVLvt|zAMtkNKCYG2nWGQ1qRpotgiqVb2Bh+kYSQM7melGnBvD/w0tURTNV+s1ikTtiFuV|upHpigDNW6CdxYPjaTWl156I8t+siU9wcEBDJP3pb5UYjqwmtfrau4c7giDPiBjJ|ffIGIQAH9PalIQ38BwIDAQAB|-----END PUBLIC KEY-----|';
		$this->privateKey = '-----BEGIN PRIVATE KEY-----|MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMjKQKzNPzv8NTkJ|VlKNbYpUu+3MAy2Q0oJgbadYZDWpGmi2CKpVvYGH6RhJAzuZ6UacG8P/DS1RFM1X|6zWKRO2IW5W6kemKAM1boJ3Fg+NpNaXXnojy36yJT3BwQEMk/elvlRiOrCa1+tq7|hzuCIM+IGMl98gYhAAf09qUhDfwHAgMBAAECgYAn8HMk7D6jw+siSUUubotXdLtc|9bO8II5++IdXPjHQqq5iHbNjjmJ/nXU0K3HFLTxFm0+6kMUiOnqUzeQvZi2HZ9dB|Xgd7UWMXZY2IJhiTBOFwX+LUC2fgjvXbsjjXirTh2nKFVM2/z/ne7M0wiAS0rCUt|rbYAuAosy7pt6k3DQQJBAOnHTLgFPsVbsvTiZl9d9LrO8Zev6f0UWJjGokINInd0|1qTy/HdJTB6WBfsWYb5oGR/CuAF+lKcg6Hf4WomDZQ8CQQDb4DiRQuFsTjWlaLf+|1qbbPsZynRXsDi0UTnfFowd4vYjyS2Sbm1II3jcLxQgqNQva6CN/DKZhOmI4Rprv|mqmJAkAbftFLI3LKi4p0utwHg2lxPz2y9YGzvlzdOx+CXUEcg6VrKRkAfqJxRnvV|mEBOwLeTwLcbleOt9HTjB1a+rbGJAkEAqgHfmymcRPLf9epXQfrUfvc1187v8Vow|rt/RKgZZM5lRNw7mVo6symCPLVGGc6Qaa4NMVuMADnNnGF43VAZBCQJBAKKQrh9k|KcwFtKTrewsccZ/JBPRBdOMS+Wj/7jF67hGpxjiQgMslGflGg33NIThRHPj/WEEc|zrTgmvPlLrhkTa8=|-----END PRIVATE KEY-----|';
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
		// $this->markTestSkipped('Skip RSA test for performance reasons');

		$data = "this has to be signed";
		$signature = $this->cryptoManager->createSignature($data, $this->privateKey);
		$this->assertTrue($this->cryptoManager->verifySignature($data, $signature, $this->publicKey));
	}

	function testModifiedDocumentDoesntVerifySignature() {
		// $this->markTestSkipped('Skip RSA test for performance reasons');

		$data = "this has to be signed";
		$signature = $this->cryptoManager->createSignature($data, $this->privateKey);
		$data = "this has been modified";
		$this->assertFalse($this->cryptoManager->verifySignature($data, $signature, $this->publicKey));
	}

	function testEncryptDecrypt() {
		// $this->markTestSkipped('Skip RSA test for performance reasons');

		$data = "top-secret";
		$crypt = $this->cryptoManager->encrypt($data, $this->publicKey);
		$decrypt = $this->cryptoManager->decrypt($crypt, $this->privateKey);
		$this->assertEquals($data, $decrypt);
	}

	function testEncryptAJson() {
		$this->markTestSkipped('Skip encryption test because open SSL salts the data and the result is always different.');

		$crypt = $this->cryptoManager->encrypt(
			'{"operations":[["foo",{"bar":"fop"}],["lorem",{"ip":"sum"}]]}',
			$this->publicKey
		);

		$this->assertEquals(
			'e96A2TuIWwcexcK8f7Dnk6aPRnIQYDdbggXz6vj/JGq9pR2838ZHOb5blMKYSWKTYOmLyuYZ5Qsci0Wrl858hq07lCkF8B6XIHu7MoGWytUAdVZOM0EsF58x9WAMCpkd+/iTThO5G03O0CXMffLFCWCAY4/IVbKHZwfQg8pXIUE=:ZdjiFGXRxwHViSSIVSa0gsRJgWjYy3O+XLp11soRIu9MN0iXf+X7Rg4vYkPZtNpEPGX4oElOR2J1Pnidqw==',
			$crypt
		);
		// t3lib_div::debug($crypt);
	}

	function testDecryptAString() {
		$this->markTestSkipped('Skip encryption test because open SSL salts the data and the result is always different.');

		$plain = $this->cryptoManager->decrypt(
			'e96A2TuIWwcexcK8f7Dnk6aPRnIQYDdbggXz6vj/JGq9pR2838ZHOb5blMKYSWKTYOmLyuYZ5Qsci0Wrl858hq07lCkF8B6XIHu7MoGWytUAdVZOM0EsF58x9WAMCpkd+/iTThO5G03O0CXMffLFCWCAY4/IVbKHZwfQg8pXIUE=:ZdjiFGXRxwHViSSIVSa0gsRJgWjYy3O+XLp11soRIu9MN0iXf+X7Rg4vYkPZtNpEPGX4oElOR2J1Pnidqw==',
			$this->privateKey
		);

		$this->assertEquals(
			'{"operations":[["foo",{"bar":"fop"}],["lorem",{"ip":"sum"}]]}',
			$plain
		);
	}

}
?>