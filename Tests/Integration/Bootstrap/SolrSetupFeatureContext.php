<?php

namespace FS\SolrBundle\Tests\Integration\Bootstrap;

use Behat\Behat\Context\Context;
use Doctrine\ORM\Configuration;
use FS\SolrBundle\Client\Solarium\SolariumClientBuilder;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver;
use FS\SolrBundle\Doctrine\ClassnameResolver\KnownNamespaceAliases;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\IndexHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Integration\DoctrineRegistryFake;
use FS\SolrBundle\Tests\Integration\EventDispatcherFake;
use Solarium\Client;

class SolrSetupFeatureContext implements Context
{
    /**
     * @var EventDispatcherFake
     */
    private $eventDispatcher;

    /**
     * @var Client
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

        $this->eventDispatcher = new EventDispatcherFake();
    }

    /**
     * @return EventDispatcherFake
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return Client
     */
    public function getSolrClient()
    {
        return $this->solrClient;
    }

    /**
     * @return Solr
     */
    public function getSolrInstance()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

        $this->solrClient = $this->setupSolrClient();
        $metaFactory = $this->setupMetaInformationFactory();
        $entityMapper = $this->setupEntityMapper();

        $solr = new Solr(
            $this->solrClient,
            $this->eventDispatcher,
            $metaFactory,
            $entityMapper
        );

        return $solr;
    }

    /**
     * @return EntityMapper
     */
    private function setupEntityMapper()
    {
        $registry = new DoctrineRegistryFake();

        $reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());

        $metaFactory = new MetaInformationFactory($reader);

        $doctrineHydrator = new DoctrineHydrator(new ValueHydrator());
        $doctrineHydrator->setOrmManager($registry);

        $entityMapper = new EntityMapper(
            $doctrineHydrator,
            new IndexHydrator(
                new ValueHydrator()
            ),
            $metaFactory
        );

        return $entityMapper;
    }

    /**
     * @return CommandFactory
     */
    private function setupCommandFactory()
    {
        $reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());

        $factory = new CommandFactory();
        $factory->add(new MapAllFieldsCommand(new MetaInformationFactory($reader)), 'all');
        $factory->add(new MapIdentifierCommand(), 'identifier');

        return $factory;
    }

    /**
     * @return MetaInformationFactory
     */
    private function setupMetaInformationFactory()
    {
        $ormConfiguration = new Configuration();
        $ormConfiguration->addEntityNamespace('FSTest:ValidTestEntity', 'FS\SolrBundle\Tests\Doctrine\Mapper');
        $ormConfiguration->addEntityNamespace('FSTest:EntityCore0', 'FS\SolrBundle\Tests\Doctrine\Mapper');
        $ormConfiguration->addEntityNamespace('FSTest:EntityCore1', 'FS\SolrBundle\Tests\Doctrine\Mapper');

        $knowNamespaces = new KnownNamespaceAliases();
        $knowNamespaces->addEntityNamespaces($ormConfiguration);

        $classnameResolver = new ClassnameResolver($knowNamespaces);

        $reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());

        $metaFactory = new MetaInformationFactory($reader);
        $metaFactory->setClassnameResolver(
            $classnameResolver
        );

        return $metaFactory;
    }

    /**
     * Solarium Client with two cores (core0, core1)
     *
     * @return Client
     */
    private function setupSolrClient()
    {
        $config = array(
            'core0' => array(
                'host' => 'localhost',
                'port' => 8983,
                'path' => '/solr/core0',
            ),
            'core1' => array(
                'host' => 'localhost',
                'port' => 8983,
                'path' => '/solr/core1',
            ),
        );

        $builder = new SolariumClientBuilder($config, $this->eventDispatcher);
        $solrClient = $builder->build();

        return $solrClient;
    }
}