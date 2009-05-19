<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Christopher Hlubek (hlubek@networkteam.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once('class.tx_caretakerinstance_CommandRequest.php');


/**
 * @author Tobias Liebig <liebig@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_RemoteCommandConnector {
	/**
	 * @var tx_caretakerinstance_CryptoManager
	 */
	protected $cryptoManager;
	
	/**
	 * @var tx_caretakerinstance_SecurityManager
	 */
	protected $securityManager;
	
	/**
	 * @var string url of the instance
	 */
	protected $instanceUrl;
	
	/**
	 * @param $cryptoManager tx_caretakerinstance_ICryptoManager
	 * @param $securityManager tx_caretakerinstance_ISecurityManager
	 * @return tx_caretakerinstance_RemoteCommandConnector
	 */
	public function __construct(tx_caretakerinstance_ICryptoManager $cryptoManager, tx_caretakerinstance_ISecurityManager $securityManager) {
		$this->cryptoManager = $cryptoManager;
		$this->securityManager = $securityManager;
	}
	
	/**
	 * executes a bunch of operation an a remote instance and takes care to secure/encrypt the communication
	 * 
	 * @param $operations array
	 * @param $baseurl string
	 * @param $instancePublicKey string
	 * @return tx_caretakerinstance_CommandResult
	 */
	public function executeOperations(array $operations, $baseurl, $instancePublicKey) {
		if (empty($baseurl) || empty($instancePublicKey)) {
			return $this->getCommandResult(false, null, 'No URL or publicKey of instance given');
		}
		
		$this->setInstanceURL($baseurl);

		$sessionToken = $this->requestSessionToken();

		if (!$sessionToken) {
			return $this->getCommandResult(false, null, 'Failed to request SessionToken');
		}
		
		$commandRequest = $this->getCommandRequest(
			$sessionToken, 
			$instancePublicKey, 
			$this->getInstanceURL(), 
			$this->getDataFromOperations($operations)
		);
		$commandRequest->setSignature(
			$this->getRequestSignature($commandRequest)
		);
		
		return $this->executeRequest($commandRequest);
	}
	
	
	/**
	 * @param $commandRequest tx_caretakerinstance_CommandRequest
	 * @return tx_caretakerinstance_CommandResult
	 */
	public function executeRequest($commandRequest) {
	// public function executeRequest(tx_caretakerinstance_CommandRequest $commandRequest) {
		$httpRequestResult = $this->executeHttpRequest(
			$commandRequest->getServerUrl(),
			array(
				'st' => $commandRequest->getSessionToken(),
				'd' => $commandRequest->getData(),
				's' => $commandRequest->getSignature()
			)
		);
		
		if (is_array($httpRequestResult)
		  && $httpRequestResult['info']['http_code'] === 200) {
			$json = $this->securityManager->decodeResult($httpRequestResult['response']);
			// TODO: check if valid json
			if ($json) {
		  		return tx_caretakerinstance_CommandResult::fromJson($json);
			}
		  		
		}
		
		return $this->getCommandResult(false, null, 'invalid result');
	}
	
	/**
	 * create a CommandRequest
	 * @param $sessionToken string
	 * @param $instancePublicKey string
	 * @param $url string
	 * @param $rawData array
	 * @return tx_caretakerinstance_CommandRequest
	 */
	public function getCommandRequest($sessionToken, $instancePublicKey, $url, $rawData) {		
		return new tx_caretakerinstance_CommandRequest(
			array(
				'session_token' => $sessionToken,
				'server_info' => array(
					'server_key' => $instancePublicKey,
					'server_url' => $url,
				),
				'data' => json_encode(array(
					'encrypted' => $this->cryptoManager->encrypt($rawData, $instancePublicKey),
				)),
				'raw' => $rawData
			)
		);
	}
	
	/**
	 * create a CommandResult
	 * @param $status boolean
	 * @param $operationResults array null
	 * @param $message string
	 * @return tx_caretakerinstance_CommandResult
	 */
	protected function getCommandResult($status, $operationResults, $message) {
		return new tx_caretakerinstance_CommandResult($status, $operationResults, $message);
	}
	
	/**
	 * request a session token from a remote instance
	 * @return string
	 */
	public function requestSessionToken() {
		$token = false;
        $request_url = $this->getInstanceURL() . '&rst=1';
        
		$httpRequestResult = $this->executeHttpRequest($request_url);
		
		if (is_array($httpRequestResult)
		  && $httpRequestResult['info']['http_code'] === 200
		  && preg_match('/^([0-9]{10}:[a-z0-9].*)$/', $httpRequestResult['response'], $matches)) {
			return $matches[1];
		}

		return $token ? $token : false;
	}

	/**
	 * set the base url for the current instance
	 * @param $baseurl
	 */
	public function setInstanceURL($baseurl) {
		$this->instanceUrl = $baseurl . 
			(substr($baseurl, -1) != '/' ? '/' : '').
			'?eID=tx_caretakerinstance';
	}
	
	/**
	 * get base url for current instance
	 * @return string
	 */
	public function getInstanceURL() {
		return $this->instanceUrl;
	}
	
	/**
	 * create a json string of operations
	 * 
	 * @param $operations array
	 * @return string json
	 */
	protected function getDataFromOperations($operations) {
		return json_encode(
			array(
				'operations' => $operations
			)	
		);
	}
	
	/**
	 * get encrypted json string of operations
	 * 
	 * @param $operations array
	 * @param $publicKey string
	 * @return string encrypted json
	 */
	protected function getEncryptedDataFromOperations($operations, $publicKey) {
		$encryptedData = $this->cryptoManager->encrypt($this->getDataFromOperations($rawdata), $publicKey);
		return $encryptedData;
	}
	
	/**
	 * get a signature for given request
	 * 
	 * @param $commandRequest tx_caretakerinstance_CommandRequest
	 * @return string
	 */
	public function getRequestSignature($commandRequest) {
		// public function getRequestSignature(tx_caretakerinstance_CommandRequest $commandRequest) {
		return $this->cryptoManager->createSignature(
			$commandRequest->getDataForSignature(), 
			$this->securityManager->getPrivateKey()
		);
	}
	
	/**
	 * @param $request_url string
	 * @param $postValues array POST
	 * @return array info/response
	 */
	protected function executeHttpRequest($request_url, $postValues = null) {
		$curl = curl_init();
        if (!$curl) {
        	return false;
        }
        
		curl_setopt($curl, CURLOPT_URL, $request_url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		/* */
		if (is_array($postValues)) {
			t3lib_div::debug($postValues);
			foreach($postValues as $key => $value) {
				$postQuery .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			rtrim($postQuery, '&');
			
			curl_setopt($curl, CURLOPT_POST, count($postValues));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postQuery);
		}
		// */
		
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);
		
		return array(
			'response' => $response,
			'info' => $info
		);
	}
}
?>