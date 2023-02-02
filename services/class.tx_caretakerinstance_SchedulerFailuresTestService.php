<?php

class tx_caretakerinstance_SchedulerFailuresTestService extends tx_caretakerinstance_RemoteTestServiceBase
{
    /**
     * {@inheritDoc}
     * @return tx_caretaker_TestResult
     */
    public function runTest(): tx_caretaker_TestResult
    {
        $operations = array(
            array('GetScheduler', array()),
        );

        $commandResult = $this->executeRemoteOperations($operations);

        if (!$this->isCommandResultSuccessful($commandResult)) {
            return $this->getFailedCommandResultTestResult($commandResult);
        }

        $results = $commandResult->getOperationResults();

        $errors = array();

        /** @var tx_caretakerinstance_OperationResult $operationResult */
        foreach ($results as $operationResult) {
            if (!$operationResult->isSuccessful()) {
                $exceptions = $operationResult->getValue();
                if (is_string($exceptions)) {
                    if ('Operation [GetScheduler] unknown' === $exceptions) {
                        return tx_caretaker_TestResult::create(
                            tx_caretaker_Constants::state_error,
                            0,
                            'The Instance does not support this Operation. Did you forget to install the additional extension?' . PHP_EOL . 'Original Exception: ' . $exceptions
                        );
                    }
                    return tx_caretaker_TestResult::create(
                        tx_caretaker_Constants::state_error,
                        0,
                        'Command execution failed: ' . $exceptions
                    );
                }
                foreach ($exceptions as $taskUid => $exception) {
                    $errors[] = sprintf(
                        'Scheduler [%d] failed with message: Exception %d in %s line %d "%s"',
                        $taskUid,
                        $exception['code'],
                        $exception['file'],
                        $exception['line'],
                        $exception['message']
                    );
                }
            }
        }

        if (!empty($errors)) {
            return tx_caretaker_TestResult::create(
                tx_caretaker_Constants::state_error,
                0,
                'Operation execution failed: ' . PHP_EOL . implode(PHP_EOL, $errors)
            );
        }

        return tx_caretaker_TestResult::create(tx_caretaker_Constants::state_ok, 0, 'No Scheduler errors found');
    }
}
