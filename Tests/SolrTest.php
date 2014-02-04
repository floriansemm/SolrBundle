<?php

namespace FS\SolrBundle\Tests\Solr;

use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\InvalidTestEntityFiltered;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityFiltered;
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
use Solarium\QueryType\Update\Query\Document\Document;

/**
 *
 * @group facade
 */
class SolrTest extends \PHPUnit_Framework_TestCase
{

    private $metaFactory = null;
    private $config = null;
    private $commandFactory = null;
    private $eventDispatcher = null;

    private $solrClientFake = null;

    public function setUp()
    {
        $this->metaFactory = $metaFactory = $this->getMock(
            'FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory',
            array(),
            array(),
            '',
            false
        );
        $this->config = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
        $this->commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher', array(), array(), '', false);

        $this->solrClientFake = $this->getMock('Solarium\Client', array(), array(), '', false);
    }

    private function assertUpdateQueryExecuted()
    {
        $updateQuery = $this->getMock('Solarium\QueryType\Update\Query\Query', array(), array(), '', false);
        $updateQuery->expects($this->once())
            ->method('addDocument');

        $updateQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
            ->will($this->returnValue($updateQuery));
    }

    private function assertUpdateQueryWasNotExecuted()
    {
        $updateQuery = $this->getMock('Solarium\QueryType\Update\Query\Query', array(), array(), '', false);
        $updateQuery->expects($this->never())
            ->method('addDocument');

        $updateQuery->expects($this->never())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->never())
            ->method('createUpdate');
    }

    private function assertDeleteQueryWasExecuted()
    {
        $deleteQuery = $this->getMock('Solarium\QueryType\Update\Query\Query', array(), array(), '', false);
        $deleteQuery->expects($this->once())
            ->method('addDeleteQuery')
            ->with($this->isType('string'));

        $deleteQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
            ->will($this->returnValue($deleteQuery));

        $this->solrClientFake
            ->expects($this->once())
            ->method('update')
            ->with($deleteQuery);
    }

    private function setupMetaFactoryLoadOneCompleteInformation($metaInformation = null)
    {
        if (null === $metaInformation) {
            $metaInformation = MetaTestInformationFactory::getMetaInformation();
        }

        $this->metaFactory->expects($this->once())
            ->method('loadInformation')
            ->will($this->returnValue($metaInformation));
    }

    public function testCreateQuery_ValidEntity()
    {
        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $query = $solr->createQuery('FSBlogBundle:ValidTestEntity');

        $this->assertTrue($query instanceof SolrQuery);
        $this->assertEquals(4, count($query->getEntityMetaInformation()->getFieldMapping()));

    }

    public function testGetRepository_UserdefinedRepository()
    {
        $metaInformation = new MetaInformation();
        $metaInformation->setClassName(get_class(new EntityWithRepository()));
        $metaInformation->setRepository('FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository');

        $this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
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

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->getRepository('Tests:EntityWithInvalidRepository');
    }

    public function testAddDocument()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->addDocument(new ValidTestEntity());
    }

    public function testUpdateDocument()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->updateDocument(new ValidTestEntity());
    }

    public function testRemoveDocument()
    {
        $this->assertDeleteQueryWasExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->removeDocument(new ValidTestEntity());
    }

    public function testClearIndex()
    {
        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->assertDeleteQueryWasExecuted();

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->clearIndex();
    }

    private function assertQueryWasExecuted($data = array())
    {
        $selectQuery = $this->getMock('Solarium\QueryType\Select\Query\Query', array(), array(), '', false);
        $selectQuery->expects($this->once())
            ->method('setQuery');

        $queryResult = new ResultFake($data);

        $this->solrClientFake
            ->expects($this->once())
            ->method('createSelect')
            ->will($this->returnValue($selectQuery));

        $this->solrClientFake
            ->expects($this->once())
            ->method('select')
            ->with($selectQuery)
            ->will($this->returnValue($queryResult));
    }

    public function testQuery_NoResponseKeyInResponseSet()
    {
        $this->assertQueryWasExecuted();

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);

        $document = new Document();
        $document->addField('document_name_s', 'name');
        $query = new FindByDocumentNameQuery();
        $query->setDocument($document);

        $entities = $solr->query($query);
        $this->assertEquals(0, count($entities));
    }

    public function testQuery_OneDocumentFound()
    {
        $arrayObj = new SolrDocumentStub(array('title_s' => 'title'));

        $this->assertQueryWasExecuted(array($arrayObj));

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);

        $document = new Document();
        $document->addField('document_name_s', 'name');
        $query = new FindByDocumentNameQuery();
        $query->setDocument($document);
        $query->setEntityMetaInformation(new ValidTestEntity());

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

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    public function testAddEntity_ShouldIndexEntity()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();
        $entity->shouldIndex = true;

        $information = MetaTestInformationFactory::getMetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        $solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    public function testAddEntity_FilteredEntityWithUnknownCallback()
    {
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $information = MetaTestInformationFactory::getMetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory);
        try {
            $solr->addDocument(new InvalidTestEntityFiltered());

            $this->fail('BadMethodCallException expected');
        } catch (\BadMethodCallException $e) {
            $this->assertTrue(true);
        }
    }
}

