<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\AnnotationReader as Reader;

class AnnotationReader {
	/**
	 * 
	 * @var Reader
	 */
	private $reader;
	
	const FIELD_TYPE_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Type';	
	const FIELD_IDENTIFIER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Id';
	
	public function __construct() {
		$this->reader = new Reader();
	}
	
	private function getPropertiesByType($entity, $type) {
		$reflectionClass = new \ReflectionClass($entity);
		$properties = $reflectionClass->getProperties();
		
		$fields = array();
		foreach ($properties as $property) {
			$annotation = $this->reader->getPropertyAnnotation($property, $type);
		
			if (null === $annotation) {
				continue;
			}
		
			$property->setAccessible(true);
			$annotation->value = $property->getValue($entity);
			$annotation->name = $property->getName();
		
			$fields[] = $annotation;
		}
		
		return $fields;		
	}
	
	/**
	 * 
	 * @param object $entity
	 * @return array
	 */
	public function getFields($entity) {
		return $this->getPropertiesByType($entity, self::FIELD_TYPE_CLASS);
	}
	
	/**
	 * 
	 * @param object $entity
	 * @return Type
	 * @throws \RuntimeException
	 */
	public function getIdentifier($entity) {
		$id = $this->getPropertiesByType($entity,self::FIELD_IDENTIFIER_CLASS);
		
		if (count($id) == 0) {
			throw new \RuntimeException('no identifer declared in entity '.get_class($entity));
		}
		
		return reset($id);
	}
	
	/**
	 * 
	 * @param object $entity
	 * @return array
	 */
	public function getFieldMapping($entity) {
		$fields = $this->getPropertiesByType($entity, self::FIELD_TYPE_CLASS);

		$mapping = array();
		foreach ($fields as $field) {
			if ($field instanceof Type) {
				$mapping[$field->getNameWithAlias()] = $field->name;
			}
		}
		
		$id = $this->getIdentifier($entity);
		$mapping['id'] = $id->name;
		
		return $mapping;
	}
}

?>