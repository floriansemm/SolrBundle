<?php

namespace FS\SolrBundle\Console;


class CommandResult
{
    /**
     * @var int
     */
    private $resultId;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $entity;

    /**
     * @param int $resultId
     * @param string $entity
     * @param string string $message
     */
    public function __construct($resultId, $entity, $message = '')
    {
        $this->resultId = $resultId;
        $this->entity = $entity;
        $this->message = $message;
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
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}