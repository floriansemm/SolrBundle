<?php
/**
 * Created by PhpStorm.
 * User: zach
 * Date: 12/9/15
 * Time: 11:45 AM
 */

namespace FS\SolrBundle\Query;


use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use Solarium\QueryType\Select\Result\Result;

/**
 * Class ResultSet
 *
 * @package FS\SolrBundle\Query
 */
class ResultSet implements \ArrayAccess
{

    /**
     * @var array
     */
    private $entities = array();

    /**
     * @var Result
     */
    private $response;

    /**
     * @var int
     */
    private $total = 0;


    public function __construct($entity, EntityMapper $mapper=null, Result $response=null)
    {
        if ($mapper === null || $response === null) {
            return $this;
        }

        $this->total = $response->getNumFound();
        if ($this->total == 0) {
            return $this;
        }

        $mappedEntities = array();
        foreach ($response as $document) {
            $mappedEntities[] = $mapper->toEntity($document, $entity);
        }

        $this->entities = $mappedEntities;
        $this->response = $response;
    }

    /**
     * Response getter
     *
     * @return Result
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Entities getter
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Entities setter
     *
     * @param array $entities
     *
     * @return ResultSet $this
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->entities[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->entities[$offset];
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->entities[$offset]);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->entities[$offset]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->entities;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return $this->toArray();
    }
}