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
 * An Operation Result encapsulates the result of
 * an Operation execution.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 * @package TYPO3
 * @subpackage caretaker_instance
 */
class tx_caretakerinstance_OperationResult
{

    /**
     * @var boolean
     */
    protected $status;

    /**
     * @var array|string
     */
    protected $value;

    /**
     * Construct a new operation result
     *
     * @param boolean $status
     * @param mixed $value
     */
    public function __construct($status, $value)
    {
        $this->status = $status;
        $this->value = $value;
    }

    /**
     * @return boolean If the operation was executed successful
     */
    public function isSuccessful()
    {
        return $this->status;
    }

    /**
     * @return array|string The operation value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return The Operation Result as an array
     */
    public function toArray()
    {
        return ['status' => $this->status, 'value' => $this->value];
    }

}
