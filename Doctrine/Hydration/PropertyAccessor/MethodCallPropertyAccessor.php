<?php

namespace FS\SolrBundle\Doctrine\Hydration\PropertyAccessor;

class MethodCallPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @var string
     */
    private $setterName;

    /**
     * @param string $setterName
     */
    public function __construct($setterName)
    {
        $this->setterName = $setterName;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($targetObject, $value)
    {
        $targetObject->{$this->setterName}($value);
    }
}