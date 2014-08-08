<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class DoctrineHydrator implements Hydrator
{

    /**
     * @var ManagerRegistry
     */
    private $doctrineManagerRegistry;

    /**
     * @var Hydrator
     */
    private $valueHydrator;

    /**
     * @param Hydrator $valueHydrator
     */
    public function __construct(Hydrator $valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
    }

    /**
     * @param ManagerRegistry $doctrineManagerRegistry
     */
    public function setDoctrineManagerRegistry(ManagerRegistry $doctrineManagerRegistry)
    {
        $this->doctrineManagerRegistry = $doctrineManagerRegistry;
    }

    /**
     * @param $document
     * @param MetaInformation $metaInformation
     * @return object
     */
    public function hydrate($document, MetaInformation $metaInformation)
    {
        $entityId = $document->id;

        $doctrineEntity = $this->doctrineManagerRegistry
            ->getManager()
            ->getRepository($metaInformation->getClassName())
            ->find($entityId);

        if ($doctrineEntity !== null) {
            $metaInformation->setEntity($doctrineEntity);
        }

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
}