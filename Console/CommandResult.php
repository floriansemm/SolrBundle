<?php

namespace FS\SolrBundle\Console;

/**
 * DTO class which is used to render command result reports
 */
class CommandResult
{
    /**
     * Entity Id
     *
     * @var int
     */
    private $resultId;

    /**
     * @var string
     */
    private $entityClassname;

    /**
     * Holds the error-message in case of an error
     *
     * @var string
     */
    private $errorMessage = '';

    /**
     * @param int    $resultId
     * @param string $entityClassname
     * @param string $errorMessage
     */
    public function __construct($resultId, $entityClassname, $errorMessage = '')
    {
        $this->resultId = $resultId;
        $this->entityClassname = $entityClassname;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return int
     */
    public function getResultId()
    {
        return $this->resultId;
    }

    /**
     * @return string
     */
    public function getEntityClassname()
    {
        return $this->entityClassname;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}