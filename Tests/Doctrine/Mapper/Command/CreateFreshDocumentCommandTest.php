<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Command;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;

use FS\SolrBundle\Doctrine\Mapper\Command\CreateFreshDocumentCommand;

use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

/**
 *  test case.
 */
class CreateFreshDocumentCommandTest extends SolrDocumentTest {

	public static $MAPPED_FIELDS = array('title_s', 'text_t', 'created_at_dt');
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();

		// TODO Auto-generated CreateFromExistingDocumentCommandTest::setUp()

	}
	

	
	public function testMapEntity_DocumentShouldContainThreeFields() {
		$command = new CreateFreshDocumentCommand(new AnnotationReader());
	
		$actual = $command->createDocument(new ValidTestEntity());
		$this->assertTrue($actual instanceof \SolrInputDocument, 'is a SolrInputDocument');
		$this->assertEquals(4, $actual->getFieldCount(), 'four fields are mapped');

		$this->assertHasDocumentFields($actual, self::$MAPPED_FIELDS);
	}	
	

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated CreateFromExistingDocumentCommandTest::tearDown()

		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}

}

