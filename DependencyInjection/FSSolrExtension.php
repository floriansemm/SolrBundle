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
     * {@inheritdoc}
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

        if (!$container->hasParameter('solr.auto_index')) {
            $container->setParameter('solr.auto_index', $config['auto_index']);
        }

        $this->setupDoctrineListener($config, $container);
        $this->setupDoctrineConfiguration($config, $container);

    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function setupClients(array $config, ContainerBuilder $container)
    {
        $endpoints = $config['endpoints'];

        $builderDefinition = $container->getDefinition('solr.client.adapter.builder');
        $builderDefinition->replaceArgument(0, $endpoints);
        $builderDefinition->addMethodCall('addPlugin', array('request_debugger', new Reference('solr.debug.client_debugger')));
    }

    /**
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function setupDoctrineConfiguration(array $config, ContainerBuilder $container)
    {
        if ($this->isOrmConfigured($container)) {
            $entityManagers = $container->getParameter('doctrine.entity_managers');

            $entityManagersNames = array_keys($entityManagers);
            foreach ($entityManagersNames as $entityManager) {
                $container->getDefinition('solr.doctrine.classnameresolver.known_entity_namespaces')->addMethodCall(
                    'addEntityNamespaces',
                    array(new Reference(sprintf('doctrine.orm.%s_configuration', $entityManager)))
                );
            }
        }

        if ($this->isODMConfigured($container)) {
            $documentManagers = $container->getParameter('doctrine_mongodb.odm.document_managers');

            $documentManagersNames = array_keys($documentManagers);
            foreach ($documentManagersNames as $documentManager) {
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
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function setupDoctrineListener(array $config, ContainerBuilder $container)
    {
        $autoIndexing = $container->getParameter('solr.auto_index');

        if ($autoIndexing == false) {
            return;
        }

        if ($this->isODMConfigured($container)) {
            $container->getDefinition('solr.document.odm.subscriber')->addTag('doctrine_mongodb.odm.event_subscriber');
        }

        if ($this->isOrmConfigured($container)) {
            $container->getDefinition('solr.document.orm.subscriber')->addTag('doctrine.event_subscriber');
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return boolean
     */
    private function isODMConfigured(ContainerBuilder $container)
    {
        return $container->hasParameter('doctrine_mongodb.odm.document_managers');
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return boolean
     */
    private function isOrmConfigured(ContainerBuilder $container)
    {
        return $container->hasParameter('doctrine.entity_managers');
    }
}
