<?php

namespace FS\SolrBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class FSSolrExtension extends Extension
{

    /**
     * @param array $configs
     * @param ContainerBuilder $container
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
        $endpoints = $config['endpoints'];

        $clientPoolDefnition = $container->getDefinition('solr.client.pool');

        foreach ($endpoints as $endpointName => $endpointConfiguration) {
            $clientBuilderName = sprintf('solr.client.adapter.builder.%s', $endpointName);

            $builderDefinition = new DefinitionDecorator('solr.client.adapter.builder');
            $connectInformation = array();
            $connectInformation[$endpointName] = $endpointConfiguration;
            $builderDefinition->replaceArgument(0, $connectInformation);

            $container->setDefinition($clientBuilderName, $builderDefinition);

            $clientAdapterDefinition = new DefinitionDecorator('solr.client.adapter');
            $clientAdapter = sprintf('solr.client.adapter.%s', $endpointName);
            $container->setDefinition($clientAdapter, $clientAdapterDefinition);
            $clientAdapterDefinition->setFactoryService($clientBuilderName);

            $clientPoolDefnition->addMethodCall('addClient', array($endpointName, new Reference($clientAdapter)));
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
                $container->getDefinition('solr.doctrine.classnameresolver.known_entity_namespaces')->addMethodCall(
                    'addEntityNamespaces',
                    array(new Reference(sprintf('doctrine.orm.%s_configuration', $entityManager)))
                );
            }
        }

        if ($this->isODMConfigured($container)) {
            $documentManagers = $container->getParameter('doctrine_mongodb.odm.document_managers');

            $documentManagersNames = array_keys($documentManagers);
            foreach($documentManagersNames as $documentManager) {
                $container->getDefinition('solr.doctrine.classnameresolver.known_entity_namespaces')->addMethodCall(
                    'addDocumentNamespaces',
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
