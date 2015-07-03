<?php

namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\Event;

/**
 * Create a log-entry if the index was cleared
 */
class ClearIndexLogListener extends AbstractLogListener
{
    /**
     * @param Event $event
     */
    public function onClearIndex(Event $event)
    {
        $this->logger->debug(sprintf('clear index'));
    }
} 