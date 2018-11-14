<?php

namespace FS\SolrBundle\Tests\Solr\Repository;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Mapper\EntityMapper;
use FS\SolrBundle\Doctrine\Mapper\EntityMapperInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Query\AbstractQuery;
use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use FS\SolrBundle\Tests\Fixtures\EntityNestedProperty;
use FS\SolrBundle\Tests\SolrClientFake;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use FS\SolrBundle\Tests\Util\CommandFactoryStub;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Update\Query\Document\Document;
use FS\SolrBundle\Repository\Repository;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;

/**
 * @group repository
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MetaTestInformationFactory
     */
    private $metaInformationFactory;

    private $mapper;

    protected function setUp()
    {
        $this->metaInformationFactory = new MetaInformationFactory($reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader()));
        $this->mapper = $this->createMock(EntityMapperInterface::class);
        $this->mapper->expects($this->once())
            ->method('setHydrationMode')
            ->with(HydrationModes::HYDRATE_DOCTRINE);
    }

    public function testFind_DocumentIsKnown()
    {
        $document = new Document();
        $document->addField('id', 2);
        $document->addField('document_name_s', 'post');

        $metaInformation = MetaTestInformationFactory::getMetaInformation();

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $this->mapper;
        $solr->response = array($entity);

        $repo = new Repository($solr, $metaInformation);
        $actual = $repo->find(2);

        $this->assertTrue($actual instanceof ValidTestEntity, 'find return no entity');

        $this->assertTrue($solr->query instanceof FindByIdentifierQuery);
        $this->assertEquals('*:*', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_2', $solr->query->getFilterQuery('id')->getQuery());
    }

    public function testFindAll()
    {
        $metaInformation = MetaTestInformationFactory::getMetaInformation();

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $this->mapper;
        $solr->response = array($entity);

        $repo = new Repository($solr, $metaInformation);
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

        $metaInformation = MetaTestInformationFactory::getMetaInformation();

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $this->mapper;
        $solr->response = array($entity);
        $solr->metaFactory = $this->metaInformationFactory;

        $repo = new Repository($solr, $metaInformation);

        $found = $repo->findBy($fields);

        $this->assertTrue(is_array($found));

        $this->assertTrue($solr->query instanceof AbstractQuery);
        $this->assertEquals('title:foo AND text_t:bar', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_*', $solr->query->getFilterQuery('id')->getQuery());
    }

    public function testFindOneBy()
    {
        $fields = array(
            'title' => 'foo',
            'text' => 'bar'
        );

        $metaInformation = MetaTestInformationFactory::getMetaInformation();

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $this->mapper;
        $solr->response = array($entity);
        $solr->metaFactory = $this->metaInformationFactory;

        $repo = new Repository($solr, $metaInformation);

        $found = $repo->findOneBy($fields);

        $this->assertEquals($entity, $found);

        $this->assertTrue($solr->query instanceof AbstractQuery);
        $this->assertEquals('title:foo AND text_t:bar', $solr->query->getQuery());
        $this->assertEquals('id:validtestentity_*', $solr->query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function findOneByNestedField()
    {
        $metaInformation = $this->metaInformationFactory->loadInformation(EntityNestedProperty::class);

        $entity = new ValidTestEntity();

        $solr = new SolrClientFake();
        $solr->mapper = $this->mapper;
        $solr->response = array($entity);
        $solr->metaFactory = $this->metaInformationFactory;

        $repo = new Repository($solr, $metaInformation);

        $found = $repo->findOneBy([
            'collection.name' => '*test*test*'
        ]);

        $this->assertEquals('{!parent which="id:entitynestedproperty_*"}name_t:*test*test*', $solr->query->getQuery());
    }
}

