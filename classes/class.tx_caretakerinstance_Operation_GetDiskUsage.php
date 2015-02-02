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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_OperationResult.php'));

/**
 * A sample Operation which returns the remaining diskspace
 *
  * @author Tomas Norre Mikkelsen <tomasnorre@gmail.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_Operation_GetDiskUsage implements tx_caretakerinstance_IOperation {

	/**
	 * Get the remaining disk space in percent
	 *
	 * @param array $parameter None
	 * @return the remaining disk space in percent
	 */
	public function execute($parameter = array()) {
		return new tx_caretakerinstance_OperationResult(true, $this->diskUsageInPercent());
	}

	/**
	 * Get remain disk space in percent.
	 * 
	 * @return int
	 */
	private function diskUsageInPercent(){

		$diskSize = disk_total_space(getcwd());
        $diskUsed = disk_free_space(getcwd());

        $remain = 100 - ($diskUsed / $diskSize * 100);

        return (int) $remain;
	}
}
?>
