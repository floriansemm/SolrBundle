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
        $document->addField('id', '1');
        $document->addField('document_name_s', 'validtestentity');

        $expectedQuery = 'id:1 AND document_name_s:validtestentity';
        $query = new FindByIdentifierQuery();
        $query->setDocument($document);

        $queryString = $query->getQuery();

        $this->assertEquals($expectedQuery, $queryString);
    }

    public function testGetQuery_DocumentNameMissing()
    {
        $document = new Document();
        $document->addField('id', '1');

        $query = new FindByIdentifierQuery();
        $query->setDocument($document);

        try {
            $query->getQuery();

            $this->fail('an exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('documentName should not be null', $e->getMessage());
        }
    }

    public function testGetQuery_IdMissing()
    {
        $query = new FindByIdentifierQuery();
        $query->setDocument(new Document());

        try {
            $query->getQuery();

            $this->fail('an exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('id should not be null', $e->getMessage());
        }
    }
}
