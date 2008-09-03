<?php
class tx_caretakerinstance_Operation_GetPHPVersion implements tx_caretakerinstance_IOperation {
	public function execute($parameter = array()) {
		return new tx_caretakerinstance_OperationResult(true, phpversion());
	}
}
?>