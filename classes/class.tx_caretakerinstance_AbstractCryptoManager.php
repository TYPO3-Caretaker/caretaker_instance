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
 * An abstract base Crypto Manager implementation
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
abstract class tx_caretakerinstance_AbstractCryptoManager implements tx_caretakerinstance_ICryptoManager
{
    /**
     * Create a session token that can be verified with the given secret
     *
     * @param string $data
     * @param string $secret
     * @return string
     */
    public function createSessionToken($data, $secret)
    {
        // Salted MD5 hash for verification and randomness
        $salt = substr(md5(rand()), 0, 12);
        $token = $data . ':' . $salt . md5($secret . ':' . $data . ':' . $salt);

        return $token;
    }

    /**
     * Verify that the given token was created with the given secret
     *
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
}
