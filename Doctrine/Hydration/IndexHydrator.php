<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;

/**
 * Hydrates blank Entity from Document
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
     * {@inheritdoc}
     */
    public function hydrate($document, MetaInformationInterface $metaInformation)
    {
        $sourceTargetEntity = $metaInformation->getEntity();
        $targetEntity = clone $sourceTargetEntity;

        $metaInformation->setEntity($targetEntity);

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
} 