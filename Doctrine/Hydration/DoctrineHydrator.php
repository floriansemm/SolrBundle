<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use Doctrine\Common\Persistence\ManagerRegistry;
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
     * @var ManagerRegistry
     */
    private $ormManager;

    /**
     * @var ManagerRegistry
     */
    private $odmManager;

    /**
     * @var HydratorInterface
     */
    private $valueHydrator;

    /**
     * @param HydratorInterface $valueHydrator
     */
    public function __construct(HydratorInterface $valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
    }

    /**
     * @param ManagerRegistry $ormManager
     */
    public function setOrmManager($ormManager)
    {
        $this->ormManager = $ormManager;
    }

    /**
     * @param ManagerRegistry $odmManager
     */
    public function setOdmManager($odmManager)
    {
        $this->odmManager = $odmManager;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($document, MetaInformationInterface $metaInformation)
    {
        $entityId = $this->valueHydrator->removePrefixedKeyValues($document['id']);

        $doctrineEntity = null;
        if ($metaInformation->getDoctrineMapperType() == MetaInformationInterface::DOCTRINE_MAPPER_TYPE_RELATIONAL) {
            $doctrineEntity = $this->ormManager
                ->getManager()
                ->getRepository($metaInformation->getClassName())
                ->find($entityId);
        } elseif ($metaInformation->getDoctrineMapperType() == MetaInformationInterface::DOCTRINE_MAPPER_TYPE_DOCUMENT) {
            $doctrineEntity = $this->odmManager
                ->getManager()
                ->getRepository($metaInformation->getClassName())
                ->find($entityId);
        }

        if ($doctrineEntity !== null) {
            $metaInformation->setEntity($doctrineEntity);
        }

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
}