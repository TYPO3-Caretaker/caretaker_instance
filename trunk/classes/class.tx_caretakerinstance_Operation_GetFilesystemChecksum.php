<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Tobias Liebig (liebig@networkteam.com)
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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_IOperation.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));

/**
 * An Operation that returns the first record matched by a field name and value as an array (excluding protected record details like be_user password).
 * This operation should be SQL injection safe. The table has to be mapped in the TCA.
 * 
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_Operation_GetFilesystemChecksum implements tx_caretakerinstance_IOperation {

	/**
	 * execute operation (getFilsystemChecksum)
	 * @param array $parameter a path 'path' to a file or folder
	 * @return a checksum of the given folder or file
	 */
	public function execute($parameter = array()) {
		$path = $this->getPath($parameter['path']);
		$getSingleChecksums = $this->getPath($parameter['getSingleChecksums']);
		
		$checksum = '';
		
		if ($path !== false) {
			if (is_dir($path)) {
				list($checksum, $md5s) = $this->getFolderChecksum($path);
			} else {
				$checksum = $this->getFileChecksum($path);
			}
		}
		if (!empty($checksum)) {
			if ($getSingleChecksums) {
				$result = array(
					'checksum' => $checksum,
					'singleChecksums' => $md5s,
				);
			} else {
				$result = $checksum;
			}
			return new tx_caretakerinstance_OperationResult(TRUE, $result);
		} else {
			return new tx_caretakerinstance_OperationResult(FALSE, 'Error: can\'t calculate checksum for file or folder');
		}
	}
	
	/**
	 * prepare path, resolve relative path and resolve EXT: path
	 * check if path is allowed
	 * 
	 * @param $path absolute or relative path or EXT:foobar/
	 * @return string/bool false if path is invalid, else the absolute path
	 */
	protected function getPath($path) {
		if (substr($path, -1) === '/') {
			$path = substr($path, 0, -1);
		}
		
		// FIXME remove this hacky part
		// skip path checks for CLI mode
		if (defined('TYPO3_cliMode')) {
			return $path;
		}
		
		// getFileAbsFileName can't handle directory path with trailing / correctly
		
		$path = t3lib_div::getFileAbsFileName($path);
		if (t3lib_div::isAllowedAbsPath($path)) {
			return $path;
		} else {
			return false;
		}
	}

	/**
	 * get a md5 checksum of a given file
	 * 
	 * @param $path file path
	 * @return string/bool false if path is not a file or md5 checksum of given file
	 */
	protected function getFileChecksum($path) {
		if (!is_file($path)) {
			return false;
		}
		$md5 = md5_file($path);
		return $md5;
	}
	
	/**
	 * get a md5 checksum of a given folder recursivly
	 * 
	 * @param $path path of folder
	 * @return string checksum
	 */
	protected function getFolderChecksum($path) {
		if (!is_dir($path)) {
			return $this->getFileChecksum($path);
		}
		$md5s = array();
		$d = dir($path);
		while(false !== ($entry = $d->read())) {
			if ($entry === '.' || $entry === '..' || $entry === '.svn') {
				continue;
			}
			if (is_dir($path . '/' . $entry)) {
				list($checksum, $md5sOfSubfolder) = $this->getFolderChecksum($path . '/' . $entry);
				$md5s = array_merge($md5s, $md5sOfSubfolder);
			} else {
				$relPath = str_replace(PATH_site, '', $path . '/' . $entry);
				$md5s[$relPath] = $this->getFileChecksum($path . '/' . $entry);
			}
		}
		
		asort($md5s);
		
		return array(
			md5(implode(',', $md5s)),
			$md5s
		);
	}
}
?>