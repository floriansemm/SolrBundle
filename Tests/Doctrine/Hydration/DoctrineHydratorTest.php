<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydratorInterface;
use FS\SolrBundle\Doctrine\Hydration\DoctrineValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @group hydration
 */
class DoctrineHydratorTest extends \PHPUnit_Framework_TestCase
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
    public function foundEntityInDbReplacesEntityOldTargetEntity()
    {
        $fetchedFromDoctrine = new ValidTestEntity();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue($fetchedFromDoctrine));

        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $doctrineRegistry = $this->setupDoctrineRegistry($metainformations, $repository);

        $obj = new SolrDocumentStub(array());
        $obj->id = 'document_1';

        $hydrator = $this->getMock('FS\SolrBundle\Doctrine\Hydration\HydratorInterface');
        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with($obj, $metainformations)
            ->will($this->returnValue($fetchedFromDoctrine));

        $doctrine = new DoctrineHydrator($doctrineRegistry, $hydrator);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEntityFromDBReplcesTargetEntity($metainformations, $fetchedFromDoctrine, $hydratedDocument);
    }

    /**
     * @test
     */
    public function hydrationShouldOverwriteComplexTypes()
    {
        $entity1 = new ValidTestEntity();
        $entity1->setTitle('title 1');

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('title 2');

        $relations = array($entity1, $entity2);

        $targetEntity = new ValidTestEntity();
        $targetEntity->setId(1);
        $targetEntity->setPosts($relations);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($targetEntity);

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue($targetEntity));

        $doctrineRegistry = $this->setupDoctrineRegistry($metainformations, $repository);

        $obj = new SolrDocumentStub(array(
            'posts_ss' => array('title 1', 'title 2')
        ));
        $obj->id = 'document_1';

        $doctrineHydrator = new DoctrineHydrator($doctrineRegistry, new DoctrineValueHydrator());

        /** @var ValidTestEntity $hydratedEntity */
        $hydratedEntity = $doctrineHydrator->hydrate($obj, $metainformations);

        $this->assertEquals($relations, $hydratedEntity->getPosts());
    }

    /**
     * @test
     */
    public function entityFromDbNotFoundShouldNotModifyMetainformations()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue(null));

        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $doctrineRegistry = $this->setupDoctrineRegistry($metainformations, $repository);

        $obj = new SolrDocumentStub(array());
        $obj->id = 'document_1';

        $hydrator = $this->getMock('FS\SolrBundle\Doctrine\Hydration\HydratorInterface');
        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with($obj, $metainformations)
            ->will($this->returnValue($entity));

        $doctrine = new DoctrineHydrator($doctrineRegistry, $hydrator);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEquals($metainformations->getEntity(), $entity);
        $this->assertEquals($entity, $hydratedDocument);

    }

    /**
     * @param MetaInformation $metainformations
     * @param object          $fetchedFromDoctrine
     * @param object          $hydratedDocument
     */
    private function assertEntityFromDBReplcesTargetEntity($metainformations, $fetchedFromDoctrine, $hydratedDocument)
    {
        $this->assertEquals($metainformations->getEntity(), $fetchedFromDoctrine);
        $this->assertEquals($fetchedFromDoctrine, $hydratedDocument);
    }

    /**
     * @param $metainformations
     * @param $repository
     * @return mixed
     */
    private function setupDoctrineRegistry($metainformations, $repository)
    {
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->once())
            ->method('getRepository')
            ->with($metainformations->getClassName())
            ->will($this->returnValue($repository));

        $doctrineRegistry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $doctrineRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));

        return $doctrineRegistry;
    }

}
 