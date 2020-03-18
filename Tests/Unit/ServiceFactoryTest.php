<?php
namespace Caretaker\CaretakerInstance\Tests\Unit;

use Caretaker\CaretakerInstance\Tests\Unit\Fixtures\DummyOperation;
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
 * Testcase for the ServiceFactory
 *
 * @author        Christopher Hlubek <hlubek (at) networkteam.com>
 * @author        Tobias Liebig <liebig (at) networkteam.com>
 */
class ServiceFactoryTest extends UnitTestCase
{
    public function testCommandServiceFactory()
    {
        // Simulate TYPO3 ext conf

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['caretaker_instance'] = array(
            'crypto' => array(
                'instance' => array(
                    'publicKey' => 'FakePublicKey',
                    'privateKey' => 'FakePrivateKey',
                ),
                'client' => array(
                    'publicKey' => 'FakeClientPublicKey',
                ),
            ),
            'security' => array(
                'clientHostAddressRestriction' => '10.0.0.1',
            ),
        );

        $factory = \tx_caretakerinstance_ServiceFactory::getInstance();
        $commandService = $factory->getCommandService();

        $this->assertInstanceOf('\tx_caretakerinstance_CommandService', $commandService);

        $securityManager = $factory->getSecurityManager();

        $this->assertInstanceOf('\tx_caretakerinstance_SecurityManager', $securityManager);

        // Test that properties have been set from extConf
        $this->assertEquals('FakePublicKey', $securityManager->getPublicKey());
        $this->assertEquals('FakePrivateKey', $securityManager->getPrivateKey());
        $this->assertEquals('FakeClientPublicKey', $securityManager->getClientPublicKey());
        $this->assertEquals('10.0.0.1', $securityManager->getClientHostAddressRestriction());
    }

    public function testOperationClassRegistrationByConfVars()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'] = array(
            'dummy' => 'Caretaker\CaretakerInstance\Tests\Unit\Fixtures\DummyOperation',
        );
        $factory = \tx_caretakerinstance_ServiceFactory::getInstance();
        $operationManager = $factory->getOperationManager();

        $result = $operationManager->executeOperation('dummy', array('foo' => 'bar'));

        $this->assertEquals('bar', $result->getValue());
    }

    public function testOperationInstanceRegistrationByConfVars()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['caretaker_instance']['operations'] = array(
            'dummyInstance' => new DummyOperation(),
        );
        $factory = \tx_caretakerinstance_ServiceFactory::getInstance();
        $operationManager = $factory->getOperationManager();

        $result = $operationManager->executeOperation('dummyInstance', array('foo' => 'bar'));

        $this->assertEquals('bar', $result->getValue());
    }

    public function testRemoteCommandConnector()
    {
        $factory = \tx_caretakerinstance_ServiceFactory::getInstance();
        $connector = $factory->getRemoteCommandConnector();

        $this->assertInstanceOf('\tx_caretakerinstance_RemoteCommandConnector', $connector);
    }

    public function tearDown()
    {
        // Destroy Service Factory singleton after each test
        \tx_caretakerinstance_ServiceFactory::destroy();
    }
}
