<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

/**
 * hydrates Entity from Document
 */
class IndexHydrator implements HydratorInterface
{
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
     * @param                 $document
     * @param MetaInformation $metaInformation
     *
     * @return object
     */
    public function hydrate($document, MetaInformation $metaInformation)
    {
        $sourceTargetEntity = $metaInformation->getEntity();
        $targetEntity = clone $sourceTargetEntity;

        $metaInformation->setEntity($targetEntity);

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
} 