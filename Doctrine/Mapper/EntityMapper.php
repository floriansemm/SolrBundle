<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use FS\SolrBundle\Doctrine\Annotation\Index as Solr;
use Doctrine\Common\Annotations\AnnotationReader;

class EntityMapper {
	/**
	 * 
	 * @var CreateDocumentCommandInterface
	 */
	private $mappingCommand = null;
	
	public function setMappingCommand(AbstractDocumentCommand $command) {
		$this->mappingCommand = $command;
	}
	
	/**
	 * 
	 * @param object $entity
	 * @return \SolrInputDocument
	 */
	public function toDocument(MetaInformation $meta) {
		if ($this->mappingCommand instanceof AbstractDocumentCommand) {
			return $this->mappingCommand->createDocument($meta);
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param \ArrayAccess $document
	 * @param object $targetEntity
	 * @return object
	 */
	public function toEntity(\ArrayAccess $document, $targetEntity) {
		if (null === $targetEntity) {
			throw new \InvalidArgumentException('$targetEntity should not be null');
		}
		
		foreach ($document as $property => $value) {
			$property = $this->removeFieldSuffix($property);
			
			$setter = 'set'. ucfirst($property);
			
			if (method_exists($targetEntity, $setter)) {
				$targetEntity->$setter($value);
			}
		}
		
		return $targetEntity;
	}
	
	private function removeFieldSuffix($property) {
		if ($pos = strpos($property, '_')) {
			return substr($property, 0, $pos);
		}
		
		return $property;
	}
}

?>