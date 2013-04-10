<?php
namespace FS\SolrBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddEventListenerPass implements CompilerPassInterface
{
    /* (non-PHPdoc)
     * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        $definitions = $container->findTaggedServiceIds('solr.event_listener');

        $factory = $container->getDefinition('solr.event_manager');

        foreach ($definitions as $service => $definition) {
            $factory->addMethodCall(
                'addListener',
                array(
                    $definition[0]['event'],
                    new Reference($service),
                )
            );
        }
    }
}
