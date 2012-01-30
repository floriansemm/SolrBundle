<?php

namespace FS\SolrBundle\Tests\Solr\Repository;

use FS\SolrBundle\Tests\Util\CommandFactoryStub;

use FS\SolrBundle\Query\SolrQuery;

use FS\SolrBundle\Repository\Repository;

use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

use FS\SolrBundle\SolrFacade;

/**
 *  test case.
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase {
	
	public function testFind_DocumentIsKnown() {
		$document = new \SolrInputDocument();
		$document->addField('id', 2);
		$document->addField('document_name_s', 'post');
		
		$mapper = $this->getMock('FS\SolrBundle\Doctrine\Mapper\EntityMapper');
		$mapper->expects($this->once())
			   ->method('toDocument')
			   ->will($this->returnValue($document));	
		
		$solr = $this->getMock('FS\SolrBundle\SolrFacade', array(), array(), '', false);
		$solr->expects($this->once())
			 ->method('getMapper')
			 ->will($this->returnValue($mapper));
		
		$solr->expects($this->once())
		->method('getCommandFactory')
		->will($this->returnValue(CommandFactoryStub::getFactoryWithAllMappingCommand()));		
		
		$entity = new ValidTestEntity();
		$solr->expects($this->once())
			 ->method('query')
			 ->will($this->returnValue(array($entity)));
		
		$repo = new Repository($solr, $entity);
		$actual = $repo->find(2);
		
		$this->assertTrue($actual instanceof ValidTestEntity, 'find return no entity');
	}
	
	public function testFindAll() {
		$document = new \SolrInputDocument();
		$document->addField('id', 2);
		$document->addField('document_name_s', 'post');
		
		$mapper = $this->getMock('FS\SolrBundle\Doctrine\Mapper\EntityMapper');
		$mapper->expects($this->once())
			   ->method('toDocument')
			   ->will($this->returnValue($document));	
		
		$solr = $this->getMock('FS\SolrBundle\SolrFacade', array(), array(), '', false);
		$solr->expects($this->once())
			 ->method('getMapper')
			 ->will($this->returnValue($mapper));
		
		$solr->expects($this->once())
			 ->method('getCommandFactory')
			 ->will($this->returnValue(CommandFactoryStub::getFactoryWithAllMappingCommand()));
		
		$entity = new ValidTestEntity();
		$solr->expects($this->once())
			 ->method('query')
			 ->will($this->returnValue(array($entity)));
		
		$repo = new Repository($solr, $entity);
		$actual = $repo->findAll();
		
		$this->assertTrue(is_array($actual));
		$this->assertFalse($document->fieldExists('id'), 'id was removed');
	}
	
	public function testFindBy() {
		$fields = array(
			'title'=>'foo', 
			'text'=>'bar'
		);

		$solr = $this->getMock('FS\SolrBundle\SolrFacade', array(), array(), '', false);
		$query = $this->getMock('FS\SolrBundle\Query\SolrQuery', array(), array(), '', false);
		$query->expects($this->exactly(2))
			  ->method('addSearchTerm');
		
		$solr->expects($this->once())
			 ->method('createQuery')
			 ->will($this->returnValue($query));
		
		$solr->expects($this->once())
			 ->method('query')
			 ->with($query)
			 ->will($this->returnValue(array()));
		
		$entity = new ValidTestEntity();		
		$repo = new Repository($solr, $entity);

		$found = $repo->findBy($fields);

		$this->assertTrue(is_array($found));
	}
	
}

