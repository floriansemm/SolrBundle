<?php

namespace FS\SolrBundle\Client;

use Solarium\Client;

class ClientPool
{

    /**
     * @var Client[]
     */
    private $clients = array();

    /**
     * @param string $clientName
     * @param Client $client
     */
    public function addClient($clientName, Client $client)
    {
        $this->clients[$clientName] = $client;
    }

    /**
     * @param $clientName
     *
     * @return Client
     *
     * @throws \RuntimeException if $clientName is unknown
     */
    public function getClient($clientName)
    {
        $clients = array_keys($this->clients);

        if (!isset($clientName)) {
            throw new \RuntimeException(sprintf('Unknow client %s, knowns clients: %s', $clientName, implode(', ', $clients)));
        }

        return $this->clients[$clientName];
    }
} 