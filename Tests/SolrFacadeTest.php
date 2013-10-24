<?php

namespace FS\SolrBundle\Tests\Solr;

use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\InvalidTestEntityFiltered;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidTestEntityFiltered;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
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
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\SolrQueryFacade;

/**
 *
 * @group facade
 */
class SolrTest extends \PHPUnit_Framework_TestCase
{

    private $metaFactory = null;
    private $config = null;
    private $commandFactory = null;
    private $eventManager = null;
    private $connectionFactory = null;

    /**
     * @var SolrClientFake
     */
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
        $this->eventManager = $this->getMock('FS\SolrBundle\Event\EventManager', array(), array(), '', false);
        $this->connectionFactory = $this->getMock('FS\SolrBundle\SolrConnectionFactory', array(), array(), '', false);

        $this->solrClientFake = new SolrClientFake();

        $this->config->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($this->solrClientFake));

        $this->connectionFactory->expects($this->any())
            ->method('getDefaultConnection')
            ->will($this->returnValue($this->config));
    }

    private function setupDoctrine($namespace)
    {
        $doctrineConfiguration = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
        $doctrineConfiguration->expects($this->any())
            ->method('getEntityNamespace')
            ->will($this->returnValue($namespace));

        return $doctrineConfiguration;
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

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
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

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
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

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        $solr->getRepository('Tests:EntityWithInvalidRepository');
    }

    public function testAddDocument()
    {
        $this->eventManager->expects($this->once())
            ->method('handle')
            ->with(EventManager::INSERT);

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        $solr->addDocument(new ValidTestEntity());

        $this->assertTrue($this->solrClientFake->isCommited(), 'commit was never called');
    }

    public function testUpdateDocument()
    {
        $this->eventManager->expects($this->once())
            ->method('handle')
            ->with(EventManager::UPDATE);

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        $solr->updateDocument(new ValidTestEntity());

        $this->assertTrue($this->solrClientFake->isCommited(), 'commit was never called');
    }

    public function testRemoveDocument()
    {
        $this->eventManager->expects($this->once())
            ->method('handle')
            ->with(EventManager::DELETE);

        $this->setupMetaFactoryLoadOneCompleteInformation();

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        $solr->removeDocument(new ValidTestEntity());

        $this->assertTrue($this->solrClientFake->isCommited(), 'commit was never called');
    }

    public function testQuery_NoResponseKeyInResponseSet()
    {
        $this->solrClientFake->setResponse(new SolrResponseFake());

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);

        $document = new \SolrInputDocument();
        $document->addField('document_name_s', 'name');
        $query = new FindByDocumentNameQuery($document);

        $entities = $solr->query($query);
        $this->assertEquals(0, count($entities));
    }

    public function testQuery_NoDocumentsFound()
    {
        $responseArray = array('response' => array('docs' => false));
        $this->solrClientFake->setResponse(new SolrResponseFake($responseArray));

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);

        $document = new \SolrInputDocument();
        $document->addField('document_name_s', 'name');
        $query = new FindByDocumentNameQuery($document);

        $entities = $solr->query($query);
        $this->assertEquals(0, count($entities));
    }

    public function testQuery_OneDocumentFound()
    {
        $arrayObj = new SolrDocumentStub(array('title_s' => 'title'));

        $responseArray['response']['docs'][] = $arrayObj;
        $this->solrClientFake->setResponse(new SolrResponseFake($responseArray));

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);

        $document = new \SolrInputDocument();
        $document->addField('document_name_s', 'name');
        $query = new FindByDocumentNameQuery($document);
        $query->setEntity(new ValidTestEntity());

        $entities = $solr->query($query);
        $this->assertEquals(1, count($entities));
    }

    public function testAddEntity_ShouldNotIndexEntity()
    {
        $this->eventManager->expects($this->never())
            ->method('handle');

        $entity = new ValidTestEntityFiltered();

        $information = new MetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        $solr->addDocument($entity);

        $this->assertFalse($this->solrClientFake->isCommited(), 'commit was called');
        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    public function testAddEntity_ShouldIndexEntity()
    {
        $this->eventManager->expects($this->once())
            ->method('handle')
            ->with(EventManager::INSERT);

        $entity = new ValidTestEntityFiltered();
        $entity->shouldIndex = true;

        $information = new MetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        $solr->addDocument($entity);

        $this->assertTrue($this->solrClientFake->isCommited(), 'commit was called');
        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    public function testAddEntity_FilteredEntityWithUnknownCallback()
    {
        $this->eventManager->expects($this->never())
            ->method('handle');

        $information = new MetaInformation();
        $information->setSynchronizationCallback('shouldBeIndex');
        $this->setupMetaFactoryLoadOneCompleteInformation($information);

        $solr = new Solr($this->connectionFactory, $this->commandFactory, $this->eventManager, $this->metaFactory);
        try {
            $solr->addDocument(new InvalidTestEntityFiltered());

            $this->fail('BadMethodCallException expected');
        } catch (\BadMethodCallException $e) {
            $this->assertTrue(true);
        }
    }
}

