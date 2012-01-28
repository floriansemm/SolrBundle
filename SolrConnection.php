<?php
namespace FS\SolrBundle;

class SolrConnection {
	
	/**
	 * 
	 * @var array
	 */
	private $connection = array();
	
	/**
	 * 
	 * @var \SolrClient
	 */
	private $client = null;
	
	public function __construct(array $connection = array()) {
		$this->connection = $connection;
		
		$this->client = new \SolrClient($this->connection);
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getConnection() {
		return $this->connection;
	}
	
	/**
	 * 
	 * @return \SolrClient
	 */
	public function getClient() {
		return $this->client;
	}
}

?>