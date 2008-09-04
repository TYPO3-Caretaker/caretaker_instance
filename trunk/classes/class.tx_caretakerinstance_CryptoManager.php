<?php
require_once(t3lib_extMgm::extPath('caretaker_instance', 'lib/Crypt/RSA.php'));

require_once('class.tx_caretakerinstance_ICryptoManager.php');

class tx_caretakerinstance_CryptoManager implements tx_caretakerinstance_ICryptoManager {
	
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