<?php

namespace FS\SolrBundle\Repository;

/**
 * Defines common finder-method for document-repositories
 */
interface RepositoryInterface
{

    /**
     * @param array $args
     *
     * @return array
     */
    public function findBy(array $args);

    /**
     * @param int $id
     *
     * @return object
     */
    public function find($id);

    /**
     * @param array $args
     *
     * @return object
     */
    public function findOneBy(array $args);

    /**
     * @return array
     */
    public function findAll();
}
