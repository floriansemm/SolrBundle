<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver;
use FS\SolrBundle\Doctrine\Configuration;

/**
 *
 * @author fs
 *
 */
class MetaInformationFactory
{

    /**
     * @var MetaInformation
     */
    private $metaInformations = null;

    /**
     * @var AnnotationReader
     */
    private $annotationReader = null;

    /**
     * @var ClassnameResolver
     */
    private $classnameResolver = null;

    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * @param ClassnameResolver $classnameResolver
     */
    public function setClassnameResolver(ClassnameResolver $classnameResolver)
    {
        $this->classnameResolver = $classnameResolver;
    }

    /**
     * @param object $entity
     *
     * @return MetaInformation
     *
     * @throws \RuntimeException if no declaration for document found in $entity
     */
    public function loadInformation($entity)
    {

        $className = $this->getClass($entity);

        if (!is_object($entity)) {
            $entity = new $className;
        }

        if (!$this->annotationReader->hasDocumentDeclaration($entity)) {
            throw new \RuntimeException(sprintf('no declaration for document found in entity %s', $className));
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

        return $metaInformation;
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
