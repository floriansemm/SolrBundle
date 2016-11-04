<?php


namespace FS\SolrBundle\Doctrine\Hydration\PropertyAccessor;


interface PropertyAccessorInterface
{

    /**
     * @param object $targetObject
     * @param mixed  $value
     */
    public function setValue($targetObject, $value);
}