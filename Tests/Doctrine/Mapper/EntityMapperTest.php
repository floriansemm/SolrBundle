<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 *
 * @group mapper
 */
class EntityMapperTest extends \PHPUnit_Framework_TestCase
{

    private $doctrineHydrator = null;
    private $indexHydrator = null;

    public function setUp()
    {
        $this->doctrineHydrator = $this->getMock('FS\SolrBundle\Doctrine\Hydration\Hydrator');
        $this->indexHydrator = $this->getMock('FS\SolrBundle\Doctrine\Hydration\Hydrator');
    }

    public function testToDocument_EntityMayNotIndexed()
    {
        $mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper($this->doctrineHydrator, $this->indexHydrator);

        $actual = $mapper->toDocument(MetaTestInformationFactory::getMetaInformation());
        $this->assertNull($actual);
    }

    public function testToDocument_DocumentIsUpdated()
    {
        $mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper($this->doctrineHydrator, $this->indexHydrator);
        $mapper->setMappingCommand(new MapAllFieldsCommand(new AnnotationReader()));

        $actual = $mapper->toDocument(MetaTestInformationFactory::getMetaInformation());
        $this->assertTrue($actual instanceof Document);

        $this->assertNotNull($actual->id);
    }

    public function testToEntity_WithDocumentStub()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 1,
            'title_t' => 'foo'
        ));

        $targetEntity = new ValidTestEntity();

        $mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper($this->doctrineHydrator, $this->indexHydrator);
        $entity = $mapper->toEntity($obj, $targetEntity);

        $this->assertTrue($entity instanceof $targetEntity);

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());
    }

    public function testToEntity_ConcreteDocumentClass()
    {
        $obj = new Document(array(
            'id' => 1,
            'title_t' => 'foo'
        ));

        $targetEntity = new ValidTestEntity();

        $mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper($this->doctrineHydrator, $this->indexHydrator);
        $entity = $mapper->toEntity($obj, $targetEntity);

        $this->assertTrue($entity instanceof $targetEntity);

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());
    }

    public function ToCamelCase()
    {
        $mapper = new EntityMapper($this->doctrineHydrator, $this->indexHydrator);

        $meta = new \ReflectionClass($mapper);
        $method = $meta->getMethod('toCamelCase');
        $method->setAccessible(true);
        $calmelCased = $method->invoke($mapper, 'test_underline');
        $this->assertEquals('testUnderline', $calmelCased);
    }
}

