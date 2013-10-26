<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Mapper\Mapping\MapAllFieldsCommand;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group mappingcommands
 */
class MapAllFieldsCommandTest extends SolrDocumentTest
{

    public static $MAPPED_FIELDS = array('title_s', 'text_t', 'created_at_dt');

    /**
     * @group foo
     */
    public function testMapEntity_DocumentShouldContainThreeFields()
    {
        $command = new MapAllFieldsCommand();

        $actual = $command->createDocument(MetaTestInformationFactory::getMetaInformation());

        $this->assertTrue($actual instanceof Document, 'is a Document');
        $this->assertFieldCount(3, $actual, 'three fields are mapped');

        $this->assertEquals(1, $actual->getBoost(), 'document boost should be 1');

        $boostTitleField = $actual->getFieldBoost('title_s');
        $this->assertEquals(1.8, $boostTitleField, 'boost value of field title_s should be 1.8');

        $this->assertHasDocumentFields($actual, self::$MAPPED_FIELDS);
    }
}

