<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;

/**
 * Creates a error log-entry if a error occurred
 */
class ErrorLogListener extends AbstractLogListener
{

    /**
     * @param Event $event
     */
    public function onSolrError(Event $event)
    {
        $exceptionMessage = '';
        if ($event instanceof ErrorEvent) {
            $exceptionMessage = $event->getExceptionMessage();
        }

        $this->logger->error(
            sprintf('the error "%s" occure while executing event %s', $exceptionMessage, $event->getSolrAction())
        );

        if ($event->hasSourceEvent()) {
            $event->getSourceEvent()->stopPropagation();
        }
    }
}
