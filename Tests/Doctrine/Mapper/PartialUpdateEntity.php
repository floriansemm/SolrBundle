<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation as Solr;

class PartialUpdateEntity extends ValidTestEntity
{
    /**
     * @var string
     *
     * @Solr\Field(fieldModifier="set")
     */
    private $subtitle;

    /**
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }
}