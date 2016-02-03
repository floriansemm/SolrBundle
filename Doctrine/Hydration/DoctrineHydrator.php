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
        $cls            = $metaInformation->getClassName();
        $doctrineEntity =  new $cls();
        $metaInformation->setEntity($doctrineEntity);

        return $this->doctrine->getManager()->getRepository($cls)->find($metaInformation->getEntityId());
    }

    /**
     * @param array                    $entities
     * @param MetaInformationInterface $metaInformation
     *
     * @return array
     */
    public function hydrateEntities(array $entities, MetaInformationInterface $metaInformation)
    {
        $ids    = [];
        foreach ($entities as $entity) {
            $metaInformation->setEntity($entity);
            array_push($ids, $metaInformation->getEntityId());
        }

        $finderMethod   = $metaInformation->getFinderMethod();
        $repo           = $this->doctrine->getManager()
            ->getRepository($metaInformation->getClassName());
        if ($finderMethod && method_exists($repo, $finderMethod)) {
            return $repo->{$finderMethod}($ids);
        } else {
            return $repo->findBy(['id' => $ids]);
        }
    }
}