<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Christopher Hlubek (hlubek@networkteam.com)
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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));

/**
 * An Operation that returns the version of an installed extension 
 * 
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_Operation_GetExtensionVersion implements tx_caretakerinstance_IOperation {
	/**
	 * @param array $parameter None
	 * @return The extension version
	 */
	public function execute($parameter = array()) {
		$extensionKey = $parameter['extensionKey'];
		
		if (!t3lib_extMgm::isLoaded($extensionKey)) {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Extension [' . $extensionKey . '] is not loaded');
		}
		
		$_EXTKEY = $extensionKey;		
		@include(t3lib_extMgm::extPath($extensionKey, 'ext_emconf.php'));

		if (is_array($EM_CONF[$extensionKey])) {
			return new tx_caretakerinstance_OperationResult(TRUE, $EM_CONF[$extensionKey]['version']);
		} else {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Cannot read EM_CONF for extension [' . $extensionKey . ']');
		}
	}
}
?>
