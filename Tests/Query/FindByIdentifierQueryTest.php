<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Query\FindByIdentifierQuery;

/**
 * @group query
 */
class FindByIdentifierQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQuery_SearchInAllFields()
    {
        $document = new \SolrInputDocument();
        $document->addField('id', '1');
        $document->addField('document_name_s', 'validtestentity');

        $expectedQuery = 'id:1';
        $query = new FindByIdentifierQuery($document);

        $filterQueries = $query->getSolrQuery()->getFilterQueries();

        $queryString = $query->getQueryString();

        $this->assertEquals($expectedQuery, $queryString);
        $this->assertEquals(1, count($filterQueries));
        $actualFilterQuery = array_pop($filterQueries);
        $this->assertEquals('document_name_s:validtestentity', $actualFilterQuery);
    }

    public function testGetQuery_DocumentNameMissing()
    {
        $document = new \SolrInputDocument();
        $document->addField('id', '1');

        $query = new FindByIdentifierQuery($document);


        try {
            $queryString = $query->getQueryString();

            $this->fail('an exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('documentName should not be null', $e->getMessage());
        }
    }

    public function testGetQuery_IdMissing()
    {
        $document = new \SolrInputDocument();

        $query = new FindByIdentifierQuery($document);

        try {
            $queryString = $query->getQueryString();

            $this->fail('an exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('id should not be null', $e->getMessage());
        }
    }
}
