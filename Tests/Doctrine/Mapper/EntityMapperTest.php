<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Hydration\HydratorInterface;
use FS\SolrBundle\Doctrine\Hydration\IndexHydrator;
use FS\SolrBundle\Doctrine\Hydration\NoDatabaseValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Tests\Fixtures\EntityWithCustomId;
use FS\SolrBundle\Tests\Fixtures\PartialUpdateEntity;
use FS\SolrBundle\Tests\Fixtures\ValidOdmTestDocument;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithCollection;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithRelation;
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

    public function testToDocument_DocumentIsUpdated()
    {

        $actual = $this->mapper->toDocument(MetaTestInformationFactory::getMetaInformation());
        $this->assertTrue($actual instanceof Document);

        $this->assertNotNull($actual->id);
    }

    /**
     * @test
     */
    public function setFieldModifier()
    {
        $entity = new PartialUpdateEntity();
        $entity->setId(uniqid());

        $actualDocument = $this->mapper->toDocument($this->metaInformationFactory->loadInformation($entity));

        $this->assertEquals('set', $actualDocument->getFieldModifier('subtitle'));
        $this->assertNull($actualDocument->getFieldModifier('title'));
    }

    public function testToEntity_WithDocumentStub_HydrateIndexOnly()
    {
        $targetEntity = new ValidTestEntity();

        $this->indexHydrator->expects($this->once())
            ->method('hydrate')
            ->will($this->returnValue($targetEntity));

        $this->doctrineHydrator->expects($this->never())
            ->method('hydrate');

        $this->mapper->setHydrationMode(HydrationModes::HYDRATE_INDEX);
        $entity = $this->mapper->toEntity(new SolrDocumentStub(), $targetEntity);

        $this->assertTrue($entity instanceof $targetEntity);
    }

    public function testToEntity_ConcreteDocumentClass_WithDoctrineOrm()
    {
        $targetEntity = new ValidTestEntity();
        $targetEntity->setField('a value');

        $this->indexHydrator = new IndexHydrator(new NoDatabaseValueHydrator());

        $this->doctrineHydrator = new DoctrineHydrator(new ValueHydrator());
        $this->doctrineHydrator->setOrmManager($this->setupOrmManager($targetEntity, 1));

        $this->mapper = new EntityMapper($this->doctrineHydrator, $this->indexHydrator, $this->metaInformationFactory);
        $this->mapper->setHydrationMode(HydrationModes::HYDRATE_DOCTRINE);
        $entity = $this->mapper->toEntity(new Document(array('id' => 'document_1', 'title' => 'value from index')), $targetEntity);

        $this->assertTrue($entity instanceof $targetEntity);

        $this->assertEquals('a value', $entity->getField());
        $this->assertEquals('value from index', $entity->getTitle());
    }

    public function testToEntity_ConcreteDocumentClass_WithDoctrineOdm()
    {
        $targetEntity = new ValidOdmTestDocument();
        $targetEntity->setField('a value');

        $this->indexHydrator = new IndexHydrator(new NoDatabaseValueHydrator());

        $this->doctrineHydrator = new DoctrineHydrator(new ValueHydrator());
        $this->doctrineHydrator->setOdmManager($this->setupOdmManager($targetEntity, 1));

        $this->mapper = new EntityMapper($this->doctrineHydrator, $this->indexHydrator, $this->metaInformationFactory);
        $this->mapper->setHydrationMode(HydrationModes::HYDRATE_DOCTRINE);
        $entity = $this->mapper->toEntity(new Document(array('id' => 'document_1', 'title' => 'value from index')), $targetEntity);

        $this->assertTrue($entity instanceof $targetEntity);

        $this->assertEquals('a value', $entity->getField());
        $this->assertEquals('value from index', $entity->getTitle());
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage Please check your config. Given entity is not a Doctrine entity, but Doctrine hydration is enabled. Use setHydrationMode(HydrationModes::HYDRATE_DOCTRINE) to fix this.
     */
    public function throwExceptionIfGivenObjectIsNotEntityButItShould()
    {
        $targetEntity = new PlainObject();

        $this->indexHydrator = new IndexHydrator(new NoDatabaseValueHydrator());

        $this->doctrineHydrator = new DoctrineHydrator(new ValueHydrator());

        $this->mapper->toEntity(new Document(array('id' => 'document_1', 'title' => 'value from index')), $targetEntity);
    }

    /**
     * @test
     */
    public function generatedDocumentIdIfRequired()
    {
        $entity = new EntityWithCustomId();

        $this->indexHydrator = new IndexHydrator(new NoDatabaseValueHydrator());

        $this->doctrineHydrator = new DoctrineHydrator(new ValueHydrator());

        $metainformation = $this->metaInformationFactory->loadInformation($entity);

        $this->mapper = new EntityMapper($this->doctrineHydrator, $this->indexHydrator, $this->metaInformationFactory);
        $document = $this->mapper->toDocument($metainformation);

        $fields = $document->getFields();
        $this->assertArrayHasKey('id', $fields);
        $this->assertNotNull($fields['id']);
    }

    public function testMapEntity_DocumentShouldContainThreeFields()
    {
        $document = $this->mapper->toDocument(MetaTestInformationFactory::getMetaInformation());

        $this->assertTrue($document instanceof Document, 'is a Document');
        $this->assertEquals(4, $document->count(), 'three fields are mapped');

        $this->assertEquals(1, $document->getBoost(), 'document boost should be 1');

        $boostTitleField = $document->getFieldBoost('title');
        $this->assertEquals(1.8, $boostTitleField, 'boost value of field title_s should be 1.8');

        $this->assertArrayHasKey('id', $document);
        $this->assertArrayHasKey('title', $document);
        $this->assertArrayHasKey('text_t', $document);
        $this->assertArrayHasKey('created_at_dt', $document);
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage No entity id set for "FS\SolrBundle\Tests\Fixtures\ValidTestEntity"
     */
    public function throwExceptionIfEntityHasNoId()
    {
        $entity = new ValidTestEntity;

        $metaInformation = $this->metaInformationFactory->loadInformation($entity);

        $this->mapper->toDocument($metaInformation);
    }

    private function setupOrmManager($entity, $expectedEntityId)
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($expectedEntityId)
            ->will($this->returnValue($entity));

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));

        return $managerRegistry;
    }

    private function setupOdmManager($entity, $expectedEntityId)
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($expectedEntityId)
            ->will($this->returnValue($entity));

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));

        return $managerRegistry;
    }
}

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document(boost="1")
 */
class PlainObject
{
    /**
     * @var int
     *
     * @Solr\Id
     */
    private $id;
}