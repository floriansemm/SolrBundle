<?php

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document(boost="1.4")
 */
class ValidTestEntityFloatBoost
{
    /**
     * @Solr\Id
     */
    private $id;

}

