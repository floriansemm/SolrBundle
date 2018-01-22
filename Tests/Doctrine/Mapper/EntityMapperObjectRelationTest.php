<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Hydration\HydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Fixtures\EntityNestedProperty;
use FS\SolrBundle\Tests\Fixtures\NestedEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithCollection;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithRelation;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

class EntityMapperObjectRelationTest extends \PHPUnit_Framework_TestCase
{
    private $doctrineHydrator = null;
    private $indexHydrator = null;

    /**
     * @var MetaInformationFactory
     */
    private $metaInformationFactory;

    /**
     * @var EntityMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->doctrineHydrator = $this->createMock(HydratorInterface::class);
        $this->indexHydrator = $this->createMock(HydratorInterface::class);
        $this->metaInformationFactory = new MetaInformationFactory(new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader()));

        $this->mapper = new EntityMapper($this->doctrineHydrator, $this->indexHydrator, $this->metaInformationFactory);
    }

    /**
     * @test
     */
    public function mapRelationFieldByGetter()
    {
        $collectionItem1 = new ValidTestEntity();
        $collectionItem1->setId(uniqid());
        $collectionItem1->setTitle('title 1');
        $collectionItem1->setText('text 1');

        $collectionItem2 = new ValidTestEntity();
        $collectionItem2->setId(uniqid());
        $collectionItem2->setTitle('title 2');
        $collectionItem2->setText('text 2');

        $collection = new ArrayCollection();
        $collection->add($collectionItem1);
        $collection->add($collectionItem2);

        $entity = new ValidTestEntityWithCollection();
        $entity->setTitle($collection);

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity);
        $fields = $metaInformation->getFields();
        $fields[] = new Field(array('name' => 'collection', 'type' => 'strings', 'boost' => '1', 'value' => $collection, 'getter'=>'getTitle'));
        $metaInformation->setFields($fields);

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('_childDocuments_', $document->getFields());
        $collectionField = $document->getFields()['_childDocuments_'];

        $this->assertCollectionItemsMappedProperly($collectionField);
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage No method "unknown()" found in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithCollection"
     */
    public function throwExceptionIfConfiguredGetterDoesNotExists()
    {
        $entity1 = new \DateTime('+2 days');

        $entity2 = new \DateTime('+1 day');

        $collection = new ArrayCollection();
        $collection->add($entity1);
        $collection->add($entity2);

        $metaInformation = MetaTestInformationFactory::getMetaInformation(new ValidTestEntityWithCollection());
        $fields = $metaInformation->getFields();
        $fields[] = new Field(array('name' => 'collection', 'type' => 'strings', 'boost' => '1', 'value' => $collection, 'getter'=>'unknown(\'d.m.Y\')'));
        $metaInformation->setFields($fields);

        $this->mapper->toDocument($metaInformation);
    }

    /**
     * @test
     */
    public function mapRelationFieldAllFields()
    {
        $collectionItem1 = new ValidTestEntity();
        $collectionItem1->setId(uniqid());
        $collectionItem1->setTitle('title 1');
        $collectionItem1->setText('text 1');

        $collectionItem2 = new ValidTestEntity();
        $collectionItem2->setId(uniqid());
        $collectionItem2->setTitle('title 2');
        $collectionItem2->setText('text 2');

        $collection = new ArrayCollection();
        $collection->add($collectionItem1);
        $collection->add($collectionItem2);

        $entity = new ValidTestEntityWithCollection();
        $entity->setId(uniqid());
        $entity->setCollectionNoGetter($collection);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('_childDocuments_', $document->getFields());
        $collectionField = $document->getFields()['_childDocuments_'];

        $this->assertCollectionItemsMappedProperly($collectionField);
    }

    /**
     * @test
     */
    public function mapRelationField_AllFields()
    {
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

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('relation_ss', $document->getFields());
        $collectionField = $document->getFields()['relation_ss'];

        $this->assertEquals(4, count($collectionField), 'collection contains 4 fields');
    }

