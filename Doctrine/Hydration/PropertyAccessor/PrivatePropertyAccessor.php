<?php

namespace FS\SolrBundle\Doctrine\Hydration\PropertyAccessor;

class PrivatePropertyAccessor implements PropertyAccessorInterface
{

    /**
     * @var \ReflectionProperty
     */
    private $classProperty;

    /**
     * @param \ReflectionProperty $classProperty
     */
    public function __construct(\ReflectionProperty $classProperty)
    {
        $this->classProperty = $classProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($targetObject, $value)
    {
        $this->classProperty->setAccessible(true);
        $this->classProperty->setValue($targetObject, $value);
    }
}