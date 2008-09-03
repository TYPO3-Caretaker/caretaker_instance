<?php
class tx_caretakerinstance_OperationResult {
	
	/**
	 * @var boolean
	 */
	protected $status;
	
	/**
	 * @var array|string
	 */
	protected $value;
	
	public function __construct($status, $value) {
		$this->status = $status;
		$this->value = $value;
	}
	
	/**
	 * @return boolean If the operation was executed successful 
	 */
	public function isSuccessful() {
		return $this->status;
	}
	
	/**
	 * @return array|string The operation value
	 */
	public function getValue() {
		return $this->value;
	}
}
?>