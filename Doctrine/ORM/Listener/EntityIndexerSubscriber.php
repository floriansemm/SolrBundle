<?php

namespace FS\SolrBundle\Doctrine\ORM\Listener;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineEmptyCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use FS\SolrBundle\Doctrine\AbstractIndexingListener;
use Doctrine\ORM\Event\OnFlushEventArgs;

class EntityIndexerSubscriber extends AbstractIndexingListener implements EventSubscriber
{
    /**
     * @var array
     */
    private $persistedEntities = [];

    /**
     * @var array
     */

    private $updatedEntities = [];

    /**
     * @var array
     */
    private $deletedRootEntities = [];

    /**
     * @var array
     */
    private $deletedNestedEntities = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['preRemove', 'postFlush', 'onFlush'];
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args) {

        $em = $args->getEntityManager();

        foreach ($em->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            $this->persistedEntities[] = $entity;
        }

        foreach ($em->getUnitOfWork()->getScheduledEntityUpdates() as $entity) {

            $doctrineChangeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
            try {
                if ($this->hasChanged($doctrineChangeSet, $entity) === false) {
                    return;
                }

                $this->updatedEntities[] = $entity;
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($this->isNested($entity)) {
            $this->deletedNestedEntities[] = $this->emptyCollections($entity);
        } else {
            $this->deletedRootEntities[] = $this->emptyCollections($entity);
        }
    }

    /**
     * @param object $object
     *
     * @return object
     */
    private function emptyCollections($object)
    {
        $deepcopy = new DeepCopy();
        $deepcopy->addFilter(new DoctrineEmptyCollectionFilter(), new PropertyTypeMatcher('Doctrine\Common\Collections\Collection'));

        return $deepcopy->copy($object);
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->persistedEntities as $entity) {

            try {
                $this->solr->addDocument($entity);
            } catch(\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
        $this->persistedEntities = [];

        foreach ($this->updatedEntities as $entity) {
            $this->solr->updateDocument($entity);
        }

        $this->updatedEntities = [];

        foreach ($this->deletedRootEntities as $entity) {
            $this->solr->removeDocument($entity);
        }
        $this->deletedRootEntities = [];

        if ($this->isNested($entity)) {
            $this->deletedNestedEntities[] = $this->emptyCollections($entity);
        } else {
            $this->deletedRootEntities[] = $this->emptyCollections($entity);
        }
    }

    /**
     * @param object $object
     *
     * @return object
     */
    private function emptyCollections($object)
    {
        $deepcopy = new DeepCopy();
        $deepcopy->addFilter(new DoctrineEmptyCollectionFilter(), new PropertyTypeMatcher('Doctrine\Common\Collections\Collection'));

        return $deepcopy->copy($object);
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->persistedEntities as $entity) {
            $this->solr->addDocument($entity);
        }
        $this->persistedEntities = [];

        foreach ($this->deletedRootEntities as $entity) {
            $this->solr->removeDocument($entity);
        }
        $this->deletedRootEntities = [];
    }
}