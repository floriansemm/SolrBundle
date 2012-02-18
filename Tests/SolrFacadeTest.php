<?php

namespace FS\SolrBundle\Tests\Solr;

use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\EntityWithRepository;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

use FS\SolrBundle\SolrFacade;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository;
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

	private $metaFactory = null;
	
	public function setUp() {
		$this->metaFactory = $metaFactory = $this->getMock('FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory', array(), array(), '', false);
	}
	
	private function setupDoctrine($namespace) {
		$doctrineConfiguration = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
		$doctrineConfiguration->expects($this->any())
							  ->method('getEntityNamespace')
							  ->will($this->returnValue($namespace));

		return $doctrineConfiguration;
	}
	
	private function setupMetaFactoryLoadOneCompleteInformation($metaInformation = null) {
		if (null === $metaInformation) {
			$metaInformation = MetaTestInformationFactory::getMetaInformation();
		}
		
		$this->metaFactory->expects($this->once())
						  ->method('loadInformation')
						  ->will($this->returnValue($metaInformation));		
	}
	
	public function testCreateQuery_ValidEntity() {
		$configMock = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
		$commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
		$eventManager = $this->getMock('FS\SolrBundle\Event\EventManager', array(), array(), '', false);
		
		$this->setupMetaFactoryLoadOneCompleteInformation();
		
		$solr = new SolrFacade($configMock, $commandFactory, $eventManager, $this->metaFactory);
		
		$query = $solr->createQuery('FSBlogBundle:ValidTestEntity');
		
		$this->assertTrue($query instanceof SolrQuery);
		$this->assertEquals(4, count($query->getMappedFields()));		
		
	}

	/**
	 * expectedException RuntimeException
	 * expectedExceptionMessage Unknown entity InvalidBundle:InvalidEntity
	 */
	public function testCreateQuery_EntityIsUnknown() {
// 		$configMock = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
// 		$commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
// 		$eventManager = $this->getMock('FS\SolrBundle\Event\EventManager', array(), array(), '', false);
		
// 		$this->setupMetaFactoryLoadOneCompleteInformation();	
		
// 		$solr = new SolrFacade($configMock, $commandFactory, $eventManager, $this->metaFactory);
	
// 		$solr->createQuery('InvalidBundle:InvalidEntity');
	}
	
	public function testGetRepository_UserdefinedRepository() {
		$configMock = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
		$commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
		$eventManager = $this->getMock('FS\SolrBundle\Event\EventManager', array(), array(), '', false);
		
		$metaInformation = new MetaInformation();
		$metaInformation->setClassName(get_class(new EntityWithRepository()));
		$metaInformation->setRepository('FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository');
		
		$this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);	
		
		$solr = new SolrFacade($configMock, $commandFactory, $eventManager, $this->metaFactory);
		$actual = $solr->getRepository('Tests:EntityWithRepository');
		
		$this->assertTrue($actual instanceof ValidEntityRepository);
	}
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testGetRepository_UserdefinedInvalidRepository() {
		$configMock = $this->getMock('FS\SolrBundle\SolrConnection', array(), array(), '', false);
		$commandFactory = CommandFactoryStub::getFactoryWithAllMappingCommand();
		$eventManager = $this->getMock('FS\SolrBundle\Event\EventManager', array(), array(), '', false);
	
		$metaInformation = new MetaInformation();
		$metaInformation->setClassName(get_class(new EntityWithRepository()));
		$metaInformation->setRepository('FS\SolrBundle\Tests\Doctrine\Annotation\Entities\InvalidEntityRepository');
		
		$this->setupMetaFactoryLoadOneCompleteInformation($metaInformation);	
		
		$solr = new SolrFacade($configMock, $commandFactory, $eventManager, $this->metaFactory);
		$solr->getRepository('Tests:EntityWithInvalidRepository');
	}	
}

