<?php

namespace FS\SolrBundle\Client\Solarium;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Query\DeleteDocumentQuery;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use Solarium\Core\Query\QueryInterface;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
use \Solarium\Client;

/**
 * Wrapper class for \Solarium\Client to perform actions on multiple cores
 */
class SolariumMulticoreClient
{
    /**
     * @var Client
     */
    private $solariumClient;

    /**
     * @param Client $solariumClient
     */
    public function __construct(Client $solariumClient)
    {
        $this->solariumClient = $solariumClient;
    }

    /**
     * @param DocumentInterface $doc
     * @param string            $index
     */
    public function update(DocumentInterface $doc, $index)
    {
        $update = $this->solariumClient->createUpdate();
        $update->addDocument($doc);
        $update->addCommit();

        $this->applyQuery($update, $index);
    }

    /**
     * @param DocumentInterface $document
     * @param string            $index
     */
    public function delete(DocumentInterface $document, $index)
    {
        $documentFields = $document->getFields();
        $documentKey = $documentFields[MetaInformationInterface::DOCUMENT_KEY_FIELD_NAME];

        $deleteQuery = new DeleteDocumentQuery();
        $deleteQuery->setDocument($document);
        $deleteQuery->setDocumentKey($documentKey);

        $delete = $this->solariumClient->createUpdate();
        $delete->addDeleteQuery($deleteQuery->getQuery());
        $delete->addCommit();

        $this->applyQuery($delete, $index);
    }

    /**
     * Runs a *:* delete query on all cores
     */
    public function clearCores()
    {
        $delete = $this->solariumClient->createUpdate();
        $delete->addDeleteQuery('*:*');
        $delete->addCommit();

        $this->applyOnAllCores($delete);
    }

    /**
     * @param QueryInterface $query
     * @param string         $index
     */
    private function applyQuery(QueryInterface $query, $index)
    {
        if ($index == '*') {
            $this->applyOnAllCores($query);
        } else {
            $this->solariumClient->update($query, $index);
        }
    }

    /**
     * @param QueryInterface $query
     */
    private function applyOnAllCores(QueryInterface $query)
    {
        foreach ($this->solariumClient->getEndpoints() as $endpointName => $endpoint) {
            $this->solariumClient->update($query, $endpointName);
        }
    }
} 