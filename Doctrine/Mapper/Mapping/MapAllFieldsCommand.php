<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Annotation\VirtualField;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use Doctrine\Common\Collections\Collection;

/**
 * command maps all fields of the entity
 *
 * uses parent method for mapping of document_name and id
 */
class MapAllFieldsCommand extends AbstractDocumentCommand
{

    /**
     * @param MetaInformationInterface $meta
     *
     * @return null|\Solarium\QueryType\Update\Query\Document\Document
     */
    public function createDocument(MetaInformationInterface $meta)
    {
        $fields = $meta->getFields();
        if (count($fields) === 0) {
            return null;
        }

        $document = parent::createDocument($meta);

        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                continue;
            }

            $value  = $field->getValue();
            $getter = $field->getGetterName();
            if (!empty($getter)) {
                if ($value instanceof Collection) {
                    $values = array();
                    foreach ($value as $relatedObj) {
                        $values[] = $relatedObj->{$getter}();
                    }
                    
                    $document->addField($field->getNameWithAlias(), $values, $field->getBoost());
                } elseif (is_object($value) && method_exists($value, $getter)) {
                    $document->addField($field->getNameWithAlias(), $value->{$getter}(), $field->getBoost());
                }
            } else {
                $document->addField($field->getNameWithAlias(), $field->getValue(), $field->getBoost());
            }
        }

        $virtualFields = $meta->getVirtualFields();
        if (count($virtualFields) === 0) {
            return $document;
        }

        foreach ($virtualFields as $virtualField) {
            if (!$virtualField instanceof VirtualField) {
                continue;
            }

            $entity = $meta->getEntity();
            $getter = $virtualField->name;
            if (empty($getter) || !method_exists($entity, $getter)) {
                continue;
            }

            $value = $entity->{$getter}();
            if ($value instanceof Collection) {
                $document->addField($virtualField->getNameWithAlias(), $value->toArray(), $virtualField->getBoost());
            } else {
                $document->addField($virtualField->getNameWithAlias(), $value, $virtualField->getBoost());
            }
        }

        return $document;
    }
}
