<?php
namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;
use phpDocumentor\Reflection\DocBlock\Type\Collection;
use FS\SolrBundle\Doctrine\Annotation\Field;

/**
 * Defines fields of a solr-document
 *
 * @Annotation
 */
class Fields extends Annotation
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $getter;
    
    /**
     * @var Field[]
     */
    public $fields;
}
