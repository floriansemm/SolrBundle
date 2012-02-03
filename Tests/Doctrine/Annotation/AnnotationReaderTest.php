<?php

namespace FS\SolrBundle\Tests\Doctrine\Annotation;

use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\EntityWithRepository;
use FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository;
use FS\SolrBundle\Tests\Doctrine\Mapper\NotIndexedEntity;

class AnnotationReaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();

		// TODO Auto-generated AnnotationReaderTest::setUp()

	}

	public function testGetFields_NoFieldsDected() {
		$reader = new AnnotationReader();
		
		$fields = $reader->getFields(new NotIndexedEntity());
		
		$this->assertEquals(0, count($fields));
	}

	public function testGetFields_ThreeFieldsDetected() {
		$reader = new AnnotationReader();
		
		$fields = $reader->getFields(new ValidTestEntity());
		
		$this->assertEquals(3, count($fields), '3 fields are mapped');		
	}
	
	/**
	 * @expectedException RuntimeException
	 */
	public function testGetIdentifier_ShouldThrowException() {
		$reader = new AnnotationReader();
		
		$reader->getIdentifier(new NotIndexedEntity());
	}
	
	public function testGetIdentifier() {
		$reader = new AnnotationReader();
	
		$id = $reader->getIdentifier(new ValidTestEntity());
		
		$this->assertEquals('id', $id->name);
	}
	
	public function testGetFieldMapping_ThreeMappingsAndId() {
		$reader = new AnnotationReader();
		
		$fields = $reader->getFieldMapping(new ValidTestEntity());
		
		$this->assertEquals(4, count($fields), 'four fields are mapped');
		$this->assertTrue(array_key_exists('title_s', $fields));
		$this->assertTrue(array_key_exists('id', $fields));
	}
	
	public function testGetRepository_ValidRepositoryDeclared() {
		$reader = new AnnotationReader();
		$repository = $reader->getRepository(new EntityWithRepository());
		
		$expected = 'FS\SolrBundle\Tests\Doctrine\Annotation\Entities\ValidEntityRepository';
		$actual = $repository;
		$this->assertEquals($expected, $actual, 'wrong declared repository');
	}
	
	public function testGetRepository_NoRepositoryAttributSet() {
		$reader = new AnnotationReader();
		$repository = $reader->getRepository(new ValidTestEntity());
	
		$expected = '';
		$actual = $repository;
		$this->assertEquals($expected, $actual, 'no repository was declared');
	}	
}

