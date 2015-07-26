<?php

namespace FS\SolrBundle\Tests\Integration\Bootstrap;

use Behat\Behat\Context\Context;

class SolrSetupFeatureContext implements Context
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
     * Solarium Client with two cores (core0, core1)
     *
     * @return \Solarium\Client
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

        $builder = new \FS\SolrBundle\Client\SolrBuilder($config);
        $solrClient = $builder->build();

        return $solrClient;
    }
}