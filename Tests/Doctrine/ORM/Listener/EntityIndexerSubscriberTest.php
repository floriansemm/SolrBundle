<?php

namespace FS\SolrBundle\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\ORM\Listener\EntityIndexerSubscriber;
use FS\SolrBundle\SolrInterface;
use FS\SolrBundle\Tests\Fixtures\NestedEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Tests\Constraints\ValidTest;

class EntityIndexerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityIndexerSubscriber
     */
    private $subscriber;

    private $solr;

    private $metaInformationFactory;

    private $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->solr = $this->createMock(SolrInterface::class);
        $this->metaInformationFactory = new MetaInformationFactory(new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader()));

        $this->subscriber = new EntityIndexerSubscriber($this->solr, $this->metaInformationFactory, $this->logger);
    }

    /**
     * @test
     */
    public function separteDeletedRootEntitiesFromNested()
    {
        $nested = new NestedEntity();
        $nested->setId(uniqid());

        $entity = new ValidTestEntityWithCollection();
        $entity->setId(uniqid());
        $entity->setCollection(new ArrayCollection([$nested]));

        $objectManager = $this->createMock(ObjectManager::class);

        $this->solr->expects($this->at(0))
            ->method('removeDocument')
            ->with($this->callback(function(ValidTestEntityWithCollection $entity) {
                if (count($entity->getCollection())) {
                    return false;
                }

                return true;
            }));

        $this->solr->expects($this->at(1))
            ->method('removeDocument')
            ->with($this->callback(function($entity) {
                if (!$entity instanceof NestedEntity) {
                    return false;
                }

                return true;
            }));

        $deleteRootEntityEvent = new LifecycleEventArgs($entity, $objectManager);
        $this->subscriber->preRemove($deleteRootEntityEvent);

        $deleteNestedEntityEvent = new LifecycleEventArgs($nested, $objectManager);
        $this->subscriber->preRemove($deleteNestedEntityEvent);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->subscriber->postFlush(new PostFlushEventArgs($entityManager));
    }

    /**
     * @test
     */
    public function indexOnlyModifiedEntites()
    {
        $changedEntity = new ValidTestEntityWithCollection();
        $this->solr->expects($this->once())
            ->method('updateDocument')
            ->with($changedEntity);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->at(0))
            ->method('getEntityChangeSet')
            ->willReturn(['title' => 'value']);

        $unitOfWork->expects($this->at(1))
            ->method('getEntityChangeSet')
            ->willReturn([]);

        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        $updateEntityEvent1 = new LifecycleEventArgs($changedEntity, $objectManager);

        $unmodifiedEntity = new ValidTestEntityWithCollection();
        $updateEntityEvent2 = new LifecycleEventArgs($unmodifiedEntity, $objectManager);

        $this->subscriber->postUpdate($updateEntityEvent1);
        $this->subscriber->postUpdate($updateEntityEvent2);
    }
}
