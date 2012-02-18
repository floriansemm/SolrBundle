<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

/**
 *  @group mappingcommands
 */
class MapAllFieldsCommandTest extends SolrDocumentTest {

	public static $MAPPED_FIELDS = array('title_s', 'text_t', 'created_at_dt');
	
	public function testMapEntity_DocumentShouldContainThreeFields() {
		$command = new MapAllFieldsCommand();
	
		$actual = $command->createDocument(MetaTestInformationFactory::getMetaInformation());
		$this->assertTrue($actual instanceof \SolrInputDocument, 'is a SolrInputDocument');
		$this->assertFieldCount(3, $actual, 'three fields are mapped');
		
		$this->assertHasDocumentFields($actual, self::$MAPPED_FIELDS);
	}	
}

