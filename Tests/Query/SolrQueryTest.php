<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Annotation\Id;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Query\Exception\UnknownFieldException;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\SolrInterface;
use FS\SolrBundle\SolrQueryFacade;

/**
 *
 * @group query
 */
class SolrQueryTest extends \PHPUnit_Framework_TestCase
{

    private function getFieldMapping()
    {
        return array(
            'id' => 'id',
            'title_s' => 'title',
            'text_t' => 'text',
            'created_at_dt' => 'created_at'
        );
    }

    /**
     * @return SolrQuery
     */
    private function createQueryWithFieldMapping()
    {
        $solr = $this->createMock(SolrInterface::class);

        $idField = new Id(array());
        $idField->name = 'id';

        $metaInformation = new MetaInformation();
        $metaInformation->setDocumentName('post');
        $metaInformation->setIdentifier($idField);

        $solrQuery = new SolrQuery();
        $solrQuery->setSolr($solr);
        $solrQuery->setMappedFields($this->getFieldMapping());
        $solrQuery->setMetaInformation($metaInformation);

        return $solrQuery;
    }

    /**
     * @return SolrQuery
     */
    private function createQueryWithSearchTerms()
    {
        $query = $this->createQueryWithFieldMapping();

        $query->addSearchTerm('title', 'foo')
            ->addSearchTerm('text', 'bar');

        return $query;
    }

    public function testAddField_AllFieldsAreMapped()
    {
        $solrQuery = $this->createQueryWithFieldMapping();

        $solrQuery->addField('title')
            ->addField('text');

        $fields = $solrQuery->getFields();

        $this->assertEquals(2, count($fields));
        $this->assertTrue(in_array('title_s', $fields));
        $this->assertTrue(in_array('text_t', $fields));
    }

    public function testAddField_OneFieldOfTwoNotMapped()
    {
        $solrQuery = $solrQuery = $this->createQueryWithFieldMapping();

        $solrQuery->addField('title')
            ->addField('foo');

        $fields = $solrQuery->getFields();

        $this->assertEquals(1, count($fields));
        $this->assertTrue(in_array('title_s', $fields));
    }

    public function testGetSolrQuery_QueryTermShouldCorrect()
    {
        $expected = 'title_s:foo OR text_t:bar';

        $query = $this->createQueryWithSearchTerms();

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());

    }

    public function testAddSearchTerm_AllFieldsAreMapped()
    {
        $solrQuery = $this->createQueryWithFieldMapping();

        $solrQuery->addSearchTerm('title', 'foo')
            ->addSearchTerm('text', 'bar');

        $terms = $solrQuery->getSearchTerms();

        $this->assertTrue(array_key_exists('title_s', $terms), 'title_s not in terms');
        $this->assertTrue(array_key_exists('text_t', $terms), 'text_t not in terms');
    }

    /**
     * @expectedException \FS\SolrBundle\Query\Exception\UnknownFieldException
     */
    public function testAddSearchTerm_UnknownField()
    {
        $solrQuery = $this->createQueryWithFieldMapping();

        $solrQuery->addSearchTerm('unknownfield', 'foo');
    }

    public function testGetQuery_TermsConcatWithOr()
    {
        $expected = 'title_s:foo OR text_t:bar';

        $query = $this->createQueryWithSearchTerms();

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_TermsConcatWithAnd()
    {
        $expected = 'title_s:foo AND text_t:bar';

        $query = $this->createQueryWithSearchTerms();
        $query->setUseAndOperator(true);

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_SearchInAllFields()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->queryAllFields('foo');

        $expected = 'title_s:foo OR text_t:foo OR created_at_dt:foo';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_SurroundTermWithDoubleQuotes()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->queryAllFields('foo 12');

        $expected = 'title_s:"foo 12" OR text_t:"foo 12" OR created_at_dt:"foo 12"';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_SurroundWildcardTermWithDoubleQuotes()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->queryAllFields('foo 12');
        $query->setUseWildcard(true);

        $expected = 'title_s:"*foo 12*" OR text_t:"*foo 12*" OR created_at_dt:"*foo 12*"';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_NoWildcard_Word()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->setUseWildcard(false);
        $query->addSearchTerm('title', 'a_word');

        $expected = 'title_s:a_word';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_NoSearchTerm()
    {
        $query = $this->createQueryWithFieldMapping();

        $expected = '*:*';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    public function testGetQuery_CustomQuery()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->setCustomQuery('title_s:[*:*]');

        $expected = 'title_s:[*:*]';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function searchInSetMultipleValues()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->addSearchTerm('title', array('value2', 'value1'));

        $expected = 'title_s:["value1" TO "value2"]';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function searchInSetSingleValues()
    {
        $query = $this->createQueryWithFieldMapping();
        $query->addSearchTerm('title', array('value #1'));

        $expected = 'title_s:"value #1"';

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_*', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function doNotAddIdFieldTwice()
    {
        $expected = '*:*';

        $query = $this->createQueryWithFieldMapping();
        $query->addSearchTerm('id', 'post_1');

        $this->assertEquals($expected, $query->getQuery());
        $this->assertEquals('id:post_1', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function generateQueryForNestedDocuments()
    {
        $mapping = [
            'id' => 'id',
            'title_s' => 'title',
            'collection.id' => 'collection.id',
            'collection.name_s' => 'collection.name'
        ];

        $query = $this->createQueryWithFieldMapping();
        $query->setMappedFields($mapping);
        $query->addSearchTerm('collection.name', 'test*bar');
        $query->addSearchTerm('title', 'test post');

        $this->assertEquals('title_s:"test post" OR {!parent which="id:post_*"}name_s:test', $query->getQuery());
    }
}
