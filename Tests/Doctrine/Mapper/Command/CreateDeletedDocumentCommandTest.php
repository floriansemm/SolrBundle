<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Command;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateDeletedDocumentCommand;
use FS\SolrBundle\Tests\Doctrine\Mapper\NoIdEntity;


use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;


/**
 *  test case.
 */
class CreateDeletedDocumentCommandTest extends SolrDocumentTest {

	const DOCUMENT_ID = 1;


	public function testCreateDocument_DocumentHasOnlyIdField() {
		$command = new CreateDeletedDocumentCommand(new AnnotationReader());
		
		$entity = new ValidTestEntity();
		$entity->setId(self::DOCUMENT_ID);
		
		$actual = $command->createDocument($entity);
		$this->assertTrue($actual instanceof \SolrInputDocument, 'is a SolrInputDocument');
		$this->assertEquals(1, $actual->getFieldCount(), 'one field was mapped');
		
		$this->assertTrue($actual->fieldExists('id'), 'unique key exists');
		
		$values = $actual->getField('id')->values;
		$this->assertTrue(in_array(self::DOCUMENT_ID, $values), 'document id set');
	}

}

