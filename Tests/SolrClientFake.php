<?php
namespace FS\SolrBundle\Tests;

use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\Repository\Repository;
use FS\SolrBundle\SolrInterface;

class SolrClientFake implements SolrInterface
{
    public $commit = false;
    public $response = array();
    public $mapper;
    public $commandFactory;
    public $metaFactory;

    /**
     * @var AbstractQuery
     */
    public $query;

    public function getMapper()
    {
        return $this->mapper;
    }

    public function getCommandFactory()
    {
        return $this->commandFactory;
    }

    public function getMetaFactory()
    {
        return $this->metaFactory;
    }

    public function addDocument($doc)
    {
    }

    public function deleteByQuery($query)
    {
    }

    public function commit()
    {
        $this->commit = true;
    }

    public function isCommited()
    {
        return $this->commit;
    }

    public function query(AbstractQuery $query)
    {
        $this->query = $query;

        return $this->response;
    }

    public function setResponse(SolrResponseFake $response)
    {
        $this->response = $response;
    }

    public function getOptions()
    {
        return array();
    }

    public function createQuery($entity)
    {
        $metaInformation = $this->metaFactory->loadInformation($entity);
        $class = $metaInformation->getClassName();
        $entity = new $class;

        $query = new SolrQuery();
        $query->setSolr($this);
        $query->setEntity($entity);
        $query->setIndex($metaInformation->getIndex());
        $query->setMetaInformation($metaInformation);
        $query->setMappedFields($metaInformation->getFieldMapping());

        return $query;
    }

    public function removeDocument($entity)
    {
        // TODO: Implement removeDocument() method.
    }

    public function updateDocument($entity)
    {
        // TODO: Implement updateDocument() method.
    }

    public function getRepository($entity)
    {
        // TODO: Implement getRepository() method.
    }

    public function computeChangeSet(array $doctrineChangeSet, $entity)
    {
        // TODO: Implement computeChangeSet() method.
    }

    public function createQueryBuilder($entity)
    {
        // TODO: Implement createQueryBuilder() method.
    }
}
