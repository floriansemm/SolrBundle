<?php

namespace FS\SolrBundle\Client;

/**
 * Defines class which can instantiate a solr-client
 */
interface Builder
{
    /**
     * returns a implementation of a solr-client
     *
     * @return mixed
     */
    public function build();
} 