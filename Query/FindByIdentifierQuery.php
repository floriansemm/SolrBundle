<?php
namespace FS\SolrBundle\Query;

class FindByIdentifierQuery extends AbstractQuery
{

    /**
     * @return string
     * @throws \RuntimeException when id or document_name is null
     */
    public function getQuery()
    {
        $idField = $this->document->id;
        $documentNameField = $this->document->document_name_s;

        if ($idField == null) {
            throw new \RuntimeException('id should not be null');
        }

        if ($documentNameField == null) {
            throw new \RuntimeException('documentName should not be null');
        }

        $query = sprintf('id:%s AND document_name_s:%s', $idField, $documentNameField);
        $this->setQuery($query);

        return parent::getQuery();
    }
}
