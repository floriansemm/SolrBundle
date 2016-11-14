<?php

namespace FS\SolrBundle\Tests;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\InvalidTestEntityFiltered;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityFiltered;
use FS\SolrBundle\Tests\Doctrine\Mapper\EntityCore0;
use FS\SolrBundle\Tests\Doctrine\Mapper\EntityCore1;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\ResultFake;
use FS\SolrBundle\Tests\SolrResponseFake;
use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Event\EventManager;
use FS\SolrBundle\Tests\SolrClientFake;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\EntityWithRepository;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository;
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
        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $query = $solr->createQuery('FSBlogBundle:ValidTestEntity');

        $this->assertTrue($query instanceof SolrQuery);
        $this->assertEquals(4, count($query->getMappedFields()));

    }

    public function testGetRepository_UserdefinedRepository()
    {
        $metaInformation = new MetaInformation();
        $metaInformation->setClassName(get_class(new EntityWithRepository()));
        $metaInformation->setRepository('FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository');

        $this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $actual = $solr->getRepository('Tests:EntityWithRepository');

        $this->assertTrue($actual instanceof ValidEntityRepository);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetRepository_UserdefinedInvalidRepository()
    {
        $metaInformation = new MetaInformation();
        $metaInformation->setClassName(get_class(new EntityWithRepository()));
        $metaInformation->setRepository('FS\SolrBundle\Tests\Doctrine\Annotation\Entities\InvalidEntityRepository');

        $this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->getRepository('Tests:EntityWithInvalidRepository');
    }

    public function testAddDocument()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapOneDocument();

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->addDocument(new ValidTestEntity());
    }

    public function testUpdateDocument()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapOneDocument();

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->updateDocument(new ValidTestEntity());
    }

    public function testDoNotUpdateDocumentIfDocumentCallbackAvoidIt()
    {
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertUpdateQueryWasNotExecuted();

        $information = new MetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $filteredEntity = new ValidTestEntityFiltered();
        $filteredEntity->shouldIndex = false;

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->updateDocument($filteredEntity);
    }

    public function testRemoveDocument()
    {
        $this->assertDeleteQueryWasExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue(new DocumentStub()));

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->removeDocument(new ValidTestEntity());
    }

    public function testClearIndex()
    {
        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array('core0' => array())));

        $this->assertDeleteQueryWasExecuted();

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->clearIndex();
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

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);

        $entities = $solr->query($query);
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

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);

        $entities = $solr->query($query);
        $this->assertEquals(1, count($entities));
    }

    public function testAddEntity_ShouldNotIndexEntity()
    {
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();

        $information = new MetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    public function testAddEntity_ShouldIndexEntity()
    {
        $this->assertUpdateQueryExecuted('index0');

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();
        $entity->shouldIndex = true;

        $information = MetaTestInformationFactory::getMetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $information->setIndex('index0');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $this->mapOneDocument();

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testAddEntity_FilteredEntityWithUnknownCallback()
    {
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $information = MetaTestInformationFactory::getMetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->addDocument(new InvalidTestEntityFiltered());
    }

    /**
     * @test
     */
    public function indexDocumentsGroupedByCore()
    {
        $entity = new ValidTestEntity();
        $entity->setTitle('title field');

        $bufferPlugin = $this->getMock(BufferedAdd::class, array(), array(), '', false);

        $bufferPlugin->expects($this->once())
            ->method('setEndpoint')
            ->with(null);

        $bufferPlugin->expects($this->once())
            ->method('commit');

        $this->solrClientFake->expects($this->once())
            ->method('getPlugin')
            ->with('bufferedadd')
            ->will($this->returnValue($bufferPlugin));

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, new MetaInformationFactory(new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader())), $this->mapper);
        $solr->synchronizeIndex(array($entity));
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

        $bufferPlugin = $this->getMock(BufferedAdd::class, array(), array(), '', false);

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

        $solr = new Solr($this->solrClientFake, $this->eventDispatcher, new MetaInformationFactory(new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader())), $this->mapper);
        $solr->synchronizeIndex(array($entity1, $entity2));
    }
}



