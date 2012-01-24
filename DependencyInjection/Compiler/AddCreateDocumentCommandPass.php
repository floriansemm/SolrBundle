<?php
namespace FS\SolrBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddCreateDocumentCommandPass implements CompilerPassInterface {
	/* (non-PHPdoc)
	 * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
	 */
	public function process(ContainerBuilder $container) {
		$definitions = $container->findTaggedServiceIds('solr.document.command');
		
		$factory = $container->getDefinition('solr.mapping.factory');
		
		foreach ($definitions as $service => $definition) {
			$factory->addMethodCall('add', array(
				new Reference($service),
				$definition[0]['command']		
			));
        }
	}


}

?>