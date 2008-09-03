<?php
interface tx_caretakerinstance_IOperation {
	/**
	 *
	 * @param array $parameter Parameters for the operation
	 * @return tx_caretakerinstance_OperationResult The operation result
	 */
	function execute($parameter = array());
}
?>