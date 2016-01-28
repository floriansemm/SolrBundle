<?php

/**
 * Created by PhpStorm.
 * User: zach
 * Date: 1/28/16
 * Time: 11:49 AM
 */

namespace FS\SolrBundle\DataCollector;


use FS\SolrBundle\Logging\DebugLogger;
use FS\SolrBundle\Logging\SolrLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * Class SolrRequestCollector
 *
 * @package FS\SolrBundle\DataCollector
 */
class SolrRequestCollector extends DataCollector
{
    /**
     * @var DebugLogger
     */
    private $logger;


    /**
     * SolrRequestCollector constructor.
     * @param DebugLogger $logger
     */
    public function __construct(DebugLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request         $request
     * @param Response        $response
     * @param \Exception|null $exception
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'queries'   => array_map(function($query) {
                return $this->parseQuery($query);
            }, $this->logger->getQueries())
        ];
    }

    /**
     * Query count
     *
     * @return int
     */
    public function getQueryCount()
    {
        return count($this->data['queries']);
    }

    /**
     * @return array
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * @return int
     */
    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $query) {
            $time += $query['executionMS'];
        }

        return $time;
    }

    /**
     * @param array $request
     *
     * @return array
     */
    public function parseQuery($request)
    {
        list($endpoint, $params) = explode('?', $request['request']);

        return array_merge($request, [
            'endpoint'  => $endpoint,
            'params'    => $params
        ]);
    }

    /**
     * @param DebugLogger $logger
     *
     * @return $this
     */
    public function setLogger(DebugLogger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'solr';
    }
}