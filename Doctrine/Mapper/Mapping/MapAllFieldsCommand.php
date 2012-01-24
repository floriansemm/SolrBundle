<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Annotation\Type;

use Doctrine\Common\Annotations\AnnotationReader;

class MapAllFieldsCommand extends AbstractDocumentCommand {
	
	/* (non-PHPdoc)
	 * @seeFS\SolrBundle\Doctrine\Mapper\Command.CreateDocumentCommandInterface::createDocument()
	 */
	public function createDocument($entity) {
		$fields = $this->reader->getFields($entity);
		
		if (count($fields) == 0) {
			return null;
		}
		
		$document = parent::createDocument($entity);
		
		foreach ($fields as $field) {
			$document->addField($field->getNameWithAlias(), $field->value);
		}
		
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