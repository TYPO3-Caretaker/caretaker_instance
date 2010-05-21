<?php
class tx_caretakerinstance_ExtensionTestService_testcase extends tx_phpunit_testcase {
	public function testExtensionMustNotBeInstalledForRequirementNone() {
		$service = new tx_caretakerinstance_ExtensionTestService();
		$result = $service->checkVersionForRequirementAndVersionRange(
			'', // Actual version
			'none', // Requirement mode
			'1.2', // Minimal allowed version
			'' // Maximal allowed version
		);
		$this->assertTrue($result);
	}

	public function testExtensionVersionHasToBeInVersionRangeIfVersionGiven() {
		$service = new tx_caretakerinstance_ExtensionTestService();
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
		$this->assertTrue($result !== TRUE);

		$result = $service->checkVersionForRequirementAndVersionRange(
			'1.5.7', // Actual version
			'none', // Requirement mode
			'1.2.8', // Minimal allowed version
			'1.4.18' // Maximal allowed version
		);
		$this->assertTrue($result !== TRUE);
	}

	public function testActualExtensionVersionSuffixIsIgnored() {
		$service = new tx_caretakerinstance_ExtensionTestService();
		$result = $service->checkVersionForRequirementAndVersionRange(
			'1.5.7_mod', // Actual version
			'none', // Requirement mode
			'1.5.8', // Minimal allowed version
			'1.6.0' // Maximal allowed version
		);
		$this->assertTrue($result !== TRUE);
	}
}
?>