<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Defines a solr-document
 *
 * @Annotation
 */
class Document extends Annotation
{
    /**
     * @var string
     */
    public $repository = '';

    /**
     * @var int
     */
    public $boost = 0;

    /**
     * @var string
     */
    public $index = null;

    /**
     * @var string
     */
    public $indexHandler;

    /**
     * @return number
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }
}
