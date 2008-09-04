<?php
class tx_caretakerinstance_CommandRequest {
	
	/**
	 * @var string
	 */
	protected $clientKey;
	
	protected $clientHostAddress;
	
	public function __construct($options) {
		$this->sessionToken = $options['session_token'];
		$this->data = $options['data'];
		$this->clientKey = $options['client_info']['client_key'];
		$this->clientHostAdress = $options['client_info']['host_address'];
		$this->rawData = $options['raw'];
		$this->signature = $options['signature'];
	}
	
	public function getClientKey() {
		return $this->clientKey;
	}
	
	public function getSessionToken() {
		return $this->sessionToken;
	}
	
	public function getClientHostAddress() {
		return $this->clientHostAddress;
	}
	
	public function getRawData() {
		return $this->rawData;
	}

	public function getSignature() {
		return $this->signature;
	}
	
	public function getData($key = null) {
		if($key != null) {
			return $this->data[$key];
		} else {
			return $this->data;
		}
	}
	
	public function mergeData(&$array) {
		$this->data = array_merge($this->data, $array);
	}
	
	public function getDataForSignature() {
		return $this->getSessionToken() . '$' . $this->getRawData();
	}
}
?>