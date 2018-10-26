<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapping\Mapper;

use Doctrine\Common\Annotations\Reader;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Tests\Fixtures\ValidEntityRepository;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityIndexHandler;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityIndexProperty;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityNoBoost;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityNoTypes;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityFiltered;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityFloatBoost;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityNumericFields;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithInvalidBoost;
use FS\SolrBundle\Tests\Fixtures\ValidOdmTestDocument;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Tests\Fixtures\EntityWithRepository;
use FS\SolrBundle\Tests\Fixtures\NotIndexedEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFields;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsNoParentGetter;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsUnknownParentGetterMethod;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsNoFieldAlias;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsNoFieldGetter;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsUnknownFieldGetter;

/**
 *
 * @group annotation
 */
class AnnotationReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());
    }

    public function testGetFields_NoFieldsDected()
    {
        $fields = $this->reader->getFields(new NotIndexedEntity());

        $this->assertEquals(0, count($fields));
    }

    public function testGetFields_ThreeFieldsDetected()
    {
        $fields = $this->reader->getFields(new ValidTestEntity());

        $this->assertEquals(5, count($fields), '5 fields are mapped');
    }

    public function testGetFields_OneFieldsOneTypes()
    {
        $fields = $this->reader->getFields(new ValidTestEntityNoTypes());

        $this->assertEquals(1, count($fields), '1 fields are mapped');

        $field = $fields[0];
        $this->assertTrue($field instanceof Field);
        $this->assertEquals('title', $field->getNameWithAlias());
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage no identifer declared in entity FS\SolrBundle\Tests\Fixtures\NotIndexedEntity
     */
    public function testGetIdentifier_ShouldThrowException()
    {
        $this->reader->getIdentifier(new NotIndexedEntity());
    }

    public function testGetIdentifier()
    {
        $id = $this->reader->getIdentifier(new ValidTestEntity());

        $this->assertEquals('id', $id->name);
        $this->assertFalse($id->generateId);
    }

    public function testGetFieldMapping_ThreeMappingsAndId()
    {
        $fields = $this->reader->getFieldMapping(new ValidTestEntity());

        $this->assertEquals(6, count($fields), 'six fields are mapped');
        $this->assertTrue(array_key_exists('title', $fields));
        $this->assertTrue(array_key_exists('id', $fields));
    }

    public function testGetRepository_ValidRepositoryDeclared()
    {
        $repositoryClassname = $this->reader->getRepository(new EntityWithRepository());

        $this->assertEquals(ValidEntityRepository::class, $repositoryClassname, 'wrong declared repository');
    }

    public function testGetRepository_NoRepositoryAttributSet()
    {
        $repository = $this->reader->getRepository(new ValidTestEntity());

        $expected = '';
        $actual = $repository;
        $this->assertEquals($expected, $actual, 'no repository was declared');
    }

    public function testGetBoost()
    {
        $boost = $this->reader->getEntityBoost(new ValidTestEntity());

        $this->assertEquals(1, $boost);
    }

    public function testReadPropertiesMultipleFields()
    {
        $entity = new ValidTestEntityWithMultipleFields();

        $nested = new ValidTestEntity();
        $nested->setId(rand(1, 10000));
        $nested->setTitle('title 123');

        $nested2 = new ValidTestEntity();
        $nested2->setId(rand(1, 10000));
        $nested2->setTitle('title 234');

        $entity->setFields([$nested,$nested2]);
        $fields = $this->reader->getFields($entity);

        $this->assertEquals(9, count($fields), 'seven fields are mapped');
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage No getter defined for @Fields annotation in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsNoParentGetter"
     */
    public function testGetFields_NoParentGetter()
    {
        $entity = new ValidTestEntityWithMultipleFieldsNoParentGetter();

        $nested = new ValidTestEntity();
        $nested->setId(rand(1, 10000));
        $nested->setTitle('title 234');

        $entity->setFields([$nested]);
        $this->reader->getFields($entity);
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage Unknown method defined "getUnknownMethod" in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsUnknownParentGetterMethod"
     */
    public function testGetFields_UnknownParentGetterMethod()
    {
        $entity = new ValidTestEntityWithMultipleFieldsUnknownParentGetterMethod();

        $nested = new ValidTestEntity();
        $nested->setId(rand(1, 10000));
        $nested->setTitle('title 234');

        $entity->setFields([$nested]);
        $this->reader->getFields($entity);
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage No fieldAlias defined for field "fields" in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsNoFieldAlias"
     */
    public function testGetFields_NoFieldAlias()
    {
        $entity = new ValidTestEntityWithMultipleFieldsNoFieldAlias();

        $nested = new ValidTestEntity();
        $nested->setId(rand(1, 10000));
        $nested->setTitle('title 234');

        $entity->setFields([$nested]);
        $this->reader->getFields($entity);
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage No getter defined for fieldAlias "title" in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsNoFieldGetter"
     */
    public function testGetFields_NoFieldGetter()
    {
        $entity = new ValidTestEntityWithMultipleFieldsNoFieldGetter();

        $nested = new ValidTestEntity();
        $nested->setId(rand(1, 10000));
        $nested->setTitle('title 234');

        $entity->setFields([$nested]);
        $this->reader->getFields($entity);
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage Unknown method defined "getUnknownMethod" in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithMultipleFieldsUnknownFieldGetter"
     */
    public function testGetFields_UnknownFieldGetter()
    {
        $entity = new ValidTestEntityWithMultipleFieldsUnknownFieldGetter();

        $nested = new ValidTestEntity();
        $nested->setId(rand(1, 10000));
        $nested->setTitle('title 234');

        $entity->setFields([$nested]);
        $this->reader->getFields($entity);
    }

    /**
     * @expectedException \FS\SolrBundle\Doctrine\Annotation\AnnotationReaderException
     * @expectedExceptionMessage Invalid boost value "aaaa" in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithInvalidBoost" configured
     */
    public function testGetBoost_BoostNotNumeric()
    {
        $this->reader->getEntityBoost(new ValidTestEntityWithInvalidBoost());
    }

    public function testGetBoost_BoostIsNumberic()
    {
        $boost = $this->reader->getEntityBoost(new ValidTestEntityFloatBoost());

        $this->assertEquals(1.4, $boost);
    }

    public function testGetBoost_BoostIsNull()
    {
        $boost = $this->reader->getEntityBoost(new ValidTestEntityNoBoost());

        $this->assertEquals(null, $boost);
    }

    public function testGetCallback_CallbackDefined()
    {
        $callback = $this->reader->getSynchronizationCallback(new ValidTestEntityFiltered());

        $this->assertEquals('shouldBeIndex', $callback);
    }

    public function testGetCallback_NoCallbackDefined()
    {
        $callback = $this->reader->getSynchronizationCallback(new ValidTestEntity());

        $this->assertEquals('', $callback);
    }

    /**
     * @test
     */
    public function numericFieldTypeAreSupported()
    {
        $fields = $this->reader->getFields(new ValidTestEntityNumericFields());

        $this->assertEquals(4, count($fields));

        $expectedFields = array('integer_i', 'double_d', 'float_f', 'long_l');
        $actualFields = array();
        foreach ($fields as $field) {
            $actualFields[] = $field->getNameWithAlias();
        }

        $this->assertEquals($expectedFields, $actualFields);
    }

    /**
     * @test
     */
    public function getIndexFromAnnotationProperty()
    {
        $index = $this->reader->getDocumentIndex(new ValidTestEntityIndexProperty());

        $this->assertEquals('my_core', $index);
    }

    /**
     * @test
     */
    public function getIndexFromIndexHandler()
    {
        $index = $this->reader->getDocumentIndex(new ValidTestEntityIndexHandler());

        $this->assertEquals('my_core', $index);
    }

    /**
     * @test
     */
    public function readAnnotationsFromBaseClass()
    {
        $fields = $this->reader->getFields(new ChildEntity());

        $this->assertEquals(3, count($fields));
        $this->assertTrue($this->reader->hasDocumentDeclaration(new ChildEntity()));
    }

    /**
     * @test
     */
    public function readAnnotationsFromMultipleClassHierarchy()
    {
        $fields = $this->reader->getFields(new ChildEntity2());

        $this->assertEquals(4, count($fields));
    }

    /**
     * @test
     */
    public function readGetterMethodWithParameters()
    {
        /** @var Field[] $fields */
        $fields = $this->reader->getFields(new EntityWithObject());

        $this->assertCount(1, $fields);
        $this->assertEquals('format(\'d.m.Y\')', $fields[0]->getGetterName());

        $this->assertEquals('object_dt', $fields[0]->getNameWithAlias());

    }

    /**
     * @test
     */
    public function checkIfPlainObjectIsNotDoctrineEntity()
    {
        $this->assertFalse($this->reader->isOrm(new ChildEntity()), 'is not a doctrine entity');
    }

    /**
     * @test
     */
    public function checkIfValidEntityIsDoctrineEntity()
    {
        $this->assertTrue($this->reader->isOrm(new ValidTestEntity()), 'is a doctrine entity');
    }

    /**
     * @test
     */
    public function checkIfPlainObjectIsNotDoctrineDocument()
    {
        $this->assertFalse($this->reader->isOdm(new ChildEntity()), 'is not a doctrine document');
    }

    /**
     * @test
     */
    public function checkIfValidDocumentIsDoctrineDocument()
    {
        $this->assertTrue($this->reader->isOdm(new ValidOdmTestDocument()), 'is a doctrine document');
    }
}

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 *
 * @Solr\Document
 */
abstract class BaseEntity
{
    /**
     * @var mixed
     */
    protected $baseField1;

    /**
     *
     * @Solr\Field(type="integer")
     */
    protected $baseField2;
}

class ChildEntity extends BaseEntity
{
    /**
     * @Solr\Field(type="integer")
     */
    protected $baseField1;

    /**
     * @Solr\Field(type="integer")
     */
    protected $childField1;
}

class ChildEntity2 extends ChildEntity
{
    /**
     * @Solr\Field(type="integer")
     */
    private $childField2;
}

class EntityWithObject
{
    /**
     * @Solr\Field(type="datetime", getter="format('d.m.Y')")
     */
    private $object;
}
