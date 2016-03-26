<?php

namespace FS\SolrBundle\Client\Solarium;

use FS\SolrBundle\Logging\SolrLoggerInterface;
use Solarium\Core\Event\Events;
use Solarium\Core\Event\PostExecute;
use Solarium\Core\Event\PreExecuteRequest;
use Solarium\Core\Plugin\AbstractPlugin;

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
        $endpoint = $event->getEndpoint();
        $uri = $event->getRequest()->getUri();

        $path = sprintf('%s://%s:%s%s/%s', $endpoint->getScheme(), $endpoint->getHost(), $endpoint->getPort(), $endpoint->getPath(), urldecode($uri));

        $this->getLogger()->startRequest($path);
    }

    /**
     * Issue stop logger
     */
    public function postExecuteRequest()
    {
        $this->getLogger()->stopRequest();
    }

    /**
     * @return \Solarium\Core\Client\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param SolrLoggerInterface $logger
     */
    public function setLogger(SolrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return SolrLoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}