<?php

namespace FS\SolrBundle\Tests;

class ResultFake implements \IteratorAggregate, \Countable
{

    private $data = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function count()
    {
        return count($this->data);
    }

    public function getNumFound()
    {
        return $this->count();
    }
} 