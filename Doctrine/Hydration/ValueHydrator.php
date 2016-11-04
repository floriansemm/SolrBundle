<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use Doctrine\Common\Collections\Collection;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Hydration\PropertyAccessor\MethodCallPropertyAccessor;
use FS\SolrBundle\Doctrine\Hydration\PropertyAccessor\PrivatePropertyAccessor;
use FS\SolrBundle\Doctrine\Hydration\PropertyAccessor\PropertyAccessorInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;

/**
 * Maps all values of a given document on a target-entity
 */
class ValueHydrator implements HydratorInterface
{
    /**
     * @var PropertyAccessorInterface[]
     */
    private $cache;

    /**
     * {@inheritdoc}
     */
    public function hydrate($document, MetaInformationInterface $metaInformation)
    {
        if (!isset($this->cache[$metaInformation->getDocumentName()])) {
            $this->cache[$metaInformation->getDocumentName()] = array();
        }

        $targetEntity = $metaInformation->getEntity();

        $reflectionClass = new \ReflectionClass($targetEntity);
        foreach ($document as $property => $value) {
            if ($property === MetaInformationInterface::DOCUMENT_KEY_FIELD_NAME) {
                $value = $this->removePrefixedKeyValues($value);
            }

            // skip field if value is array or "flat" object
            // hydrated object should contain a list of real entities / entity
            if ($this->mapValue($property, $value, $metaInformation) == false) {
                continue;
            }

            if (isset($this->cache[$metaInformation->getDocumentName()][$property])) {
                $this->cache[$metaInformation->getDocumentName()][$property]->setValue($targetEntity, $value);

                continue;
            }

            // find setter method
            $camelCasePropertyName = $this->toCamelCase($this->removeFieldSuffix($property));
            $setterMethodName = 'set'.ucfirst($camelCasePropertyName);
            if (method_exists($targetEntity, $setterMethodName)) {
                $accessor = new MethodCallPropertyAccessor($setterMethodName);
                $accessor->setValue($targetEntity, $value);

                $this->cache[$metaInformation->getDocumentName()][$property] = $accessor;

                continue;
            }


            if ($reflectionClass->hasProperty($this->removeFieldSuffix($property))) {
                $classProperty = $reflectionClass->getProperty($this->removeFieldSuffix($property));
            } else {
                // could no found document-field in underscore notation, transform them to camel-case notation
                $camelCasePropertyName = $this->toCamelCase($this->removeFieldSuffix($property));
                if ($reflectionClass->hasProperty($camelCasePropertyName) == false) {
                    continue;
                }

                $classProperty = $reflectionClass->getProperty($camelCasePropertyName);
            }

            $accessor = new PrivatePropertyAccessor($classProperty);
            $accessor->setValue($targetEntity, $value);

            $this->cache[$metaInformation->getDocumentName()][$property] = $accessor;
        }

        return $targetEntity;
    }

    /**
     * returns the clean fieldname without type-suffix
     *
     * eg: title_s => title
     *
     * @param string $property
     *
     * @return string
     */
    protected function removeFieldSuffix($property)
    {
        if (($pos = strrpos($property, '_')) !== false) {
            return substr($property, 0, $pos);
        }

        return $property;
    }

    /**
     * keyfield product_1 becomes 1
     *
     * @param string $value
     *
     * @return string
     */
    protected function removePrefixedKeyValues($value)
    {
        if (($pos = strrpos($value, '_')) !== false) {
            return substr($value, ($pos + 1));
        }

        return $value;
    }

    /**
     * returns field name camelcased if it has underlines
     *
     * eg: user_id => userId
     *
     * @param string $fieldname
     *
     * @return string
     */
    private function toCamelCase($fieldname)
    {
        $words = str_replace('_', ' ', $fieldname);
        $words = ucwords($words);
        $pascalCased = str_replace(' ', '', $words);

        return lcfirst($pascalCased);
    }

    /**
     * Check if given field and value can be mapped
     *
     * @param string                   $fieldName
     * @param string                   $value
     * @param MetaInformationInterface $metaInformation
     *
     * @return bool
     */
    public function mapValue($fieldName, $value, MetaInformationInterface $metaInformation)
    {
        return true;
    }
} 