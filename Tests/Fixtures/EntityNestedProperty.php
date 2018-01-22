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
     * @var object
     *
     * @Solr\Field(nestedClass="FS\SolrBundle\Tests\Fixtures\NestedEntity")
     */
    private $nestedProperty;

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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param array $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return object
     */
    public function getNestedProperty()
    {
        return $this->nestedProperty;
    }

    /**
     * @param object $nestedProperty
     */
    public function setNestedProperty($nestedProperty)
    {
        $this->nestedProperty = $nestedProperty;
    }
    
    
}