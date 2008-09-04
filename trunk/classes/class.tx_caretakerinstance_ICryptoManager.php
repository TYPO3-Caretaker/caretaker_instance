<?php
interface tx_caretakerinstance_ICryptoManager {
	function createSessionToken($data, $secret);
	
	function verifySessionToken($token, $secret);
	
	function createSignature($data, $privateKey);
	
	function verifySignature($data, $signature, $publicKey);
	
	function encrypt($data, $key);
	
	function decrypt($data, $key);
}
?>