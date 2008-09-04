<?php
interface tx_caretakerinstance_ISecurityManager {
	function decodeRequest(tx_caretakerinstance_CommandRequest $commandRequest);

	function validateRequest(tx_caretakerinstance_CommandRequest $commandRequest);
	
	function createSessionToken($clientHostAddress);
}
?>