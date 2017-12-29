<?php

declare(strict_types=1);

namespace FS\SolrBundle;

use FS\SolrBundle\Client\Solarium\SolariumMulticoreClient;
use FS\SolrBundle\Doctrine\Mapper\EntityMapperInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Helper\DocumentHelper;
use FS\SolrBundle\Query\QueryBuilder;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Repository\RepositoryInterface;
use Solarium\Plugin\BufferedAdd\BufferedAdd;
use Solarium\QueryType\Update\Query\Document\Document;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;
use FS\SolrBundle\Event\Events;
use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\Repository\Repository;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
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
        EventDispatcherInterface $manager = null,
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
    public function getClient(): Client
    {
        return $this->solrClientCore;
    }

    /**
     * @return EntityMapper
     */
    public function getMapper(): EntityMapper
    {
        return $this->entityMapper;
    }

    /**
     * @return MetaInformationFactory
     */
    public function getMetaFactory(): MetaInformationFactory
    {
        return $this->metaInformationFactory;
    }

    /**
     * @return DocumentHelper
     */
    public function getDocumentHelper()
    {
        return new DocumentHelper($this);
    }

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return SolrQuery
     */
    public function createQuery($entity): SolrQuery
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $query = new SolrQuery();
        $query->setSolr($this);
        $query->setEntity($metaInformation->getClassName());
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
    public function getQueryBuilder($entity): QueryBuilderInterface
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        return new QueryBuilder($this, $metaInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($entity): RepositoryInterface
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $repositoryClass = $metaInformation->getRepository();
        if (class_exists($repositoryClass)) {
            $repositoryInstance = new $repositoryClass($this, $metaInformation);

            if ($repositoryInstance instanceof Repository) {
                return $repositoryInstance;
            }

            throw new SolrException(sprintf('%s must extends the FS\SolrBundle\Repository\Repository', $repositoryClass));
        }

        return new Repository($this, $metaInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($entity): QueryBuilderInterface
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        return new QueryBuilder($this, $metaInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDocument($entity)
    {
        $metaInformations = $this->metaInformationFactory->loadInformation($entity);

        $event = new Event($this->solrClientCore, $metaInformations);
        $this->eventManager->dispatch(Events::PRE_DELETE, $event);

        if ($document = $this->entityMapper->toDocument($metaInformations)) {

            try {
                $indexName = $metaInformations->getIndex();

                $client = new SolariumMulticoreClient($this->solrClientCore);

                $client->delete($document, $indexName);
            } catch (\Exception $e) {
                $errorEvent = new ErrorEvent(null, $metaInformations, 'delete-document', $event);
                $errorEvent->setException($e);

                $this->eventManager->dispatch(Events::ERROR, $errorEvent);

                throw new SolrException($e->getMessage(), $e->getCode(), $e);
            }

            $this->eventManager->dispatch(Events::POST_DELETE, $event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDocument($entity): bool
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        if (!$this->addToIndex($metaInformation, $entity)) {
            return false;
        }

        $event = new Event($this->solrClientCore, $metaInformation);
        $this->eventManager->dispatch(Events::PRE_INSERT, $event);

        $doc = $this->toDocument($metaInformation);

        $this->addDocumentToIndex($doc, $metaInformation, $event);

        $this->eventManager->dispatch(Events::POST_INSERT, $event);

        return true;
    }

    /**
     * @param MetaInformationInterface $metaInformation
     * @param object                   $entity
     *
     * @return boolean
     *
     * @throws SolrException if callback method not exists
     */
    private function addToIndex(MetaInformationInterface $metaInformation, $entity): bool
    {
        if (!$metaInformation->hasSynchronizationFilter()) {
            return true;
        }

        $callback = $metaInformation->getSynchronizationCallback();
        if (!method_exists($entity, $callback)) {
            throw new SolrException(sprintf('unknown method %s in entity %s', $callback, get_class($entity)));
        }

        return $entity->$callback();
    }

    /**
     * Get select query
     *
     * @param AbstractQuery $query
     *
     * @return SolariumQuery
     */
    public function getSelectQuery(AbstractQuery $query): SolariumQuery
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
    public function query(AbstractQuery $query): array
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

            throw new SolrException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Number of overall found documents for a given query
     *
     * @return integer
     */
    public function getNumFound(): int
    {
        return $this->numberOfFoundDocuments;
    }

    /**
     * clears the whole index by using the query *:*
     *
     * @throws SolrException if an error occurs
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

            throw new SolrException($e->getMessage(), $e->getCode(), $e);
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

            if ($core == '') {
                $core = null;
            }
            $buffer->setEndpoint($core);

            $buffer->commit();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateDocument($entity): bool
    {
        $metaInformations = $this->metaInformationFactory->loadInformation($entity);

        if (!$this->addToIndex($metaInformations, $entity)) {
            return false;
        }

        $event = new Event($this->solrClientCore, $metaInformations);
        $this->eventManager->dispatch(Events::PRE_UPDATE, $event);

        $doc = $this->toDocument($metaInformations);

        $this->addDocumentToIndex($doc, $metaInformations, $event);

        $this->eventManager->dispatch(Events::POST_UPDATE, $event);

        return true;
    }

    /**
     * @param MetaInformationInterface $metaInformation
     *
     * @return DocumentInterface
     */
    private function toDocument(MetaInformationInterface $metaInformation): DocumentInterface
    {
        $doc = $this->entityMapper->toDocument($metaInformation);

        return $doc;
    }

    /**
     * @param object                   $doc
     * @param MetaInformationInterface $metaInformation
     * @param Event                    $event
     *
     * @throws SolrException if an error occurs
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

            throw new SolrException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
