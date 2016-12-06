<?php

namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;

/**
 * Defines common methods for meta-information
 */
interface MetaInformationInterface
{
    const DOCUMENT_KEY_FIELD_NAME = 'id';

    /**
     * used when given object is a ORM entity
     */
    const DOCTRINE_MAPPER_TYPE_RELATIONAL = 'relational';

    /**
     * used when given object is a ODM document
     */
    const DOCTRINE_MAPPER_TYPE_DOCUMENT = 'document';

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
     * @param string $fieldName
     *
     * @return Field|null
     */
    public function getField($fieldName);

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

    /**
     * @return string
     */
    public function getIdentifierFieldName();

    /**
     * @return string
     */
    public function getDoctrineMapperType();
}