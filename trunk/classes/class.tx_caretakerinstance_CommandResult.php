<?php
class tx_caretakerinstance_CommandResult {
	
	protected $status;
	
	protected $operationResults;
	
	protected $message;
	
	public function __construct($status, $operationResults = array(), $message = '') {
		$this->status = $status;
		$this->operationResults = $operationResults;
		$this->message = $message;
	}
	
	public function isSuccessful() {
		return $this->status;
	}
	
	public function getOperationResults() {
		return $this->operationResults;
	}

	public function getMessage() {
		return $this->message;
	}
}
?>