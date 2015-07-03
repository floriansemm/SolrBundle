<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

interface HydratorInterface
{
    /**
     * @param object          $document
     * @param MetaInformation $metaInformation holds the target entity
     *
     * @return object
     */
    public function hydrate($document, MetaInformation $metaInformation);
} 