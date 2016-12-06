<?php

namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydratorInterface;
use FS\SolrBundle\Doctrine\Hydration\DoctrineValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidOdmTestDocument;
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

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue($fetchedFromDoctrine));

        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $ormManager = $this->setupManager($metainformations, $repository);

        $obj = new SolrDocumentStub(array('id' => 'document_1'));

        $doctrine = new DoctrineHydrator(new ValueHydrator());
        $doctrine->setOrmManager($ormManager);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEntityFromDBReplcesTargetEntity($metainformations, $fetchedFromDoctrine, $hydratedDocument);
    }

    /**
     * @test
     */
    public function useOdmManagerIfObjectIsOdmDocument()
    {
        $fetchedFromDoctrine = new ValidOdmTestDocument();

        $odmRepository = $this->createMock(ObjectRepository::class);
        $odmRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue($fetchedFromDoctrine));

        $entity = new ValidOdmTestDocument();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $ormManager = $this->createMock(ObjectManager::class);
        $ormManager->expects($this->never())
            ->method('getRepository');
        $odmManager = $this->setupManager($metainformations, $odmRepository);

        $obj = new SolrDocumentStub(array('id' => 'document_1'));

        $doctrine = new DoctrineHydrator(new ValueHydrator());
        $doctrine->setOdmManager($odmManager);
        $doctrine->setOrmManager($ormManager);
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

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue($targetEntity));

        $ormManager = $this->setupManager($metainformations, $repository);

        $obj = new SolrDocumentStub(array(
            'id' => 'document_1',
            'posts_ss' => array('title 1', 'title 2')
        ));

        $doctrineHydrator = new DoctrineHydrator(new DoctrineValueHydrator());
        $doctrineHydrator->setOrmManager($ormManager);

        /** @var ValidTestEntity $hydratedEntity */
        $hydratedEntity = $doctrineHydrator->hydrate($obj, $metainformations);

        $this->assertEquals($relations, $hydratedEntity->getPosts());
    }

    /**
     * @test
     */
    public function entityFromDbNotFoundShouldNotModifyMetainformations()
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue(null));

        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $ormManager = $this->setupManager($metainformations, $repository);

        $obj = new SolrDocumentStub(array('id' => 'document_1'));

        $hydrator = new ValueHydrator();

        $doctrine = new DoctrineHydrator($hydrator);
        $doctrine->setOrmManager($ormManager);
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
     * @param MetaInformationInterface $metainformations
     * @param $repository
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setupManager($metainformations, $repository)
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('getRepository')
            ->with($metainformations->getClassName())
            ->will($this->returnValue($repository));

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));

        return $managerRegistry;
    }

}
 