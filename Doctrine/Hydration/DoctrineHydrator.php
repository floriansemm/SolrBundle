<?php

namespace FS\SolrBundle\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * hydrates full Entity from DB and merge with result from IndexHydrator
 */
class DoctrineHydrator implements Hydrator
{

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    private $valueHydrator;

    public function __construct(RegistryInterface $doctrine, Hydrator $valueHydrator)
    {
        $this->doctrine = $doctrine;
        $this->valueHydrator = $valueHydrator;
    }

    public function hydrate($document, MetaInformation $metaInformation)
    {
        $entityId = $document->id;
        $doctrineEntity = $this->doctrine
            ->getManager()
            ->getRepository($metaInformation->getClassName())
            ->find($entityId);

        $metaInformation->setEntity($doctrineEntity);

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
}