<?php

namespace FS\SolrBundle;

use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Repository\Repository;

interface SolrInterface
{

    /**
     * @param object|string $entity entity, entity-alias or classname
     */
    public function removeDocument($entity);

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return bool
     */
    public function addDocument($entity);

    /**
     * @param AbstractQuery $query
     *
     * @return array of found documents
     *
     * @throws SolrException
     */
    public function query(AbstractQuery $query);

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return bool
     */
    public function updateDocument($entity);

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return Repository
     *
     * @throws \RuntimeException if repository of the given $entityAlias does not extend FS\SolrBundle\Repository\Repository
     */
    public function getRepository($entity);

    /**
     * @param object|string $entity entity, entity-alias or classname
     *
     * @return QueryBuilderInterface
     */
    public function createQueryBuilder($entity);
}