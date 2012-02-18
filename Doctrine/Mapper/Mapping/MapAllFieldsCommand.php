<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

use FS\SolrBundle\Annotation\Type;

use Doctrine\Common\Annotations\AnnotationReader;

class MapAllFieldsCommand extends AbstractDocumentCommand {
	
	/**
	 * (non-PHPdoc)
	 * @see FS\SolrBundle\Doctrine\Mapper\Mapping.AbstractDocumentCommand::createDocument()
	 */
	public function createDocument(MetaInformation $meta) {
		$fields = $meta->getFields();
		if (count($fields) == 0) {
			return null;
		}
		
		$document = parent::createDocument($meta);
		
		foreach ($fields as $field) {
			$document->addField($field->getNameWithAlias(), $field->value);
		}
		
		return $document;
	}
}

?>