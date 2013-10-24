<?php
namespace FS\SolrBundle;

use Solarium\Client;

class SolrConnection
{

    /**
     * @var array
     */
    private $connection = array();

    /**
     * @var Solarium\Client
     */
    private $client = null;

    /**
     * @param array $connection
     */
    public function __construct(array $connection = array())
    {
        $this->connection = $connection;

        $this->client = new Client(array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => '192.168.178.24',
                    'port' => 8983,
                    'path' => '/solr/',
                )
            )
        ));
    }


    /**
     * @return array
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @throws \RuntimeException if the client cannot connect so Solr host
     * @return Solarium\Client
     */
    public function getClient()
    {
        try {
            $ping = $this->client->createPing();

            $this->client->ping($ping);
        } catch (\Exception $e) {
            $host = $this->connection['hostname'];
            $port = $this->connection['port'];
            $path = $this->connection['path'];

            throw new \RuntimeException(sprintf('Cannot connect to Solr host: %s:%s, path: %s', $host, $port, $path));
        }

        return $this->client;
    }
}
