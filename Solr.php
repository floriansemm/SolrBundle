<?php

namespace FS\SolrBundle;

use FS\SolrBundle\Client\Solarium\SolariumMulticoreClient;
use FS\SolrBundle\Doctrine\Mapper\EntityMapperInterface;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Query\QueryBuilder;
use FS\SolrBundle\Query\QueryBuilderInterface;
use Solarium\Plugin\BufferedAdd\BufferedAdd;
use Solarium\QueryType\Update\Query\Document\Document;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;
use FS\SolrBundle\Event\Events;
use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\Repository\Repository;
use Solarium\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class allows to index doctrine entities
 */
class Solr implements SolrInterface
{
    /**
     * @var Client
     */
    protected $solrClientCore = null;

    /**
     * @var EntityMapper
     */
    protected $entityMapper = null;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventManager = null;

    /**
     * @var MetaInformationFactory
     */
    protected $metaInformationFactory = null;

    /**
     * @var int numFound
     */
    private $numberOfFoundDocuments = 0;

    /**
     * @param Client                   $client
     * @param EventDispatcherInterface $manager
     * @param MetaInformationFactory   $metaInformationFactory
     * @param EntityMapperInterface    $entityMapper
     */
    public function __construct(
        Client $client,
        EventDispatcherInterface $manager,
        MetaInformationFactory $metaInformationFactory,
        EntityMapperInterface $entityMapper
    )
    {
        $this->solrClientCore = $client;
        $this->eventManager = $manager;
        $this->metaInformationFactory = $metaInformationFactory;

        $this->entityMapper = $entityMapper;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->solrClientCore;
    }

    /**
     * @return EntityMapper
     */
    public function getMapper()
    {
        return $this->entityMapper;
    }

    /**
     * @return CommandFactory
     */
    public function getCommandFactory()
    {
        return $this->commandFactory;
    }

    /**
     * @return MetaInformationFactory
     */
    public function getMetaFactory()
    {
        return $this->metaInformationFactory;
    }

