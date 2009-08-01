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

require_once(t3lib_extMgm::extPath('caretaker_instance', 'lib/Crypt/RSA.php'));

require_once('class.tx_caretakerinstance_ICryptoManager.php');

/**
 * A Crypt based Crypto Manager implementation
 * 
 * 
 * 
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_CryptoManager implements tx_caretakerinstance_ICryptoManager {

	const KEY_LENGTH = 64;
	
	protected $rsa;
	
	public function createSessionToken($data, $secret) {				
		$salt = substr(md5(rand()), 0, 12);
		$token = $data . ':' . $salt . md5($secret . ':' . $data . ':' . $salt);
		
		return $token;
	}

	public function verifySessionToken($token, $secret) {
		list($data, $hash) = explode(':', $token, 2);
		$salt = substr($hash, 0, 12);
		
		if ($token == $data . ':' . $salt . md5($secret . ':' . $data . ':' . $salt)) {
			return $data;
		} else {
			return false;
		}
	}

	public function createSignature($data, $privateKey) {
		$rsa = $this->getRsa();
		return $rsa->createSign($data, $this->rsaKey($privateKey));
	}
	
	public function verifySignature($data, $signature, $publicKey) {
		$rsa = $this->getRsa();
		return $rsa->validateSign($data, $signature, $this->rsaKey($publicKey));
	}
	
	public function encrypt($data, $key) {
		$rsa = $this->getRsa();
		return $rsa->encrypt($data, $this->rsaKey($key));
	}
	
	public function decrypt($data, $key) {
		$rsa = $this->getRsa();
		return $rsa->decrypt($data, $this->rsaKey($key));
	}

	public function generateKeyPair() {
		$keyPair = new Crypt_RSA_KeyPair(self::KEY_LENGTH);

		return array($keyPair->getPublicKey()->toString(), $keyPair->getPrivateKey()->toString()); 
	}
	
	/**
	 * @return Crypt_RSA RSA instance
	 */
	protected function getRsa() {
		if($this->rsa == null) {
			$this->rsa = new Crypt_RSA();
		}
		return $this->rsa;
	}
	
	protected function rsaKey($key) {
		if(is_string($key)) {
			$key = Crypt_RSA_Key::fromString($key);
		}
		return $key;
	}
}
?>