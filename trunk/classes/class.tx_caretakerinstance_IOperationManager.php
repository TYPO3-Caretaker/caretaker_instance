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

/**
 * The Operation manager is responsible for registering and
 * executing Operations. An Operation is registered with
 * a unique key either as a class name or instance.
 * 
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
interface tx_caretakerinstance_IOperationManager {
	/**
	 * Register a new operation with the given key.
	 * 
	 * @param string $operationKey The key of the operation (All lowercase, underscores)
	 * @param string|object $operation Operation instance or class 
	 */
	function registerOperation($operationKey, $operation);
	
	/**
	 * Get a registered operation as instance by key
	 *
	 * @param string $operationKey
	 * @return tx_caretakerinstance_IOperation|boolean The Operation instance or FALSE if not registered
	 */
	function getOperation($operationKey);

	/**
	 * Execute an Operation by key with optional parameters
	 *
	 * @param string $operationKey
	 * @param array $parameter
	 * @return tx_caretakerinstance_OperationResult
	 */
	function executeOperation($operationKey, $parameter = array());
}
?>