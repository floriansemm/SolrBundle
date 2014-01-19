<?php

namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Console\ConsoleCommandResults;
use FS\SolrBundle\Console\CommandResult;
use FS\SolrBundle\Console\ConsoleResultFactory;
use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;

class SynchronizationSummaryListener
{
    private $commandResult = null;
    private $resultFactory = null;

    public function __construct(ConsoleCommandResults $commandResult, ConsoleResultFactory $resultFactory)
    {
        $this->commandResult = $commandResult;
        $this->resultFactory = $resultFactory;
    }

    public function onSolrError(Event $event)
    {
        if ($event instanceof ErrorEvent) {
            $this->commandResult->error(
                $this->resultFactory->fromEvent($event)
            );
        }
    }

    public function onSolrSuccess(Event $event)
    {
        $this->commandResult->success(
            $this->resultFactory->fromEvent($event)
        );
    }
} 