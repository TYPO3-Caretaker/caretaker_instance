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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_IOperation.php'));
require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));

/**
 * Checks wether the given path exists or not
 *
 * @author Felix Oertel <oertel@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_Operation_CheckPathExists implements tx_caretakerinstance_IOperation {

	/**
	 * execute operation (checkPathExists)
	 * @param array $parameter a path 'path' to a file or folder
	 * @return 'file' if path is a file, 'directory' if it's a directory and false if it doesn't exist
	 */
	public function execute($parameter = null) {
		$path = $this->getPath($parameter);
				
		if (is_file($path)) {
			//if file exists, get the tstamp
			$time = filemtime($path);
			$size = filesize($path);
			
			return new tx_caretakerinstance_OperationResult(TRUE, array(
				'type' => 'file',
				'path' => $parameter,
				'time' => $time,
				'size' => $size
			));
		} elseif (is_dir($path)) {
			return new tx_caretakerinstance_OperationResult(TRUE, array(
				'type' => 'folder',
				'path' => $parameter
			));
		} else {
			return new tx_caretakerinstance_OperationResult(FALSE, array('path' => $parameter));
		}
	}
	
	/**
	 * prepare path, resolve relative path and resolve EXT: path
	 * 
	 * @param $path absolute or relative path or EXT:foobar/
	 * @return string/bool false if path is invalid, else the absolute path
	 */
	protected function getPath($path) {
			// getFileAbsFileName can't handle directory path with trailing / correctly
		if (substr($path, -1) === '/') {
 			$path = substr($path, 0, -1);
		}
		
			// FIXME remove this hacky part
			// skip path checks for CLI mode
		if (defined('TYPO3_cliMode')) {
			return $path;
		}
				
		$path = t3lib_div::getFileAbsFileName($path);
		if (t3lib_div::isAllowedAbsPath($path)) {
			return $path;
		} else {
			return false;
		}
	}
}
?>
