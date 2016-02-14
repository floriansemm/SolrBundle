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

class ResultSet implements \ArrayAccess, \Countable
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

    /**
     * @param object       $entity
     * @param EntityMapper $mapper
     * @param Result       $response
     */
    public function __construct($entity, EntityMapper $mapper = null, \IteratorAggregate $response = null)
    {
        $this->response = $response;
        if ($response !== null) {
            $this->total = $response->getNumFound();
        }

        $this->entities = array();
        if ($response !== null) {
            foreach ($response as $document) {
                $this->entities[] = $mapper->toEntity($document, $entity);
            }
        }
    }

    /**
     * @return Result
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
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
        if (count($this->entities) === 0) {
            throw new \OutOfBoundsException(sprintf('Index %s is not defined', $offset));
        }

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

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->entities);
    }
}