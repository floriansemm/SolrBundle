<?php
/**
 * Created by PhpStorm.
 * User: zach
 * Date: 12/9/15
 * Time: 11:45 AM
 */

namespace FS\SolrBundle\Query;


use Doctrine\Common\Collections\ArrayCollection;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use Solarium\QueryType\Select\Result\Result;

/**
 * Class ResultSet
 *
 * @package FS\SolrBundle\Query
 */
class ResultSet extends ArrayCollection
{
    /**
     * @var Result
     */
    private $response;


    /**
     * ResultSet constructor.
     *
     * @param array $elements
     * @param Result|null $result
     */
    public function __construct(array $elements = array(), Result $result = null)
    {
        parent::__construct($elements);

        if ($result !== null) {
            $this->response = $result;
        }
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
}