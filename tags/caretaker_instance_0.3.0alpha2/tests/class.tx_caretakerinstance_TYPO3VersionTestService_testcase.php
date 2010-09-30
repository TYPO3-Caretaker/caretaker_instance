<?php

require_once(t3lib_extMgm::extPath('caretaker_instance', 'services/class.tx_caretakerinstance_TYPO3VersionTestService.php'));

class tx_caretakerinstance_TYPO3VersionTestService_testcase extends tx_phpunit_testcase {
	public function testVersionWithAlphaIsHigherThanLowerVersions() {
		$service = new tx_caretakerinstance_TYPO3VersionTestService();
		$result = $service->checkVersionRange(
			'4.3.0alpha3', // Actual version
			'4.2.8', // Minimal allowed version
			'' // Maximal allowed version
		);
		$this->assertTrue($result);
	}
}
?>