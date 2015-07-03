<?php

namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Id extends Annotation
{
    /**
     * @var string name of the identifier field
     */
    public $name;
}
