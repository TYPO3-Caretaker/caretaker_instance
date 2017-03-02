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
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
if (!defined('PATH_typo3conf')) {
    die('Could not access this script directly!');
}

try {
    $factory = tx_caretakerinstance_ServiceFactory::getInstance();
    $commandService = $factory->getCommandService();

    $remoteAddress = $_SERVER['REMOTE_ADDR'];

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['rst'])) {
            $token = $commandService->requestSessionToken($remoteAddress);
            if (!$token) {
                header('HTTP/1.0 403 Request not allowed');
            } else {
                echo $token;
            }
        } else {
            header('HTTP/1.0 500 Invalid request');
        }
    } else {
        $sessionToken = null;
        $data = null;
        $signature = null;
        if (isset($_POST['st']) && isset($_POST['d']) && isset($_POST['s'])) {
            $sessionToken = $_POST['st'];
            $data = $_POST['d'];
            $signature = $_POST['s'];
        } else {
            header('HTTP/1.0 500 Invalid request');
        }
        // handle data string correctly, if typo3 added slashes to the post vars
        if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) < 7005000 && !get_magic_quotes_gpc()) {
            $data = stripslashes($data);
        }
        $request = new tx_caretakerinstance_CommandRequest(
            array(
                'session_token' => $sessionToken,
                'client_info' => array(
                    'host_address' => $remoteAddress,
                ),
                'data' => array(),
                'raw' => $data,
                'signature' => $signature,
            ));

        $result = $commandService->executeCommand($request);

        // TODO Check for result failure and maybe throw a HTTP status code

        echo $commandService->wrapCommandResult($result);
    }
} catch (Exception $exception) {
    echo json_encode(array(
        'status' => tx_caretakerinstance_CommandResult::status_undefined,
        'exception' => array(
            'code' => $exception->getCode(),
        ),
        'message' => $exception->getMessage(),
    ));
}

exit;
