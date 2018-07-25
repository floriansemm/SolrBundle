<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\NoDatabaseValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;

class NoDatabaseValueHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function doNotCutIdFields()
    {
        $reader = new AnnotationReader(new DoctrineAnnotationReader());
        $hydrator = new NoDatabaseValueHydrator();

        $document = new SolrDocumentStub(array(
            'id' => '0003115-2231_S',
            'title_t' => 'fooo_bar'
        ));

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory($reader);
        $metainformations = $metainformations->loadInformation($entity);

        $entity = $hydrator->hydrate($document, $metainformations);

        $this->assertEquals('0003115-2231_S', $entity->getId());
        $this->assertEquals('fooo_bar', $entity->getTitle());
    }
}
