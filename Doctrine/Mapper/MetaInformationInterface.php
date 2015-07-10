<?php

namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;

/**
 * Defines common methods for meta-information
 */
interface MetaInformationInterface
{

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
     * @return string
     */
    public function getRepository();

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

}