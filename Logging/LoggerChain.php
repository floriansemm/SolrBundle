<?php

namespace FS\SolrBundle\Logging;

class LoggerChain implements SolrLoggerInterface
{
    /**
     * @var SolrLoggerInterface[]
     */
    protected $loggers = [];

    /**
     * @param SolrLoggerInterface $logger
     */
    public function addLogger(SolrLoggerInterface $logger)
    {
        array_push($this->loggers, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function startRequest($request)
    {
        foreach ($this->loggers as $logger) {
            $logger->startRequest($request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopRequest()
    {
        foreach ($this->loggers as $logger) {
            $logger->stopRequest();
        }
    }
}