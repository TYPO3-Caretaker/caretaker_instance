<?php
require_once('class.tx_caretakerinstance_CommandRequest.php');
require_once('class.tx_caretakerinstance_CommandResult.php');

class tx_caretakerinstance_CommandService {
	/**
	 * @var tx_caretakerinstance_ISecurityManager
	 */
	protected $securityManager;

	/**
	 * @var tx_caretakerinstance_OperationManager
	 */
	protected $operationManager;

	public function __construct(tx_caretakerinstance_OperationManager $operationManager, tx_caretakerinstance_ISecurityManager $securityManager) {
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
		if($this->securityManager->validateRequest($commandRequest)) {
			
			if($this->securityManager->decodeRequest($commandRequest)) {
			
				$operations = $commandRequest->getData('operations');
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
	
	public function requestSessionToken($clientHostAddress) {
		return $this->securityManager->createSessionToken($clientHostAddress);
	}
}
?>