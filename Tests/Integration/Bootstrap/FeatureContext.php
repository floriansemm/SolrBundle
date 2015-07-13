<?php

namespace FS\SolrBundle\Tests\Integration\Bootstrap;

use Behat\Behat\Context\Context;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * Features context.
 */
class FeatureContext implements Context
{

    /**
     * @var \FS\SolrBundle\Tests\Integration\EventDispatcherFake
     */
    private $eventDispatcher;

    /**
     * @var \Solarium\Client
     */
    private $solrClient;

    public function __construct()
    {
        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        } else {
            require_once 'vendor/autoload.php';
        }

        $this->eventDispatcher = new \FS\SolrBundle\Tests\Integration\EventDispatcherFake();
    }

    /**
     * @return \FS\SolrBundle\Tests\Integration\EventDispatcherFake
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return \Solarium\Client
     */
    public function getSolrClient()
    {
        return $this->solrClient;
    }

    /**
     * @return \FS\SolrBundle\Solr
     */
    public function getSolrInstance()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

        $this->solrClient = $this->setupSolrClient();
        $factory = $this->setupCommandFactory();
        $metaFactory = $this->setupMetaInformationFactory();
        $entityMapper = $this->setupEntityMapper();

        $solr = new \FS\SolrBundle\Solr(
            $this->solrClient,
            $factory,
            $this->eventDispatcher,
            $metaFactory,
            $entityMapper
        );

        return $solr;
    }

    /**
     * @return \FS\SolrBundle\Doctrine\Mapper\EntityMapper
     */
    private function setupEntityMapper()
    {
        $registry = new \FS\SolrBundle\Tests\Integration\DoctrineRegistryFake();

        $entityMapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper(
            new \FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator(
                $registry,
                new \FS\SolrBundle\Doctrine\Hydration\ValueHydrator()
            ),
            new \FS\SolrBundle\Doctrine\Hydration\IndexHydrator(
                new \FS\SolrBundle\Doctrine\Hydration\ValueHydrator()
            )
        );

        return $entityMapper;
    }

    /**
     * @return \FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory
     */
    private function setupCommandFactory()
    {
        $factory = new \FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory();
        $factory->add(new \FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand(), 'all');
        $factory->add(new \FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand(), 'identifier');

        return $factory;
    }

    /**
     * @return \FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory
     */
    private function setupMetaInformationFactory()
    {
        $ormConfiguration = new \Doctrine\ORM\Configuration();
        $ormConfiguration->addEntityNamespace('FSTest:ValidTestEntity', 'FS\SolrBundle\Tests\Doctrine\Mapper');

        $knowNamespaces = new \FS\SolrBundle\Doctrine\ClassnameResolver\KnownNamespaceAliases();
        $knowNamespaces->addEntityNamespaces($ormConfiguration);

        $classnameResolver = new \FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver($knowNamespaces);

        $metaFactory = new \FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory();
        $metaFactory->setClassnameResolver(
            $classnameResolver
        );

        return $metaFactory;
    }

    /**
     * Solarium Client with one core (core0)
     *
     * @return \Solarium\Client
     */
    private function setupSolrClient()
    {
        $config = array(
            'default' => array(
                'host' => 'localhost',
                'port' => 8983,
                'path' => '/solr/multicore/core0',
            )
        );

        $builder = new \FS\SolrBundle\Client\SolrBuilder($config);
        $solrClient = $builder->build();

        return $solrClient;
    }

    /**
     * @param int $entityId
     *
     * @throws \RuntimeException if Events::POST_INSERT or Events::PRE_INSERT was fired or $entityId not equal to found document id
     */
    public function assertInsertSuccessful($entityId)
    {
        if (!$this->eventDispatcher->eventOccurred(\FS\SolrBundle\Event\Events::POST_INSERT) ||
            !$this->eventDispatcher->eventOccurred(\FS\SolrBundle\Event\Events::PRE_INSERT)
        ) {
            throw new \RuntimeException('Insert was not successful');
        }

        $document = $this->findDocumentById($entityId);
        $idFieldValue = $document->getFields()['id'];

        if (intval($idFieldValue) !== intval($entityId)) {
            throw new \RuntimeException(sprintf('found document has ID %s, expected %s', $idFieldValue, $entityId));
        }
    }

    /**
     * uses Solarium query to find a document by ID
     *
     * @return Document
     *
     * @throws \RuntimeException if resultset is empty, no document with given ID was found
     */
    protected function findDocumentById($entityId)
    {
        $client = $this->getSolrClient();

        $query = $client->createSelect();
        $query->setQuery(sprintf('id:%s', $entityId));
        $resultset = $client->select($query);

        if ($resultset->getNumFound() == 0) {
            throw new \RuntimeException(sprintf('could not find document with id %s after update', $entityId));
        }

        $documents = $resultset->getDocuments();

        /* @var Document $document */
        foreach ($documents as $document) {
            $idFieldValue = $document->getFields()['id'];

            if (intval($idFieldValue) == intval($entityId)) {
                return $document;
            }
        }

        throw new \RuntimeException(sprintf('no document with Id %s was found', $entityId));
    }
}
