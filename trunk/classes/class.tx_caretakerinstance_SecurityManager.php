<?php

class tx_caretakerinstance_SecurityManager {
	
	protected $publicKey;
	
	protected $privateKey;
	
	protected $serverPublicKey;
	
	public function decryptRequest(tx_caretakerinstance_CommandRequest $commandRequest) {
		$clientKey = $commandRequest->getClientKey();
		$encrypted = $commandRequest->getEncrypted();
	}
}
?>