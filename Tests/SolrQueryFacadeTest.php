<?php

namespace FS\SolrBundle\Tests\Solr;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;
use FS\SolrBundle\SolrQuery;
use FS\SolrBundle\SolrQueryFacade;

/**
 *  test case.
 */
class SolrQueryFacadeTest extends \PHPUnit_Framework_TestCase {

	private $registry = null;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
		
		$this->registry = $this->getMock('Symfony\Bundle\DoctrineBundle\Registry', array(), array(), '', false);
	}
	
	private function createConfiguration($namespace) {
		$configuration = $this->getMock('Doctrine\ORM\Configuration', array(), array(),'',false);
		$configuration->expects($this->any())
		->method('getEntityNamespace')
		->will($this->returnValue($namespace));
		
		$em = $this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false);
		$em->expects($this->any())
		->method('getConfiguration')
		->will($this->returnValue($configuration));
		
		$this->registry->expects($this->any())
		->method('getEntityManager')
		->will($this->returnValue($em));

		return $this->registry;
	}

	private function createCommandFactory() {
		$commandFactory = new CommandFactory();
		$commandFactory->add(new MapAllFieldsCommand(new AnnotationReader()), 'all');
		
		return $commandFactory;
	}
	
	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Unknown entity InvalidBundle:InvalidEntity
	 */
	public function testCreateQuery_EntityIsUnknown() {
		$registry = $this->createConfiguration('Invalidnamespace');
		$commandFactory = $this->createCommandFactory();
		
		$queryFacade = new SolrQueryFacade($registry, $commandFactory);
		$queryFacade->createQuery('InvalidBundle:InvalidEntity');
	}
	
	public function testCreateQuery_EntityIsDocument() {
		$registry = $this->createConfiguration('FS\SolrBundle\Tests\Doctrine\Mapper');
		$commandFactory = $this->createCommandFactory();
		
		$queryFacade = new SolrQueryFacade($registry, $commandFactory);
		$query = $queryFacade->createQuery('FSBlogBundle:ValidTestEntity');

		$this->assertTrue($query instanceof SolrQuery);
		$this->assertEquals(4, count($query->getMappedFields()));
	}
	
}

