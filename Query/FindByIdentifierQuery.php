<?php

namespace FS\SolrBundle\Query;

use FS\SolrBundle\Query\Exception\QueryException;

class FindByIdentifierQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $documentKey;

    /**
     * @param string $documentKey
     */
    public function setDocumentKey($documentKey)
    {
        $this->documentKey = $documentKey;
    }

    /**
     * @return string
     *
     * @throws QueryException when id or document_name is null
     */
    public function getQuery()
    {
        $idField = $this->documentKey;

        if ($idField == null) {
            throw new QueryException('id should not be null');
        }

        $documentLimitation = $this->createFilterQuery('id')->setQuery(sprintf('id:%s', $idField));
        $this->addFilterQuery($documentLimitation);

        $this->setQuery('*:*');

        return parent::getQuery();
    }
}
