<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * A doctrine-hydrator finds the entity for a given solr-document. This entity is updated with the solr-document values.
 *
 * The hydration is necessary because fields, which are not declared as solr-field, will not populate in the result.
 */
class DoctrineHydrator implements HydratorInterface
{

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var HydratorInterface
     */
    private $valueHydrator;

    /**
     * @param RegistryInterface $doctrine
     * @param HydratorInterface $valueHydrator
     */
    public function __construct(RegistryInterface $doctrine, HydratorInterface $valueHydrator)
    {
        $this->doctrine = $doctrine;
        $this->valueHydrator = $valueHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($document, MetaInformationInterface $metaInformation)
    {
        $entityId = $this->valueHydrator->removePrefixedKeyValues($document['id']);
        $doctrineEntity = $this->doctrine
            ->getManager()
            ->getRepository($metaInformation->getClassName())
            ->find($entityId);

        if ($doctrineEntity !== null) {
            $metaInformation->setEntity($doctrineEntity);
        }

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
}