<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use FS\SolrBundle\Doctrine\Annotation\Index as Solr;
use Doctrine\Common\Annotations\AnnotationReader;

class EntityMapper {
	/**
	 * @var CreateDocumentCommandInterface
	 */
	private $mappingCommand = null;
	
	/**
	 * @param AbstractDocumentCommand $command
	 */
	public function setMappingCommand(AbstractDocumentCommand $command) {
		$this->mappingCommand = $command;
	}
	
	/**
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
	 * @param \ArrayAccess $document
	 * @param object $targetEntity
	 * @return object
	 */
	public function toEntity(\ArrayAccess $document, $sourceTargetEntity) {
		if (null === $sourceTargetEntity) {
			throw new \InvalidArgumentException('$sourceTargetEntity should not be null');
		}
		
		$targetEntity = clone $sourceTargetEntity;
		
		$reflectionClass = new \ReflectionClass($targetEntity);
		foreach ($document as $property => $value) {
			try {
				$classProperty = $reflectionClass->getProperty($this->removeFieldSuffix($property));
			} catch (\ReflectionException $e) { 
				try {
					$classProperty = $reflectionClass->getProperty($this->toCamelCase($this->removeFieldSuffix($property)));
				} catch (\ReflectionException $e) {
					continue;
				}
			}

			$classProperty->setAccessible(true);
			$classProperty->setValue($targetEntity, $value);
		}
		
		return $targetEntity;
	}
	
	/**
	 * returns the clean fieldname without type-suffix
	 * 
	 * eg: title_s => title
	 * 
	 * @param string $property
	 * @return string
	 */
	private function removeFieldSuffix($property) {
		if ($pos = strpos($property, '_')) {
			return substr($property, 0, $pos);
		}
		
		return $property;
	}

	/**
	 * returns field name camelcased if it has underlines
	 * 
	 * eg: user_id => userId
	 *
	 * @param string $fieldname
	 * @return string
	 */
	private function toCamelCase($fieldname) {
		$words = str_replace('_', ' ', $fieldname);
		$words = ucwords($words);
		$pascalCased = str_replace(' ', '', $words);
		return lcfirst($pascalCased);
	}
}

?>