    /**
     * @param object $entity
     *
     * @return SolrQuery
     */
    public function createQuery($entity)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);
        $class = $metaInformation->getClassName();
        $entity = new $class;

        $query = new SolrQuery();
        $query->setSolr($this);
        $query->setEntity($entity);
        $query->setIndex($metaInformation->getIndex());
        $query->setMetaInformation($metaInformation);
        $query->setMappedFields($metaInformation->getFieldMapping());

        return $query;
    }

    /**
     * @param string|object $entity
     *
     * @return QueryBuilderInterface
     */
    public function getQueryBuilder($entity)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        return new QueryBuilder($this, $metaInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($entityAlias)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entityAlias);
        $class = $metaInformation->getClassName();

        $entity = new $class;

        $repositoryClass = $metaInformation->getRepository();
        if (class_exists($repositoryClass)) {
            $repositoryInstance = new $repositoryClass($this, $entity);

            if ($repositoryInstance instanceof Repository) {
                return $repositoryInstance;
            }

            throw new \RuntimeException(sprintf(
                '%s must extends the FS\SolrBundle\Repository\Repository',
                $repositoryClass
            ));
        }

        return new Repository($this, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($entityAlias)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entityAlias);
        $class = $metaInformation->getClassName();

        $entity = new $class;

        return new QueryBuilder($this, $metaInformation, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDocument($entity)
    {
        $this->entityMapper->setMappingCommand(new MapIdentifierCommand());

        $metaInformations = $this->metaInformationFactory->loadInformation($entity);

        if ($document = $this->entityMapper->toDocument($metaInformations)) {
            $event = new Event($this->solrClientCore, $metaInformations);
            $this->eventManager->dispatch(Events::PRE_DELETE, $event);

            try {
                $indexName = $metaInformations->getIndex();

                $client = new SolariumMulticoreClient($this->solrClientCore);

                $client->delete($document, $indexName);
            } catch (\Exception $e) {
                $errorEvent = new ErrorEvent(null, $metaInformations, 'delete-document', $event);
                $errorEvent->setException($e);

                $this->eventManager->dispatch(Events::ERROR, $errorEvent);
            }

            $this->eventManager->dispatch(Events::POST_DELETE, $event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDocument($entity)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        if (!$this->addToIndex($metaInformation, $entity)) {
            return false;
        }

        $doc = $this->toDocument($metaInformation);

        $event = new Event($this->solrClientCore, $metaInformation);
        $this->eventManager->dispatch(Events::PRE_INSERT, $event);

        $this->addDocumentToIndex($doc, $metaInformation, $event);

        $this->eventManager->dispatch(Events::POST_INSERT, $event);
    }

    /**
     * @param MetaInformationInterface $metaInformation
     * @param object                   $entity
     *
     * @return boolean
     *
     * @throws \BadMethodCallException if callback method not exists
     */
    private function addToIndex(MetaInformationInterface $metaInformation, $entity)
    {
        if (!$metaInformation->hasSynchronizationFilter()) {
            return true;
        }

        $callback = $metaInformation->getSynchronizationCallback();
        if (!method_exists($entity, $callback)) {
            throw new \BadMethodCallException(sprintf('unknown method %s in entity %s', $callback, get_class($entity)));
        }

        return $entity->$callback();
    }

    /**
     * {@inheritdoc}
     */
    public function computeChangeSet(array $doctrineChangeSet, $entity)
    {
        /* If not set, act the same way as if there are changes */
        if (empty($doctrineChangeSet)) {
            return array();
        }

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $documentChangeSet = array();

        /* Check all Solr fields on this entity and check if this field is in the change set */
        foreach ($metaInformation->getFields() as $field) {
            if (array_key_exists($field->name, $doctrineChangeSet)) {
                $documentChangeSet[] = $field->name;
            }
        }

        return $documentChangeSet;
    }

    /**
     * Get select query
     *
     * @param AbstractQuery $query
     *
     * @return \Solarium\QueryType\Select\Query\Query
     */
    public function getSelectQuery(AbstractQuery $query)
    {
        $selectQuery = $this->solrClientCore->createSelect($query->getOptions());

        $selectQuery->setQuery($query->getQuery());
        $selectQuery->setFilterQueries($query->getFilterQueries());
        $selectQuery->setSorts($query->getSorts());
        $selectQuery->setFields($query->getFields());

        return $selectQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function query(AbstractQuery $query)
    {
        $entity = $query->getEntity();
        $runQueryInIndex = $query->getIndex();
        $selectQuery = $this->getSelectQuery($query);

        try {
            $response = $this->solrClientCore->select($selectQuery, $runQueryInIndex);

            $this->numberOfFoundDocuments = $response->getNumFound();

            $entities = array();
            foreach ($response as $document) {
                $entities[] = $this->entityMapper->toEntity($document, $entity);
            }

            return $entities;
        } catch (\Exception $e) {
            $errorEvent = new ErrorEvent(null, null, 'query solr');
            $errorEvent->setException($e);

            $this->eventManager->dispatch(Events::ERROR, $errorEvent);

            return array();
        }
    }

    /**
     * Number of overall found documents for a given query
     *
     * @return integer
     */
    public function getNumFound()
    {
        return $this->numberOfFoundDocuments;
    }

    /**
     * clears the whole index by using the query *:*
     */
    public function clearIndex()
    {
        $this->eventManager->dispatch(Events::PRE_CLEAR_INDEX, new Event($this->solrClientCore));

        try {
            $client = new SolariumMulticoreClient($this->solrClientCore);
            $client->clearCores();
        } catch (\Exception $e) {
            $errorEvent = new ErrorEvent(null, null, 'clear-index');
            $errorEvent->setException($e);

            $this->eventManager->dispatch(Events::ERROR, $errorEvent);
        }

        $this->eventManager->dispatch(Events::POST_CLEAR_INDEX, new Event($this->solrClientCore));
    }

    /**
     * @param array $entities
     */
    public function synchronizeIndex($entities)
    {
        /** @var BufferedAdd $buffer */
        $buffer = $this->solrClientCore->getPlugin('bufferedadd');
        $buffer->setBufferSize(500);

        $allDocuments = array();
        foreach ($entities as $entity) {
            $metaInformations = $this->metaInformationFactory->loadInformation($entity);

            if (!$this->addToIndex($metaInformations, $entity)) {
                continue;
            }

            $doc = $this->toDocument($metaInformations);

            $allDocuments[$metaInformations->getIndex()][] = $doc;
        }

        foreach ($allDocuments as $core => $documents) {
            $buffer->addDocuments($documents);
            $buffer->setEndpoint($core);

            $buffer->commit();
        }
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function updateDocument($entity)
    {
        $metaInformations = $this->metaInformationFactory->loadInformation($entity);

        if (!$this->addToIndex($metaInformations, $entity)) {
            return false;
        }

        $doc = $this->toDocument($metaInformations);

        $event = new Event($this->solrClientCore, $metaInformations);
        $this->eventManager->dispatch(Events::PRE_UPDATE, $event);

        $this->addDocumentToIndex($doc, $metaInformations, $event);

        $this->eventManager->dispatch(Events::POST_UPDATE, $event);

        return true;
    }

    /**
     * @param MetaInformationInterface $metaInformation
     *
     * @return Document
     */
    private function toDocument(MetaInformationInterface $metaInformation)
    {
        $this->entityMapper->setMappingCommand(new MapAllFieldsCommand($this->metaInformationFactory));
        $doc = $this->entityMapper->toDocument($metaInformation);

        return $doc;
    }

    /**
     * @param object                   $doc
     * @param MetaInformationInterface $metaInformation
     * @param Event                    $event
     */
    private function addDocumentToIndex($doc, MetaInformationInterface $metaInformation, Event $event)
    {
        try {
            $indexName = $metaInformation->getIndex();

            $client = new SolariumMulticoreClient($this->solrClientCore);
            $client->update($doc, $indexName);

        } catch (\Exception $e) {
            $errorEvent = new ErrorEvent(null, $metaInformation, json_encode($this->solrClientCore->getOptions()), $event);
            $errorEvent->setException($e);

            $this->eventManager->dispatch(Events::ERROR, $errorEvent);
        }
    }
}
