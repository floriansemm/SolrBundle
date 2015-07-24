<?php

namespace FS\SolrBundle\Tests\Doctrine\Mapper\Mapping;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\Command\CreateDeletedDocumentCommand;
use FS\SolrBundle\Doctrine\Mapper\Mapping\MapIdentifierCommand;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;


/**
 * @group mappingcommands
 */
class MapIdentifierCommandTest extends SolrDocumentTest
{

    public function testCreateDocument_DocumentHasOnlyIdAndNameField()
    {
        $command = new MapIdentifierCommand();

        $document = $command->createDocument(MetaTestInformationFactory::getMetaInformation());

        $this->assertEquals(1, $document->count(), 'fieldcount is two');
        $this->assertEquals('validtestentity_2', $document->id, 'id is 2');

    }

}

