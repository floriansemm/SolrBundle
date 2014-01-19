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

    public function success(CommandResult $result)
    {
        $this->success[$result->getResultId()] = $result;
    }

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

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

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

    public function getErrored()
    {
        return count($this->errors);
    }
}