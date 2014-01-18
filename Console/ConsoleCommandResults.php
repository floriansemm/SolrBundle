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
        $this->success[] = $result;
    }

    public function error(CommandResult $result)
    {
        $this->errors[] = $result;
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
        return count($this->success);
    }

    public function getErrored()
    {
        return count($this->errors);
    }
}