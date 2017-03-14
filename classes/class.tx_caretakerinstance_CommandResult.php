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
 * The Command Result encapsulates the result of a Command execution.
 *
 * @author Martin Ficzel <martin@work.de>
 * @author Thomas Hempel <thomas@work.de>
 * @author Christopher Hlubek <hlubek@networkteam.com>
 * @author Tobias Liebig <liebig@networkteam.com>
 *
 */
class tx_caretakerinstance_CommandResult
{
    /**
     * @const int
     * @see tx_caretaker_Constants
     */
    const status_ok = 0;
    const status_warning = 1;
    const status_error = 2;
    const status_undefined = -1;

    /**
     * @var int Status of the Command execution
     * @see tx_caretakerinstance_CommandResult::status_ok
     */
    protected $status;

    /**
     * @var array of tx_caretakerinstance_OperationResult
     */
    protected $operationResults;

    /**
     * @var string The message of the command execution
     */
    protected $message;

    /**
     * Create a new Command Result object
     *
     * @param int|bool $status tx_caretakerinstance_CommandResult::status_ok or TRUE if execution was successful
     * @param array $operationResults of tx_caretakerinstance_OperationResult $operationResults The results of the executed operations
     * @param string $message An optional message for errors
     */
    public function __construct($status, $operationResults = array(), $message = '')
    {
        $this->status = $status;
        if ($status === true) {
            $this->status = self::status_ok;
        } elseif ($status === false) {
            $this->status = self::status_error;
        }

        $this->operationResults = $operationResults;
        $this->message = $message;
    }

    /**
     * @return bool TRUE if the execution of the whole command was successful
     */
    public function isSuccessful()
    {
        return $this->status === self::status_ok;
    }

    /**
     * @return array of tx_caretakerinstance_OperationResult The results of the operations
     */
    public function getOperationResults()
    {
        return $this->operationResults;
    }

    /**
     * @return string An optional error message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string JSON representation of the Command Result
     */
    public function toJson()
    {
        $results = array();
        if (is_array($this->operationResults)) {
            /** @var tx_caretakerinstance_OperationResult $result */
            foreach ($this->operationResults as $result) {
                $results[] = $result->toArray();
            }
        }

        $array = array(
            'status' => $this->status,
            'results' => $results,
            'message' => $this->message,
        );

        return json_encode($array);
    }

    /**
     * create a new CommandResult from a Json-String (e.g. receive by http-call)
     *
     * @param $json string
     * @return tx_caretakerinstance_CommandResult
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);

        if (is_array($data)) {
            $results = array();
            foreach ($data['results'] as $key => $result) {
                $results[] = new tx_caretakerinstance_OperationResult($result['status'], $result['value']);
            }

            return new self($data['status'], $results, $data['message']);
        }
        return new self(self::status_undefined, null, 'Cannot decode command result');
    }
}
