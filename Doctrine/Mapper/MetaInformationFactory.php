<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use Doctrine\ORM\Configuration;

/**
 * 
 * @author fs
 *
 */
class MetaInformationFactory {
	
	/**
	 * @var MetaInformation
	 */
	private $metaInformations = null;
	
	/**
	 * @var AnnotationReader
	 */
	private $annotationReader = null;
	
	/**
	 * @var Configuration
	 */
	private $doctrineConfiguration = null;	
	
	public function __construct() {
		$this->annotationReader = new AnnotationReader(); 		
	}
	
	/**
	 * @param Configuration $doctrineConfiguration
	 */
	public function setDoctrineConfiguration(Configuration $doctrineConfiguration) {
		$this->doctrineConfiguration = $doctrineConfiguration;
	}	
	
	/**
	 * @param string|object entityAlias
	 * @return MetaInformation
	 */
	public function loadInformation($entity) {
		$className = $this->getClass($entity);

		if (!is_object($entity)) {
			$entity = new $className;
		}		
		
		if (!$this->annotationReader->hasDocumentDeclaration($entity)) {
			throw new \RuntimeException(sprintf('no declaration for document found in entity %s', $className));
		}

		$metaInformation = new MetaInformation();
		$metaInformation->setEntity($entity);
		$metaInformation->setClassName($className);
		$metaInformation->setDocumentName($this->getDocumentName($className));
		$metaInformation->setFieldMapping($this->annotationReader->getFieldMapping($entity));
		$metaInformation->setFields($this->annotationReader->getFields($entity));
		$metaInformation->setRepository($this->annotationReader->getRepository($entity));
		$metaInformation->setIdentifier($this->annotationReader->getIdentifier($entity));
		$metaInformation->setBoost($this->annotationReader->getEntityBoost($entity));
		
		return $metaInformation;
	}
	
	/**
	 * @param object $entity
	 * @throws \RuntimeException
	 * @return string
	 */
	private function getClass($entity) {
		if (is_object($entity)) {
			return get_class($entity);
		}
		
		if (class_exists($entity)) {
			return $entity;
		}
	
		list($namespaceAlias, $simpleClassName) = explode(':', $entity);
		$realClassName = $this->doctrineConfiguration->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
	
		if (!class_exists($realClassName)) {
			throw new \RuntimeException(sprintf('Unknown entity %s', $entity));
		}
	
		return $realClassName;
	}	
	
	/**
	 * @param string $fullClassName
	 * @return string
	 */
	private function getDocumentName($fullClassName) {
		$className = substr($fullClassName, (strrpos($fullClassName, '\\') + 1));
	
		return strtolower($className);
	}	
}

?>