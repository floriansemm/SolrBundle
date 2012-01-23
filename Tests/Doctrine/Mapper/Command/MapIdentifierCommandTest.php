<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Command;

use FS\SolrBundle\Doctrine\Mapper\Command\MapIdentifierCommand;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateDeletedDocumentCommand;
use FS\SolrBundle\Tests\Doctrine\Mapper\NoIdEntity;


use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;


/**
 *  test case.
 */
class MapIdentifierCommandTest extends SolrDocumentTest {

	public function testCreateDocument_DocumentHasOnlyIdAndNameField() {
		$command = new MapIdentifierCommand(new AnnotationReader());
		
		$entity = new ValidTestEntity();
		$entity->setId(2);
		
		$document = $command->createDocument($entity);
		
		$this->assertEquals(2, $document->getFieldCount(), 'fieldcount is two');
		$this->assertEquals(2, $document->getField('id')->values[0], 'id is 2');
		
	}

}

