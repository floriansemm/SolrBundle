<?php

namespace FS\SolrBundle\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * hydrates full Entity from DB and merge with result from IndexHydrator
 */
class DoctrineHydrator
{

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine;
    }

    public function hydrate($document)
    {
    }
} 