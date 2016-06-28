<?php

namespace FS\SolrBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds plugins tagged with solarium.client.plugin directly to Solarium
 */
class AddSolariumPluginsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $plugins = $container->findTaggedServiceIds('solarium.client.plugin');

        $clientBuilder = $container->getDefinition('solr.client.adapter.builder');
        foreach ($plugins as $service => $definition) {
            $clientBuilder->addMethodCall(
                'addPlugin',
                array(
                    $definition[0]['plugin-name'],
                    new Reference($service)
                )
            );
        }
    }
}