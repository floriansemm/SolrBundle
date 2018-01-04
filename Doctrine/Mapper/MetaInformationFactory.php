<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver;

/**
 * instantiates a new MetaInformation object by a given entity
 */
class MetaInformationFactory
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader = null;

    /**
     * @var ClassnameResolver
     */
    private $classnameResolver = null;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * @param ClassnameResolver $classnameResolver
     */
    public function setClassnameResolver(ClassnameResolver $classnameResolver)
    {
        $this->classnameResolver = $classnameResolver;
    }

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return MetaInformation
     *
     * @throws SolrMappingException if no declaration for document found in $entity
     */
    public function loadInformation($entity)
    {
        $className = $this->getClass($entity);

        if (!is_object($entity)) {
            $reflectionClass = new \ReflectionClass($className);
            if (!$reflectionClass->isInstantiable()) {
                throw new SolrMappingException(sprintf('Cannot instantiate entity %s', $className));
            }
            $entity = $reflectionClass->newInstanceWithoutConstructor();
        }

        if (!$this->annotationReader->hasDocumentDeclaration($entity)) {
            throw new SolrMappingException(sprintf('no declaration for document found in entity %s', $className));
        }

        $metaInformation = new MetaInformation();
        $metaInformation->setEntity($entity);
        $metaInformation->setClassName($className);
        $metaInformation->setDocumentName($this->getDocumentName($className));
        $metaInformation->setFieldMapping($this->annotationReader->getFieldMapping($entity));
        $metaInformation->setFields($this->annotationReader->getFields($entity));
        $metaInformation->setRepository($this->annotationReader->getRepository($entity));
        $metaInformation->setIdentifier($this->annotationReader->getIdentifier($entity));
        $metaInformation->setBoost($this->annotationReader->getEntityBoost($entity));
        $metaInformation->setSynchronizationCallback($this->annotationReader->getSynchronizationCallback($entity));
        $metaInformation->setIndex($this->annotationReader->getDocumentIndex($entity));
        $metaInformation->setIsDoctrineEntity($this->isDoctrineEntity($entity));
        $metaInformation->setDoctrineMapperType($this->getDoctrineMapperType($entity));
        $metaInformation->setNested($this->annotationReader->isNested($entity));

        $fields = $this->annotationReader->getFields($entity);
        foreach ($fields as $field) {
            if (!$field->nestedClass) {
                continue;
            }

            $nestedObjectMetainformation = $this->loadInformation($field->nestedClass);

            $subentityMapping = [];
            $nestedFieldName = $field->name;
            foreach ($nestedObjectMetainformation->getFieldMapping() as $documentName => $fieldName) {
                $subentityMapping[$nestedFieldName . '.' . $documentName] = $nestedFieldName . '.' . $fieldName;
            }

            $rootEntityMapping = $metaInformation->getFieldMapping();
            $subentityMapping = array_merge($subentityMapping, $rootEntityMapping);
            unset($subentityMapping[$field->name]);
            $metaInformation->setFieldMapping($subentityMapping);
        }

        return $metaInformation;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    private function isDoctrineEntity($entity)
    {
        if ($this->annotationReader->isOrm($entity) || $this->annotationReader->isOdm($entity)) {
            return true;
        }

        return false;
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    private function getDoctrineMapperType($entity)
    {
        if ($this->isDoctrineEntity($entity) == false) {
            return '';
        }

        if ($this->annotationReader->isOdm($entity)) {
            return MetaInformationInterface::DOCTRINE_MAPPER_TYPE_DOCUMENT;
        }

        if ($this->annotationReader->isOrm($entity)) {
            return MetaInformationInterface::DOCTRINE_MAPPER_TYPE_RELATIONAL;
        }
    }

    /**
     * @param object $entity
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    private function getClass($entity)
    {
        if (is_object($entity)) {
            return get_class($entity);
        }

        if (class_exists($entity)) {
            return $entity;
        }

        $realClassName = $this->classnameResolver->resolveFullQualifiedClassname($entity);

        return $realClassName;
    }

    /**
     * @param string $fullClassName
     *
     * @return string
     */
    private function getDocumentName($fullClassName)
    {
        $className = substr($fullClassName, (strrpos($fullClassName, '\\') + 1));

        return strtolower($className);
    }
}
