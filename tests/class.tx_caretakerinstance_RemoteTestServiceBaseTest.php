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

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
    'caretaker_instance',
    'services/class.tx_caretakerinstance_RemoteTestServiceBase.php'
);


class tx_caretakerinstance_RemoteTestServiceTest_BaseImpl
    extends tx_caretakerinstance_RemoteTestServiceBase
{
}

class tx_caretakerinstance_RemoteTestServiceTest extends tx_phpunit_testcase
{
    /**
     * @var tx_caretakerinstance_RemoteTestService
     */
    protected $rts;

    public function setUp()
    {
        $this->rts = new tx_caretakerinstance_RemoteTestServiceTest_BaseImpl();
    }

    public function testCheckVersionRangeOk()
    {
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.8', // Actual version
                '4.6.0', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            )
        );
    }

    public function testCheckVersionRangeOkExactMin()
    {
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.8', // Actual version
                '4.6.8', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            )
        );
    }

    public function testCheckVersionRangeOkExactMax()
    {
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.8', // Actual version
                '4.6.0', // Minimal allowed version
                '4.6.8' // Maximal allowed version
            )
        );
    }

    public function testCheckVersionRangeMaxDoesNotMatch()
    {
        $this->assertFalse(
            $this->rts->checkVersionRange(
                '4.6.8', // Actual version
                '4.6.0', // Minimal allowed version
                '4.6.7' // Maximal allowed version
            )
        );
    }

    public function testCheckVersionRangeMinDoesNotMatch()
    {
        $this->assertFalse(
            $this->rts->checkVersionRange(
                '4.6.8', // Actual version
                '4.6.9', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            )
        );
    }

    public function testCheckVersionRangeTypeAlpha()
    {
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.0', // Actual version
                '4.6.0alpha1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0 is higher than .0alpha1'
        );
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.0alpha1', // Actual version
                '4.6.0alpha1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0alpha1 == .0alpha1'
        );
        $this->assertFalse(
            $this->rts->checkVersionRange(
                '4.6.0alpha1', // Actual version
                '4.6.0alpha2', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0alpha1 < .0alpha2'
        );
        $this->assertFalse(
            $this->rts->checkVersionRange(
                '4.6.0alpha1', // Actual version
                '4.6.0', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0alpha1 < .0'
        );
    }

    public function testCheckVersionRangeTypeAlphaBeta()
    {
        $this->assertFalse(
            $this->rts->checkVersionRange(
                '4.6.0alpha1', // Actual version
                '4.6.0beta1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0alpha1 < .0beta1'
        );
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.0beta1', // Actual version
                '4.6.0alpha1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0beta1 > .0alpha1'
        );
    }

    public function testCheckVersionRangeTypeRc()
    {
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.0rc1', // Actual version
                '4.6.0beta1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0rc1 > .0beta1'
        );
        $this->assertFalse(
            $this->rts->checkVersionRange(
                '4.6.0alpha1', // Actual version
                '4.6.0rc1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0alpha1 < .0rc1'
        );
        $this->assertTrue(
            $this->rts->checkVersionRange(
                '4.6.0', // Actual version
                '4.6.0rc1', // Minimal allowed version
                '4.6.99' // Maximal allowed version
            ),
            '.0 > .0rc1'
        );
    }

}
?>
