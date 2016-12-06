<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineHydratorFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return DoctrineHydrator
     */
    public function factory()
    {
        $valueHydrator = $this->container->get('solr.doctrine.hydration.doctrine_value_hydrator');

        $hydrator = new DoctrineHydrator($valueHydrator);
        if ($this->container->has('doctrine')) {
            $hydrator->setOrmManager($this->container->get('doctrine'));
        }

        if ($this->container->has('doctrine_mongodb')) {
            $hydrator->setOdmManager($this->container->get('doctrine_mongodb'));
        }

        return $hydrator;
    }
}