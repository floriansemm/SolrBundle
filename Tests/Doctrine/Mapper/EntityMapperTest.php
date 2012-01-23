<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Mapper\Command\MapAllFieldsCommand;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateFreshDocumentCommand;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateFromExistingDocumentCommand;

use FS\SolrBundle\Doctrine\Mapper\EntityMapper;

class EntityMapperTest extends \PHPUnit_Framework_TestCase {

	public function testToDocument_EntityMayNotIndexed() {
		$mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper();
		
		$actual = $mapper->toDocument(new NotIndexedEntity());
		$this->assertNull($actual);
	}
	
	public function testToDocument_DocumentIsUpdated() {
		$mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper();
		$mapper->setMappingCommand(new MapAllFieldsCommand(new AnnotationReader()));
		
		$updatedEntity = new ValidTestEntity();
		$updatedEntity->setId(123);
		
		$actual = $mapper->toDocument($updatedEntity);
		$this->assertTrue($actual instanceof \SolrInputDocument);
		$this->assertTrue($actual->fieldExists('id'));
	}
	
	public function testToEntity() {
		$obj = new SolrDocumentStub(array(
			'id' 	=> 1,
			'title_t'	=> 'foo'		
		));
		
		$targetEntity = new ValidTestEntity();
		
		$mapper = new \FS\SolrBundle\Doctrine\Mapper\EntityMapper();
		$entity = $mapper->toEntity($obj, $targetEntity);
		
		$this->assertTrue($entity instanceof $targetEntity);
		
		$this->assertEquals(1, $targetEntity->getId());
		$this->assertEquals('foo', $targetEntity->getTitle());
	}
}

