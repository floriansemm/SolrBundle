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
class FSSolrExtension extends Extension {
 
	/**
	 * (non-PHPdoc)
	 * @see \Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
	 */
    public function load(array $configs, ContainerBuilder $container) {
    	$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    	$loader->load('services.xml');  
    	$loader->load('listener.xml');  	
    	
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setupConnections($config, $container);

        $container->setParameter('solr.auto_index', $config['auto_index']);
        
        if (!$this->isDoctrineConfigured($container, $config)) {
        	throw new \RuntimeException('No doctrine configuration was found, please setup a orm/odm doctrine configuration.');
        }
        
        $this->setupDoctrineListener($config, $container);
        $this->setupDoctrineConfiguration($config, $container);
       
    }
    
    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @return boolean
     */
    private function isDoctrineConfigured(ContainerBuilder $container, array $config) {
    	$ormConfig = sprintf('doctrine.orm.%s_configuration', $config['entity_manager']);
    	
    	return $this->isODMConfigured($container) || $container->hasDefinition($ormConfig);
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
    
    /**
     * if mongo_db is not configured, then use the doctrine_orm configuration
     * 
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function setupDoctrineConfiguration(array $config, ContainerBuilder $container) {
    	if (!$this->isODMConfigured($container)) {
    		$container->getDefinition('solr.doctrine.configuration')->setArguments(array(
    			new Reference(sprintf('doctrine.orm.%s_configuration', $config['entity_manager']))
    		));
    	} else {
    		$container->getDefinition('solr.doctrine.configuration')->setArguments(array(
    			new Reference(sprintf('doctrine_mongodb.odm.%s_configuration', $config['entity_manager']))
    		));
    	}
    	
    	$container->getDefinition('solr.meta.information.factory')->addMethodCall(
    			'setDoctrineConfiguration',
    			array(new Reference('solr.doctrine.configuration'))
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
    private function setupDoctrineListener(array $config, ContainerBuilder $container) {
    	$autoIndexing = $container->getParameter('solr.auto_index');
    	
    	if ($autoIndexing == false) {
    		return;
    	}
		
		if ($this->isODMConfigured($container)) {
            $container->getDefinition('solr.delete.document.odm.listener')->addTag('doctrine_mongodb.odm.event_listener', array('event'=>'preRemove'));
            $container->getDefinition('solr.update.document.odm.listener')->addTag('doctrine_mongodb.odm.event_listener', array('event'=>'postUpdate'));
            $container->getDefinition('solr.add.document.odm.listener')->addTag('doctrine_mongodb.odm.event_listener', array('event'=>'postPersist'));

    	} else {
    		$container->getDefinition('solr.add.document.orm.listener')->addTag('doctrine.event_listener', array('event'=>'postPersist'));
    		$container->getDefinition('solr.delete.document.orm.listener')->addTag('doctrine.event_listener', array('event'=>'preRemove'));
    		$container->getDefinition('solr.update.document.orm.listener')->addTag('doctrine.event_listener', array('event'=>'postUpdate'));    		
    	}
    }
    
    /**
     * @param ContainerBuilder $container
     * @return boolean 
     */
    private function isODMConfigured(ContainerBuilder $container) {
    	return $container->hasParameter('doctrine_mongodb.odm.document_managers');
    }
    
}
