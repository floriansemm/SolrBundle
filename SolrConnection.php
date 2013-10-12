<?php
namespace FS\SolrBundle;

class SolrConnection {
	
	/**
	 * @var array
	 */
	private $connection = array();
	
	/**
	 * @var \SolrClient
	 */
	private $client = null;
	
	/**
	 * @param array $connection
	 */
	public function __construct(array $connection = array()) {
		$this->connection = $connection;
		
		$this->client = new \SolrClient($this->connection);
	}


	/**
	 * @return array
	 */
	public function getConnection() {
		return $this->connection;
	}
	
	/**
	 * @throws \RuntimeException if the client cannot connect so Solr host
	 * @return \SolrClient
	 */
	public function getClient() {
		try {
			$this->client->ping();
		} catch (\Exception $e) {
			$host = $this->connection['hostname'];
			$port = $this->connection['port'];
			$path = $this->connection['path'];
			
			throw new \RuntimeException(sprintf('Cannot connect to Solr host: %s:%s, path: %s', $host, $port, $path));
		}
		
		return $this->client;
	}
}
