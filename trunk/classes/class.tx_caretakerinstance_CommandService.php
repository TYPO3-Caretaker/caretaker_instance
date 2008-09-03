<?php
require_once('class.tx_caretakerinstance_CommandRequest.php');
require_once('class.tx_caretakerinstance_CommandResult.php');

class tx_caretakerinstance_CommandService {
	/**
	 * @var tx_caretakerinstance_SecurityManager
	 */
	protected $securityManager;

	/**
	 * @var tx_caretakerinstance_OperationManager
	 */
	protected $operationManager;

	public function __construct(tx_caretakerinstance_OperationManager $operationManager, tx_caretakerinstance_SecurityManager $securityManager) {
		$this->operationManager = $operationManager;
		$this->securityManager = $securityManager;
	}

	/**
	 * Execute a command (multiple operations)
	 *
	 * @param tx_caretakerinstance_CommandRequest $commandRequest
	 * @return tx_caretakerinstance_CommandResult The command result object
	 */
	public function executeCommand(tx_caretakerinstance_CommandRequest $commandRequest) {
		if($this->securityManager->checkRequest($commandRequest)) {
			
			if($this->securityManager->decryptRequest($commandRequest)) {
			
				$operations = $commandRequest->getOperations();
				$results = array();
				foreach($operations as $operation) {
					$results[] = $this->operationManager->executeOperation($operation[0], $operation[1]);
				}
				return new tx_caretakerinstance_CommandResult(true, $results);
			
			} else {
				return new tx_caretakerinstance_CommandResult(false, null, 'The request could not be decrypted');
			}
		} else {
			return new tx_caretakerinstance_CommandResult(false, null, 'The request could not be certified');
		}
	}
}
?>