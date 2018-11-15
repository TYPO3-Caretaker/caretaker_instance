<?php
namespace Caretaker\CaretakerInstance\Tests\Unit;

use Nimut\TestingFramework\TestCase\UnitTestCase;

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

/**
 * Testcase for the RemoteCommandConnector
 *
 * @author        Christopher Hlubek <hlubek (at) networkteam.com>
 * @author        Tobias Liebig <liebig (at) networkteam.com>
 */
class RemoteCommandConnectorTest extends UnitTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\tx_caretakerinstance_ISecurityManager
     */
    protected $securityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\tx_caretakerinstance_ICryptoManager
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

    public function setUp()
    {
        $this->securityManager = $this->getMock('\tx_caretakerinstance_ISecurityManager');
        $this->cryptoManager = $this->getMock('\tx_caretakerinstance_ICryptoManager');

        $this->privateKey = 'YTozOntpOjA7czo4OiLphXB4HJXTjyI7aToxO3M6ODoidb0GlUnBGmgiO2k6MjtzOjc6InByaXZhdGUiO30=';
        $this->publicKey = 'YTozOntpOjA7czo4OiLphXB4HJXTjyI7aToxO3M6MzoiAQABIjtpOjI7czo2OiJwdWJsaWMiO30=';
    }

    public function testExecuteOperationsReturnsValidCommandResult()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\tx_caretaker_InstanceNode $instance */
        $instance = $this->getMock('\tx_caretaker_InstanceNode', array('getUrl', 'getPublicKey'), array(), '', false);
        $instance->expects($this->atLeastOnce())->method('getUrl')->will($this->returnValue('http:://foo.bar/'));
        $instance->expects($this->atLeastOnce())->method('getPublicKey')->will($this->returnValue('publicKey'));

        // Mock the http/curl request
        /** @var \PHPUnit_Framework_MockObject_MockObject|\tx_caretakerinstance_RemoteCommandConnector $connector */
        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('requestSessionToken', 'buildCommandRequest', 'executeRequest'),
            array($this->cryptoManager, $this->securityManager)
        );
        $request = $this->getMock(
            '\tx_caretakerinstance_CommandRequest',
            array('setSignature'),
            array(array())
        );
        $exceptedResult = new \tx_caretakerinstance_CommandResult(true, array('foo' => 'bar'), 'foobar');

        $connector->expects($this->once())->method('requestSessionToken')->will($this->returnValue('SessionToken'));
        $connector->expects($this->once())->method('buildCommandRequest')->will($this->returnValue($request));
        $connector->expects($this->once())->method('executeRequest')->with($this->isInstanceOf('\tx_caretakerinstance_CommandRequest'))->will($this->returnValue($exceptedResult));
        $request->expects($this->once())->method('setSignature');

        $connector->setInstance($instance);
        $result = $connector->executeOperations(array('foo' => 'bar'));

        $this->assertInstanceOf('\tx_caretakerinstance_CommandResult', $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals($exceptedResult, $result);
    }

    public function testExecuteOperationsReturnsFalseResultIfSessionTokenIsInvalid()
    {
        $this->markTestSkipped();

        $instance = $this->getMock('\tx_caretaker_InstanceNode');
        $instance->expects($this->any())->method('getUrl')->will($this->returnValue('http:://foo.bar/'));
        $instance->expects($this->any())->method('getPublicKey')->will($this->returnValue('publicKey'));

        // Mock the http/curl request
        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('requestSessionToken', 'executeRequest'),
            array($this->cryptoManager, $this->securityManager)
        );

        $connector->setInstance($instance);
        $connector->expects($this->once())->method('requestSessionToken')->will($this->throwException(new \tx_caretakerinstance_RequestSessionTokenFailedException()));
        $connector->expects($this->never())->method('executeRequest');

        $result = $connector->executeOperations(array('foo' => 'bar'));

        $this->assertInstanceOf('\tx_caretakerinstance_CommandResult', $result);
        $this->assertFalse($result->isSuccessful());
    }

    public function testExecuteOperationsReturnsFalseResultIfURLMissing()
    {
        $this->markTestSkipped();

        $instance = $this->getMock('\tx_caretaker_InstanceNode');
        $instance->expects($this->any())->method('getUrl')->will($this->returnValue(''));
        $instance->expects($this->any())->method('getPublicKey')->will($this->returnValue('publicKey'));

        $connector = new \tx_caretakerinstance_RemoteCommandConnector($this->cryptoManager, $this->securityManager);
        $connector->setInstance($instance);

        $result = $connector->executeOperations(array('foo' => 'bar'));

        $this->assertInstanceOf('\tx_caretakerinstance_CommandResult', $result);
        $this->assertFalse($result->isSuccessful());
    }

    public function testGetCommandRequestCreatesValidEncryptedCommandRequest()
    {
        $this->markTestSkipped();

        $connector = new \tx_caretakerinstance_RemoteCommandConnector($this->cryptoManager, $this->securityManager);

        $this->cryptoManager->expects($this->once())->method('encrypt')->will($this->returnValue('encryptedString'));

        $request = $connector->buildCommandRequest('sessionToken', 'publicKey', 'http://foo.barr/', 'rawData');

        $this->assertInstanceOf('\tx_caretakerinstance_CommandRequest', $request);
        $this->assertEquals('sessionToken', $request->getSessionToken());
        $this->assertEquals('publicKey', $request->getServerKey());
        $this->assertEquals('http://foo.barr/', $request->getServerUrl());
        $this->assertEquals('{"encrypted":"encryptedString"}', $request->getRawData());
        $this->assertEquals('{"encrypted":"encryptedString"}', $request->getData());
    }

    public function testRequestSessionTokenReturnsValidToken()
    {
        $this->markTestSkipped();

        $url = 'http://foo.bar/';
        $fakeSessionToken = '1242475687:d566026bfd3aa7d2d5de8a70ea525a0c4c578cdc45b8';

        $instance = $this->getMock('\tx_caretaker_InstanceNode');
        $instance->expects($this->any())->method('getUrl')->will($this->returnValue($url));
        $instance->expects($this->any())->method('getPublicKey')->will($this->returnValue('publicKey'));

        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('executeHttpRequest'),
            array($this->cryptoManager, $this->securityManager)
        );

        $connector->expects($this->once())->method('executeHttpRequest')
            ->with($this->equalTo($url . '?eID=\tx_caretakerinstance&rst=1'))
            ->will($this->returnValue(array(
                'response' => $fakeSessionToken,
                'info' => array('http_code' => 200),
            ))
            );

        $connector->setInstance($instance);
        $sessionToken = $connector->requestSessionToken();

        $this->assertEquals($fakeSessionToken, $sessionToken);
    }

    public function testRequestSessionTokenThrowsExceptionWithInvalidToken()
    {
        $this->markTestSkipped();

        $url = 'http://foo.bar/';
        $fakeSessionToken = '==invalidtoken==';

        $instance = $this->getMock('\tx_caretaker_InstanceNode');
        $instance->expects($this->any())->method('getUrl')->will($this->returnValue($url));
        $instance->expects($this->any())->method('getPublicKey')->will($this->returnValue('publicKey'));

        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('executeHttpRequest'),
            array($this->cryptoManager, $this->securityManager)
        );

        $connector->expects($this->once())
            ->method('executeHttpRequest')
            ->with($this->equalTo($url . '?eID=\tx_caretakerinstance&rst=1'))
            ->will($this->returnValue(array(
                'response' => $fakeSessionToken,
                'info' => array('http_code' => 200),
            ))
            );

        $connector->setInstance($instance);

        try {
            $connector->requestSessionToken();
            $this->fail("requestSessionToken should throw an \tx_caretakerinstance_RequestSessionTokenFailedException exception");
        } catch (\tx_caretakerinstance_RequestSessionTokenFailedException $e) {
            // ok
        } catch (Exception $e) {
            $this->fail("requestSessionToken should throw an \tx_caretakerinstance_RequestSessionTokenFailedException exception");
        }
    }

    public function testRequestSessionTokenThrowsExceptionIfHttpRequestFails()
    {
        $this->markTestSkipped();

        $url = 'http://foo.bar/';
        $fakeSessionToken = '1242475687:d566026bfd3aa7d2d5de8a70ea525a0c4c578cdc45b8';

        $instance = $this->getMock('\tx_caretaker_InstanceNode');
        $instance->expects($this->any())->method('getUrl')->will($this->returnValue($url));
        $instance->expects($this->any())->method('getPublicKey')->will($this->returnValue('publicKey'));

        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('executeHttpRequest'),
            array($this->cryptoManager, $this->securityManager)
        );

        $connector->expects($this->once())->method('executeHttpRequest')
            ->with($this->equalTo($url . '?eID=\tx_caretakerinstance&rst=1'))
            ->will($this->returnValue(array(
                'response' => $fakeSessionToken,
                'info' => array('http_code' => 404),
            ))
            );

        $connector->setInstance($instance);

        try {
            $connector->requestSessionToken();
            $this->fail("requestSessionToken should throw an \tx_caretakerinstance_RequestSessionTokenFailedException exception");
        } catch (\tx_caretakerinstance_RequestSessionTokenFailedException $e) {
            // ok
        } catch (Exception $e) {
            $this->fail("requestSessionToken should throw an \tx_caretakerinstance_RequestSessionTokenFailedException exception");
        }
    }

    public function testGetRequestSignature()
    {
        $request = $this->getMock(
            '\tx_caretakerinstance_CommandResult',
            array('getDataForSignature'),
            array(array())
        );
        // FIXME: interface f�r CommandResult?

        $request->expects($this->once())->method('getDataForSignature')->will($this->returnValue('==SomeData=='));
        $this->cryptoManager->expects($this->once())->method('createSignature')->with()->will($this->returnValue('==aSignature=='));

        $connector = new \tx_caretakerinstance_RemoteCommandConnector($this->cryptoManager, $this->securityManager);

        $signature = $connector->getRequestSignature($request);

        $this->assertEquals('==aSignature==', $signature);
    }

    public function testExecuteRequestCreatesValidCommandResult()
    {
        $url = 'http://foo.bar/';

        $request = $this->getMock(
            '\tx_caretakerinstance_CommandResult',
            array('getSessionToken', 'getData', 'getSignature', 'getServerUrl'),
            array(array())
        );

        $request->expects($this->once())->method('getSessionToken')->will($this->returnValue('==sessionToken=='));
        $request->expects($this->once())->method('getData')->will($this->returnValue('==data=='));
        $request->expects($this->once())->method('getSignature')->will($this->returnValue('==Signature=='));
        $request->expects($this->once())->method('getServerUrl')->will($this->returnValue($url));

        $this->securityManager->expects($this->once())->method('decodeResult')->with($this->equalTo('==encryptedString=='))->will($this->returnValue('{"status":true,"results":[{"status":true,"value":"foo"},{"status":true,"value":false},{"status":true,"value":["foo","bar"]}],"message":"Test message"}'));

        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('executeHttpRequest'),
            array($this->cryptoManager, $this->securityManager)
        );

        // Mock session token request
        $connector->expects($this->once())->method('executeHttpRequest')
            ->with(
                $this->equalTo($url),
                $this->equalTo(array(
                    'd' => '==data==',
                    'st' => '==sessionToken==',
                    's' => '==Signature==',
                ))
            )->will($this->returnValue(
                array(
                    'response' => '==encryptedString==',
                    'info' => array('http_code' => 200),
                )
            )
            );

        $result = $connector->executeRequest($request);

        $this->assertInstanceOf('\tx_caretakerinstance_CommandResult', $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('Test message', $result->getMessage());
        $this->assertEquals(array(
            new \tx_caretakerinstance_OperationResult(true, 'foo'),
            new \tx_caretakerinstance_OperationResult(true, false),
            new \tx_caretakerinstance_OperationResult(true, array('foo', 'bar')),
        ), $result->getOperationResults());
    }

    public function testExecuteRequestReturnsFalseCommandResultOnFailure()
    {
        $url = 'http://foo.bar/';

        $request = $this->getMock(
            '\tx_caretakerinstance_CommandResult',
            array('getSessionToken', 'getData', 'getSignature', 'getServerUrl'),
            array(array())
        );

        $request->expects($this->once())->method('getSessionToken')->will($this->returnValue('==sessionToken=='));
        $request->expects($this->once())->method('getData')->will($this->returnValue('==data=='));
        $request->expects($this->once())->method('getSignature')->will($this->returnValue('==Signature=='));
        $request->expects($this->once())->method('getServerUrl')->will($this->returnValue($url));

        $connector = $this->getMock(
            '\tx_caretakerinstance_RemoteCommandConnector',
            array('executeHttpRequest'),
            array($this->cryptoManager, $this->securityManager)
        );

        // Mock session token request
        $connector->expects($this->once())->method('executeHttpRequest')
            ->with(
                $this->equalTo($url),
                $this->equalTo(array(
                    'd' => '==data==',
                    'st' => '==sessionToken==',
                    's' => '==Signature==',
                ))
            )->will($this->returnValue(
                array(
                    'response' => 'AnyStringButJson',
                    'info' => array('http_code' => 404),
                )
            )
            );

        $result = $connector->executeRequest($request);

        $this->assertInstanceOf('\tx_caretakerinstance_CommandResult', $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(null, $result->getOperationResults());
    }
}
