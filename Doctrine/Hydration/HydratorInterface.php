<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;

/**
 * When the index was queried the resulting entities can be instantiated in different ways:
 *
 * 1. use corresponding db-entity which can contain not indexed properties. The fields of the solr-document will update
 * the fields of the entity. Not indexed entity-fields remain untouched with their db-values.
 * 2. use a blank entity. Not index fields remain untouched.
 */
interface HydratorInterface
{
    /**
     * @param object                   $document
     * @param MetaInformationInterface $metaInformation holds the target entity
     *
     * @return object
     */
    public function hydrate($document, MetaInformationInterface $metaInformation);
} 