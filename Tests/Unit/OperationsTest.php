<?php
namespace Caretaker\CaretakerInstance\Tests\Unit;

use Caretaker\CaretakerInstance\Tests\Unit\Fixtures\DummyOperation;
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
 * Testcase for Operations
 *
 * @author        Christopher Hlubek <hlubek (at) networkteam.com>
 * @author        Tobias Liebig <liebig (at) networkteam.com>
 */
class OperationsTest extends UnitTestCase
{
    public function testOperationInterface()
    {
        $parameter = array('foo' => 'bar');
        $operation = new DummyOperation();
        $result = $operation->execute($parameter);
        $this->assertInstanceOf('\tx_caretakerinstance_OperationResult', $result);

        $status = $result->isSuccessful();
        $this->assertTrue($status);
        $value = $result->getValue();
        // Value is always an string or array of strings or array of array of strings
        $this->assertEquals('bar', $value);
    }

    public function testOperation_GetFilesystemChecksumReturnsCorrectChecksumForFile()
    {
        $this->markTestSkipped();

        $operation = new \tx_caretakerinstance_Operation_GetFilesystemChecksum();

        $result = $operation->execute(array('path' => 'EXT:caretaker_instance/Tests/Unit/Fixtures/Operation_GetFilesystemChecksum.txt'));

        var_dump($result);
        $this->assertTrue($result->isSuccessful());
        $value = $result->getValue();
        $this->assertInternalType('array', $value);
        $this->assertEquals('0', count($value['singleChecksums']));
        $this->assertInternalType('string', $value['checksum']);
        $this->assertEquals('23d35ef1a611fc75561b0d71d8b3234b', $value['checksum']);
    }

    public function testOperation_GetFilesystemChecksumReturnsExtendedResultForFolder()
    {
        $this->markTestSkipped();

        $operation = new \tx_caretakerinstance_Operation_GetFilesystemChecksum();

        $result = $operation->execute(array(
            'path' => 'EXT:caretaker_instance/Tests/Unit/Fixtures',
            'getSingleChecksums' => true,
        ));

        $this->assertTrue($result->isSuccessful());
        $value = $result->getValue();

        $this->assertInternalType('array', $value);
        $this->assertInternalType('array', $value['singleChecksums']);
        $this->assertInternalType('string', $value['checksum']);
        $this->assertEquals(32, strlen($value['checksum']));
    }

    public function testOperation_GetFilesystemChecksumFailsIfPathIsNotAllowed()
    {
        $this->markTestSkipped();

        $this->fail('test runs indefinitely');
        $operation = new \tx_caretakerinstance_Operation_GetFilesystemChecksum();

        $result = $operation->execute(array('path' => PATH_site . '../../'));

        $this->assertFalse($result->isSuccessful());
    }

    public function testOperation_GetPHPVersion()
    {
        $operation = new \tx_caretakerinstance_Operation_GetPHPVersion();

        $result = $operation->execute();

        $this->assertTrue($result->isSuccessful());

        $this->assertEquals(phpversion(), $result->getValue());
    }

    public function testOperation_GetTYPO3Version()
    {
        $operation = new \tx_caretakerinstance_Operation_GetTYPO3Version();

        $result = $operation->execute();

        $this->assertTrue($result->isSuccessful());

        $this->assertEquals(TYPO3_version, $result->getValue());
    }

    public function testOperation_GetExtensionVersionReturnsExtensionVersionForInstalledExtension()
    {
        $this->markTestSkipped();

        $operation = new \tx_caretakerinstance_Operation_GetExtensionVersion();

        $result = $operation->execute(array('extensionKey' => 'caretaker_instance'));

        $this->assertTrue($result->isSuccessful());

        // TODO This depends on the current caretaker_instance extension version! Better mock this up.
        $this->assertEquals('0.3.3', $result->getValue());
    }

    public function testOperation_GetExtensionVersionReturnsFailureForNotLoadedExtension()
    {
        $operation = new \tx_caretakerinstance_Operation_GetExtensionVersion();

        $result = $operation->execute(array('extensionKey' => 'not_loaded_extension'));

        $this->assertFalse($result->isSuccessful());
    }

    public function testOperation_GetExtensionListFailsIfNoLocationListIsGiven()
    {
        $operation = new \tx_caretakerinstance_Operation_GetExtensionList();

        $result = $operation->execute();

        $this->assertFalse($result->isSuccessful());
    }

    public function testOperation_GetExtensionListReturnsAnArrayOfExtensions()
    {
        $operation = new \tx_caretakerinstance_Operation_GetExtensionList();

        $result = $operation->execute(array('locations' => array('global', 'local', 'system')));

        $this->assertTrue($result->isSuccessful());
        $this->assertGreaterThan(0, count($result->getValue()));
    }

    public function testOperation_GetRecordFindsAndCleansARecord()
    {
        $this->markTestSkipped('FIXME this test is tied to a specific record uid');

        $operation = new \tx_caretakerinstance_Operation_GetRecord();

        // FIXME this test is tied to a specific record uid

        $result = $operation->execute(array('table' => 'be_users', 'field' => 'uid', 'value' => 1));

        $record = $result->getValue();

        $this->assertTrue($result->isSuccessful());

        $this->assertEquals($record['uid'], 1);

        $this->assertTrue(!isset($record['password']));
    }

    public function testOperation_MatchPredefinedVariableReturnsTrueIfValueMatch()
    {
        $GLOBALS['Foo']['bar'] = 'baz';
        $key = 'GLOBALS|Foo|bar';
        $operation = new \tx_caretakerinstance_Operation_MatchPredefinedVariable();

        $result = $operation->execute(array(
                'key' => $key,
                'match' => $GLOBALS['Foo']['bar'],
            )
        );
        $this->assertTrue($result->isSuccessful());
    }

    public function testOperation_MatchPredefinedVariableReturnsTrueIfValueMatchUsingRegexp()
    {
        $GLOBALS['Foo']['bar'] = 'baz';
        $key = 'GLOBALS|Foo|bar';
        $operation = new \tx_caretakerinstance_Operation_MatchPredefinedVariable();

        $result = $operation->execute(array(
                'key' => $key,
                'match' => '/baz/',
                'usingRegexp' => true,
            )
        );

        $this->assertTrue($result->isSuccessful());
    }

    public function testOperation_MatchPredefinedVariableReturnsFalseIfValueDoesNotMatch()
    {
        $GLOBALS['Foo']['bar'] = 'anyValue';
        $key = 'GLOBALS|Foo|bar';
        $operation = new \tx_caretakerinstance_Operation_MatchPredefinedVariable();

        $result = $operation->execute(array(
                'key' => $key,
                'match' => 'an other value',
            )
        );

        $this->assertFalse($result->isSuccessful());
    }

    public function testOperation_CheckPathExistsReturnsTrueIfPathExists()
    {
        $this->markTestSkipped();

        $operation = new \tx_caretakerinstance_Operation_CheckPathExists();

        $result = $operation->execute('EXT:caretaker_instance/Tests/Unit/Fixtures/Operation_CheckPathExists.txt');

        $this->assertTrue($result->isSuccessful());
    }

    public function testOperation_CheckPathExistsReturnsFalseIfPathNotExists()
    {
        $this->markTestSkipped();

        $operation = new \tx_caretakerinstance_Operation_CheckPathExists();

        $result = $operation->execute('EXT:caretaker_instance/Tests/Unit/Fixtures/Operation_CheckPathExists_notExisting.txt');

        $this->assertFalse($result->isSuccessful());
    }
}
