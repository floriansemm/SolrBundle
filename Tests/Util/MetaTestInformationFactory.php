<?php
namespace FS\SolrBundle\Tests\Util;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Annotation\Id;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class MetaTestInformationFactory
{
    /**
     * @param object $entity
     *
     * @return MetaInformation
     */
    public static function getMetaInformation($entity = null)
    {
        if ($entity === null) {
            $entity = new ValidTestEntity();
        }
        $entity->setId(2);

        $metaInformation = new MetaInformation();

        $title = new Field(array('name' => 'title', 'boost' => '1.8', 'value' => 'A title'));
        $text = new Field(array('name' => 'text', 'type' => 'text', 'value' => 'A text'));
        $createdAt = new Field(array('name' => 'created_at', 'type' => 'date', 'boost' => '1', 'value' => 'A created at'));

        $metaInformation->setFields(array($title, $text, $createdAt));

        $fieldMapping = array(
            'id' => 'id',
            'title_s' => 'title',
            'text_t' => 'text',
            'created_at_dt' => 'created_at'
        );
        $metaInformation->setIdentifier(new Id(array()));
        $metaInformation->setBoost(1);
        $metaInformation->setFieldMapping($fieldMapping);
        $metaInformation->setEntity($entity);
        $metaInformation->setDocumentName('validtestentity');
        $metaInformation->setClassName(get_class($entity));
        $metaInformation->setIndex(null);

        return $metaInformation;
    }
}

