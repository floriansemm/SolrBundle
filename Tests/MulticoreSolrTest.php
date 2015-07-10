<?php

namespace FS\SolrBundle\Tests;

use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Tests\DocumentStub;
use FS\SolrBundle\Tests\Util\CommandFactoryStub;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

class MulticoreSolrTest extends AbstractSolrTest
{
    /**
     * parent method assert that Client::update is called only once. We have to verify that all cores are called.
     *
     * @param string $index
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function assertUpdateQueryExecuted($index = null)
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

        return $updateQuery;
    }

    /**
     * @test
     */
    public function addDocumentToAllCores()
    {
        $updateQuery = $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');

        $this->mapOneDocument();

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array(
                'core0' => array(),
                'core1' => array()
            )));

        $this->solrClientFake->expects($this->at(2))
            ->method('update')
            ->with($updateQuery, 'core0');

        $this->solrClientFake->expects($this->at(3))
            ->method('update')
            ->with($updateQuery, 'core1');

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
        $updateQuery = $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapOneDocument();

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->will($this->returnValue(array(
                'core0' => array(),
                'core1' => array()
            )));

        $this->solrClientFake->expects($this->at(2))
            ->method('update')
            ->with($updateQuery, 'core0');

        $this->solrClientFake->expects($this->at(3))
            ->method('update')
            ->with($updateQuery, 'core1');


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
 