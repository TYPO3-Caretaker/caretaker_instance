<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 by n@work GmbH and networkteam GmbH
 *
 * All rights reserved
 *
 * This script is part of the Caretaker project. The Caretaker project
 * is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This is a file of the caretaker project.
 * http://forge.typo3.org/projects/show/extension-caretaker
 *
 * Project sponsored by:
 * n@work GmbH - http://www.work.de
 * networkteam GmbH - http://www.networkteam.com/
 *
 * $Id$
 */

/**
 * Holds public/private Keys and does encoding/decoding (using CryptoManager)
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
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
	 * @var tx_caretakerinstance_OpenSSLCryptoManager
	 */
	protected $cryptoManager;

	/**
	 * Constructor
	 *
	 * @param tx_caretakerinstance_ICryptoManager $cryptoManager
	 */
	public function __construct(tx_caretakerinstance_ICryptoManager $cryptoManager) {
		$this->cryptoManager = $cryptoManager;
	}

    /**
     * Validate a command request
     * - Validity of session token
     * - Session token expiration
     * - Client host address
     * - Encrypted data signature
     *
     * @param tx_caretakerinstance_CommandRequest $commandRequest
     * @return bool
     * @throws Exception
     */
	public function validateRequest(tx_caretakerinstance_CommandRequest $commandRequest) {
		$sessionToken = $commandRequest->getSessionToken();
		$timestamp = $this->cryptoManager->verifySessionToken($sessionToken, $this->privateKey);
		if ((time() - $timestamp) > $this->sessionTokenExpiration) {
            throw new Exception('Session token expired');
		} elseif (strlen($this->clientHostAddressRestriction) &&
				$commandRequest->getClientHostAddress() != $this->clientHostAddressRestriction
		) {
            throw new Exception('Client IP address is not allowed');
		} elseif (!$this->cryptoManager->verifySignature(
				$commandRequest->getDataForSignature(),
				$commandRequest->getSignature(),
				$this->clientPublicKey)
		) {
            throw new Exception('Signature didn\'t verify');
		}

		return TRUE;
	}

	/**
	 * Decrypt and merge encrypted data for the command request
	 *
	 * @param tx_caretakerinstance_CommandRequest $commandRequest
	 * @return boolean TRUE if the command request could be decrypted
	 */
	public function decodeRequest(tx_caretakerinstance_CommandRequest $commandRequest) {
		$data = json_decode($commandRequest->getRawData(), TRUE);
		$commandRequest->mergeData($data);

		if (strlen($commandRequest->getData('encrypted'))) {
			$raw = $this->cryptoManager->decrypt($commandRequest->getData('encrypted'), $this->privateKey);
			if (!$raw) {
				// Decryption failed
				return FALSE;
			}
			$data = json_decode($raw, TRUE);

			// merge decrypted data into raw data
			$commandRequest->mergeData($data);
		}

		return TRUE;
	}

	/**
	 * Create a session token
	 *
	 * @param string $clientHostAddress
	 * @return string
	 */
	public function createSessionToken($clientHostAddress) {
		if (strlen($this->clientHostAddressRestriction) &&
				$clientHostAddress != $this->clientHostAddressRestriction
		) {
			return FALSE;
		}
		return $this->cryptoManager->createSessionToken(time(), $this->privateKey);
	}

	/**
	 *
	 * @return string
	 */
	public function getPublicKey() {
		return $this->publicKey;
	}

	/**
	 * @param string $publicKey
	 * @return void
	 */
	public function setPublicKey($publicKey) {
		$this->publicKey = $publicKey;
	}

	/**
	 *
	 * @return string
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}

	/**
	 *
	 * @param string $privateKey
	 * @return void
	 */
	public function setPrivateKey($privateKey) {
		$this->privateKey = $privateKey;
	}

	/**
	 *
	 * @return string
	 */
	public function getClientHostAddressRestriction() {
		return $this->clientHostAddressRestriction;
	}

	/**
	 *
	 * @param string $address
	 * @return void
	 */
	public function setClientHostAddressRestriction($address) {
		$this->clientHostAddressRestriction = $address;
	}

	/**
	 *
	 * @return string
	 */
	public function getClientPublicKey() {
		return $this->clientPublicKey;
	}

	/**
	 *
	 * @param string $clientPublicKey
	 * @return void
	 */
	public function setClientPublicKey($clientPublicKey) {
		$this->clientPublicKey = $clientPublicKey;
	}

	/**
	 *
	 * @return int
	 */
	public function getSessionTokenExpiration() {
		return $this->sessionTokenExpiration;
	}

	/**
	 * @param string $resultData
	 * @return string
	 */
	public function encodeResult($resultData) {
		return $this->cryptoManager->encrypt($resultData, $this->clientPublicKey);
	}

	/**
	 *
	 * @param string $encryptedData
	 * @return string
	 */
	public function decodeResult($encryptedData) {
		return $this->cryptoManager->decrypt($encryptedData, $this->privateKey);
	}

}