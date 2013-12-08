<?php

namespace FS\SolrBundle\Event\Listener;


use FS\SolrBundle\Event\Event;

class ClearIndexLogListener extends AbstractLogListener
{
    public function onClearIndex(Event $event)
    {
        $this->logger->debug(sprintf('clear index'));
    }
} 