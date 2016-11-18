<?php

namespace FS\SolrBundle;

use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Repository\Repository;

interface SolrInterface
{

    /**
     * @param object $entity
     */
    public function removeDocument($entity);

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function addDocument($entity);

    /**
     * @param AbstractQuery $query
     *
     * @return array of found documents
     */
    public function query(AbstractQuery $query);

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function updateDocument($entity);

    /**
     * @param string $entityAlias
     *
     * @return Repository
     *
     * @throws \RuntimeException if repository of the given $entityAlias does not extend FS\SolrBundle\Repository\Repository
     */
    public function getRepository($entityAlias);

    /**
     * @param string $entityAlias
     *
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($entityAlias);
}