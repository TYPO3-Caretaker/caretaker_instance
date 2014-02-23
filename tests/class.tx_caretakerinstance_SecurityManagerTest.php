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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_SecurityManager.php'));

/**
 * Testcase for the SecurityManager
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_SecurityManagerTest extends tx_phpunit_testcase {

	protected $cryptoManager;

	/**
	 * @var tx_caretakerinstance_ISecurityManager
	 */
	protected $securityManager;

	/**
	 * @var tx_caretakerinstance_CommandRequest
	 */
	protected $commandRequest;

	function setUp() {

		$this->cryptoManager = $this->getMock('tx_caretakerinstance_ICryptoManager');

		$this->securityManager = new tx_caretakerinstance_SecurityManager($this->cryptoManager);
		$this->securityManager->setPrivateKey('FakePrivateKey');
		$this->securityManager->setClientPublicKey('FakeClientPublicKey');

		$this->commandRequest = new tx_caretakerinstance_CommandRequest(
			array(
				'session_token' => '12345:abcdefg',
				'client_info' => array(
					'host_address' => '192.168.10.100'
				),
				'data' => array(
					// Unpacked from raw data.
					'operations' => array(
						array('mock', array('foo' => 'bar')),
						array('mock', array('foo' => 'bar'))
					),
					// Fake crypted JSON
					'encrypted' => 'xxer4rt34x'
				),
				// Data in JSON raw (fake)
				'raw' => '{"foo": "bar"}',
				// Signature over raw data and session token sent from client
				'signature' => 'abcdefg'
			));
	}

	function testCreateSessionToken() {
		$this->cryptoManager->expects($this->once())
			->method('createSessionToken')
			->with($this->equalTo(time()), $this->equalTo('FakePrivateKey'))
			->will($this->returnValue('me_is_a_token'));

		$token = $this->securityManager->createSessionToken('192.168.10.100');
		$this->assertEquals('me_is_a_token', $token);
	}

	function testClientRestrictionForSessionTokenCreation() {
		$this->securityManager->setClientHostAddressRestriction('192.168.10.200');

		$this->cryptoManager->expects($this->never())
			->method('createSessionToken');

		$token = $this->securityManager->createSessionToken('192.168.10.100');
		$this->assertFalse($token);
	}

	function testDecodeRequest() {
		$this->cryptoManager->expects($this->once())
			->method('decrypt')
			->with($this->equalTo('xxer4rt34x'), $this->equalTo('FakePrivateKey'))
			->will($this->returnValue('{"secret": "top-secret"}'));

		$this->assertTrue($this->securityManager->decodeRequest($this->commandRequest));

		$data = $this->commandRequest->getData();
		$this->assertEquals($data['foo'], 'bar', 'Plain JSON data was decoded');
		$this->assertEquals($data['secret'], 'top-secret', 'Encrypted JSON data was decoded');
	}

	function testDecodeInvalidEncryptedRequest() {
		$this->cryptoManager->expects($this->once())
			->method('decrypt')
			->will($this->returnValue(false));

		$this->assertFalse($this->securityManager->decodeRequest($this->commandRequest));
	}

	function testValidateValidRequest() {
		$this->cryptoManager->expects($this->once())
			->method('verifySessionToken')
			->with($this->equalTo('12345:abcdefg'), $this->equalTo('FakePrivateKey'))
			->will($this->returnValue(time() - 1));

		$this->cryptoManager->expects($this->any())
			->method('verifySignature')
			->will($this->returnValue(true));

		$this->assertTrue($this->securityManager->validateRequest($this->commandRequest));
	}

	function testValidateExpiredRequest() {
		$this->cryptoManager->expects($this->once())
			->method('verifySessionToken')
			->with($this->equalTo('12345:abcdefg'), $this->equalTo('FakePrivateKey'))
			->will($this->returnValue(time() - ($this->securityManager->getSessionTokenExpiration() + 1)));

		$this->cryptoManager->expects($this->any())
			->method('verifySignature')
			->will($this->returnValue(true));

		$this->assertFalse($this->securityManager->validateRequest($this->commandRequest));
	}

	function testClientRestrictionForRequestValidation() {
		$this->securityManager->setClientHostAddressRestriction('192.168.10.200');

		$this->cryptoManager->expects($this->once())
			->method('verifySessionToken')
			->will($this->returnValue(time() - 1));

		$this->cryptoManager->expects($this->any())
			->method('verifySignature')
			->will($this->returnValue(true));

		$this->assertFalse($this->securityManager->validateRequest($this->commandRequest));
	}

	function testValidationVerifiesSignature() {
		$this->cryptoManager->expects($this->any())
			->method('verifySessionToken')
			->will($this->returnValue(time() - 1));

		$this->cryptoManager->expects($this->once())
			->method('verifySignature')
			// Verify session token and raw data
			->with($this->equalTo('12345:abcdefg${"foo": "bar"}'),
				$this->equalTo('abcdefg'),
				$this->equalTo('FakeClientPublicKey'))
			->will($this->returnValue(true));

		$this->assertTrue($this->securityManager->validateRequest($this->commandRequest));
	}

	function testWrongSignatureDoesntValidate() {
		$this->cryptoManager->expects($this->any())
			->method('verifySessionToken')
			->will($this->returnValue(time() - 1));

		$this->cryptoManager->expects($this->any())
			->method('verifySignature')
			->will($this->returnValue(false));

		$this->assertFalse($this->securityManager->validateRequest($this->commandRequest));
	}

	function testEncodeResultEncodesStringWithClientPublicKey() {
		$this->cryptoManager->expects($this->once())
			->method('encrypt')
			->with($this->equalTo('My result data'), $this->equalTo('FakeClientPublicKey'))
			->will($this->returnValue('Encoded result'));

		$encodedResult = $this->securityManager->encodeResult('My result data');
		$this->assertEquals('Encoded result', $encodedResult);
	}

	function testEncodeResultDecodesStringWithPrivateKey() {
		$this->cryptoManager->expects($this->once())
			->method('decrypt')
			->with($this->equalTo('Encoded result'), $this->equalTo('FakePrivateKey'))
			->will($this->returnValue('My result data'));

		$encodedResult = $this->securityManager->decodeResult('Encoded result');
		$this->assertEquals('My result data', $encodedResult);
	}
}
?>