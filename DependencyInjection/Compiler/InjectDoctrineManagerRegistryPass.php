<?php

namespace FS\SolrBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;


class InjectDoctrineManagerRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('solr.doctrine.hydration.doctrine_hydrator');

        $definition->addMethodCall(
            'setDoctrineManagerRegistry',
            array(
                new Reference($this->findManagerRegistryServiceId($container))
            )
        );
    }

    /**
     * @param $container
     * @return string
     * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
     */
    protected function findManagerRegistryServiceId($container)
    {
        if ($container->hasDefinition('doctrine')) {
            $managerRegistryServiceId = 'doctrine';
        } elseif ($container->hasDefinition('doctrine_mongodb')) {
            $managerRegistryServiceId = 'doctrine_mongodb';
        } else {
            throw new RuntimeException();
        }

        return $managerRegistryServiceId;
    }
}