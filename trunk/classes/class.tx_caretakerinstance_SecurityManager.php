<?php

require_once('class.tx_caretakerinstance_ISecurityManager.php');

class tx_caretakerinstance_SecurityManager implements tx_caretakerinstance_ISecurityManager {
	
	/**
	 * Public key of this instance
	 *
	 * @var string
	 */
	protected $publicKey;
	
	/**
	 * Private key of this instance
	 *
	 * @var string
	 */
	protected $privateKey;
	
	/**
	 * Public key of the client accessing the instance (a.k.a caretaker server), this must be preconfigured
	 *
	 * @var string
	 */
	protected $clientPublicKey;
	
	/**
	 * Expiration of session token in seconds
	 *
	 * @var int
	 */
	protected $sessionTokenExpiration = 600;
	
	/**
	 * Restrict client (Caretaker server) to an IP address
	 * 
	 * @var string
	 */
	protected $clientHostAddressRestriction;
	
	/**
	 * @var tx_caretakerinstance_CryptoManager
	 */
	protected $cryptoManager;
	
	public function __construct(tx_caretakerinstance_ICryptoManager $cryptoManager) {
		$this->cryptoManager = $cryptoManager;
	}
	
	public function validateRequest(tx_caretakerinstance_CommandRequest $commandRequest) {
		$sessionToken = $commandRequest->getSessionToken();
		$timestamp = $this->cryptoManager->verifySessionToken($sessionToken, $this->privateKey);
		if ((time() - $timestamp) > $this->sessionTokenExpiration) {
			// Session token expired
			return false;
		} elseif (strlen($this->clientHostAddressRestriction) &&
			$commandRequest->getClientHostAddress() != $this->clientHostAddressRestriction) {
			// Client IP address is not allowed
			return false;
		} elseif (!$this->cryptoManager->verifySignature(
			$commandRequest->getDataForSignature(),
			$commandRequest->getSignature(),
			$this->clientPublicKey)) {
			// Signature didn't verify
			return false;
		}
		
		return true;
	}
	
	public function decodeRequest(tx_caretakerinstance_CommandRequest $commandRequest) {
		$data = json_decode($commandRequest->getRawData(), TRUE);
		$commandRequest->mergeData($data);
		
		if(strlen($commandRequest->getData('encrypted'))) {
			$raw = $this->cryptoManager->decrypt($commandRequest->getData('encrypted'), $this->privateKey);
			if(!$raw) {
				// Decryption failed
				return false;
			}
			$data = json_decode($raw, true);
			
			// merge decrypted data into raw data
			$commandRequest->mergeData($data);
		}
		
		return true;
	}
	
	public function createSessionToken($clientHostAddress) {
		if(strlen($this->clientHostAddressRestriction) &&
			$clientHostAddress != $this->clientHostAddressRestriction) {
			return false;
		}
		return $this->cryptoManager->createSessionToken(time(), $this->privateKey);
	}
	

	public function getPublicKey() {
		return $this->publicKey;
	}

	public function setPublicKey($publicKey) {
		$this->publicKey = $publicKey;
	}
	
	public function getPrivateKey() {
		return $this->privateKey;
	}
	
	public function setPrivateKey($privateKey) {
		$this->privateKey = $privateKey;
	}
	
	public function getClientHostAddressRestriction() {
		return $this->clientHostAddressRestriction;
	}

	public function setClientHostAddressRestriction($address) {
		$this->clientHostAddressRestriction = $address;
	}
	
	public function getClientPublicKey() {
		return $this->clientPublicKey;
	}
	
	public function setClientPublicKey($clientPublicKey) {
		$this->clientPublicKey = $clientPublicKey;
	}
	
	public function getSessionTokenExpiration() {
		return $this->sessionTokenExpiration;
	}
	
	public function encodeResult($resultData) {
		return $this->cryptoManager->encrypt($resultData, $this->clientPublicKey);
	}
	
	public function decodeResult($encryptedData) {
		return $this->cryptoManager->decrypt($encryptedData, $this->privateKey);
	}
}
?>