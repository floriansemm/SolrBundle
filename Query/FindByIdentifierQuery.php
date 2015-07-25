<?php

namespace FS\SolrBundle\Query;

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
     * @throws \RuntimeException when id or document_name is null
     */
    public function getQuery()
    {
        $idField = $this->documentKey;

        if ($idField == null) {
            throw new \RuntimeException('id should not be null');
        }

        $query = sprintf('id:%s', $idField);
        $this->setQuery($query);

        return parent::getQuery();
    }
}
