<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapping\Mapper;

use Doctrine\Common\Annotations\Reader;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityIndexHandler;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityIndexProperty;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityNoBoost;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityNoTypes;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityFiltered;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityFloatBoost;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityNumericFields;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityWithInvalidBoost;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\EntityWithRepository;
use FS\SolrBundle\Tests\Doctrine\Mapper\NotIndexedEntity;

/**
 *
 * @group annotation
 */
class AnnotationReaderTest extends \PHPUnit_Framework_TestCase
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

        $this->assertEquals(4, count($fields), '4 fields are mapped');
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
     * @expectedException RuntimeException
     */
    public function testGetIdentifier_ShouldThrowException()
    {
        $this->reader->getIdentifier(new NotIndexedEntity());
    }

    public function testGetIdentifier()
    {
        $id = $this->reader->getIdentifier(new ValidTestEntity());

        $this->assertEquals('id', $id->name);
    }

    public function testGetFieldMapping_ThreeMappingsAndId()
    {
        $fields = $this->reader->getFieldMapping(new ValidTestEntity());

        $this->assertEquals(5, count($fields), 'five fields are mapped');
        $this->assertTrue(array_key_exists('title', $fields));
        $this->assertTrue(array_key_exists('id', $fields));
    }

    public function testGetRepository_ValidRepositoryDeclared()
    {
        $repository = $this->reader->getRepository(new EntityWithRepository());

        $expected = 'FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository';
        $actual = $repository;
        $this->assertEquals($expected, $actual, 'wrong declared repository');
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

    public function testGetBoost_BoostNotNumeric()
    {

        try {
            $boost = $this->reader->getEntityBoost(new ValidTestEntityWithInvalidBoost());

            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals(
                'Invalid boost value aaaa for entity FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityWithInvalidBoost',
                $e->getMessage()
            );
        }
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