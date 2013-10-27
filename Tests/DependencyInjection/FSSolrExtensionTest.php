<?php

namespace FS\SolrBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FS\SolrBundle\DependencyInjection\FSSolrExtension;

/**
 *
 * @group extension
 */
class FSSolrExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerBuilder
     */
    private $container = null;

    public function setUp()
    {
        $this->container = new ContainerBuilder();

        $definition = new Definition();
        $this->container->setDefinition('doctrine.orm.default_configuration', $definition);
    }

    private function commonConfig()
    {
        return array(array(

            'endpoints' => array(
                'default' => array(
                    'host' => '192.168.178.24',
                    'port' => 8983,
                    'path' => '/solr/',
                )
            ),
            'clients' => array(
                'default' => array('endpoints' => array('default'))
            )
        ));
    }

    /**
     * @test
     */
    public function solrClientsWithCommonSettings()
    {
        $config = $this->commonConfig();

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('solr.client.adapter.builder.default'));
        $this->assertTrue($this->container->hasDefinition('solr.client.adapter.default'));
        $this->assertTrue($this->container->hasDefinition('solr.client.default'));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The endpoint foo_endpoint is not defined
     */
    public function solrClientsWithUndefinedEndpoint()
    {
        $config = array(array(
            'endpoints' => array(
                'default' => array(
                    'host' => '192.168.178.24',
                    'port' => 8983,
                    'path' => '/solr/',
                )
            ),
            'clients' => array(
                'default' => array('endpoints' => array('foo_endpoint'))
            )
        ));

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);
    }

    /**
     * @test
     */
    public function noClientsConfiguredFirstEndpointIsFallback()
    {
        $config = array(array(
            'endpoints' => array(
                'default1' => array(
                    'host' => '192.168.178.24',
                    'port' => 8983,
                    'path' => '/solr/',
                ),
                'default2' => array(
                    'host' => '192.168.178.24',
                    'port' => 8983,
                    'path' => '/solr/',
                )
            )
        ));

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('solr.client.adapter.builder.default1'));
        $this->assertTrue($this->container->hasDefinition('solr.client.adapter.default1'));
        $this->assertTrue($this->container->hasDefinition('solr.client.default1'));
    }

    public function testDoctrineORMSetup()
    {
        $config = $this->commonConfig();

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->has('solr.update.document.orm.listener'), 'update listener');
        $this->assertTrue($this->container->has('solr.delete.document.orm.listener'), 'delete listener');
        $this->assertTrue($this->container->has('solr.add.document.orm.listener'), 'insert listener');

        $this->assertDefinitionHasTag('solr.update.document.orm.listener', 'doctrine.event_listener');
        $this->assertDefinitionHasTag('solr.delete.document.orm.listener', 'doctrine.event_listener');
        $this->assertDefinitionHasTag('solr.add.document.orm.listener', 'doctrine.event_listener');

        $doctrineArguments = $this->container->getDefinition('solr.doctrine.configuration')->getArguments();
        $arguments = array_pop($doctrineArguments);
        $doctrineConfiguration = $arguments;

        $this->assertEquals('doctrine.orm.default_configuration', $doctrineConfiguration);
    }

    public function testDoctrineODMSetup()
    {
        $config = $this->commonConfig();

        $this->container->setParameter('doctrine_mongodb.odm.document_managers', true);

        $extension = new FSSolrExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->has('solr.update.document.odm.listener'), 'update listener');
        $this->assertTrue($this->container->has('solr.delete.document.odm.listener'), 'delete listener');
        $this->assertTrue($this->container->has('solr.add.document.odm.listener'), 'insert listener');

        $this->assertDefinitionHasTag('solr.update.document.odm.listener', 'doctrine_mongodb.odm.event_listener');
        $this->assertDefinitionHasTag('solr.delete.document.odm.listener', 'doctrine_mongodb.odm.event_listener');
        $this->assertDefinitionHasTag('solr.add.document.odm.listener', 'doctrine_mongodb.odm.event_listener');

        $doctrineArguments = $this->container->getDefinition('solr.doctrine.configuration')->getArguments();
        $arguments = array_pop($doctrineArguments);
        $doctrineConfiguration = $arguments;

        $this->assertEquals('doctrine_mongodb.odm.default_configuration', $doctrineConfiguration);
    }

    private function assertDefinitionHasTag($definition, $tag)
    {
        $this->assertTrue(
            $this->container->getDefinition($definition)->hasTag($tag),
            sprintf('%s with %s tag', $definition, $tag)
        );
    }
}

