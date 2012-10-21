<?php
namespace FS\SolrBundle\Query;

class FindByIdentifierQuery extends AbstractQuery {
	
	/**
	 * @var \SolrInputDocument
	 */
	private $document = null;
	
	/**
	 * @param \SolrInputDocument $document
	 */
	public function __construct(\SolrInputDocument $document) {
		parent::__construct();
		
		$this->document = $document;
	}

	/**
	 * (non-PHPdoc)
	 * @see \FS\SolrBundle\Query\AbstractQuery::getQueryString()
	 */
	public function getQueryString() {
		$idField = $this->document->getField('id');
		$documentNameField = $this->document->getField('document_name_s');
		
		if ($idField == null) {
			throw new \RuntimeException('id should not be null');
		}
		
		if ($documentNameField == null) {
			throw new \RuntimeException('documentName should not be null');
		}		
		
		$this->solrQuery->addFilterQuery(sprintf('document_name_s:%s', $documentNameField->values[0]));
		
		$query = sprintf('id:%s', $idField->values[0]);
		
		return $query;
	}
}

?>