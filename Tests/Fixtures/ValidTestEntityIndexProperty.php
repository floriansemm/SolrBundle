<?php

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document(index="my_core")
 */
class ValidTestEntityIndexProperty
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
}

