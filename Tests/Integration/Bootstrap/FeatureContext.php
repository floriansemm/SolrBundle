<?php

namespace FS\SolrBundle\Tests\Integration\Bootstrap;

use Behat\Behat\Context\Context;
use FS\SolrBundle\Event\Events;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * Features context.
 */
class FeatureContext extends SolrSetupFeatureContext
{
    /**
     * @param int    $entityId
     * @param string $documentName
     *
     * @throws \RuntimeException if Events::POST_INSERT or Events::PRE_INSERT was fired or $entityId not equal to found document id
     */
    public function assertInsertSuccessful($entityId, $documentName)
    {
        if (!$this->getEventDispatcher()->eventOccurred(Events::POST_INSERT) ||
            !$this->getEventDispatcher()->eventOccurred(Events::PRE_INSERT)
        ) {
            throw new \RuntimeException('Insert was not successful');
        }

        $document = $this->findDocumentById($entityId, $documentName);
        $idFieldValue = $document->getFields()['id'];

        if (intval($this->removeKeyFieldNameSuffix($idFieldValue)) !== intval($entityId)) {
            throw new \RuntimeException(sprintf('found document has ID %s, expected %s', $idFieldValue, $entityId));
        }
    }

    /**
     * Field value documentname_1 becomes 1
     *
     * @param string $keyField
     *
     * @return string
     */
    private function removeKeyFieldNameSuffix($keyField)
    {
        return substr($keyField, strpos($keyField, '_') + 1);
    }

    /**
     * uses Solarium query to find a document by ID
     *
     * @return Document
     *
     * @throws \RuntimeException if resultset is empty, no document with given ID was found
     */
    protected function findDocumentById($entityId, $documentName, $index = null)
    {
        $client = $this->getSolrClient();

        $identifier = $documentName . '_' . $entityId;

        $query = $client->createSelect();
        $query->setQuery(sprintf('id:%s', $identifier));
        $resultset = $client->select($query, $index);

        $documents = $resultset->getDocuments();

        /* @var Document $document */
        foreach ($documents as $document) {
            $idFieldValue = $document->getFields()['id'];

            if (intval($idFieldValue) == intval($identifier)) {
                return $document;
            }
        }

        return null;
    }
}
