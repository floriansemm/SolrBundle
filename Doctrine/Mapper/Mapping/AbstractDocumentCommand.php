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

        $document->setKey(MetaInformationInterface::DOCUMENT_KEY_FIELD_NAME, $meta->getDocumentKey());
        $document->setBoost($meta->getBoost());

        return $document;
    }
}
