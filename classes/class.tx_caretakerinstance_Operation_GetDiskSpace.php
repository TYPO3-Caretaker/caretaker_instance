<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2015 by Tobias Liebig <tobias.liebig@typo3.org>
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

/**
 * A Operation which returns the current TYPO3 version
 *
 * @author Tobias Liebig <tobias.liebig@typo3.org>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_Operation_GetDiskSpace implements tx_caretakerinstance_IOperation {

	/**
	 * @param array $parameter
	 * @return tx_caretakerinstance_OperationResult
	 */
	public function execute($parameter = array()) {
		$path = !empty($parameter['path']) ? $parameter['path'] : '/';
		return new tx_caretakerinstance_OperationResult(true, array(
			'total' => disk_total_space($path),
			'free' => disk_free_space($path)
		));
	}
}