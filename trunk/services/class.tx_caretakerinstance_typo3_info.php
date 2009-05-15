<?php
/**
 * This is a file of the caretaker project.
 * Copyright 2008 by n@work Internet Informationssystem GmbH (www.work.de)
 * 
 * @Author	Thomas Hempel 		<thomas@work.de>
 * @Author	Martin Ficzel		<martin@work.de>
 * @Author	Patrick Kollodzik	<patrick@work.de>
 * 
 * $$Id: class.tx_caretaker_typo3_version.php 33 2008-06-13 14:00:38Z thomas $$
 */

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Patrick Kollodzik <patrick@work.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_t3lib.'class.t3lib_svbase.php');
require_once(t3lib_extMgm::extPath('caretaker').'/services/interface.tx_caretaker_TestService.php');
require_once(t3lib_extMgm::extPath('caretaker').'/classes/class.tx_caretaker_TestConf.php');
require_once(t3lib_extMgm::extPath('caretaker').'/classes/class.tx_caretaker_TestResult.php');

require_once (t3lib_extMgm::extPath('caretaker').'/services/class.tx_caretaker_TestServiceBase.php');
	
class tx_caretakerinstance_typo3_version extends tx_caretaker_TestServiceBase {
	var $prefixId = 'tx_caretakerinstance_typo3_version';		// Same as class name
	var $scriptRelPath = 'services/class.tx_caretakerinstance_typo3_version.php';	// Path to this script relative to the extension dir.
	var $extKey = 'caretaker_instance';	// The extension key.
	
	public function runTest($flexFormData){
		$testResult = new tx_caretaker_TestResult();
		return $testResult;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/services/class.tx_caretaker_typo3_version.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caretaker/sv1/class.tx_caretaker_version.php']);
}

?>