<?php
namespace Caretaker\CaretakerInstance\Tests\Unit;

use TYPO3\CMS\Core\Tests\UnitTestCase;

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
 * @author        Martin Ficzel <ficzel@work.de>
 */
class ServicesTest extends UnitTestCase
{
    public function testFindInsecureExtensionCommand()
    {
        $this->markTestSkipped();
        $stub = $this->getMock(
            '\tx_caretakerinstance_FindInsecureExtensionTestService',
            array('getLocationList', 'executeRemoteOperations', 'checkExtension')
        );

        $stub->expects($this->once())
            ->method('getLocationList')
            ->with()
            ->will($this->returnValue(array('local')));

        $stub->expects($this->once())
            ->method('executeRemoteOperations')
            ->with($this->equalTo(array(array('GetExtensionList', array('locations' => array('local'))))))
            ->will($this->returnValue(
                new \tx_caretakerinstance_CommandResult(
                    true,
                    array(
                        new \tx_caretakerinstance_OperationResult(
                            true,
                            array(
                                'tt_address' => array(
                                    'isInstalled' => true,
                                    'version' => '2.1.4',
                                    'location' => array('local'),
                                ),
                            )
                        ),
                    )
                )
            )
            );

        $stub->expects($this->once())
            ->method('checkExtension')
            ->with()
            ->will($this->returnValue(true));

        $result = $stub->runTest();

        $this->assertInstanceOf('\tx_caretaker_TestResult', $result);
        $this->assertEquals(\tx_caretaker_Constants::state_ok, $result->getState());
    }

    public function providerFindInsecureExtensionGetLocationList()
    {
        return array(
            array(1, array('system')),
            array(2, array('global')),
            array(4, array('local')),
            array(3, array('system', 'global')),
            array(6, array('global', 'local')),
        );
    }

    /**
     * @dataProvider providerFindInsecureExtensionGetLocationList
     * @param mixed $input
     * @param mixed $output
     */
    public function testFindInsecureExtensionGetLocationList($input, $output)
    {
        $this->markTestSkipped();

        $stub = $this->getMock(
            '\tx_caretakerinstance_FindInsecureExtensionTestService',
            array('getConfigValue')
        );

        $stub->expects($this->once())
            ->method('getConfigValue')
            ->with()
            ->will($this->returnValue($input));

        $this->assertEquals($output, $stub->getLocationList());
    }
}
