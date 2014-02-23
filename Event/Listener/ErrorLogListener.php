<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;

class ErrorLogListener extends AbstractLogListener
{

    public function onSolrError(Event $event)
    {

        $exceptionMessage = '';
        if ($event instanceof ErrorEvent) {
            $exceptionMessage = $event->getExceptionMessage();
        }

        $this->logger->debug(
            sprintf('the error "%s" occure while executing event %s', $exceptionMessage, $event->getSolrAction())
        );

        if ($event->hasSourceEvent()) {
            $event->getSourceEvent()->stopPropagation();
        }
    }
}
