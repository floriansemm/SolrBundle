<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateDeletedDocumentCommand;
use FS\SolrBundle\Tests\Doctrine\Mapper\NoIdEntity;


use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;


/**
 *  @group mappingcommands
 */
class MapIdentifierCommandTest extends SolrDocumentTest {

	public function testCreateDocument_DocumentHasOnlyIdAndNameField() {
		$command = new MapIdentifierCommand();
		
		$document = $command->createDocument(MetaTestInformationFactory::getMetaInformation());
		
		$this->assertEquals(2, $document->getFieldCount(), 'fieldcount is two');
		$this->assertEquals(2, $document->getField('id')->values[0], 'id is 2');
		
	}

}

