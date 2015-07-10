<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;

/**
 * Holds meta-information about an entity
 */
class MetaInformation implements MetaInformationInterface
{

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $className = '';

    /**
     * @var string
     */
    private $documentName = '';

    /**
     * @var Field[]
     */
    private $fields = array();

    /**
     * @var array
     */
    private $fieldMapping = array();

    /**
     * @var string
     */
    private $repository = '';

    /**
     * @var object
     */
    private $entity = null;

    /**
     * @var number
     */
    private $boost = 0;

    /**
     * @var string
     */
    private $synchronizationCallback = '';

    /**
     * @var string
     */
    private $index = '';

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        if ($this->entity !== null) {
            return $this->entity->getId();
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @param string $documentName
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }

    /**
     * @param Field[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param string $field
     *
     * @return boolean
     */
    public function hasField($field)
    {
        if (count($this->fields) == 0) {
            return false;
        }

        return isset($this->fields[$field]);
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function setFieldValue($field, $value)
    {
        $this->fields[$field]->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($field)
    {
        if (!$this->hasField($field)) {
            return null;
        }

        return $this->fields[$field];
    }

    /**
     * @param string $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * @param array $fieldMapping
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @param number $boost
     */
    public function setBoost($boost)
    {
        $this->boost = $boost;
    }

    /**
     * @return boolean
     */
    public function hasSynchronizationFilter()
    {
        if ($this->synchronizationCallback == '') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSynchronizationCallback()
    {
        return $this->synchronizationCallback;
    }

    /**
     * @param string $synchronizationCallback
     */
    public function setSynchronizationCallback($synchronizationCallback)
    {
        $this->synchronizationCallback = $synchronizationCallback;
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex()
    {
        return $this->index;
    }
}
