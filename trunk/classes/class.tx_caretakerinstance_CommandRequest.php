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

/**
 * The Command Request encapsulates data about the client key, host address,
 * session token and the raw data (encrypted). For signature
 * verification the Command Request computes the
 * signature relevant data (session token + raw data).
 * 
 * Before executing the Commands in a Command Request the
 * Command Service verifies and decrypts the data of the Request.
 * 
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 * 
 */
class tx_caretakerinstance_CommandRequest {
	
	/**
	 * @var string The client public key
	 */
	protected $clientKey;
	
	/**
	 * @var string The client host address (IP)
	 */
	protected $clientHostAddress;
	
	/**
	 * Create a new Command Request 
	 *
	 * @param array $options Options of the Command Request object
	 */
	public function __construct($options) {
		$this->sessionToken = $options['session_token'];
		$this->data = $options['data'];		
		$this->rawData = $options['raw'];
		$this->signature = $options['signature'];
		
		// if we have client infos, we are recieving a command
		if (is_array($options['client_info'])) {
			$this->clientKey = $options['client_info']['client_key'];
			$this->clientHostAdress = $options['client_info']['host_address'];
		}
		
		// if we have server infos, we are going to send this Request
		if (is_array($options['server_info'])) {
			$this->serverKey = $options['server_info']['server_key'];
			$this->serverUrl = $options['server_info']['server_url'];
		}
	}
	
	/**
	 * @return string The client public key
	 */
	public function getClientKey() {
		return $this->clientKey;
	}
	
	/**
	 * @return string The session token
	 */
	public function getSessionToken() {
		return $this->sessionToken;
	}
	
	/**
	 * @return string The client host address
	 */
	public function getClientHostAddress() {
		return $this->clientHostAddress;
	}
	
	/**
	 * @return string The Server's (read: instance) URL
	 */
	public function getServerUrl() {
		return $this->serverUrl;
	}
	
	/**
	 * @return string The Server's (read: instance) URL
	 */
	public function getServerKey() {
		return $this->serverKey;
	}
	
	/**
	 * @return string The raw data (encrypted)
	 */
	public function getRawData() {
		return $this->rawData;
	}

	/**
	 * @return string The signature
	 */
	public function getSignature() {
		return $this->signature;
	}
	
	/**
	 * @param string The signature
	 */
	public function setSignature($signature) {
		$this->signature = $signature;
	}
	
	/**
	 * @param string $key A key for the data entry to fetch
	 * @return mixed The entry for the key in the Command Request data
	 */
	public function getData($key = null) {
		if($key != null) {
			return $this->data[$key];
		} else {
			return $this->data;
		}
	}
	
	/**
	 * Merge data from another array
	 *
	 * @param array $array
	 */
	public function mergeData(&$array) {
		$this->data = array_merge($this->data, $array);
	}
	
	/**
	 * @return string The relevant data for signature verification
	 */
	public function getDataForSignature() {
		return $this->getSessionToken() . '$' . $this->getRawData();
	}
}
?>