<?php

namespace FS\SolrBundle\Tests\Solr\Repository;

use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use FS\SolrBundle\Tests\Util\CommandFactoryStub;
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

        $metaFactory = $this->getMock(
            'FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory',
            array(),
            array(),
            '',
            false
        );
        $metaFactory->expects($this->once())
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $mapper = $this->getMock('FS\SolrBundle\Doctrine\Mapper\EntityMapper', array(), array(), '', false);
        $mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue($document));

        $solr = $this->getMock('FS\SolrBundle\Solr', array(), array(), '', false);
        $solr->expects($this->exactly(2))
            ->method('getMapper')
            ->will($this->returnValue($mapper));

        $solr->expects($this->once())
            ->method('getCommandFactory')
            ->will($this->returnValue(CommandFactoryStub::getFactoryWithAllMappingCommand()));

        $solr->expects($this->once())
            ->method('getMetaFactory')
            ->will($this->returnValue($metaFactory));

        $entity = new ValidTestEntity();
        $solr->expects($this->once())
            ->method('query')
            ->will($this->returnValue(array($entity)));

        $repo = new Repository($solr, $entity);
        $actual = $repo->find(2);

        $this->assertTrue($actual instanceof ValidTestEntity, 'find return no entity');
    }

    public function testFindAll()
    {
        $document = new Document();
        $document->addField('id', 2);
        $document->addField('document_name_s', 'post');

        $metaFactory = $this->getMock(
            'FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory',
            array(),
            array(),
            '',
            false
        );
        $metaFactory->expects($this->once())
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $mapper = $this->getMock('FS\SolrBundle\Doctrine\Mapper\EntityMapper', array(), array(), '', false);
        $mapper->expects($this->once())
            ->method('toDocument')
            ->will($this->returnValue($document));

        $solr = $this->getMock('FS\SolrBundle\Solr', array(), array(), '', false);
        $solr->expects($this->exactly(2))
            ->method('getMapper')
            ->will($this->returnValue($mapper));

        $solr->expects($this->once())
            ->method('getCommandFactory')
            ->will($this->returnValue(CommandFactoryStub::getFactoryWithAllMappingCommand()));

        $solr->expects($this->once())
            ->method('getMetaFactory')
            ->will($this->returnValue($metaFactory));

        $entity = new ValidTestEntity();
        $solr->expects($this->once())
            ->method('query')
            ->will($this->returnValue(array($entity)));

        $repo = new Repository($solr, $entity);
        $actual = $repo->findAll();

        $this->assertTrue(is_array($actual));

        $this->assertNull($document->id, 'id was removed');
    }

    public function testFindBy()
    {
        $fields = array(
            'title' => 'foo',
            'text' => 'bar'
        );

        $metaFactory = $this->getMock(
            'FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory',
            array(),
            array(),
            '',
            false
        );
        $metaFactory->expects($this->once())
            ->method('loadInformation')
            ->will($this->returnValue(MetaTestInformationFactory::getMetaInformation()));

        $solr = $this->getMock('FS\SolrBundle\Solr', array(), array(), '', false);
        $query = $this->getMock('FS\SolrBundle\Query\SolrQuery', array(), array(), '', false);
        $query->expects($this->exactly(3))
            ->method('addSearchTerm');

        $solr->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $solr->expects($this->once())
            ->method('query')
            ->with($query)
            ->will($this->returnValue(array()));

        $solr->expects($this->once())
            ->method('getMetaFactory')
            ->will($this->returnValue($metaFactory));

        $entity = new ValidTestEntity();
        $repo = new Repository($solr, $entity);

        $found = $repo->findBy($fields);

        $this->assertTrue(is_array($found));
    }

}

