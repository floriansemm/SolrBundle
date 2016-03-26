<?php

namespace FS\SolrBundle\Client;

interface Builder
{
    /**
     * returns a implementation of a solr-client
     *
     * @return mixed
     */
    public function build();
} 