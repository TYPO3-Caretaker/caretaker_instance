<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_FindInsecureExtensionTestService.php'));

/**
 * Testcase for the ServiceFactory
 *
 * @author		Martin Ficzel <ficzel@work.de> 
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_Services_testcase extends tx_phpunit_testcase {

	public function testFindInsecureExtensionCommand (){
		
		$stub = $this->getMock(
			'tx_caretakerinstance_FindInsecureExtensionTestService',
			array('getLocationList','executeRemoteOperations','checkExtension')
		);
		
		$stub->expects($this->once())
			->method('getLocationList')
			->with()
			->will($this->returnValue(array('local')));
			 
		$stub->expects($this->once())
			->method('executeRemoteOperations')
			->with($this->equalTo (array(array('GetExtensionList', array('locations'=>array('local'))))))
			->will($this->returnValue( 
				new tx_caretakerinstance_CommandResult(
					TRUE, 
					array(
						new tx_caretakerinstance_OperationResult(
							true, 
							array('tt_address'=>array('isInstalled'=>true, 'version'=>'2.1.4', 'location'=>array('local'))) 
						) 
					) 
				) 
			) 
		); 

		$stub->expects($this->once())
			->method('checkExtension')
			->with()
			->will($this->returnValue( true ) );
			
		$result = $stub->runTest();
		
		$this->assertType('tx_caretaker_TestResult',$result);
		$this->assertEquals(tx_caretaker_Constants::state_ok,$result->getState());
		
	}
	
	public function providerFindInsecureExtensionGetLocationList (){
		return array(
			array( 1, array('system') ),
			array( 2, array('global') ),
			array( 4, array('local') ),
			array( 3, array('system','global') ),
			array( 6, array('global','local') ),
		);
	}
	
	/** 
     * @dataProvider providerFindInsecureExtensionGetLocationList
     */ 
	public function testFindInsecureExtensionGetLocationList ($input, $output){
		
		$stub = $this->getMock(
			'tx_caretakerinstance_FindInsecureExtensionTestService',
			array('getConfigValue')
		);
		
		$stub->expects($this->once())
			->method('getConfigValue')
			->with()
			->will($this->returnValue($input)); 
			
		$this->assertEquals( $output, $stub->getLocationList() );

	}
	
	

	
}
?>