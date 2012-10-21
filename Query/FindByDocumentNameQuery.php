<?php
namespace FS\SolrBundle\Query;

class FindByDocumentNameQuery extends AbstractQuery {
	/**
	 *
	 * @var \SolrInputDocument
	 */
	private $document = null;
	
	public function __construct(\SolrInputDocument $document) {
		parent::__construct();
	
		$this->document = $document;
	}
	
	public function getQueryString() {
		$documentNameField = $this->document->getField('document_name_s');
	
		if ($documentNameField == null) {
			throw new \RuntimeException('documentName should not be null');
		}
	
		$this->solrQuery->addFilterQuery(sprintf('document_name_s:%s', $documentNameField->values[0]));
		
		return '';
	}
}

?>