<?php

use Behat\Behat\Context\BehatContext;


require_once '../../../../autoload.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('save', new SaveEntityFeatureContext());
    }

    /**
     * @return \FS\SolrBundle\Solr
     */
    public static function getSolrInstance()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

        $config = array('default' => array(
            'host' => '192.168.178.24',
            'port' => 8983,
            'path' => '/solr/',
        ));

        $builder = new \FS\SolrBundle\Builder\SolrBuilder($config);

        $factory = new \FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory();
        $factory->add(new \FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand(), 'all');
        $factory->add(new \FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand(), 'identifier');

        $ormConfiguration = new Doctrine\ORM\Configuration();
        $ormConfiguration->addEntityNamespace('FSTest:ValidTestEntity', 'FS\SolrBundle\Tests\Doctrine\Mapper');

        $classnameResolver = new \FS\SolrBundle\Doctrine\ClassnameResolver();
        $classnameResolver->addOrmConfiguration($ormConfiguration);

        $metaFactory = new \FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory();
        $metaFactory->setClassnameResolver(
            $classnameResolver
        );

        $solr = new \FS\SolrBundle\Solr(
            $builder->build(),
            $factory,
            new \FS\SolrBundle\Tests\Integration\EventDispatcherFake(),
            $metaFactory
        );

        return $solr;
    }

}
