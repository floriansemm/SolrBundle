<?php

namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Reader;
use FS\SolrBundle\Doctrine\Annotation\Fields;

class AnnotationReader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $entityProperties;

    const DOCUMENT_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Document';
    const FIELDS_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Fields';
    const FIELD_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Field';
    const FIELD_IDENTIFIER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Id';
    const DOCUMENT_INDEX_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Document';
    const SYNCHRONIZATION_FILTER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\SynchronizationFilter';

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

     /**
     * reads the entity and returns a set of annotations
     *
     * @param object $entity
     * @param string $type
     * @param array $fields
     *
     * @return Annotation[]
     */
    private function getPropertiesByType($entity, $type, $fields = array())
    {
        $properties = $this->readClassProperties($entity);

        foreach ($properties as $property) {

            $property->setAccessible(true); 
            $annotation = $this->reader->getPropertyAnnotation($property, $type);
            
            if (null === $annotation) {
                continue;
            }

            if ($type == $this::FIELDS_CLASS) {
                $fields = $this->processFieldsAnnotation($property, $annotation, $entity, $fields);
            }
            else {
                $annotation->value = $property->getValue($entity);
                $annotation->name = $property->getName();

                $fields[] = $annotation;
            }
        }
        
        return $fields;
    }

     /**
     * Process fields annotation
     * 
     * @param \ReflectionProperty $property
     * @param \FS\SolrBundle\Doctrine\Annotation\Fields $annotation
     * @param object $entity
     * @param array $fields
     * @return array
     * @throws AnnotationReaderException
     */
    private function processFieldsAnnotation(\ReflectionProperty $property, Fields $annotation, $entity, $fields = array())
    {

        if (!$annotation->getter) {
            throw new AnnotationReaderException(sprintf('No getter defined for @Fields annotation in class "%s"', get_class($entity)));
        }

        $fieldsGetter = Field::removeParenthesis($annotation->getter);

        if (method_exists($entity, $fieldsGetter)) {
            
            $relations = $entity->$fieldsGetter();

            if ($relations) {

                if($relations instanceof \Doctrine\ORM\PersistentCollection ) {
                    $relations = $relations->getValues();

                    if (empty($relations)) {
                        foreach ($annotation->fields as $field) {
                             $field->name = $property->getName();
                             $fields[] = $field;
                        }

                        return $fields;
                    }
                }

                if (!(is_array($relations))) {
                    $relations = array($relations);
                }

                foreach($relations as $relation) {

                    foreach ($annotation->fields as $field) {

                        $field->name = $property->getName();

                        if (!$field->fieldAlias) {
                            throw new AnnotationReaderException(sprintf('No fieldAlias defined for field "%s" in class "%s"', $field->name, get_class($entity)));
                        }

                        if ($field->getter) {
                            $method = Field::removeParenthesis($field->getter);

                            if (method_exists($relation, $method)) {
                                $field->fieldsGetter = $fieldsGetter;
                                $field->value = $relation->$method();
                                $field->getter = $method;
                            } else {
                                throw new AnnotationReaderException(sprintf('Unknown method defined "%s" in class "%s"', $method, get_class($entity)));
                            }
                        } else {
                            throw new AnnotationReaderException(sprintf('No getter defined for fieldAlias "%s" in class "%s"', $field->fieldAlias, get_class($entity)));
                        }

                        $fields[] = $field;
                    }
                }
            }
        }
        else {
            throw new AnnotationReaderException(sprintf('Unknown method defined "%s" in class "%s"', $fieldsGetter, get_class($entity)));
        }
        
        return $fields;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return \ReflectionProperty[]
     */
    private function getParentProperties(\ReflectionClass $reflectionClass)
    {
        $parent = $reflectionClass->getParentClass();
        if ($parent != null) {
            return array_merge($reflectionClass->getProperties(), $this->getParentProperties($parent));
        }

        return $reflectionClass->getProperties();
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    public function getFields($entity)
    {
        $fields = $this->getPropertiesByType($entity, self::FIELD_CLASS);
        $fields = $this->getPropertiesByType($entity, self::FIELDS_CLASS,$fields);

        return $fields;
    }

    /**
     * @param object $entity
     *
     * @return number
     *
     * @throws AnnotationReaderException if the boost value is not numeric
     */
    public function getEntityBoost($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_INDEX_CLASS);

        if (!$annotation instanceof Document) {
            return 0;
        }

        $boostValue = $annotation->getBoost();
        if (!is_numeric($boostValue)) {
            throw new AnnotationReaderException(sprintf('Invalid boost value "%s" in class "%s" configured', $boostValue, get_class($entity)));
        }

        if ($boostValue === 0) {
            return null;
        }

        return $boostValue;
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    public function getDocumentIndex($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_INDEX_CLASS);
        if (!$annotation instanceof Document) {
            return '';
        }

        $indexHandler = $annotation->indexHandler;
        if ($indexHandler != '' && method_exists($entity, $indexHandler)) {
            return $entity->$indexHandler();
        }

        return $annotation->getIndex();
    }

    /**
     * @param object $entity
     *
     * @return Id
     *
     * @throws AnnotationReaderException if given $entity has no identifier
     */
    public function getIdentifier($entity)
    {
        $id = $this->getPropertiesByType($entity, self::FIELD_IDENTIFIER_CLASS);

        if (count($id) == 0) {
            throw new AnnotationReaderException('no identifer declared in entity ' . get_class($entity));
        }

        return reset($id);
    }

    /**
     * @param object $entity
     *
     * @return string classname of repository
     */
    public function getRepository($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_CLASS);

        if ($annotation instanceof Document) {
            return $annotation->repository;
        }

        return '';
    }

    /**
     * returns all fields and field for idendification
     *
     * @param object $entity
     *
     * @return array
     */
    public function getFieldMapping($entity)
    {
        $fields = $this->getPropertiesByType($entity, self::FIELD_CLASS);
        $fields = $this->getPropertiesByType($entity, self::FIELD_CLASS, $fields);

        $mapping = array();
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $mapping[$field->getNameWithAlias()] = $field->name;
            }
        }

        $id = $this->getIdentifier($entity);
        $mapping['id'] = $id->name;

        return $mapping;
    }

    /**
     * @param object $entity
     *
     * @return boolean
     */
    public function hasDocumentDeclaration($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_INDEX_CLASS);

        return $annotation !== null;
    }

    /**
     * @param string $entity
     *
     * @return string
     */
    public function getSynchronizationCallback($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::SYNCHRONIZATION_FILTER_CLASS);

        if (!$annotation) {
            return '';
        }

        return $annotation->callback;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isOrm($entity)
    {
        $annotation = $this->getClassAnnotation($entity, 'Doctrine\ORM\Mapping\Entity');

        if ($annotation === null) {
            return false;
        }

        return true;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isOdm($entity)
    {
        $annotation = $this->getClassAnnotation($entity, 'Doctrine\ODM\MongoDB\Mapping\Annotations\Document');

        if ($annotation === null) {
            return false;
        }

        return true;
    }

    /**
     * @param string $entity
     * @param string $annotationName
     *
     * @return Annotation|null
     */
    private function getClassAnnotation($entity, $annotationName)
    {
        $reflectionClass = new \ReflectionClass($entity);

        $annotation = $this->reader->getClassAnnotation($reflectionClass, $annotationName);

        if ($annotation === null && $reflectionClass->getParentClass()) {
            $annotation = $this->reader->getClassAnnotation($reflectionClass->getParentClass(), $annotationName);
        }

        return $annotation;
    }

    /**
     * @param object $entity
     *
     * @return \ReflectionProperty[]
     */
    private function readClassProperties($entity)
    {
        $className = get_class($entity);
        if (isset($this->entityProperties[$className])) {
            return $this->entityProperties[$className];
        }

        $reflectionClass = new \ReflectionClass($entity);
        $inheritedProperties = array_merge($this->getParentProperties($reflectionClass), $reflectionClass->getProperties());

        $properties = array();
        foreach ($inheritedProperties as $property) {
            $properties[$property->getName()] = $property;
        }

        $this->entityProperties[$className] = $properties;

        return $properties;
    }
}
