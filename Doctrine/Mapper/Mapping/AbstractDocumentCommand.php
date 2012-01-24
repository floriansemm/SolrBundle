<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

abstract class AbstractDocumentCommand {
	
	/**
	 * 
	 * @var AnnotationReader
	 */
	protected $reader;
	
	public function __construct(AnnotationReader $reader) {
		$this->reader = $reader;
	}	
	
	public function getDocumentName($entity) {
		$fullClassName = get_class($entity);
		$className = substr($fullClassName, (strrpos($fullClassName, '\\') + 1));
		
		return strtolower($className);
	}
	
	/**
	 *
	 * @param object $entity
	 * @return \SolrDocument
	 */
	public function createDocument($entity) {
		$document = new \SolrInputDocument();
		
		$document->addField('id', $entity->getId());
		$document->addField('document_name_s', $this->getDocumentName($entity));
		
		return $document;
	}	
}

?>