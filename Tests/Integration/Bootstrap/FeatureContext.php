<?php

use Behat\Behat\Context\BehatContext;


require_once '../../../../autoload.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{

    /**
     * @var \FS\SolrBundle\Tests\Integration\EventDispatcherFake
     */
    private $eventDispatcher;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('save', new SaveEntityFeatureContext());

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
     * @return \FS\SolrBundle\Solr
     */
    public function getSolrInstance()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

        $solrClient = $this->setupSolrClient();
        $factory = $this->setupCommandFactory();
        $metaFactory = $this->setupMetaInformationFactory();

        $solr = new \FS\SolrBundle\Solr(
            $solrClient,
            $factory,
            $this->eventDispatcher,
            $metaFactory
        );

        return $solr;
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
        $ormConfiguration = new Doctrine\ORM\Configuration();
        $ormConfiguration->addEntityNamespace('FSTest:ValidTestEntity', 'FS\SolrBundle\Tests\Doctrine\Mapper');

        $classnameResolver = new \FS\SolrBundle\Doctrine\ClassnameResolver();
        $classnameResolver->addOrmConfiguration($ormConfiguration);

        $metaFactory = new \FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory();
        $metaFactory->setClassnameResolver(
            $classnameResolver
        );

        return $metaFactory;
    }

    /**
     * @return \Solarium\Client
     */
    private function setupSolrClient()
    {
        $config = array(
            'default' => array(
                'host' => '192.168.178.24',
                'port' => 8983,
                'path' => '/solr/',
            )
        );

        $builder = new \FS\SolrBundle\Builder\SolrBuilder($config);
        $solrClient = $builder->build();

        return $solrClient;
    }

    public function assertInsertSuccessful()
    {
        if (!$this->eventDispatcher->eventOccurred(\FS\SolrBundle\Event\Events::POST_INSERT) ||
            !$this->eventDispatcher->eventOccurred(\FS\SolrBundle\Event\Events::PRE_INSERT)) {
            throw new RuntimeException('Insert was not successful');
        }
    }
}
