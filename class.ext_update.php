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
 * Extension manager update class to generate public / private key pairs.
 *
 * @author        Christopher Hlubek <hlubek@networkteam.com>
 */
class ext_update
{
    /**
     * @var tx_caretakerinstance_ServiceFactory
     */
    protected $factory;

    /**
     * @return bool Whether the update should be shown / allowed
     */
    public function access()
    {
        $extConf = $this->getExtConf();

        $show = !strlen($extConf['crypto.']['instance.']['publicKey']) ||
            !strlen($extConf['crypto.']['instance.']['privateKey']);

        return $show;
    }

    /**
     * Return the update process HTML content
     *
     * @return string
     */
    public function main()
    {
        $extConf = $this->getExtConf();

        $this->factory = tx_caretakerinstance_ServiceFactory::getInstance();
        try {
            list($publicKey, $privateKey) = $this->factory->getCryptoManager()->generateKeyPair();
            $extConf['crypto.']['instance.']['publicKey'] = $publicKey;
            $extConf['crypto.']['instance.']['privateKey'] = $privateKey;
            $this->writeExtensionConfiguration($extConf);
            $content = 'Success: Generated public / private key<br /><br />Public key:<br />' . $publicKey;
        } catch (Exception $exception) {
            $content = 'Error: ' . $exception->getMessage();
        }

        return $content;
    }

    /**
     * Writes the extension's configuration (version for TYPO3 CMS 6.0+)
     *
     * @param $extensionConfigurationValue
     * @return void
     */
    protected function writeExtensionConfiguration($extensionConfigurationValue)
    {
        /** @var $extensionConfiguration \TYPO3\CMS\Core\Configuration\ExtensionConfiguration */
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class);
        $extensionConfiguration->set('caretaker_instance', '', $extensionConfigurationValue);
    }

    /**
     * Get the extension configuration
     *
     * @return array
     */
    protected function getExtConf()
    {
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker_instance']);
        if (!$extConf) {
            $extConf = array();
        }

        return $extConf;
    }
}
