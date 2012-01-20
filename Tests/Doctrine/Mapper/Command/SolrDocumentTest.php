<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper\Command;

abstract class SolrDocumentTest extends \PHPUnit_Framework_TestCase {
	protected function assertHasDocumentFields($document, $expectedFields) {
		$actualFields = $document->getFieldNames();
		foreach ($expectedFields as $expectedField) {
			$this->assertTrue(in_array($expectedField, $actualFields), 'field'. $expectedField .' not in document');
		}
	}
}

?>