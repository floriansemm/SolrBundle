<?php

namespace FS\SolrBundle\Tests\Solr;

use FS\SolrBundle\SolrFacade;

use FS\SolrBundle\Tests\Util\CommandFactoryStub;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\Mapping\CommandFactory;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\SolrQueryFacade;

/**
 *  test case.
 */
class SolrFacadeTest extends \PHPUnit_Framework_TestCase {

	private function setupDoctrine($namespace) {
		$doctrineConfiguration = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
		$doctrineConfiguration->expects($this->any())
							  ->method('getEntityNamespace')
							  ->will($this->returnValue($namespace));

		return $doctrineConfiguration;
	}
	
	public function testCreateQuery_ValidEntity() {
		$configMock = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
		$commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
		$logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface', array(), array(), '', false);
		
		$doctrineConfiguration = $this->setupDoctrine('FS\SolrBundle\Tests\Doctrine\Mapper');
		
		$solr = new SolrFacade($configMock, $commandFactory, $logger);
		$solr->setDoctrineConfiguration($doctrineConfiguration);
		
		$query = $solr->createQuery('FSBlogBundle:ValidTestEntity');
		
		$this->assertTrue($query instanceof SolrQuery);
		$this->assertEquals(4, count($query->getMappedFields()));		
		
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Unknown entity InvalidBundle:InvalidEntity
	 */
	public function testCreateQuery_EntityIsUnknown() {
		$configMock = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
		$commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
		$logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface', array(), array(), '', false);
		
		$doctrineConfiguration = $this->setupDoctrine('FS\SolrBundle\Tests\Doctrine\Mapper');
		
		$solr = new SolrFacade($configMock, $commandFactory, $logger);
		$solr->setDoctrineConfiguration($doctrineConfiguration);
	
		$solr->createQuery('InvalidBundle:InvalidEntity');
	}	
}

