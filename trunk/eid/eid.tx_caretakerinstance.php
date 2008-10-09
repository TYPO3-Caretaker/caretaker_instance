<?php
// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

/*
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.0 500 Invalid request');
	exit;
}
*/

require_once(t3lib_extMgm::extPath('caretaker_instance', 'classes/class.tx_caretakerinstance_ServiceFactory.php'));

tslib_eidtools::connectDB();

$factory = tx_caretakerinstance_ServiceFactory::getInstance();
$commandService = $factory->getCommandService();

$remoteAddress = $_SERVER['REMOTE_ADDR'];

if($_SERVER['REQUEST_METHOD'] == 'GET') {
	if(isset($_GET['rst'])) {
		$token = $commandService->requestSessionToken($remoteAddress);
		if(!$token) {
			header('HTTP/1.0 403 Request not allowed');
		} else {
			echo $token;
		}
	} else {
		header('HTTP/1.0 500 Invalid request');
	}
} else {
	if(isset($_POST['st']) && isset($_POST['d']) && isset($_POST['s'])) {
		$sessionToken = $_POST['st'];
		$data = $_POST['d'];
		$signature = $_POST['s'];
	} else {
		header('HTTP/1.0 500 Invalid request');
	}
	$request = new tx_caretakerinstance_CommandRequest(
		array(
			'session_token' => $sessionToken,
			array('client_info' =>
				array(
					'host_address' => $remoteAddress
				)
			),
			'raw' => $data,
			'signature' => $signature
		));
	$result = $commandService->executeCommand($request);
	
	// TODO Check for result failure and maybe throw a HTTP status code
	
	echo $commandService->wrapCommandResult($result);
}

exit;
?>