<?php

namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use Solarium\QueryType\Update\Query\Document\Document;

interface EntityMapperInterface
{

    /**
     * @param MetaInformationInterface $meta
     *
     * @return Document
     */
    public function toDocument(MetaInformationInterface $meta);

    /**
     * @param \ArrayAccess $document
     * @param object       $sourceTargetEntity
     *
     * @return object
     *
     * @throws \InvalidArgumentException if $sourceTargetEntity is null
     */
    public function toEntity(\ArrayAccess $document, $sourceTargetEntity);

    /**
     * @param string $mode
     */
    public function setHydrationMode($mode);
}