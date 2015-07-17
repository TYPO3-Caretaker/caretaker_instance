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
 * Helper which provides service methods for fast and convenient registration of
 * testServices.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker
 */
class tx_caretakerinstance_ServiceHelper {

	public static $deferredTestServicesToRegister = array();

	/**
	 * Adds a service for caretaker. The service is registered and the type and flexform is added to the testconf
	 *
	 * @param string $extKey kex of the extension wich is adding the service
	 * @param string $path path to the flexform and service class without slahes before and after
	 * @param string $key key wich is used for to identify the service
	 * @param string $title title of the testservice
	 * @param string $description description of the testservice
	 */
	public static function registerCaretakerTestService($extKey, $path, $key, $title, $description = '') {
		if ($GLOBALS['T3_SERVICES']['caretaker_test_service'] === NULL) {
			// EXT:caretaker not yet loaded. Memorize the data for later registration
			self::$deferredTestServicesToRegister[$extKey . $path . $key] = array(
					$extKey, $path, $key, $title, $description
			);
			return;
		} else {
			tx_caretaker_ServiceHelper::registerCaretakerTestService($extKey, $path, $key, $title, $description);
		}
	}

}
