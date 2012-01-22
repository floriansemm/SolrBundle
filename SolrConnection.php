<?php
namespace FS\SolrBundle;

class SolrConnection {
	
	/**
	 * 
	 * @var array
	 */
	private $connection = array();
	
	public function __construct(array $connection = array()) {
		$this->connection = $connection;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getConnection() {
		return $this->connection;
	}
}

?>