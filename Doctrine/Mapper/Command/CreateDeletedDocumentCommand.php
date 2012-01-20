<?php
namespace FS\SolrBundle\Doctrine\Mapper\Command;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

class CreateDeletedDocumentCommand implements CreateDocumentCommandInterface {

	private $reader;
	
	public function __construct(AnnotationReader $reader) {
		$this->reader = $reader;
	}
	
	/* (non-PHPdoc)
	 * @see FS\SolrBundle\Doctrine\Mapper\Command.CreateDocumentCommandInterface::createDocument()
	 */
	public function createDocument($entity) {
		$idField = $this->reader->getIdentifier($entity);
		
		$document = new \SolrInputDocument();
		$document->addField($idField->name, $idField->value);
		
		return $document;
	}
}
