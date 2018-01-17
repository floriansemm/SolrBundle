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
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getCollection() : array
    {
        return $this->collection;
    }

    /**
     * @param array $collection
     */
    public function setCollection(array $collection)
    {
        $this->collection = $collection;
    }
}