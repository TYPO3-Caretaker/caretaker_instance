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
 * Checks wether the given path exists or not
 *
 * @author Felix Oertel <oertel@networkteam.com>
 *
 */
class tx_caretakerinstance_Operation_CheckPathExists implements tx_caretakerinstance_IOperation
{
    /**
     * execute operation (checkPathExists)
     *
     * @param array $parameter a path 'path' to a file or folder
     * @return tx_caretakerinstance_OperationResult 'file' if path is a file, 'directory' if it's a directory and false if it doesn't exist
     */
    public function execute($parameter = null)
    {
        $path = $this->getPath($parameter);
        list($path) = glob($path);

        if (is_file($path)) {
            //if file exists, get the tstamp
            $time = filemtime($path);
            $size = filesize($path);

            return new tx_caretakerinstance_OperationResult(true, array(
                'type' => 'file',
                'path' => $parameter,
                'time' => $time,
                'size' => $size,
            ));
        } elseif (is_dir($path)) {
            return new tx_caretakerinstance_OperationResult(true, array(
                'type' => 'folder',
                'path' => $parameter,
            ));
        }
        return new tx_caretakerinstance_OperationResult(false, array('path' => $parameter));
    }

    /**
     * prepare path, resolve relative path and resolve EXT: path
     *
     * @param string $path absolute or relative path or EXT:foobar/
     * @return string/bool false if path is invalid, else the absolute path
     */
    protected function getPath($path)
    {
        // getFileAbsFileName can't handle directory path with trailing / correctly
        if (substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }

        // FIXME remove this hacky part
        // skip path checks for CLI mode
        if (defined('TYPO3_cliMode')) {
            return $path;
        }

        $path = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($path);
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($path)) {
            return $path;
        }
        return false;
    }
}
