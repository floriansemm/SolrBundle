<?php

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document(indexHandler="indexHandler")
 */
class ValidTestEntityIndexHandler
{

    /**
     * @Solr\Id
     */
    private $id;

    /**
     *
     * @Solr\Field
     */
    private $title;

    public function indexHandler()
    {
        return 'my_core';
    }
}

