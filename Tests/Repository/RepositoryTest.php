<?php

namespace FS\SolrBundle\Tests\Solr\Repository;

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\EntityMapperInterface;
use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use FS\SolrBundle\Tests\SolrClientFake;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use FS\SolrBundle\Tests\Util\CommandFactoryStub;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Update\Query\Document\Document;
use FS\SolrBundle\Repository\Repository;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

/**
 * @group repository
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{

    public function testFind_DocumentIsKnown()
    {
        $document = new Document();
        $document->addField('id', 2);
        $document->addField('document_name_s', 'post');

        $metaFactory = $this->getMock('FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory', array(), array(), '', false);
        $metaFactory->expects($this->once())
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $mapper = $this->getMock(EntityMapperInterface::class);
        $mapper->expects($this->once())
            ->method('setHydrationMode')
            ->with(HydrationModes::HYDRATE_DOCTRINE);

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $mapper;
        $solr->metaFactory = $metaFactory;
        $solr->response = array($entity);

        $repo = new Repository($solr, $entity);
        $actual = $repo->find(2);

        $this->assertTrue($actual instanceof ValidTestEntity, 'find return no entity');

        $this->assertTrue($solr->query instanceof FindByIdentifierQuery);
        $this->assertEquals('*:*', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_2', $solr->query->getFilterQuery('id')->getQuery());
    }

    public function testFindAll()
    {
        $metaFactory = $this->getMock('FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory', array(), array(), '', false);
        $metaFactory->expects($this->once())
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $mapper = $this->getMock(EntityMapperInterface::class);
        $mapper->expects($this->once())
            ->method('setHydrationMode')
            ->with(HydrationModes::HYDRATE_DOCTRINE);

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $mapper;
        $solr->metaFactory = $metaFactory;
        $solr->response = array($entity);

        $repo = new Repository($solr, $entity);
        $actual = $repo->findAll();

        $this->assertTrue(is_array($actual));

        $this->assertTrue($solr->query instanceof FindByDocumentNameQuery);
        $this->assertEquals('*:*', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_*', $solr->query->getFilterQuery('id')->getQuery());
    }

    public function testFindBy()
    {
        $fields = array(
            'title' => 'foo',
            'text' => 'bar'
        );

        $metaFactory = $this->getMock('FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory', array(), array(), '', false);
        $metaFactory->expects($this->exactly(2))
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $mapper = $this->getMock(EntityMapperInterface::class);
        $mapper->expects($this->once())
            ->method('setHydrationMode')
            ->with(HydrationModes::HYDRATE_DOCTRINE);

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $mapper;
        $solr->metaFactory = $metaFactory;
        $solr->response = array($entity);

        $repo = new Repository($solr, $entity);

        $found = $repo->findBy($fields);

        $this->assertTrue(is_array($found));

        $this->assertTrue($solr->query instanceof AbstractQuery);
        $this->assertEquals('title_s:foo AND text_t:bar', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_*', $solr->query->getFilterQuery('id')->getQuery());
    }

    public function testFindOneBy()
    {
        $fields = array(
            'title' => 'foo',
            'text' => 'bar'
        );

        $metaFactory = $this->getMock('FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory', array(), array(), '', false);
        $metaFactory->expects($this->exactly(2))
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $mapper = $this->getMock(EntityMapperInterface::class);
        $mapper->expects($this->once())
            ->method('setHydrationMode')
            ->with(HydrationModes::HYDRATE_DOCTRINE);

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $mapper;
        $solr->metaFactory = $metaFactory;
        $solr->response = array($entity);

        $repo = new Repository($solr, $entity);

        $found = $repo->findOneBy($fields);

        $this->assertEquals($entity, $found);

        $this->assertTrue($solr->query instanceof AbstractQuery);
        $this->assertEquals('title_s:foo AND text_t:bar', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_*', $solr->query->getFilterQuery('id')->getQuery());
    }

}

