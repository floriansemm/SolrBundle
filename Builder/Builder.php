<?php
namespace FS\SolrBundle\Builder;


interface Builder
{
    /**
     * returns a implementation of a solr-client
     *
     * @return mixed
     */
    public function build();
} 