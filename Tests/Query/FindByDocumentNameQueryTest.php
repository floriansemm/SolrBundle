<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Query\FindByDocumentNameQuery;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group query
 */
class FindByDocumentNameQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQuery_SearchInAllFields()
    {
        $document = new Document();
        $document->addField('document_name_s', 'validtestentity');

        $expectedQuery = '';
        $query = new FindByDocumentNameQuery($document);

        $filterQueries = $query->getSolrQuery()->getFilterQueries();
        $queryString = $query->getQueryString();

        $this->assertEquals($expectedQuery, $queryString, 'query');
        $this->assertEquals(1, count($filterQueries));
        $actualFilterQuery = array_pop($filterQueries);
        $this->assertEquals('document_name_s:validtestentity', $actualFilterQuery, 'filter query');
    }

    public function testGetQuery_DocumentnameMissing()
    {
        $document = new Document();

        $query = new FindByDocumentNameQuery($document);

        try {
            $queryString = $query->getQueryString();

            $this->fail('an exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
    }

}
