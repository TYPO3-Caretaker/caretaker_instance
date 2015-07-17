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

/**
 * An Operation is an atomic, executable and SAFE action with optional parameters that returns a value
 * wrapped in the Operation Result.
 * A Command can combine several Operations with different parameters.
 *
 * Operations should be as modular as possible, as they are the basic building blocks of
 * Checks on the caretaker server. Operations are executed on remote hosts via the caretaker
 * instance and should NEVER modify any data or allow for remote execution of arbitrary code.
 *
 * An example operation execution could be:
 *
 * "GetPHPVersion" returns OperationResult("5.2.0")
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
interface tx_caretakerinstance_IOperation {

	/**
	 * Execute this Operation. The execution should not rely
	 * on the execution of previous Operations. The execution
	 * of the Operation MUST NOT modify any data (database, file)
	 * on the instance.
	 *
	 * @param array $parameter Parameters for the operation
	 * @return tx_caretakerinstance_OperationResult The operation result
	 */
	function execute($parameter = array());

}