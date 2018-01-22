<?php

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @Solr\Document()
 */
class EntityNestedProperty
{
    /**
     * @Solr\Id
     */
    private $id;

    /**
     * @var string
     *
     * @Solr\Field(type="text")
     */
    private $name;

    /**
     * @var array
     *
     * @Solr\Field(nestedClass="FS\SolrBundle\Tests\Fixtures\NestedEntity")
     */
    private $collection;

    /**
     * @var array
     *
     * @Solr\Field(nestedClass="FS\SolrBundle\Tests\Fixtures\NestedEntity", getter="sliceCollection")
     */
    private $collectionValidGetter;

    /**
     * @var array
     *
     * @Solr\Field(nestedClass="FS\SolrBundle\Tests\Fixtures\NestedEntity", getter="unknown")
     */
    private $collectionInvalidGetter;
    
    /**
     * @var object
     *
     * @Solr\Field(nestedClass="FS\SolrBundle\Tests\Fixtures\NestedEntity")
     */
    private $nestedProperty;

    /**
     * @Solr\Field(type="datetime", getter="format('d.m.Y')")
     */
    private $getterWithParameters;

    /**
     * @Solr\Field(type="string", getter="getName")
     */
    private $simpleGetter;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function sliceCollection()
    {
        return [$this->collectionValidGetter[0]];
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**

    /**
     * @param array $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param object $nestedProperty
     */
    public function setNestedProperty($nestedProperty)
    {
        $this->nestedProperty = $nestedProperty;
    }

    /**
     * @param array $collectionValidGetter
     */
    public function setCollectionValidGetter($collectionValidGetter)
    {
        $this->collectionValidGetter = $collectionValidGetter;
    }

    /**
     * @param array $collectionInvalidGetter
     */
    public function setCollectionInvalidGetter($collectionInvalidGetter)
    {
        $this->collectionInvalidGetter = $collectionInvalidGetter;
    }

    /**
     * @param mixed $objectToSimpleFormat
     */
    public function setGetterWithParameters($getterWithParameters)
    {
        $this->getterWithParameters = $getterWithParameters;
    }

    /**
     * @param mixed $simpleGetter
     */
    public function setSimpleGetter($simpleGetter)
    {
        $this->simpleGetter = $simpleGetter;
    }
}