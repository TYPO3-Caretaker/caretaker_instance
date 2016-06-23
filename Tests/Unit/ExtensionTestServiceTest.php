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
class ExtensionTestServiceTest extends UnitTestCase
{
    public function testExtensionMustNotBeInstalledForRequirementNone()
    {
        $this->markTestSkipped();

        $service = new \tx_caretakerinstance_ExtensionTestService();
        $result = $service->checkVersionForRequirementAndVersionRange(
                '', // Actual version
                'none', // Requirement mode
                '1.2', // Minimal allowed version
                '' // Maximal allowed version
        );
        $this->assertTrue($result);
    }

    public function testExtensionVersionHasToBeInVersionRangeIfVersionGiven()
    {
        $this->markTestSkipped();

        $service = new \tx_caretakerinstance_ExtensionTestService();
        $result = $service->checkVersionForRequirementAndVersionRange(
                '1.3.1', // Actual version
                'none', // Requirement mode
                '1.2.0', // Minimal allowed version
                '' // Maximal allowed version
        );
        $this->assertTrue($result);

        $result = $service->checkVersionForRequirementAndVersionRange(
                '1.3.1', // Actual version
                'none', // Requirement mode
                '1.2.0', // Minimal allowed version
                '1.5.3' // Maximal allowed version
        );
        $this->assertTrue($result);

        $result = $service->checkVersionForRequirementAndVersionRange(
                '2.0.1', // Actual version
                'none', // Requirement mode
                '1.8.9', // Minimal allowed version
                '2.2.0' // Maximal allowed version
        );
        $this->assertTrue($result);

        $result = $service->checkVersionForRequirementAndVersionRange(
                '1.1.4', // Actual version
                'none', // Requirement mode
                '1.2.5', // Minimal allowed version
                '' // Maximal allowed version
        );
        $this->assertTrue($result !== true);

        $result = $service->checkVersionForRequirementAndVersionRange(
                '1.5.7', // Actual version
                'none', // Requirement mode
                '1.2.8', // Minimal allowed version
                '1.4.18' // Maximal allowed version
        );
        $this->assertTrue($result !== true);
    }

    public function testActualExtensionVersionSuffixIsIgnored()
    {
        $this->markTestSkipped();

        $service = new \tx_caretakerinstance_ExtensionTestService();
        $result = $service->checkVersionForRequirementAndVersionRange(
                '1.5.7_mod', // Actual version
                'none', // Requirement mode
                '1.5.8', // Minimal allowed version
                '1.6.0' // Maximal allowed version
        );
        $this->assertTrue($result !== true);
    }
}