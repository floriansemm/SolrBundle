<?php
namespace FS\SolrBundle\Query;

use FS\SolrBundle\Solr;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Update\Query\Document\Document;

abstract class AbstractQuery extends Query
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     *
     * @var Solr
     */
    protected $solr = null;

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

    /**
     * @param \FS\SolrBundle\Solr $solr
     */
    public function setSolr($solr)
    {
        $this->solr = $solr;
    }

    /**
     * @return \FS\SolrBundle\Solr
     */
    public function getSolr()
    {
        return $this->solr;
    }

    public function setHydrationMode($mode)
    {
        $this->getSolr()->getMapper()->setHydrationMode($mode);
    }
}
