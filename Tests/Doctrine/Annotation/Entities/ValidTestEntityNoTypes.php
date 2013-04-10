<?php
namespace FS\SolrBundle\Tests\Doctrine\Annotation\Entities;

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 *
 * @Solr\Document
 */
class ValidTestEntityNoTypes
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

