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
			'client_info' =>
				array(
					'host_address' => $remoteAddress
				)
			,
			'data' => array(),
			'raw' => stripslashes($data),
			'signature' => $signature
		));

	$result = $commandService->executeCommand($request);

	// TODO Check for result failure and maybe throw a HTTP status code
	
	echo $commandService->wrapCommandResult($result);
}

exit;
?>