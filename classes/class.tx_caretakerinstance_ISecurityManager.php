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

/**
 * The Security Manager
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
interface tx_caretakerinstance_ISecurityManager
{

    /**
     * Decode a Command Request (decrypt, merge data)
     *
     * @param tx_caretakerinstance_CommandRequest $commandRequest
     */
    function decodeRequest(tx_caretakerinstance_CommandRequest $commandRequest);

    /**
     * Validate a Command Request (check session token, host address)
     *
     * @param tx_caretakerinstance_CommandRequest $commandRequest
     * @throws Exception
     */
    function validateRequest(tx_caretakerinstance_CommandRequest $commandRequest);

    /**
     * Create a new session token for allowed hosts
     *
     * @param string $clientHostAddress
     */
    function createSessionToken($clientHostAddress);

    /**
     * Encode the result data
     *
     * @param string $resultData The Command Result data (e.g. JSON)
     * @return string The encrypted Command Result data
     */
    function encodeResult($resultData);

    /**
     * Decode the result data
     *
     * @param string $encryptedString
     * @return string
     */
    function decodeResult($encryptedString);

    /**
     * @return string
     */
    function getPrivateKey();

}