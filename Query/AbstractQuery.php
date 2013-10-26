<?php
namespace FS\SolrBundle\Query;

use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Update\Query\Document\Document;

abstract class AbstractQuery extends Query
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var object
     */
    private $entity = null;

    /**
     * @return the $entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param \Solarium\QueryType\Update\Query\Document\Document $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return \Solarium\QueryType\Update\Query\Document\Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
