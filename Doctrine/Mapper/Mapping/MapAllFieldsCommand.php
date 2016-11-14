<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * command maps all fields of the entity
 *
 * uses parent method for mapping of document_name and id
 */
class MapAllFieldsCommand extends AbstractDocumentCommand
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
     * @param MetaInformationInterface $meta
     *
     * @return null|\Solarium\QueryType\Update\Query\Document\Document
     */
    public function createDocument(MetaInformationInterface $meta)
    {
        $fields = $meta->getFields();
        if (count($fields) == 0) {
            return null;
        }

        $document = parent::createDocument($meta);

        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                continue;
            }

            $value = $field->getValue();
            if ($value instanceof Collection) {
                $document->addField($field->getNameWithAlias(), $this->mapCollection($field), $field->getBoost());
            } elseif (is_object($value)) {
                $document->addField($field->getNameWithAlias(), $this->mapObject($field), $field->getBoost());
            } else {
                $document->addField($field->getNameWithAlias(), $field->getValue(), $field->getBoost());
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
     */
    private function mapObject(Field $field)
    {
        $value = $field->getValue();
        $getter = $field->getGetterName();
        if (!empty($getter)) {
            return $this->callGetterMethod($value, $getter);
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
     */
    private function callGetterMethod($object, $getter)
    {
        $methodName = $getter;
        if (strpos($getter, '(') !== false) {
            $methodName = substr($getter, 0, strpos($getter, '('));
        }

        $method = new \ReflectionMethod($object, $methodName);
        if (strpos($getter, ')') !== false) {
            $parameters = explode(',', substr($getter, strpos($getter, '(') + 1, -1));
            $parameters = array_map(function ($parameter) {
                return trim(preg_replace('#[\'"]#', '', $parameter));
            }, $parameters);

            return $method->invokeArgs($object, $parameters);
        }

        return $method->invoke($object);
    }

    /**
     * @param Field $field
     *
     * @return array
     */
    private function mapCollection(Field $field)
    {
        /** @var Collection $value */
        $value = $field->getValue();
        $getter = $field->getGetterName();
        if (!empty($getter)) {
            $values = array();
            foreach ($value as $relatedObj) {
                $values[] = $relatedObj->{$getter}();
            }

            return $values;
        }

        $collection = array();
        foreach ($value as $object) {
            $metaInformation = $this->metaInformationFactory->loadInformation($object);

            $field = array();
            $document = $this->createDocument($metaInformation);
            foreach ($document as $fieldName => $value) {
                $field[$fieldName] = $value;
            }

            $collection[] = $field;
        }

        return $collection;
    }
}
