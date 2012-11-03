<?php
namespace FS\SolrBundle\Doctrine;

class Configuration {
	
	/**
	 * @var mixed
	 */
	private $doctrineConfiguration = null;
	
	/**
	 * @param mixed $configuration
	 */
	public function __construct($configuration = null) {
		$this->doctrineConfiguration = $configuration;
	}
	
	/**
	 * @param string $entity
	 */
	public function getNamespace($entity) {
		if ($this->doctrineConfiguration instanceof \Doctrine\ORM\Configuration) {
			return $this->doctrineConfiguration->getEntityNamespace($entity);
		}
		
		if ($this->doctrineConfiguration instanceof \Doctrine\ODM\MongoDB\Configuration) {
			return $this->doctrineConfiguration->getDocumentNamespace($entity);
		}
	}
}

?>