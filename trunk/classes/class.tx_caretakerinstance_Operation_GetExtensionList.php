<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Martin Ficzel (ficzel@work.de)
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
class tx_caretakerinstance_Operation_GetExtensionList implements tx_caretakerinstance_IOperation {
	/**
	 * @param array $parameter None
	 * @return The extension version
	 */
	public function execute($parameter = array()) {

		
		$result = array();
		
		$this->getPathExtensionList(&$result, PATH_typo3.'sysext/'  , 'system');
		$this->getPathExtensionList(&$result, PATH_typo3.'ext/'     , 'global');
		$this->getPathExtensionList(&$result, PATH_typo3conf.'ext/' , 'local' );
		
		return new tx_caretakerinstance_OperationResult(TRUE, $result );
		
	}
	
	function getPathExtensionList(&$extensionInfo, $path, $scope)	{

		if (@is_dir($path))	{
			$extensionList = t3lib_div::get_dirs($path);
			if (is_array($extensionList))	{
				//$old = $GLOBALS['_EXTKEY'];
				foreach($extensionList as $extKey)	{
						// is installed
					$extensionInfo[$extKey]['isLoaded'] = (boolean)t3lib_extMgm::isLoaded($extKey);
						// get Version
					if (@is_file($path.$extKey.'/ext_emconf.php') )	{
						$_EXTKEY = $extKey;
						@include($path.$extKey.'/ext_emconf.php');
						if($EM_CONF[$extKey]['version']) {
							$extensionInfo[$extKey]['version'] = $EM_CONF[$extKey]['version'];
							$extensionInfo[$extKey]['scope'][$scope] = $EM_CONF[$extKey]['version'];
						}
					}
				}
			}
		}
	}
}
?>
