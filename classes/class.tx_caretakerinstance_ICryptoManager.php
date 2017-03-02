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
 * The Crypto Manager encrypts, decrypts and verifies data
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
interface tx_caretakerinstance_ICryptoManager
{
    /**
     * Create a session token that can be verified with the given secret
     *
     * @param string $data
     * @param string $secret
     * @return string
     */
    public function createSessionToken($data, $secret);

    /**
     * Verify that the given token was created with the given secret
     *
     * @param string $token
     * @param string $secret
     * @return bool
     */
    public function verifySessionToken($token, $secret);

    /**
     * Sign the data with the given private key
     *
     * @param string $data
     * @param string $privateKey The private key
     * @return string
     */
    public function createSignature($data, $privateKey);

    /**
     * Verify the signature of data with the given public key
     *
     * @param string $data
     * @param string $signature
     * @param string $publicKey The private key
     * @return string
     */
    public function verifySignature($data, $signature, $publicKey);

    /**
     * Encrypt data with the given public key
     *
     * @param $data string The data to encrypt
     * @param $publicKey string The public key for encryption
     * @return string The encrypted data
     */
    public function encrypt($data, $publicKey);

    /**
     * Decrypt data with the given private key
     *
     * @param $data string The data to decrypt
     * @param $privateKey string The private key for decryption
     * @return string The decrypted data
     */
    public function decrypt($data, $privateKey);

    /**
     * Generate a new key pair
     *
     * @return array Public and private key as string
     */
    public function generateKeyPair();
}
