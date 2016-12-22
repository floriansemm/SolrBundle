<?php

namespace FS\SolrBundle\Tests\Fixtures;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document
 * @Solr\SynchronizationFilter(callback="shouldBeIndex")
 */
class ValidTestEntityNumericFields
{

    /**
     * @Solr\Field(type="integer")
     */
    private $integer;

    /**
     *
     * @Solr\Field(type="double")
     */
    private $double;

    /**
     *
     * @Solr\Field(type="float")
     */
    private $float;

    /**
     *
     * @Solr\Field(type="long")
     */
    private $long;
}

