<?php

namespace FS\SolrBundle\Console;


use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;

class ConsoleResultFactory
{
    public function fromEvent(Event $event)
    {
        return new CommandResult(
            $this->getResultId($event),
            $this->getClassname($event),
            $this->getMessage($event)
        );

    }

    private function getResultId(Event $event)
    {
        if ($event->getMetaInformation() == null) {
            return null;
        }

        return $event->getMetaInformation()->getEntityId();
    }

    private function getClassname(Event $event)
    {
        if ($event->getMetaInformation() == null) {
            return '';
        }

        return $event->getMetaInformation()->getClassName();
    }

    private function getMessage(Event $event)
    {
        if (!$event instanceof ErrorEvent) {
            return '';
        }

        return $event->getExceptionMessage();
    }
} 