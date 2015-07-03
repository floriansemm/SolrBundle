<?php

namespace FS\SolrBundle\Query;

class FindByDocumentNameQuery extends AbstractQuery
{
    /**
     * @return string
     *
     * @throws \RuntimeException if documentName is null
     */
    public function getQuery()
    {
        $documentNameField = $this->document->document_name_s;

        if ($documentNameField == null) {
            throw new \RuntimeException('documentName should not be null');
        }

        $query = sprintf('document_name_s:%s', $documentNameField);

        $this->setQuery($query);

        return parent::getQuery();
    }
}
