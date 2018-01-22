<?php

namespace FS\SolrBundle\Tests;

use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\SolrException;
use FS\SolrBundle\Tests\Fixtures\EntityWithInvalidRepository;
use FS\SolrBundle\Tests\Fixtures\InvalidTestEntityFiltered;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityFiltered;
use FS\SolrBundle\Tests\Fixtures\EntityCore0;
use FS\SolrBundle\Tests\Fixtures\EntityCore1;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Event\EventManager;
use FS\SolrBundle\Tests\SolrClientFake;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Tests\Fixtures\EntityWithRepository;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Fixtures\ValidEntityRepository;
use FS\SolrBundle\Tests\Util\CommandFactoryStub;
use FS\SolrBundle\Query\SolrQuery;
use Solarium\Plugin\BufferedAdd\BufferedAdd;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 *
 * @group facade
 */
class SolrTest extends AbstractSolrTest
{

    public function testCreateQuery_ValidEntity()
    {
        $query = $this->solr->createQuery(ValidTestEntity::class);

        $this->assertTrue($query instanceof SolrQuery);
        $this->assertEquals(6, count($query->getMappedFields()));

    }

    public function testGetRepository_UserdefinedRepository()
    {
        $actual = $this->solr->getRepository(EntityWithRepository::class);

        $this->assertTrue($actual instanceof ValidEntityRepository);
    }

    /**
     * @expectedException \FS\SolrBundle\SolrException
     * @expectedExceptionMessage FS\SolrBundle\Tests\Fixtures\InvalidEntityRepository must extends the FS\SolrBundle\Repository\Repository
     */
    public function testGetRepository_UserdefinedInvalidRepository()
    {
        $this->solr->getRepository(EntityWithInvalidRepository::class);
    }

    public function testAddDocument()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue(new DocumentStub()));

        $entity = new ValidTestEntity();
        $entity->setTitle('title');

        $this->solr->addDocument($entity);
    }

    public function testUpdateDocument()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue(new DocumentStub()));

        $entity = new ValidTestEntity();
        $entity->setTitle('title');

        $this->solr->updateDocument($entity);
    }

    public function testDoNotUpdateDocumentIfDocumentCallbackAvoidIt()
    {
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertUpdateQueryWasNotExecuted();

        $filteredEntity = new ValidTestEntityFiltered();
        $filteredEntity->shouldIndex = false;

        $this->solr->updateDocument($filteredEntity);
    }

    public function testRemoveDocument()
    {
        $this->assertDeleteQueryWasExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue(new DocumentStub()));

        $this->solr->removeDocument(new ValidTestEntity());
    }

    public function testClearIndex()
    {
        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array('core0' => array())));

        $this->assertDeleteQueryWasExecuted();

        $this->solr->clearIndex();
    }

    public function testQuery_NoResponseKeyInResponseSet()
    {
        $document = new Document();
        $document->addField('document_name_s', 'name');

        $query = new FindByDocumentNameQuery();
        $query->setDocumentName('name');
        $query->setDocument($document);
        $query->setIndex('index0');

        $this->assertQueryWasExecuted(array(), 'index0');

        $entities = $this->solr->query($query);
        $this->assertEquals(0, count($entities));
    }

    public function testQuery_OneDocumentFound()
    {
        $arrayObj = new SolrDocumentStub(array('title_s' => 'title'));

        $document = new Document();
        $document->addField('document_name_s', 'name');

        $query = new FindByDocumentNameQuery();
        $query->setDocumentName('name');
        $query->setDocument($document);
        $query->setEntity(new ValidTestEntity());
        $query->setIndex('index0');

        $this->assertQueryWasExecuted(array($arrayObj), 'index0');

        $entities = $this->solr->query($query);
        $this->assertEquals(1, count($entities));
    }

    public function testAddEntity_ShouldNotIndexEntity()
    {
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();

        $this->solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    public function testAddEntity_ShouldIndexEntity()
    {
        $this->assertUpdateQueryExecuted('index0');

        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();
        $entity->shouldIndex = true;

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue(new DocumentStub()));

        $this->solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    /**
     * @expectedException \FS\SolrBundle\SolrException
     */
    public function testAddEntity_FilteredEntityWithUnknownCallback()
    {
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->solr->addDocument(new InvalidTestEntityFiltered());
    }

    /**
     * @test
     */
    public function indexDocumentsGroupedByCore()
    {
        $entity = new ValidTestEntity();
        $entity->setTitle('title field');

        $bufferPlugin = $this->createMock(BufferedAdd::class);

        $bufferPlugin->expects($this->once())
            ->method('setEndpoint')
            ->with(null);

        $bufferPlugin->expects($this->once())
            ->method('commit');

        $this->solrClientFake->expects($this->once())
            ->method('getPlugin')
            ->with('bufferedadd')
            ->will($this->returnValue($bufferPlugin));

        $this->solr->synchronizeIndex(array($entity));
    }

    /**
     * @test
     */
    public function setCoreToNullIfNoIndexExists()
    {
        $entity1 = new EntityCore0();
        $entity1->setText('a text');

        $entity2 = new EntityCore1();
        $entity2->setText('a text');

        $bufferPlugin = $this->createMock(BufferedAdd::class);

        $bufferPlugin->expects($this->at(2))
            ->method('setEndpoint')
            ->with('core0');

        $bufferPlugin->expects($this->at(5))
            ->method('setEndpoint')
            ->with('core1');

        $bufferPlugin->expects($this->exactly(2))
            ->method('commit');

        $this->solrClientFake->expects($this->once())
            ->method('getPlugin')
            ->with('bufferedadd')
            ->will($this->returnValue($bufferPlugin));

        $this->solr->synchronizeIndex(array($entity1, $entity2));
    }

    /**
     * @test
     */
    public function createQueryBuilder()
    {
        $queryBuilder = $this->solr->createQueryBuilder(ValidTestEntity::class);

        $this->assertTrue($queryBuilder instanceof QueryBuilderInterface);
    }
}



