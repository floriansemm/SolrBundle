<?php
namespace FS\SolrBundle;

use Solarium\QueryType\Update\Query\Document\Document;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;
use FS\SolrBundle\Event\EventManager;
use FS\SolrBundle\Event\Events;
use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\Repository\Repository;
use Solarium\Client;

class Solr
{


    /**
     * @var Client
     */
    private $solrClient = null;

    /**
     * @var EntityMapper
     */
    private $entityMapper = null;

    /**
     * @var CommandFactory
     */
    private $commandFactory = null;

    /**
     * @var EventManager
     */
    private $eventManager = null;

    /**
     * @var MetaInformationFactory
     */
    private $metaInformationFactory = null;

    /**
     * @param Client $client
     * @param CommandFactory $commandFactory
     * @param EventManager $manager
     * @param MetaInformationFactory $metaInformationFactory
     */
    public function __construct(
        Client $client,
        CommandFactory $commandFactory,
        EventManager $manager,
        MetaInformationFactory $metaInformationFactory
    ) {
        $this->solrClient = $client;
        $this->commandFactory = $commandFactory;
        $this->eventManager = $manager;
        $this->metaInformationFactory = $metaInformationFactory;

        $this->entityMapper = new EntityMapper();
    }

    /**
     * @return EntityMapper
     */
    public function getMapper()
    {
        return $this->entityMapper;
    }

    /**
     * @param EntityMapper $mapper
     */
    public function setMapper($mapper)
    {
        $this->entityMapper = $mapper;
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

        $query->setMappedFields($metaInformation->getFieldMapping());

        return $query;
    }

    /**
     * @param string repositoryClassity
     * @return RepositoryInterface
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
     * @param object $entity
     */
    public function removeDocument($entity)
    {
        $command = $this->commandFactory->get('identifier');

        $this->entityMapper->setMappingCommand($command);

        $metaInformations = $this->metaInformationFactory->loadInformation($entity);

        if ($document = $this->entityMapper->toDocument($metaInformations)) {
            $deleteQuery = new FindByIdentifierQuery();
            $deleteQuery->setDocument($document);

            try {
                $delete = $this->solrClient->createUpdate();
                $delete->addDeleteQuery($deleteQuery->getQuery());
                $delete->addCommit();

                $this->solrClient->update($delete);
            } catch (\Exception $e) {
                $errorEvent = new ErrorEvent(null, null, 'delete-document');
                $errorEvent->setException($e);

                $this->eventManager->handle(EventManager::ERROR, $errorEvent);
            }

            $this->eventManager->handle(EventManager::DELETE, new Event($this->solrClient, $metaInformations));
        }
    }

    /**
     * @param object $entity
     */
    public function addDocument($entity)
    {
        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        if (!$this->addToIndex($metaInformation, $entity)) {
            return;
        }

        $doc = $this->toDocument($metaInformation);

        $this->eventManager->handle(EventManager::INSERT, new Event($this->solrClient, $metaInformation));

        $this->addDocumentToIndex($doc);
    }

    /**
     * @param MetaInformation $metaInformation
     * @param object $entity
     * @throws \BadMethodCallException if callback method not exists
     * @return boolean
     */
    private function addToIndex(MetaInformation $metaInformation, $entity)
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
     * @param AbstractQuery $query
     * @return array found entities
     */
    public function query(AbstractQuery $query)
    {
        $entity = $query->getEntity();

        $queryString = $query->getQuery();
        $query = $this->solrClient->createSelect();
        $query->setQuery($queryString);

        try {
            $response = $this->solrClient->select($query);
        } catch (\Exception $e) {
            $errorEvent = new ErrorEvent(null, null, 'query solr');
            $errorEvent->setException($e);

            $this->eventManager->handle(EventManager::ERROR, $errorEvent);

            return array();
        }

        if ($response->getNumFound() == 0) {
            return array();
        }

        $targetEntity = $entity;
        $mappedEntities = array();
        foreach ($response as $document) {
            $mappedEntities[] = $this->entityMapper->toEntity($document, $targetEntity);
        }

        return $mappedEntities;
    }

    public function clearIndex()
    {
        try {
            $delete = $this->solrClient->createUpdate();
            $delete->addDeleteQuery('*:*');
            $delete->addCommit();

            $this->solrClient->update($delete);
        } catch (\Exception $e) {
            $errorEvent = new ErrorEvent(null, null, 'clear-index');
            $errorEvent->setException($e);

            $this->eventManager->handle(EventManager::ERROR, $errorEvent);
        }
    }

    /**
     * @param object $entity
     */
    public function synchronizeIndex($entity)
    {
        $this->updateDocument($entity);
    }

    /**
     * @param object $entity
     */
    public function updateDocument($entity)
    {
        $metaInformations = $this->metaInformationFactory->loadInformation($entity);

        $doc = $this->toDocument($metaInformations);

        $this->eventManager->handle(EventManager::UPDATE, new Event($this->solrClient, $metaInformations));

        $this->addDocumentToIndex($doc);

        return true;
    }

    /**
     * @param MetaInformation metaInformationsy
     * @return Document|null
     */
    private function toDocument(MetaInformation $metaInformation)
    {
        $command = $this->commandFactory->get('all');

        $this->entityMapper->setMappingCommand($command);
        $doc = $this->entityMapper->toDocument($metaInformation);

        return $doc;
    }

    /**
     * @param Document $doc
     */
    private function addDocumentToIndex($doc)
    {
        try {
            $update = $this->solrClient->createUpdate();
            $update->addDocument($doc);
            $update->addCommit();

            $this->solrClient->update($update);
        } catch (\Exception $e) {
            $errorEvent = new ErrorEvent(null, null, json_encode($this->solrClient->getOptions()));
            $errorEvent->setException($e);

            $this->eventManager->handle(EventManager::ERROR, $errorEvent);
        }
    }
}
