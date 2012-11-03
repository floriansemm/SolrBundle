<?php
namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class Event {
	
	/**
	 * @var object
	 */
	private $client = null;
	
	/**
	 * @var MetaInformation
	 */
	private $metainformation = null;

	/**
	 * @param object $client
	 * @param MetaInformation $metainformation
	 */
	public function __construct($client, MetaInformation $metainformation) {
		$this->client = $client;
		$this->metainformation = $metainformation;
	}
	
	/**
	 * @return MetaInformation
	 */
	public function getMetaInformation() {
		return $this->metainformation;
	}
	
	/**
	 * @return string
	 */
	public function getCore() {
		$options = $this->client->getOptions();
		
		if (isset($options['path'])) {
			return $options['path'];
		}
		
		return '';
	}
}

?>