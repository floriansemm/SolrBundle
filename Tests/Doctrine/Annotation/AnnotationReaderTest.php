<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapping\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;
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

    public function testGetFields_NoFieldsDected()
    {
        $reader = new AnnotationReader();

        $fields = $reader->getFields(new NotIndexedEntity());

        $this->assertEquals(0, count($fields));
    }

    public function testGetFields_ThreeFieldsDetected()
    {
        $reader = new AnnotationReader();

        $fields = $reader->getFields(new ValidTestEntity());

        $this->assertEquals(4, count($fields), '4 fields are mapped');
    }

    public function testGetFields_OneFieldsOneTypes()
    {
        $reader = new AnnotationReader();

        $fields = $reader->getFields(new ValidTestEntityNoTypes());

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
        $reader = new AnnotationReader();

        $reader->getIdentifier(new NotIndexedEntity());
    }

    public function testGetIdentifier()
    {
        $reader = new AnnotationReader();

        $id = $reader->getIdentifier(new ValidTestEntity());

        $this->assertEquals('id', $id->name);
    }

    public function testGetFieldMapping_ThreeMappingsAndId()
    {
        $reader = new AnnotationReader();

        $fields = $reader->getFieldMapping(new ValidTestEntity());

        $this->assertEquals(5, count($fields), 'five fields are mapped');
        $this->assertTrue(array_key_exists('title', $fields));
        $this->assertTrue(array_key_exists('id', $fields));
    }

    public function testGetRepository_ValidRepositoryDeclared()
    {
        $reader = new AnnotationReader();
        $repository = $reader->getRepository(new EntityWithRepository());

        $expected = 'FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository';
        $actual = $repository;
        $this->assertEquals($expected, $actual, 'wrong declared repository');
    }

    public function testGetRepository_NoRepositoryAttributSet()
    {
        $reader = new AnnotationReader();
        $repository = $reader->getRepository(new ValidTestEntity());

        $expected = '';
        $actual = $repository;
        $this->assertEquals($expected, $actual, 'no repository was declared');
    }

    public function testGetBoost()
    {
        $reader = new AnnotationReader();
        $boost = $reader->getEntityBoost(new ValidTestEntity());

        $this->assertEquals(1, $boost);
    }

    public function testGetBoost_BoostNotNumeric()
    {
        $reader = new AnnotationReader();

        try {
            $boost = $reader->getEntityBoost(new ValidTestEntityWithInvalidBoost());

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
        $reader = new AnnotationReader();
        $boost = $reader->getEntityBoost(new ValidTestEntityFloatBoost());

        $this->assertEquals(1.4, $boost);
    }

    public function testGetBoost_BoostIsNull()
    {
        $reader = new AnnotationReader();
        $boost = $reader->getEntityBoost(new ValidTestEntityNoBoost());

        $this->assertEquals(null, $boost);
    }

    public function testGetCallback_CallbackDefined()
    {
        $reader = new AnnotationReader();
        $callback = $reader->getSynchronizationCallback(new ValidTestEntityFiltered());

        $this->assertEquals('shouldBeIndex', $callback);
    }

    public function testGetCallback_NoCallbackDefined()
    {
        $reader = new AnnotationReader();
        $callback = $reader->getSynchronizationCallback(new ValidTestEntity());

        $this->assertEquals('', $callback);
    }

    /**
     * @test
     */
    public function numericFieldTypeAreSupported()
    {
        $reader = new AnnotationReader();
        $fields = $reader->getFields(new ValidTestEntityNumericFields());

        $this->assertEquals(4, count($fields));

        $expectedFields = array('integer_i', 'double_d', 'float_f', 'long_l');
        $actualFields = array();
        foreach ($fields as $field) {
            $actualFields[] = $field->getNameWithAlias();
        }

        $this->assertEquals($expectedFields, $actualFields);
    }
}

