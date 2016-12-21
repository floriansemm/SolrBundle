<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Ramsey\Uuid\Uuid;
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

        $documentId = $meta->getDocumentKey();
        if ($meta->generateDocumentId()) {
            $documentId = $meta->getDocumentName() . '_' . Uuid::uuid1()->toString();
        }
        $document->setKey(MetaInformationInterface::DOCUMENT_KEY_FIELD_NAME, $documentId);

        $document->setBoost($meta->getBoost());

        return $document;
    }
}
