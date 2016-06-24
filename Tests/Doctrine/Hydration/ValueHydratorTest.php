<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use Doctrine\Common\Collections\ArrayCollection;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntityWithCollection;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntityWithRelation;

/**
 * @group hydration
 */
class ValueHydratorTest extends \PHPUnit_Framework_TestCase
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
            'title_t' => 'foo'
        ));

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory($this->reader);
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
            'posts_ss' => array('title 1', 'title 2')
        ));

        $entity1 = new ValidTestEntity();
        $entity1->setTitle('title 1');

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('title 2');

        $entity = new ValidTestEntityWithCollection();
        $entity->setCollection(new ArrayCollection(array($entity1, $entity2)));

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $hydrator = new ValueHydrator();
        $hydratedDocument = $hydrator->hydrate($obj, $metainformations);

        $this->assertTrue($hydratedDocument instanceof $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('foo', $entity->getTitle());

        $this->assertTrue($hydratedDocument->getCollection()[0] === $entity1);
    }

    /**
     * @test
     */
    public function doNotOverwriteComplexTypes_Relation()
    {
        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'title_t' => 'foo',
            'posts_ss' => 'title 1'
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
 