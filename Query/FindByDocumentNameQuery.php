<?php

namespace FS\SolrBundle\Query;

/**
 * Builds a wildcard query to find all documents
 *
 * Query: id:documentname_*
 */
class FindByDocumentNameQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $documentName;

    /**
     * @param string $documentName
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }

    /**
     * @return string
     *
     * @throws \RuntimeException if documentName is null
     */
    public function getQuery()
    {
        $documentName = $this->documentName;

        if ($documentName == null) {
            throw new \RuntimeException('documentName should not be null');
        }

        $documentLimitation = $this->createFilterQuery('id')->setQuery(sprintf('id:%s_*', $documentName));
        $this->addFilterQuery($documentLimitation);

        $this->setQuery('*:*');

        return parent::getQuery();
    }
}
