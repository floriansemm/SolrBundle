<?php

namespace FS\SolrBundle\Doctrine\Mapper\Factory;

use Doctrine\Common\Collections\Collection;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Doctrine\Mapper\SolrMappingException;
use Ramsey\Uuid\Uuid;
use Solarium\QueryType\Update\Query\Document\Document;

class DocumentFactory
{
    /**
     * @var MetaInformationFactory
     */
    private $metaInformationFactory;

    /**
     * @param MetaInformationFactory $metaInformationFactory
     */
    public function __construct(MetaInformationFactory $metaInformationFactory)
    {
        $this->metaInformationFactory = $metaInformationFactory;
    }

    /**
     * @param MetaInformationInterface $metaInformation
     *
     * @return null|Document
     *
     * @throws SolrMappingException if no id is set
     */
    public function createDocument(MetaInformationInterface $metaInformation)
    {
        $fields = $metaInformation->getFields();
        if (count($fields) == 0) {
            return null;
        }

        if (!$metaInformation->getEntityId() && !$metaInformation->generateDocumentId()) {
            throw new SolrMappingException(sprintf('No entity id set for "%s"', $metaInformation->getClassName()));
        }

        $documentId = $metaInformation->getDocumentKey();
        if ($metaInformation->generateDocumentId()) {
            $documentId = $metaInformation->getDocumentName() . '_' . Uuid::uuid1()->toString();
        }

        $document = new Document();
        $document->setKey(MetaInformationInterface::DOCUMENT_KEY_FIELD_NAME, $documentId);

        $document->setBoost($metaInformation->getBoost());

        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                continue;
            }

            $value = $field->getValue();
            if ($value instanceof Collection) {
                $document->addField($field->getNameWithAlias(), $this->mapCollection($field, $metaInformation->getClassName()), $field->getBoost());
            } elseif (is_object($value)) {
                $document->addField($field->getNameWithAlias(), $this->mapObject($field), $field->getBoost());
            } else {
                if ($getter = $field->getGetterName()) {
                    $document->addField($field->getNameWithAlias(), $metaInformation->getEntity()->$getter(), $field->getBoost());
                } else {
                    $document->addField($field->getNameWithAlias(), $field->getValue(), $field->getBoost());
                }
            }

            if ($field->getFieldModifier()) {
                $document->setFieldModifier($field->getNameWithAlias(), $field->getFieldModifier());
            }
        }

        return $document;
    }

    /**
     * @param Field $field
     *
     * @return array|string
     *
     * @throws SolrMappingException if getter return value is object
     */
    private function mapObject(Field $field)
    {
        $value = $field->getValue();
        $getter = $field->getGetterName();
        if (!empty($getter)) {
            $getterReturnValue = $this->callGetterMethod($value, $getter);

            if (is_object($getterReturnValue)) {
                throw new SolrMappingException(sprintf('The configured getter "%s" in "%s" must return a string or array, got object', $getter, get_class($value)));
            }

            return $getterReturnValue;
        }

        $metaInformation = $this->metaInformationFactory->loadInformation($value);

        $field = array();
        $document = $this->createDocument($metaInformation);
        foreach ($document as $fieldName => $value) {
            $field[$fieldName] = $value;
        }

        return $field;
    }

    /**
     * @param object $object
     * @param string $getter
     *
     * @return mixed
     *
     * @throws SolrMappingException if given getter does not exists
     */
    private function callGetterMethod($object, $getter)
    {
        $methodName = $getter;
        if (strpos($getter, '(') !== false) {
            $methodName = substr($getter, 0, strpos($getter, '('));
        }

        if (!method_exists($object, $methodName)) {
            throw new SolrMappingException(sprintf('No method "%s()" found in class "%s"', $methodName, get_class($object)));
        }

        $method = new \ReflectionMethod($object, $methodName);
        // getter with arguments
        if (strpos($getter, ')') !== false) {
            $getterArguments = explode(',', substr($getter, strpos($getter, '(') + 1, -1));
            $getterArguments = array_map(function ($parameter) {
                return trim(preg_replace('#[\'"]#', '', $parameter));
            }, $getterArguments);

            return $method->invokeArgs($object, $getterArguments);
        }

        return $method->invoke($object);
    }

    /**
     * @param Field  $field
     * @param string $sourceTargetClass
     *
     * @return array
     *
     * @throws SolrMappingException if no getter method was found
     */
    private function mapCollection(Field $field, $sourceTargetClass)
    {
        /** @var Collection $value */
        $value = $field->getValue();
        $getter = $field->getGetterName();
        if ($getter == '') {
            throw new SolrMappingException(sprintf('No getter method for property "%s" configured in class "%s"', $field->name, $sourceTargetClass));
        }

        $values = array();
        foreach ($value as $relatedObj) {
            $values[] = $this->callGetterMethod($relatedObj, $getter);
        }

        return $values;
    }
}