<?php
namespace FS\SolrBundle\Doctrine\Mapper\Command;

use FS\SolrBundle\Annotation\Type;

use Doctrine\Common\Annotations\AnnotationReader;

class CreateFreshDocumentCommand implements CreateDocumentCommandInterface {

	/**
	 * 
	 * @var \FS\BlogBundle\Solr\Doctrine\Annotation\AnnotationReader
	 */
	private $reader;
	
	public function __construct(\FS\SolrBundle\Doctrine\Annotation\AnnotationReader $reader) {
		$this->reader = $reader;
	}
	
	/* (non-PHPdoc)
	 * @seeFS\SolrBundle\Doctrine\Mapper\Command.CreateDocumentCommandInterface::createDocument()
	 */
	public function createDocument($entity) {
		$fields = $this->reader->getFields($entity);
		
		if (count($fields) == 0) {
			return null;
		}
		
		$document = new \SolrInputDocument();
		
		foreach ($fields as $field) {
			$document->addField($field->getNameWithAlias(), $field->value);
		}
		
		$document->addField('id', $entity->getId());
		
		return $document;
	}
	
	/**
	 * 
	 * @return FS\SolrBundle\Doctrine\Annotation\AnnotationReader
	 */
	public function getAnnotationReader() {
		return $this->reader;
	}
}

?>