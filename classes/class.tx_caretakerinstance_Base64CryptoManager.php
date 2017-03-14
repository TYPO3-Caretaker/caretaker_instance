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
 * A Bas64 based Crypto Manager implementation
 *
 * FIXME: Do *not* use Base64CryptoManager since the caretaker server cannot differentiate crypto implementations
 * of instances. This is for demonstration / debugging only!
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @deprecated Use OpenSSLCryptoManager instead!
 */
class tx_caretakerinstance_Base64CryptoManager implements tx_caretakerinstance_ICryptoManager
{
    /**
     * @param string $data
     * @param string $secret
     * @return string
     */
    public function createSessionToken($data, $secret)
    {
        $salt = substr(md5(rand()), 0, 12);
        $token = $data . ':' . $salt . md5($secret . ':' . $data . ':' . $salt);

        return $token;
    }

    /**
     * @param string $token
     * @param string $secret
     * @return bool
     */
    public function verifySessionToken($token, $secret)
    {
        list($data, $hash) = explode(':', $token, 2);
        $salt = substr($hash, 0, 12);

        if ($token == $data . ':' . $salt . md5($secret . ':' . $data . ':' . $salt)) {
            return $data;
        }
        return false;
    }

    /**
     * @param string $data
     * @param string $privateKey
     * @return string
     */
    public function createSignature($data, $privateKey)
    {
        return md5($data);
    }

    /**
     * @param string $data
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    public function verifySignature($data, $signature, $publicKey)
    {
        return md5($data) == $signature;
    }

    /**
     * @param string $data
     * @param $key
     * @return string
     */
    public function encrypt($data, $key)
    {
        return base64_encode($data);
    }

    /**
     * @param string $data
     * @param $key
     * @return string
     */
    public function decrypt($data, $key)
    {
        return base64_decode($data);
    }

    /**
     * @return array
     */
    public function generateKeyPair()
    {
        return array('', '');
    }
}
