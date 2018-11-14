<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use Doctrine\Common\Collections\ArrayCollection;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithCollection;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithRelation;

/**
 * @group hydration
 */
class ValueHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());
    }

    /**
     * @test
     */
    public function documentShouldMapToEntity()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'title_t' => 'foo',
            'publish_date_s' => '10.10.2016',
            'field_s' => 'value 1234',
            'unknown_field_s' => 'value'
        ));

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());
        $this->assertEquals('10.10.2016', $entity->getPublishDate());
        $this->assertEquals('value 1234', $entity->getField());
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

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals(12345, $entity->getCreatedAt());
    }

    /**
     * @test
     */
    public function doNotOverwriteComplexTypes_Collection()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'title_t' => 'foo',
            'collection_ss' => array('title 1', 'title 2')
        ));

        $entity = new ValidTestEntityWithCollection();

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());
        $this->assertEquals(array('title 1', 'title 2'), $entity->getCollection());
    }

    /**
     * @test
     */
    public function doNotOverwriteComplexTypes_Relation()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'title_t' => 'foo',
            'posts_ss' => array('title 1', 'title2')
        ));

        $entity1 = new ValidTestEntity();
        $entity1->setTitle('title 1');

        $entity = new ValidTestEntityWithRelation();
        $entity->setRelation($entity1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());

        $this->assertTrue($hydratedDocument->getRelation() === $entity1);
    }
}
 
