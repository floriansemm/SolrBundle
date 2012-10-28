<?php
namespace FS\SolrBundle;

class SolrConnectionFactory {
	
	/**
	 * @var array
	 */
	private $connections = array();
	
	/**
	 * @param array $connections
	 */
	public function __construct(array $connections = array()) {
		$this->connections = $connections;
	}
	
	/**
	 * @param string $name
	 * @throws \RuntimeException if there are no connections
	 * @throws \InvalidArgumentException if the given connection name is unknown
	 * @return SolrConnection
	 */
	public function getConnection($name) {
		if (count($this->connections) == 0) {
			throw new \RuntimeException('No connections found');
		}
		
		if (!array_key_exists($name, $this->connections)) {
			throw new \InvalidArgumentException(sprintf('Unknown connection %s', $name));
		}
		
		$connectionParams = $this->connections[$name];
		
		return new SolrConnection($connectionParams);;
	}
}

?>