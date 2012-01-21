<?php
namespace FS\SolrBundle\Query;

class DeleteDocumentQuery {
	public function getQueryString(\SolrInputDocument $document) {
		$idField = $document->getField('id');
		$documentNameField = $document->getField('document_name_s');
		
		$query = 'id:'.$idField->values[0].' AND document_name_s:'.$documentNameField->values[0];
		
		return $query;
	}
}

?>