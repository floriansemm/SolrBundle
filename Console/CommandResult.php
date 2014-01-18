<?php

namespace FS\SolrBundle\Console;


class CommandResult
{

    private $message;
    private $entity;

    public function __construct($entity, $message = '')
    {
        $this->entity = $entity;
        $this->message = $message;
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