<?php

namespace FS\SolrBundle\Doctrine\Hydration;

/**
 * Used when the index is not based on/in sync with a Database.
 */
class NoDatabaseValueHydrator extends ValueHydrator
{
    /**
     * Let the original values from the index untouched.
     *
     * {@inheritdoc}
     */
    public function removePrefixedKeyValues($property)
    {
        return $property;
    }

}