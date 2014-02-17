<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

/**
 * hydrates Entity from Document
 */
class IndexHydrator implements Hydrator
{
    private $valueHydrator;

    public function __construct(Hydrator $valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
    }

    public function hydrate($document, MetaInformation $metaInformation)
    {
        $sourceTargetEntity = $metaInformation->getEntity();
        $targetEntity = clone $sourceTargetEntity;

        $metaInformation->setEntity($targetEntity);

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
} 