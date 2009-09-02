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

	protected $scopes = array('system', 'global', 'local');

	/**
	 * 
	 * @param array $parameter Array of extension locations as string (system, global, local)
	 * @return The extension list
	 */
	public function execute($parameter = array()) {
		$locations = $parameter['locations'];
		if (is_array($locations) && count($locations) > 0 ) {
			$extensionList = array();
			foreach ($locations as $scope) {
				if (in_array($scope, $this->scopes)) {
					$extensionList = array_merge($extensionList, $this->getExtensionListForScope($scope));
				}
			}
			return new tx_caretakerinstance_OperationResult(TRUE, $extensionList);
		} else {
			return new tx_caretakerinstance_OperationResult(FALSE, 'No extension locations given');
		}
		
	}
	
	protected function getPathForScope($scope) {
		$path = '';
		switch ($scope) {
			case 'system':
				$path = PATH_typo3 . 'sysext/';
				break;
			case 'global':
				$path = PATH_typo3 . 'ext/';
				break;
			case 'local':
			default:
				$path = PATH_typo3conf . 'ext/';
				break;
		}
		return $path;
	}
	
	protected function getExtensionListForScope($scope) {
		$path = $this->getPathForScope($scope);
		$extensionInfo = array();
		if (@is_dir($path))	{
			$extensionFolders = t3lib_div::get_dirs($path);
			if (is_array($extensionFolders)) {
				foreach($extensionFolders as $extKey) {
					$extensionInfo[$extKey]['ext_key'] = $extKey;					
					$extensionInfo[$extKey]['installed'] = (boolean)t3lib_extMgm::isLoaded($extKey);

					if (@is_file($path . $extKey . '/ext_emconf.php'))	{
						$_EXTKEY = $extKey;
						@include($path . $extKey . '/ext_emconf.php');
						$extensionVersion = $EM_CONF[$extKey]['version']; 
					} else {
						$extensionVersion = FALSE;
					}

					if ($extensionVersion) {
						$extensionInfo[$extKey]['version'] = $extensionVersion;
						$extensionInfo[$extKey]['scope'][$scope] = $extensionVersion;
					}					
				}
			}
		}
		
		return $extensionInfo;
	}
}
?>
