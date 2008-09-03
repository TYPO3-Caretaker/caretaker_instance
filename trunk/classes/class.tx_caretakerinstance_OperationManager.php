<?php
class tx_caretakerinstance_OperationManager {
	
	protected $operations;
	
	/**
	 * Register a new operation
	 * 
	 * @param string $operationKey The key of the operation (All lowercase, underscores)
	 * @param string|object $operation Operation instance or class 
	 */
	function addOperation($operationKey, $operation) {
		$this->operations[$operationKey] = $operation;
	}
	
	function getOperation($operationKey) {
		if(is_string($this->operations[$operationKey])) {
			return t3lib_div::makeInstance($this->operations[$operationKey]);
		} elseif(is_object($this->operations[$operationKey])) {
			return $this->operations[$operationKey];
		} else {
			return false;
		}
	}
	
	function executeOperation($operationKey, $parameter = array()) {
		$operation = $this->getOperation($operationKey); 
		if($operation) {
			return $operation->execute($parameter);
		} else {
			return new tx_caretakerinstance_OperationResult(false, 'Operation [' . $operationKey . '] unknown');
		}
	}
}
?>