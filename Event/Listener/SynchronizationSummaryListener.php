<?php

namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Console\ConsoleCommandResults;
use FS\SolrBundle\Console\CommandResult;
use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;

class SynchronizationSummaryListener
{
    private $commandResult = null;

    public function __construct(ConsoleCommandResults $commandResult)
    {
        $this->commandResult = $commandResult;
    }

    public function onSolrError(Event $event)
    {
        if ($event instanceof ErrorEvent) {

            $this->commandResult->error(new CommandResult(
                $event->getMetaInformation()->getClassName(),
                $event->getExceptionMessage()
            ));
        }
    }

    public function onSolrSuccess(Event $event)
    {
        $this->commandResult->success(new CommandResult(
            $event->getMetaInformation()->getClassName()
        ));
    }
} 