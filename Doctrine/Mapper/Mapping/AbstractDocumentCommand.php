<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * maps the common fields id and document_name
 */
abstract class AbstractDocumentCommand
{

    /**
     * @param MetaInformationInterface $meta
     *
     * @return Document
     */
    public function createDocument(MetaInformationInterface $meta)
    {
        $document = new Document();

        $key = $meta->getDocumentName() . '_' . $meta->getEntityId();
        $document->setKey('id', $key);

        $document->setBoost($meta->getBoost());

        return $document;
    }
}
