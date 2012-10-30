<?php

namespace FS\SolrBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
    	$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    	$loader->load('services.xml');    	
    	
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setupConnections($config, $container);

        $container->setParameter('solr.auto_index', $config['auto_index']);
        
        $this->setupDoctrineListener($config, $container);
        
        $container->getDefinition('solr.meta.information.factory')->addMethodCall(
        	'setDoctrineConfiguration',
        	array(new Reference(sprintf('doctrine.orm.%s_configuration', $config['entity_manager'])))
        );
    }
    
    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function setupConnections(array $config, ContainerBuilder $container) {
    	$connectionParameters = $config['solr'];
    	
    	$cores = $config['solr']['path'];
    	$connections = array();
    	if (count($cores) > 0) {
    		foreach ($cores as $coreName => $path) {
    			$connectionParameters['path'] = $path;
    			$connections[$coreName] = $connectionParameters;
    		}
    	} else {
    		$connectionParameters['path'] = '/solr';
			$connections['default'] = $connectionParameters;    		
    	}
    	
    	$container->getDefinition('solr.connection_factory')->setArguments(array($connections));
    }
    
    private function setupDoctrineListener(array $config, ContainerBuilder $container) {
    	$autoIndexing = $container->getParameter('solr.auto_index');
    	
    	if ($autoIndexing == false) {
    		return;
    	}
    	
    	$container->getDefinition('solr.add.document.listener')->addTag('doctrine.event_listener', array('event'=>'postPersist'));
    	$container->getDefinition('solr.delete.document.listener')->addTag('doctrine.event_listener', array('event'=>'preRemove'));
    	$container->getDefinition('solr.update.document.listener')->addTag('doctrine.event_listener', array('event'=>'postUpdate'));
    }
    
}
