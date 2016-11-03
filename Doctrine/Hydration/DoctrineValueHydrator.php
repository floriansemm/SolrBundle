<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Doctrine\Annotation\Field;

class DoctrineValueHydrator extends ValueHydrator
{
    /**
     * {@inheritdoc}
     */
    public function mapValue($fieldName, $value, MetaInformationInterface $metaInformation)
    {
        if (is_array($value)) {
            return false;
        }

        // is object with getter
        if ($metaInformation->getField($fieldName) && $metaInformation->getField($fieldName)->getter) {
            return false;
        }

        return true;
    }

}