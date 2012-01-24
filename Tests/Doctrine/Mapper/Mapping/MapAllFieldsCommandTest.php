<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

/**
 *  test case.
 */
class MapAllFieldsCommandTest extends SolrDocumentTest {

	public static $MAPPED_FIELDS = array('title_s', 'text_t', 'created_at_dt');
	
	public function testMapEntity_DocumentShouldContainThreeFields() {
		$command = new MapAllFieldsCommand(new AnnotationReader());
	
		$actual = $command->createDocument(new ValidTestEntity());
		$this->assertTrue($actual instanceof \SolrInputDocument, 'is a SolrInputDocument');
		$this->assertFieldCount(3, $actual, 'three fields are mapped');
		
		$this->assertHasDocumentFields($actual, self::$MAPPED_FIELDS);
	}	

	public function testGetDocumentName() {
		$command = new MapAllFieldsCommand(new AnnotationReader());
		$name = $command->getDocumentName(new ValidTestEntity());
		
		$this->assertEquals('validtestentity', $name);
	}

}

