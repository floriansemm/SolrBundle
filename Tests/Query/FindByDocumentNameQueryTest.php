<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Query\FindByDocumentNameQuery;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group query
 */
class FindByDocumentNameQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group query1
     */
    public function testGetQuery_SearchInAllFields()
    {
        $document = new Document();
        $document->addField('document_name_s', 'validtestentity');

        $query = new FindByDocumentNameQuery();
        $query->setDocument($document);

        $queryString = $query->getQuery();

        $this->assertEquals('document_name_s:validtestentity', $queryString, 'filter query');
    }

    public function testGetQuery_DocumentnameMissing()
    {
        $query = new FindByDocumentNameQuery();
        $query->setDocument(new Document());

        try {
            $query->getQuery();

            $this->fail('an exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertTrue(true);
        }
    }

}
