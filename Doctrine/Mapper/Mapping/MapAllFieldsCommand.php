<?php
namespace FS\SolrBundle\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class MapAllFieldsCommand extends AbstractDocumentCommand
{

    /**
     * (non-PHPdoc)
     * @see FS\SolrBundle\Doctrine\Mapper\Mapping.AbstractDocumentCommand::createDocument()
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
