<?php
namespace FS\SolrBundle\Query;

use Solarium\QueryType\Update\Query\Document\Document;

class FindByIdentifierQuery extends AbstractQuery
{

    /**
     * @var Document
     */
    private $document = null;

    /**
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        parent::__construct();

        $this->document = $document;
    }

    /**
     * (non-PHPdoc)
     * @see \FS\SolrBundle\Query\AbstractQuery::getQueryString()
     */
    public function getQueryString()
    {
        $idField = $this->document->id;
        $documentNameField = $this->document->document_name_s;

        if ($idField == null) {
            throw new \RuntimeException('id should not be null');
        }

        if ($documentNameField == null) {
            throw new \RuntimeException('documentName should not be null');
        }

        $this->solrQuery->addFilterQuery(sprintf('document_name_s:%s', $documentNameField));

        $query = sprintf('id:%s', $idField);

        return $query;
    }
}
