<?php

namespace FS\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Config\Definition\ConfigurationInterface::getConfigTreeBuilder()
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fs_solr');
        $rootNode->children()
            ->arrayNode('solr')
            ->children()
            ->scalarNode('hostname')->defaultValue('localhost')->end()
            ->scalarNode('port')->defaultValue('8983')->end()
            ->arrayNode('path')
            ->useAttributeAsKey('name')
            ->prototype('scalar')->end()
            ->end()
            ->scalarNode('login')->end()
            ->scalarNode('password')->end()
            ->end()
            ->end()
            ->booleanNode('auto_index')->defaultValue(true)->end()
            ->scalarNode('entity_manager')->defaultValue('default')->end()
            ->end();

        return $treeBuilder;
    }
}
