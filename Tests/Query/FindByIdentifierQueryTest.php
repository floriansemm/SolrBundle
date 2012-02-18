<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Query\FindByIdentifierQuery;

/**
 *  @group query
 */
class FindByIdentifierQueryTest extends \PHPUnit_Framework_TestCase {

	public function testGetQuery_SearchInAllFields() {
		$document = new \SolrInputDocument();
		$document->addField('id', '1');
		$document->addField('document_name_s', 'validtestentity');
	
		$expected = 'id:1 AND document_name_s:validtestentity';
		$query = new FindByIdentifierQuery($document);
	
		$queryString = $query->getQueryString();
		
		$this->assertEquals($expected, $queryString);
	}
	
}
