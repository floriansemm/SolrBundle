<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

/**
 * command maps all fields of the entity
 *
 * uses parent method for mapping of document_name and id
 */
class MapAllFieldsCommand extends AbstractDocumentCommand
{

    /**
     * @param MetaInformation $meta
     * @return null|\Solarium\QueryType\Update\Query\Document\Document
     */
    public function createDocument(MetaInformation $meta)
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

            $document->addField($field->getNameWithAlias(), $field->getValue(), $field->getBoost());
        }

        return $document;
    }
}