    /**
     * @test
     */
    public function mapEntityWithRelation_singleObject()
    {
        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());

        $nested1 = new NestedEntity();
        $nested1->setId(uniqid());
        $nested1->setName('nested document');

        $entity->setNestedProperty($nested1);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();

        $this->assertArrayHasKey('_childDocuments_', $fields);

        $subDocument = $fields['_childDocuments_'][0];

        $this->assertArrayHasKey('id', $subDocument);
        $this->assertArrayHasKey('name_t', $subDocument);
    }

    /**
     * @test
     */
    public function mapRelationField_Getter()
    {
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

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('relation_ss', $document->getFields());
        $collectionField = $document->getFields()['relation_ss'];

        $this->assertEquals('embedded object', $collectionField);
    }

    /**
     * @test
     */
    public function callGetterWithParameter_ObjectProperty()
    {
        $entity1 = new ValidTestEntity();
        $date = new \DateTime();

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $metaInformation->setFields(array(
            new Field(array('name' => 'created_at', 'type' => 'datetime', 'boost' => '1', 'value' => $date, 'getter' => "format('d.m.Y')"))
        ));

        $fields = $metaInformation->getFields();
        $metaInformation->setFields($fields);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();

        $this->assertArrayHasKey('created_at_dt', $fields);
        $this->assertEquals($date->format('d.m.Y'), $fields['created_at_dt']);
    }

    /**
     * @test
     */
    public function callGetterWithParameters_ObjectProperty()
    {
        $entity1 = new ValidTestEntity();

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $metaInformation->setFields(array(
            new Field(array('name' => 'test_field', 'type' => 'datetime', 'boost' => '1', 'value' => new TestObject(), 'getter' => "testGetter('string3', 'string1', 'string')"))
        ));

        $fields = $metaInformation->getFields();
        $metaInformation->setFields($fields);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();

        $this->assertArrayHasKey('test_field_dt', $fields);
        $this->assertEquals(array('string3', 'string1', 'string'), $fields['test_field_dt']);
    }

    /**
     * @test
     */
    public function callGetterWithParameter_SimpleProperty()
    {
        $data = ['key' => 'value'];

        $date = new \DateTime();
        $entity1 = new ValidTestEntity();
        $entity1->setId(uniqid());
        $entity1->setCreatedAt($date);
        $entity1->setComplexDataType(json_encode($data));

        $metaInformation = $this->metaInformationFactory->loadInformation($entity1);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();

        $this->assertArrayHasKey('created_at_dt', $fields);
        $this->assertEquals($date->format('d.m.Y'), $fields['created_at_dt']);
        $this->assertArrayHasKey('complex_data_type', $fields);

        $this->assertEquals($data, $fields['complex_data_type']);
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage The configured getter "asString" in "FS\SolrBundle\Tests\Doctrine\Mapper\TestObject" must return a string or array, got object
     */
    public function callGetterWithObjectAsReturnValue()
    {
        $entity1 = new ValidTestEntity();

        $metaInformation = MetaTestInformationFactory::getMetaInformation($entity1);
        $metaInformation->setFields(array(
            new Field(array('name' => 'test_field', 'type' => 'datetime', 'boost' => '1', 'value' => new TestObject(), 'getter' => "asString"))
        ));

        $fields = $metaInformation->getFields();
        $metaInformation->setFields($fields);

        $this->mapper->toDocument($metaInformation);
    }

    /**
     * @param array $collectionField
     */
    private function assertCollectionItemsMappedProperly($collectionField)
    {
        $this->assertEquals(2, count($collectionField), 'should be 2 collection items');

        foreach ($collectionField as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertEquals(3, count($item), 'field has 3 properties');
        }
    }
}

class TestObject {
    public function testGetter($para1, $para2, $para3)
    {
        return array($para1, $para2, $para3);
    }

    public function asString()
    {
        return $this;
    }
}