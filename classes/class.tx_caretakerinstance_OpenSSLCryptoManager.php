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
 * An OpenSSL based Crypto Manager implementation
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class tx_caretakerinstance_OpenSSLCryptoManager extends tx_caretakerinstance_AbstractCryptoManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL PHP extension is required for Caretaker OpenSSLCryptoManager', 1298644422);
        }
    }

    /**
     * Encrypt data with the <em>public</em> key of the recipient.
     * Will be encrypted using openssl_seal.
     *
     * @param $data string The data to encrypt
     * @param $publicKey string The public key for encryption (as PEM formatted string)
     * @throws Exception
     * @return string The encrypted data
     */
    public function encrypt($data, $publicKey)
    {
        $publicKey = $this->decodeKey($publicKey);
        if (empty($publicKey)) {
            throw new \Exception('Public key missing', 1423738632);
        }

        $iv = openssl_random_pseudo_bytes(32);
        $result = openssl_seal($data, $cryptedData, $envelopeKeys, array($publicKey), "AES256", $iv);

        $envelopeKey = $envelopeKeys[0];

        $crypted = base64_encode($envelopeKey) . ':' . base64_encode($cryptedData) . ':' . base64_encode($iv);

        return $crypted;
    }

    /**
     * Decrypt data with <em>private</em> key
     *
     * @param $data string The data to decrypt
     * @param string $privateKey The private key for decryption (as PEM formatted string)
     * @throws Exception
     * @return string The decrypted data
     */
    public function decrypt($data, $privateKey)
    {
        $privateKey = $this->decodeKey($privateKey);
        if (empty($privateKey)) {
            throw new \Exception('Private key missing', 1423738633);
        }

        list($envelopeKey, $cryptedData, $iv) = explode(':', $data);

        $envelopeKey = base64_decode($envelopeKey);
        $cryptedData = base64_decode($cryptedData);
        $iv = base64_decode($iv);

        openssl_open($cryptedData, $decrypted, $envelopeKey, $privateKey, "AES256", $iv);

        return $decrypted;
    }


    /**
     * Sign the data with the given private key
     *
     * @param string $data
     * @param string $privateKey The private key in PEM form
     * @throws Exception
     * @return string
     */
    public function createSignature($data, $privateKey)
    {
        $privateKey = $this->decodeKey($privateKey);
        if (empty($privateKey)) {
            throw new \Exception('Private key missing', 1423738634);
        }

        openssl_sign($data, $signature, $privateKey);
        $signature = base64_encode($signature);

        return $signature;
    }

    /**
     * Verify the signature of data with the given public key
     *
     * @param string $data
     * @param string $signature
     * @param string $publicKey The private key in PEM form
     * @throws Exception
     * @return string
     */
    public function verifySignature($data, $signature, $publicKey)
    {
        $publicKey = $this->decodeKey($publicKey);
        if (empty($publicKey)) {
            throw new \Exception('Public key missing', 1423738635);
        }

        $signature = base64_decode($signature);
        $correct = openssl_verify($data, $signature, $publicKey);

        return $correct === 1;
    }

    /**
     * Generate a new key pair
     *
     * @throws Exception
     * @return array Public and private key as string
     */
    public function generateKeyPair()
    {
        $keyPair = openssl_pkey_new();

        if (!$keyPair) {
            throw new Exception('Cant create OpenSSL private key.');
        }

        openssl_pkey_export($keyPair, $privateKey);

        $publicKey = openssl_pkey_get_details($keyPair);
        $publicKey = $publicKey['key'];

        return array($this->encodeKey($publicKey), $this->encodeKey($privateKey));
    }

    /**
     * Encode linebreaks in key to make it usable in ext config
     *
     * @param string $key The key in PEM format
     * @return string The key without linebreaks (not in PEM!)
     */
    protected function encodeKey($key)
    {
        return str_replace("\n", '|', $key);
    }

    /**
     * Add linebreaks in key to make it conform to PEM format
     *
     * @param string $key The key without linebreaks
     * @return string The key with linebreaks (in PEM)
     */
    protected function decodeKey($key)
    {
        return str_replace('|', "\n", $key);
    }
}
