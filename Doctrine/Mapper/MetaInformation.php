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
     * @var int
     */
    private $entityId;

    /**
     * @var bool
     */
    private $isDoctrineEntity;

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
     * @param string $fieldName
     *
     * @return boolean
     */
    public function hasField($fieldName)
    {
        $fields = array_filter($this->fields, function(Field $field) use ($fieldName) {
            return $field->name == $fieldName || $field->getNameWithAlias() == $fieldName;
        });

        if (count($fields) == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $fieldName
     * @param string $value
     *
     * @throws \InvalidArgumentException if $fieldName does not exist
     */
    public function setFieldValue($fieldName, $value)
    {
        if ($this->hasField($fieldName) == false) {
            throw new \InvalidArgumentException(sprintf('Field %s does not exist', $fieldName));
        }

        $field = $this->getField($fieldName);
        $field->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($fieldName)
    {
        if (!$this->hasField($fieldName)) {
            return null;
        }

        $fields = array_filter($this->fields, function(Field $field) use ($fieldName) {
            return $field->name == $fieldName || $field->getNameWithAlias() == $fieldName;
        });

        return array_pop($fields);
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
     * @return boolean
     */
    public function isDoctrineEntity()
    {
        return $this->isDoctrineEntity;
    }

    /**
     * @param boolean $isDoctrineEntity
     */
    public function setIsDoctrineEntity($isDoctrineEntity)
    {
        $this->isDoctrineEntity = $isDoctrineEntity;
    }
}
