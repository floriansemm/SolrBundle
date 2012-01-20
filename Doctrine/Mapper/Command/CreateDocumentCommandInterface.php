<?php
namespace FS\SolrBundle\Doctrine\Mapper\Command;

interface CreateDocumentCommandInterface {
	
	/**
	 * 
	 * @param object $entity
	 * @return SolrDocument
	 */
	public function createDocument($entity);
}

?>