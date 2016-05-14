<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use Doctrine\Common\Collections\ArrayCollection;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntityWithCollection;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntityWithRelation;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group mappingcommands
 */
class MapAllFieldsCommandTest extends SolrDocumentTest
{

    public static $MAPPED_FIELDS = array('title_s', 'text_t', 'created_at_dt');

    public function testMapEntity_DocumentShouldContainThreeFields()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $actual = $command->createDocument(MetaTestInformationFactory::getMetaInformation());

        $this->assertTrue($actual instanceof Document, 'is a Document');
        $this->assertFieldCount(3, $actual, 'three fields are mapped');

        $this->assertEquals(1, $actual->getBoost(), 'document boost should be 1');

        $boostTitleField = $actual->getFieldBoost('title_s');
        $this->assertEquals(1.8, $boostTitleField, 'boost value of field title_s should be 1.8');

        $this->assertHasDocumentFields($actual, self::$MAPPED_FIELDS);
    }

    /**
     * @test
     */
    public function mapRelationFieldByGetter()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $entity1 = new ValidTestEntity();
        $entity1->setTitle('title 1');

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('title 2');

        $collection = new ArrayCollection();
        $collection->add($entity1);
        $collection->add($entity2);

        $metaInformation = MetaTestInformationFactory::getMetaInformation(new ValidTestEntityWithCollection());
        $fields = $metaInformation->getFields();
        $fields[] = new Field(array('name' => 'collection', 'type' => 'strings', 'boost' => '1', 'value' => $collection, 'getter'=>'getTitle'));
        $metaInformation->setFields($fields);

        $actual = $command->createDocument($metaInformation);

        $this->assertArrayHasKey('collection_ss', $actual->getFields());
        $collectionField = $actual->getFields()['collection_ss'];

        $this->assertEquals(2, count($collectionField));
    }

    /**
     * @test
     */
    public function mapRelationFieldAllFields()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $entity1 = new ValidTestEntity();
        $entity1->setTitle('title 1');
        $entity1->setText('text 1');

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('title 2');
        $entity1->setText('text 2');

        $collection = new ArrayCollection();
        $collection->add($entity1);
        $collection->add($entity2);

        $metaInformation = MetaTestInformationFactory::getMetaInformation(new ValidTestEntityWithCollection());
        $fields = $metaInformation->getFields();
        $fields[] = new Field(array('name' => 'collection', 'type' => 'strings', 'boost' => '1', 'value' => $collection));
        $metaInformation->setFields($fields);

        $actual = $command->createDocument($metaInformation);

        $this->assertArrayHasKey('collection_ss', $actual->getFields());
        $collectionField = $actual->getFields()['collection_ss'];

        $this->assertEquals(2, count($collectionField), 'collection contains 2 fields');
        $this->assertEquals(3, count($collectionField[0]), 'field has 2 properties');
    }

    /**
     * @test
     */
    public function mapRelationField_AllFields()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('embbeded object');

        $entity1 = new ValidTestEntityWithRelation();
        $entity1->setTitle('title 1');
        $entity1->setText('text 1');
        $entity1->setRelation($entity2);

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $fields = $metaInformation->getFields();
        $fields[] = new Field(array('name' => 'relation', 'type' => 'strings', 'boost' => '1', 'value' => $entity1));
        $metaInformation->setFields($fields);

        $actual = $command->createDocument($metaInformation);

        $this->assertArrayHasKey('relation_ss', $actual->getFields());
        $collectionField = $actual->getFields()['relation_ss'];

        $this->assertEquals(4, count($collectionField), 'collection contains 4 fields');
    }

    /**
     * @test
     */
    public function mapRelationField_Getter()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('embedded object');

        $entity1 = new ValidTestEntityWithRelation();
        $entity1->setTitle('title 1');
        $entity1->setText('text 1');
        $entity1->setRelation($entity2);

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $fields = $metaInformation->getFields();
        $fields[] = new Field(array('name' => 'relation', 'type' => 'strings', 'boost' => '1', 'value' => $entity2, 'getter'=>'getTitle'));
        $metaInformation->setFields($fields);

        $actual = $command->createDocument($metaInformation);

        $this->assertArrayHasKey('relation_ss', $actual->getFields());
        $collectionField = $actual->getFields()['relation_ss'];

        $this->assertEquals('embedded object', $collectionField);
    }

    /**
     * @test
     */
    public function callGetterWithParameter()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $entity1 = new ValidTestEntity();
        $date = new \DateTime();

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $metaInformation->setFields(array(
            new Field(array('name' => 'created_at', 'type' => 'datetime', 'boost' => '1', 'value' => $date, 'getter' => "format('d.m.Y')"))
        ));

        $fields = $metaInformation->getFields();
        $metaInformation->setFields($fields);

        $actual = $command->createDocument($metaInformation);

        $fields = $actual->getFields();

        $this->assertArrayHasKey('created_at_dt', $fields);
        $this->assertEquals($date->format('d.m.Y'), $fields['created_at_dt']);
    }

    /**
     * @test
     */
    public function callGetterWithParameters()
    {
        $command = new MapAllFieldsCommand(new MetaInformationFactory());

        $entity1 = new ValidTestEntity();

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $metaInformation->setFields(array(
            new Field(array('name' => 'test_field', 'type' => 'datetime', 'boost' => '1', 'value' => new TestObject(), 'getter' => "testGetter('string3', 'string1', 'string')"))
        ));

        $fields = $metaInformation->getFields();
        $metaInformation->setFields($fields);

        $actual = $command->createDocument($metaInformation);

        $fields = $actual->getFields();

        $this->assertArrayHasKey('test_field_dt', $fields);
        $this->assertEquals(array('string3', 'string1', 'string'), $fields['test_field_dt']);
    }
}

class TestObject {
    public function testGetter($para1, $para2, $para3)
    {
        return array($para1, $para2, $para3);
    }
}
