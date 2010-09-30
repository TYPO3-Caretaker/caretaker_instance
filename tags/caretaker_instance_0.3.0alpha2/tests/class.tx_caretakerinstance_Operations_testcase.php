<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_IOperation.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));
require_once('fixtures/class.tx_caretakerinstance_DummyOperation.php');
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetPHPVersion.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetTYPO3Version.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetExtensionVersion.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetExtensionList.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetRecord.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_GetFilesystemChecksum.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_MatchPredefinedVariable.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_Operation_CheckPathExists.php'));

/**
 * Testcase for Operations
 *
 * @author		Christopher Hlubek <hlubek (at) networkteam.com>
 * @author		Tobias Liebig <liebig (at) networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_Operations_testcase extends tx_phpunit_testcase {
	public function testOperationInterface() {
		$parameter = array('foo' => 'bar');
		$operation = new tx_caretakerinstance_DummyOperation();
		$result = $operation->execute($parameter);
		$this->assertType("tx_caretakerinstance_OperationResult", $result);
		
		$status = $result->isSuccessful();
		$this->assertTrue($status);
		$value = $result->getValue();
		// Value is always an string or array of strings or array of array of strings
		$this->assertEquals('bar', $value);
	}
	
	public function testOperation_GetFilesystemChecksumReturnsCorrectChecksumForFile() {
		$operation = new tx_caretakerinstance_Operation_GetFilesystemChecksum();
		
		$result = $operation->execute(array('path' => 'EXT:caretaker_instance/tests/fixtures/Operation_GetFilesystemChecksum.txt'));
		
		$this->assertTrue($result->isSuccessful());
		$value = $result->getValue();
		$this->assertType('array', $value);
		$this->assertEquals('0', count($value['singleChecksums']));
		$this->assertType('string', $value['checksum']);
		$this->assertEquals('23d35ef1a611fc75561b0d71d8b3234b', $value['checksum']);
	}
	
	public function testOperation_GetFilesystemChecksumReturnsExtendedResultForFolder() {
		$operation = new tx_caretakerinstance_Operation_GetFilesystemChecksum();
		
		$result = $operation->execute(array('path' => 'EXT:caretaker_instance/tests/', 'getSingleChecksums' => true));
		
		$this->assertTrue($result->isSuccessful());
		$value = $result->getValue();
		
		$this->assertType('array', $value);
		$this->assertType('array', $value['singleChecksums']);
		$this->assertType('string', $value['checksum']);
		$this->assertEquals(32, strlen($value['checksum']));
	}
	
	public function testOperation_GetFilesystemChecksumFailsIfPathIsNotAllowed() {
		$operation = new tx_caretakerinstance_Operation_GetFilesystemChecksum();
		
		$result = $operation->execute(array('path' => PATH_site . '../../'));
		
		$this->assertFalse($result->isSuccessful());
	}
	
	public function testOperation_GetPHPVersion() {
		$operation = new tx_caretakerinstance_Operation_GetPHPVersion();
		
		$result = $operation->execute();
		
		$this->assertTrue($result->isSuccessful());
		
		$this->assertEquals(phpversion(), $result->getValue());
	}
	
	public function testOperation_GetTYPO3Version() {
		$operation = new tx_caretakerinstance_Operation_GetTYPO3Version();
		
		$result = $operation->execute();
		
		$this->assertTrue($result->isSuccessful());

		$this->assertEquals(TYPO3_version, $result->getValue());
	}
	
	public function testOperation_GetExtensionVersionReturnsExtensionVersionForInstalledExtension() {
		$operation = new tx_caretakerinstance_Operation_GetExtensionVersion();
		
		$result = $operation->execute(array('extensionKey' => 'caretaker_instance'));
		
		$this->assertTrue($result->isSuccessful());

		// TODO This depends on the current caretaker_instance extension version! Better mock this up.
		$this->assertEquals('0.0.2', $result->getValue());
	}
	
	public function testOperation_GetExtensionVersionReturnsFailureForNotLoadedExtension() {
		$operation = new tx_caretakerinstance_Operation_GetExtensionVersion();
		
		$result = $operation->execute(array('extensionKey' => 'not_loaded_extension'));
		
		$this->assertFalse($result->isSuccessful());
	}
	
	public function testOperation_GetExtensionListFailsIfNoLocationListIsGiven(){
		$operation = new tx_caretakerinstance_Operation_GetExtensionList();
		
		$result = $operation->execute();
		
		$this->assertFalse($result->isSuccessful());
	}

	public function testOperation_GetExtensionListReturnsAnArrayOfExtensions(){
		$operation = new tx_caretakerinstance_Operation_GetExtensionList();
		
		$result = $operation->execute(array('locations' => array('global','local','system')));
				
		$this->assertTrue($result->isSuccessful());
		$this->assertGreaterThan( 0 , count($result->getValue() ) );
	}

	public function testOperation_GetRecordFindsAndCleansARecord() {
		$operation = new tx_caretakerinstance_Operation_GetRecord();
		
		// FIXME this test is tied to a specific record uid
		
		$result = $operation->execute(array('table' => 'be_users', 'field' => 'uid', 'value' => 1));
		
		$record = $result->getValue();
		
		$this->assertTrue($result->isSuccessful());
		
		$this->assertEquals($record['uid'], 1);

		$this->assertTrue(!isset($record['password']));
	}

	public function testOperation_MatchPredefinedVariableReturnsTrueIfValueMatch() {
		$GLOBALS['Foo']['bar'] = 'baz';
		$key = 'GLOBALS|Foo|bar';
		$operation = new tx_caretakerinstance_Operation_MatchPredefinedVariable();

		$result = $operation->execute(array(
			'key' => $key,
			'match' => $GLOBALS['Foo']['bar'],
			)
		);
		$this->assertTrue($result->isSuccessful());
	}

	public function testOperation_MatchPredefinedVariableReturnsTrueIfValueMatchUsingRegexp() {
		$GLOBALS['Foo']['bar'] = 'baz';
		$key = 'GLOBALS|Foo|bar';
		$operation = new tx_caretakerinstance_Operation_MatchPredefinedVariable();

		$result = $operation->execute(array(
			'key' => $key,
			'match' => '/baz/',
			'usingRegexp' => true,
			)
		);

		$this->assertTrue($result->isSuccessful());
	}


	public function testOperation_MatchPredefinedVariableReturnsFalseIfValueDoesNotMatch() {
		$GLOBALS['Foo']['bar'] = 'anyValue';
		$key = 'GLOBALS|Foo|bar';
		$operation = new tx_caretakerinstance_Operation_MatchPredefinedVariable();

		$result = $operation->execute(array(
			'key' => $key,
			'match' => 'an other value',
			)
		);

		$this->assertFalse($result->isSuccessful());
	}
	
	public function testOperation_CheckPathExistsReturnsTrueIfPathExists() {		
		$operation = new tx_caretakerinstance_Operation_CheckPathExists();

		$result = $operation->execute(array(
			'path' => 'EXT:caretaker_instance/tests/fixtures/Operation_CheckPathExists.txt',
			)
		);

		$this->assertTrue($result->isSuccessful());
	}
	
	public function testOperation_CheckPathExistsReturnsFalseIfPathNotExists() {		
		$operation = new tx_caretakerinstance_Operation_CheckPathExists();

		$result = $operation->execute(array(
			'path' => 'EXT:caretaker_instance/tests/fixtures/Operation_CheckPathExists_notExisting.txt',
			)
		);

		$this->assertFalse($result->isSuccessful());
	}
}
?>