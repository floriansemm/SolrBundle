<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

/**
 * @group hydration
 */
class ValueHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function documentShouldMapToEntity()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'title_t' => 'foo'
        ));

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory();
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());
    }

    /**
     * @test
     */
    public function underscoreFieldBecomeCamelCase()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'created_at_d' => 12345
        ));

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory();
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals(12345, $entity->getCreatedAt());
    }
}
 