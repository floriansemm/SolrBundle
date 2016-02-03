<?php

namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Annotation\VirtualField;

/**
 * Defines common methods for meta-information
 */
interface MetaInformationInterface
{

    const DOCUMENT_KEY_FIELD_NAME = 'id';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return string
     */
    public function getDocumentName();

    /**
     * @return Field[]
     */
    public function getFields();

    /**
     * @return VirtualField[]
     */
    public function getVirtualFields();

    /**
     * @return string
     */
    public function getRepository();

    /**
     * @return string
     */
    public function getFinderMethod();

    /**
     * @return object
     */
    public function getEntity();

    /**
     * @param string $field
     *
     * @return Field|null
     */
    public function getField($field);

    /**
     * @return array
     */
    public function getFieldMapping();

    /**
     * @return number
     */
    public function getBoost();

    /**
     * @return string
     */
    public function getSynchronizationCallback();

    /**
     * @return boolean
     */
    public function hasSynchronizationFilter();

    /**
     * @return string
     */
    public function getIndex();

    /**
     * @return string
     */
    public function getDocumentKey();
}