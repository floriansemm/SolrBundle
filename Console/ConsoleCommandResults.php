<?php

namespace FS\SolrBundle\Console;


class ConsoleCommandResults
{

    /**
     * @var CommandResult[]
     */
    private $errors = array();

    /**
     * @var CommandResult[]
     */
    private $success = array();

    /**
     * @param CommandResult $result
     */
    public function success(CommandResult $result)
    {
        $this->success[$result->getResultId()] = $result;
    }

    /**
     * @param CommandResult $result
     */
    public function error(CommandResult $result)
    {
        $this->errors[$result->getResultId()] = $result;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * @return int
     */
    public function getOverall()
    {
        return $this->getErrored() + $this->getSucceed();
    }

    /**
     * filtering of succeed result required:
     *
     * error-event will trigger after exception. the normal program-flow continues WITH post_update/insert events
     *
     * @return int
     */
    public function getSucceed()
    {
        foreach ($this->success as $resultId => $result) {
            if (isset($this->errors[$resultId])) {
                unset($this->success[$resultId]);
            }
        }

        return count($this->success);
    }

    /**
     * @return int
     */
    public function getErrored()
    {
        return count($this->errors);
    }
}