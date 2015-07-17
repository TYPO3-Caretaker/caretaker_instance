<?php
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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));

/**
 * An Operation that returns a list of installed extensions
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_Operation_GetExtensionList implements tx_caretakerinstance_IOperation {

	/**
	 * @var array Available extension scopes
	 */
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

	/**
	 * Get the path for the given scope
	 *
	 * @param string $scope
	 * @return string
	 */
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

	/**
	 * Get the list of extensions in the given scope
	 *
	 * @param string $scope
	 * @return boolean
	 */
	protected function getExtensionListForScope($scope) {
		$path = $this->getPathForScope($scope);
		$extensionInfo = array();
		if (@is_dir($path))	{
			$extensionFolders = \TYPO3\CMS\Core\Utility\GeneralUtility::get_dirs($path);
			if (is_array($extensionFolders)) {
				foreach($extensionFolders as $extKey) {
					$extensionInfo[$extKey]['ext_key'] = $extKey;
					$extensionInfo[$extKey]['installed'] = (boolean)\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey);

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
