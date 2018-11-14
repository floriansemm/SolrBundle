<?php

namespace FS\SolrBundle\Client\Solarium\Plugin;

use FS\SolrBundle\Logging\SolrLoggerInterface;
use Solarium\Core\Client\Client;
use Solarium\Core\Event\Events;
use Solarium\Core\Event\PreExecuteRequest;
use Solarium\Core\Plugin\AbstractPlugin;

/**
 * Registers a logger to collection data about request
 */
class LoggerPlugin extends AbstractPlugin
{
    /**
     * @var SolrLoggerInterface
     */
    protected $logger;

    /**
     * @param SolrLoggerInterface $logger
     */
    public function __construct(SolrLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
        $request = $event->getRequest();
        $uri = $request->getUri();

        $path = sprintf('%s://%s:%s%s/%s', $endpoint->getScheme(), $endpoint->getHost(), $endpoint->getPort(), $endpoint->getPath(), urldecode($uri));

        $requestInformation = [
            'uri' => $path,
            'method' => $request->getMethod(),
            'raw_data' => $request->getRawData()
        ];

        $this->logger->startRequest($requestInformation);
    }

    /**
     * Issue stop logger
     */
    public function postExecuteRequest()
    {
        $this->logger->stopRequest();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}