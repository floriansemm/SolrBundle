<?php

/**
 * Created by PhpStorm.
 * User: zach
 * Date: 1/28/16
 * Time: 11:03 AM
 */

namespace FS\SolrBundle\Plugin;


use FS\SolrBundle\Logging\SolrLoggerInterface;
use Solarium\Core\Event\Events;
use Solarium\Core\Event\PostExecute;
use Solarium\Core\Event\PreExecuteRequest;
use Solarium\Core\Plugin\AbstractPlugin;

/**
 * Class LoggerPlugin
 *
 * @package FS\SolrBundle\Plugin
 */
class LoggerPlugin extends AbstractPlugin
{
    /**
     * @var SolrLoggerInterface
     */
    protected $logger;


    /**
     * {@inheritdoc}
     */
    protected function initPluginType()
    {
        $dispatcher = $this->client->getEventDispatcher();
        $dispatcher->addListener(Events::PRE_EXECUTE_REQUEST,   [$this, 'preExecuteRequest']);
        $dispatcher->addListener(Events::POST_EXECUTE_REQUEST,  [$this, 'postExecuteRequest']);
    }

    /**
     * @param PreExecuteRequest $event
     */
    public function preExecuteRequest(PreExecuteRequest $event)
    {
        $this->getLogger()->startRequest($event->getRequest()->getUri());
    }

    /**
     * Issue stop logger
     */
    public function postExecuteRequest()
    {
        $this->getLogger()->stopRequest();
    }

    /**
     * Client getter
     *
     * @return \Solarium\Core\Client\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param SolrLoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(SolrLoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Logger getter
     *
     * @return SolrLoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}