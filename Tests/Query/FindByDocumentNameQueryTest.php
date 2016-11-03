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
        $document->addField('id', 'validtestentity_1');

        $query = new FindByDocumentNameQuery();
        $query->setDocumentName('validtestentity');
        $query->setDocument($document);

        $this->assertEquals('*:*', $query->getQuery(), 'filter query');
        $this->assertEquals('id:validtestentity_*', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @expectedException FS\SolrBundle\Query\Exception\QueryException
     * @expectedExceptionMessage documentName should not be null
     */
    public function testGetQuery_DocumentnameMissing()
    {
        $query = new FindByDocumentNameQuery();
        $query->setDocument(new Document());

        $query->getQuery();
    }

}
