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

require_once('class.tx_caretakerinstance_ICryptoManager.php');

/**
 * A Crypt based Crypto Manager implementation
 * FIXME: Do *not* use Base64CryptoManager. This is for demonstration/debugging only!
 * 
 * 
 * @author Tobias Liebig <liebig@networkteam.com>
 * @package		TYPO3
 * @subpackage	tx_caretakerinstance
 */
class tx_caretakerinstance_Base64CryptoManager implements tx_caretakerinstance_ICryptoManager {
	
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
		return md5($data);
	}
	
	public function verifySignature($data, $signature, $publicKey) {
		
		return md5($data) == $signature;
	}
	
	public function encrypt($data, $key) {
		return base64_encode($data);
	}
	
	public function decrypt($data, $key) {
		return base64_decode($data);
	}

	public function generateKeyPair() {
		return array('', ''); 
	}
	
}
?>