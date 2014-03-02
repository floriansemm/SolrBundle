<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

/**
 * @group hydration
 */
class DoctrineHydratorTest extends \PHPUnit_Framework_TestCase
{

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

        $metainformations = new MetaInformationFactory();
        $metainformations = $metainformations->loadInformation($entity);

        $doctrineRegistry = $this->setupDoctrineRegistry($metainformations, $repository);

        $obj = new SolrDocumentStub(array());
        $obj->id = 1;

        $hydrator = $this->getMock('FS\SolrBundle\Doctrine\Hydration\Hydrator');
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
    public function entityFromDbNotFoundShouldNotModifyMetainformations()
    {
        $fetchedFromDoctrine = new ValidTestEntity();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue(null));

        $entity = new ValidTestEntity();

        $metainformations = new MetaInformationFactory();
        $metainformations = $metainformations->loadInformation($entity);

        $doctrineRegistry = $this->setupDoctrineRegistry($metainformations, $repository);

        $obj = new SolrDocumentStub(array());
        $obj->id = 1;

        $hydrator = $this->getMock('FS\SolrBundle\Doctrine\Hydration\Hydrator');
        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with($obj, $metainformations)
            ->will($this->returnValue($fetchedFromDoctrine));

        $doctrine = new DoctrineHydrator($doctrineRegistry, $hydrator);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEquals($metainformations->getEntity(), $entity);
        $this->assertEquals($entity, $hydratedDocument);

    }

    /**
     * @param $metainformations
     * @param $fetchedFromDoctrine
     * @param $hydratedDocument
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
 