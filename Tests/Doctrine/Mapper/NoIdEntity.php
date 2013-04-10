<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation as Solr;


/**
 *
 *
 *
 * @author Florian
 * @Solr\Index
 */
class NoIdEntity
{
    private $id;

    /**
     * @Solr\Field(type="string")
     * @var string
     */
    private $text;

    public function getId()
    {
        return $this->id;
    }
}

