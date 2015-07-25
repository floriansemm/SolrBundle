<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Query\FindByIdentifierQuery;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group query
 */
class FindByIdentifierQueryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQuery_SearchInAllFields()
    {
        $document = new Document();
        $document->setKey('id', 'validtestentity_1');

        $expectedQuery = 'id:validtestentity_1';
        $query = new FindByIdentifierQuery();
        $query->setDocumentKey('validtestentity_1');
        $query->setDocument($document);

        $queryString = $query->getQuery();

        $this->assertEquals($expectedQuery, $queryString);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage id should not be null
     */
    public function testGetQuery_IdMissing()
    {
        $query = new FindByIdentifierQuery();
        $query->setDocument(new Document());

        $query->getQuery();
    }
}
