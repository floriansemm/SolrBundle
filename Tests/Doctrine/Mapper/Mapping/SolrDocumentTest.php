<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use Solarium\QueryType\Update\Query\Document\Document;

abstract class SolrDocumentTest extends \PHPUnit_Framework_TestCase
{
    const FIELDS_ALWAYS_MAPPED = 2;

    protected function assertHasDocumentFields(Document $document, $expectedFields)
    {
        $actualFields = $document->getFields();
        foreach ($expectedFields as $expectedField) {
            $this->assertTrue(array_key_exists($expectedField, $actualFields), 'field' . $expectedField . ' not in document');
        }
    }

    protected function assertFieldCount($expectedCount, Document $document, $message = '')
    {
        $this->assertEquals($expectedCount + self::FIELDS_ALWAYS_MAPPED, $document->count(), $message);
    }
}

