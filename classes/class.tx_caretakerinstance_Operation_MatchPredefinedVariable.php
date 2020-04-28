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

use TYPO3\CMS\Core\SingletonInterface;

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
 * A Operation which checks if a Predefined Variable (like $GLOBALS['foo']['bar']) has a certain value
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class tx_caretakerinstance_Operation_MatchPredefinedVariable implements tx_caretakerinstance_IOperation, SingletonInterface
{
    /**
     * Check if the key matches the variable. Nested keys can be given
     * using the | separator. To prevent information disclosure, the key
     * value will not be returned.
     *
     * @param array $parameter key, match, usingRegexp, comparisonOperator
     * @return tx_caretakerinstance_OperationResult The current PHP version
     */
    public function execute($parameter = array())
    {
        $keyPath = explode('|', $parameter['key']);
        $value = $this->getValueForKeyPath($keyPath);

        $success = false;
        if ($parameter['usingRegexp']) {
            $success = (preg_match($parameter['match'], $value) >= 1);
        } else {
            switch ($parameter['comparisonOperator']) {
                case ':regex:':
                    $success = (preg_match($parameter['match'], $value) >= 1);
                    break;
                case '>=':
                    $success = ($value >= $parameter['match']);
                    break;
                case '<=':
                    $success = ($value <= $parameter['match']);
                    break;
                case '>':
                    $success = ($value > $parameter['match']);
                    break;
                case '<':
                    $success = ($value < $parameter['match']);
                    break;
                case '!=':
                    $success = ($parameter['match'] != $value);
                    break;
                default:
                case '=':
                case '==':
                    $success = ($parameter['match'] == $value);
                    break;
            }
        }

        return new tx_caretakerinstance_OperationResult($success, '');
    }

    /**
     *
     * @param array $keyPath
     * @return bool
     */
    protected function getValueForKeyPath(array $keyPath)
    {
        $key = array_shift($keyPath);
        $value = null;
        switch ($key) {
            case 'GLOBALS':
                $value = $GLOBALS;

                // decode TYPO3_CONF_VARS->EXT->extConf children if requested
                if ($keyPath[0] == 'TYPO3_CONF_VARS' && $keyPath[1] == 'EXT' && $keyPath[2] == 'extConf' && $keyPath[3]) {
                    $value = clone $GLOBALS;
                    $serializedValue = $value[$keyPath[0]][$keyPath[1]][$keyPath[2]][$keyPath[3]];
                    $value[$keyPath[0]][$keyPath[1]][$keyPath[2]][$keyPath[3]] = unserialize($serializedValue);
                }

                break;

            case '_POST':
                $value = $_POST;
                break;

            case '_GET':
                $value = $_GET;
                break;

            case '_FILES':
                $value = $_FILES;
                break;

            case '_REQUEST':
                $value = $_REQUEST;
                break;

            case '_SERVER':
                $value = $_SERVER;
                break;

            case '_SESSION':
                $value = $_SESSION;
                break;

            case '_ENV':
                $value = $_ENV;
                break;

            case '_COOKIE':
                $value = $_COOKIE;
                break;
        }
        foreach ($keyPath as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                $value = false;
                break;
            }
        }

        return $value;
    }
}
