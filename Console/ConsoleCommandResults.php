<?php

namespace FS\SolrBundle\Console;


class ConsoleCommandResults
{

    /**
     * @var CommandResult[]
     */
    private $errors;

    /**
     * @var CommandResult[]
     */
    private $success;

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

    public function getSucceed()
    {
        $succeed = 0;
        foreach ($this->success as $resultId => $result) {
            if (!isset($this->errors[$resultId])) {
                $succeed++;
            }
        }

        return $succeed;
    }

    public function getErrored()
    {
        return count($this->errors);
    }
}