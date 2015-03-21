<?php

namespace FS\SolrBundle\Tests;

use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Tests\DocumentStub;
use FS\SolrBundle\Tests\Util\CommandFactoryStub;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

class MulticoreSolrTest extends AbstractSolrTest
{

    protected function assertUpdateQueryExecuted()
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

    /**
     * @test
     */
    public function addDocumentToAllCores()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');

        $this->mapOneDocument();

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array(
                'core0' => array(),
                'core1' => array()
            )));

        $this->solrClientFake->expects($this->exactly(2))
            ->method('update');

        $metaInformation = MetaTestInformationFactory::getMetaInformation();
        $metaInformation->setIndex('*');
        $this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->addDocument(new ValidTestEntity());
    }

    /**
     * @test
     */
    public function updateDocumentInAllCores()
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapOneDocument();

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array(
                'core0' => array(),
                'core1' => array()
            )));

        $this->solrClientFake->expects($this->exactly(2))
            ->method('update');


        $metaInformation = MetaTestInformationFactory::getMetaInformation();
        $metaInformation->setIndex('*');
        $this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->updateDocument(new ValidTestEntity());
    }

    /**
     * @test
     */
    public function removeDocumentFromAllCores()
    {
        $metaInformation = MetaTestInformationFactory::getMetaInformation();
        $metaInformation->setIndex('*');
        $this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue(new DocumentStub()));

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array(
                'core0' => array(),
                'core1' => array()
            )));

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

        $this->solrClientFake->expects($this->exactly(2))
            ->method('update');

        $solr = new Solr($this->solrClientFake, $this->commandFactory, $this->eventDispatcher, $this->metaFactory, $this->mapper);
        $solr->removeDocument(new ValidTestEntity());
    }
}
 