<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Solarium\QueryType\Update\Query\Document\Document;

abstract class AbstractDocumentCommand
{

    /**
     * @param MetaInformation $meta
     * @return Document
     */
    public function createDocument(MetaInformation $meta)
    {
        $document = new Document();

        $document->addField('id', $meta->getEntityId());
        $document->addField('document_name_s', $meta->getDocumentName());
        $document->setBoost($meta->getBoost());

        return $document;
    }
}
