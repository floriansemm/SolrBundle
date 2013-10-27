<?php

namespace FS\SolrBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FSSolrExtension extends Extension
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('event_listener.xml');
        $loader->load('log_listener.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setupClients($config, $container);

        $container->setParameter('solr.auto_index', $config['auto_index']);

        $this->setupDoctrineListener($config, $container);
        $this->setupDoctrineConfiguration($config, $container);

    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function setupClients(array $config, ContainerBuilder $container)
    {
        $clients = $config['clients'];

        if (count($clients) == 0) {
            $endpoints = array_keys($config['endpoints']);
            $defaultClient = $endpoints[0];

            $clients[$defaultClient] = array('endpoints' => array($defaultClient));
        }

        foreach ($clients as $clientName => $client) {
            // client mit endpoint
            $endpoint = array_pop($client['endpoints']);

            $builderDefinition = new DefinitionDecorator('solr.client.adapter.builder');
            $builder = sprintf('solr.client.adapter.builder.%s', $clientName);
            $container->setDefinition($builder, $builderDefinition);

            if (!isset($config['endpoints'][$endpoint])) {
                throw new RuntimeException(sprintf('The endpoint %s is not defined', $endpoint));
            }

            $connectInformation[$endpoint] = $config['endpoints'][$endpoint];
            $builderDefinition->replaceArgument(0, $connectInformation);

            // adapter
            $clientAdapterDefinition = new DefinitionDecorator('solr.client.adapter');
            $clientAdapter = sprintf('solr.client.adapter.%s', $clientName);
            $container->setDefinition($clientAdapter, $clientAdapterDefinition);
            $clientAdapterDefinition->setFactoryService($builder);

            // eigentlich client
            $clientDefinition = new DefinitionDecorator('solr.client');
            $container->setDefinition(sprintf('solr.client.%s', $clientName), $clientDefinition);

            $clientDefinition->replaceArgument(0, new Reference($clientAdapter));
        }
    }

    /**
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function setupDoctrineConfiguration(array $config, ContainerBuilder $container)
    {
        if ($this->isOrmConfigured($container)) {
            $entityManagers = $container->getParameter('doctrine.entity_managers');

            $entityManagersNames = array_keys($entityManagers);
            foreach($entityManagersNames as $entityManager) {
                $container->getDefinition('solr.doctrine.classnameresolver')->addMethodCall(
                    'addOrmConfiguration',
                    array(new Reference(sprintf('doctrine.orm.%s_configuration', $entityManager)))
                );
            }
        }

        if ($this->isODMConfigured($container)) {
            $documentManagers = $container->getParameter('doctrine_mongodb.odm.document_managers');

            $documentManagersNames = array_keys($documentManagers);
            foreach($documentManagersNames as $documentManager) {
                $container->getDefinition('solr.doctrine.classnameresolver')->addMethodCall(
                    'addOdmConfiguration',
                    array(new Reference(sprintf('doctrine_mongodb.odm.%s_configuration', $documentManager)))
                );
            }
        }

        $container->getDefinition('solr.meta.information.factory')->addMethodCall(
            'setClassnameResolver',
            array(new Reference('solr.doctrine.classnameresolver'))
        );
    }

    /**
     * doctrine_orm and doctrine_mongoDB can't be used together. mongo_db wins when it is configured.
     *
     * listener-methods expecting different types of events
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function setupDoctrineListener(array $config, ContainerBuilder $container)
    {
        $autoIndexing = $container->getParameter('solr.auto_index');

        if ($autoIndexing == false) {
            return;
        }

        if ($this->isODMConfigured($container)) {
            $container->getDefinition('solr.delete.document.odm.listener')->addTag(
                'doctrine_mongodb.odm.event_listener',
                array('event' => 'preRemove')
            );
            $container->getDefinition('solr.update.document.odm.listener')->addTag(
                'doctrine_mongodb.odm.event_listener',
                array('event' => 'postUpdate')
            );
            $container->getDefinition('solr.add.document.odm.listener')->addTag(
                'doctrine_mongodb.odm.event_listener',
                array('event' => 'postPersist')
            );

        }

        if ($this->isOrmConfigured($container)) {
            $container->getDefinition('solr.add.document.orm.listener')->addTag(
                'doctrine.event_listener',
                array('event' => 'postPersist')
            );
            $container->getDefinition('solr.delete.document.orm.listener')->addTag(
                'doctrine.event_listener',
                array('event' => 'preRemove')
            );
            $container->getDefinition('solr.update.document.orm.listener')->addTag(
                'doctrine.event_listener',
                array('event' => 'postUpdate')
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     * @return boolean
     */
    private function isODMConfigured(ContainerBuilder $container)
    {
        return $container->hasParameter('doctrine_mongodb.odm.document_managers');
    }

    /**
     * @param ContainerBuilder $container
     * @return boolean
     */
    private function isOrmConfigured(ContainerBuilder $container)
    {
        return $container->hasParameter('doctrine.entity_managers');
    }
}
