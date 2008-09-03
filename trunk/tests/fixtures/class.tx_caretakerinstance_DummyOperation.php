<?php
class tx_caretakerinstance_DummyOperation implements tx_caretakerinstance_IOperation {
	function execute($parameter = array()) {
		return new tx_caretakerinstance_OperationResult(true, $parameter['foo']);
	}
}
?>