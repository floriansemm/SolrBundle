<?php

namespace FS\SolrBundle\DataCollector;

use FS\SolrBundle\Logging\DebugLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class RequestCollector extends DataCollector
{
    /**
     * @var DebugLogger
     */
    private $logger;

    /**
     * @param DebugLogger $logger
     */
    public function __construct(DebugLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'queries' => array_map(function ($query) {
                return $this->parseQuery($query);
            }, $this->logger->getQueries())
        ];
    }

    /**
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
        list($endpoint, $params) = explode('?', $request['request']['uri']);

        $request['endpoint'] = $endpoint;
        $request['params'] = $params;
        $request['method'] = $request['request']['method'];
        $request['raw_data'] = $request['request']['raw_data'];

        if (class_exists(VarCloner::class)) {
            $varCloner = new VarCloner();

            parse_str($params, $stub);
            $request['stub'] = Kernel::VERSION_ID >= 30200 ? $varCloner->cloneVar($stub) : $stub;
        }

        return $request;
    }

    /**
     * @param DebugLogger $logger
     */
    public function setLogger(DebugLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'solr';
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [
            'queries' => [],
        ];
    }
}
