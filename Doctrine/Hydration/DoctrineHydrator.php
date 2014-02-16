<?php

namespace FS\SolrBundle\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
        $foo = $document;
    }
} 