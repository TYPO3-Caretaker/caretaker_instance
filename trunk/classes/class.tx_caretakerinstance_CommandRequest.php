<?php
class tx_caretakerinstance_CommandRequest {
	
	/**
	 * @var array
	 */
	protected $operations;
	
	public function __construct($options) {
		$this->operations = $options['operations'];
	}
	
	public function getOperations() {
		return $this->operations;
	}
}
?>