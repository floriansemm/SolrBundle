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

        if ($metaInformation->getField($fieldName) && $metaInformation->getField($fieldName)->getter) {
            return false;
        }

        $fieldSuffix = $this->removePrefixedKeyFieldName($fieldName);
        if ($fieldSuffix === false) {
            return false;
        }

        if (array_key_exists($fieldSuffix, Field::getComplexFieldMapping())) {
            return false;
        }

        return true;
    }

}