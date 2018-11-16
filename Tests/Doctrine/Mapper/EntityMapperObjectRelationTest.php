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
use FS\SolrBundle\Doctrine\Annotation as Solr;

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
        $collectionItem1 = new NestedEntity();
        $collectionItem1->setId(uniqid());
        $collectionItem1->setName('title 1');

        $collectionItem2 = new NestedEntity();
        $collectionItem2->setId(uniqid());
        $collectionItem2->setName('title 2');

        $collection = new ArrayCollection([$collectionItem1, $collectionItem2]);

        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());
        $entity->setCollectionValidGetter($collection);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('_childDocuments_', $document->getFields());
        $collectionField = $document->getFields()['_childDocuments_'];

        $this->assertCollectionItemsMappedProperly($collectionField, 1);
    }

    /**
     * @test
     */
    public function doNotIndexEmptyNestedCollection()
    {
        $collection = new ArrayCollection([]);

        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());
        $entity->setCollectionValidGetter($collection);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayNotHasKey('_childDocuments_', $document->getFields());
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage No method "unknown()" found in class "FS\SolrBundle\Tests\Fixtures\EntityNestedProperty"
     */
    public function throwExceptionIfConfiguredGetterDoesNotExists()
    {
        $collection = new ArrayCollection([new \DateTime(), new \DateTime()]);

        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());
        $entity->setCollectionInvalidGetter($collection);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $this->mapper->toDocument($metaInformation);
    }

    /**
     * @test
     */
    public function mapRelationFieldAllFields()
    {
        $collectionItem1 = new NestedEntity();
        $collectionItem1->setId(uniqid());
        $collectionItem1->setName('title 1');

        $collectionItem2 = new NestedEntity();
        $collectionItem2->setId(uniqid());
        $collectionItem2->setName('title 2');

        $collection = [$collectionItem1, $collectionItem2];

        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());
        $entity->setCollection($collection);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('_childDocuments_', $document->getFields());
        $collectionField = $document->getFields()['_childDocuments_'];

        $this->assertCollectionItemsMappedProperly($collectionField, 2);
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
    public function indexEntityMultipleRelations()
    {
        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());

        $nested1 = new NestedEntity();
        $nested1->setId(uniqid());
        $nested1->setName('nested document');

        $entity->setNestedProperty($nested1);

        $collectionItem1 = new NestedEntity();
        $collectionItem1->setId(uniqid());
        $collectionItem1->setName('collection item 1');

        $collectionItem2 = new NestedEntity();
        $collectionItem2->setId(uniqid());
        $collectionItem2->setName('collection item 2');

        $collection = new ArrayCollection([$collectionItem1, $collectionItem2]);

        $entity->setCollection($collection);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();

        $this->assertEquals(3, count($fields['_childDocuments_']));
    }

    /**
     * @test
     */
    public function mapRelationField_Getter()
    {
        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());

        $object = new NestedEntity();
        $object->setId(uniqid());
        $object->setName('nested entity');

        $entity->setSimpleGetter($object);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $document = $this->mapper->toDocument($metaInformation);

        $this->assertArrayHasKey('simple_getter_s', $document->getFields());

        $collectionField = $document->getFields()['simple_getter_s'];

        $this->assertEquals('nested entity', $collectionField);
    }

    /**
     * @test
     */
    public function callGetterWithParameter_ObjectProperty()
    {
        $date = new \DateTime();

        $entity = new EntityNestedProperty();
        $entity->setId(uniqid());
        $entity->setGetterWithParameters($date);

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $fields = $metaInformation->getFields();
        $metaInformation->setFields($fields);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();
        $this->assertArrayHasKey('getter_with_parameters_dt', $fields);
        $this->assertEquals($date->format('d.m.Y'), $fields['getter_with_parameters_dt']);
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
        $entity1->setComplexDataType(json_encode($data));

        $metaInformation = $this->metaInformationFactory->loadInformation($entity1);

        $document = $this->mapper->toDocument($metaInformation);

        $fields = $document->getFields();

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
     * @test
     */
    public function callGetterToRetrieveFieldValue()
    {
        $metainformation = $this->metaInformationFactory->loadInformation(new TestObject());

        $document = $this->mapper->toDocument($metainformation);

        $fields = $document->getFields();

        $this->assertArrayHasKey('property_s', $fields);
        $this->assertEquals(1234, $fields['property_s']);
    }

    /**
     * @param array $collectionField
     * @param int $expectedItems
     */
    private function assertCollectionItemsMappedProperly($collectionField, $expectedItems)
    {
        $this->assertEquals($expectedItems, count($collectionField), 'should be 2 collection items');

        foreach ($collectionField as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name_t', $item);
            $this->assertEquals(2, count($item), 'field has 2 properties');
        }
    }
}

/** @Solr\Document() */
class TestObject {

    /** @Solr\Id  */
    private $id;

    public function __construct()
    {
        $this->id = uniqid();
    }

    public function getId()
    {
        return $this->id;
    }

    /** @Solr\Field(type="string", name="property") */
    public function getPropertyValue()
    {
        return 1234;
    }

    public function testGetter($para1, $para2, $para3)
    {
        return array($para1, $para2, $para3);
    }

    public function asString()
    {
        return $this;
    }
}