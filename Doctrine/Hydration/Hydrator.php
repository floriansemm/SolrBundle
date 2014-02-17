<?php

namespace FS\SolrBundle\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

interface Hydrator
{
    /**
     * @param $document
     * @param MetaInformation $metaInformation
     * @return object
     */
    public function hydrate($document, MetaInformation $metaInformation);
} 