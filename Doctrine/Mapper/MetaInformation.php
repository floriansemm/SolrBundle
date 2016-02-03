<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Annotation\VirtualField;

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
     * @var VirtualField[]
     */
    private $virtualFields = array();

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
     * @var int
     */
    private $entityId;

    /**
     * @var string
     */
    private $finderMethod;

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        if ($this->entity !== null && $this->entity->getId()) {
            return $this->entity->getId();
        }

        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
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
    public function getVirtualFields()
    {
        return $this->virtualFields;
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
        $transformedFields = [];
        foreach ($fields as $field) {
            $transformedFields[$field->name] = $field;
        }

        $this->fields = $transformedFields;
    }

    /**
     * @param VirtualField[] $virtualFields
     */
    public function setVirtualFields($virtualFields)
    {
        $transformedFields = [];
        foreach ($virtualFields as $field) {
            $transformedFields[$field->name] = $field;
        }

        $this->virtualFields = $transformedFields;
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
     *
     * @return boolean
     */
    public function hasVirtualField($field)
    {
        if (count($this->virtualFields) == 0) {
            return false;
        }

        return isset($this->virtualFields[$field]);
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

    /**
     * {@inheritdoc}
     */
    public function getDocumentKey()
    {
        return $this->documentName . '_' . $this->getEntityId();
    }

    /**
     * @param string $method
     *
     * @return MetaInformation $this
     */
    public function setFinderMethod($method)
    {
        $this->finderMethod = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinderMethod()
    {
        return $this->finderMethod;
    }
}
