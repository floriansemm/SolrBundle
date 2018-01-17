<?php

namespace FS\SolrBundle\Doctrine\ORM\Listener;

use DeepCopy\DeepCopy;
use DeepCopy\Filter\Doctrine\DoctrineEmptyCollectionFilter;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use FS\SolrBundle\Doctrine\AbstractIndexingListener;

class EntityIndexerSubscriber extends AbstractIndexingListener implements EventSubscriber
{
    /**
     * @var array
     */
    private $persistedEntities = [];

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
        return ['postUpdate', 'postPersist', 'preRemove', 'postFlush'];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $doctrineChangeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
        try {
            if ($this->hasChanged($doctrineChangeSet, $entity) === false) {
                return;
            }

            $this->solr->updateDocument($entity);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->persistedEntities[] = $entity;
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
            $this->solr->addDocument($entity);
        }
        $this->persistedEntities = [];

        foreach ($this->deletedRootEntities as $entity) {
            $this->solr->removeDocument($entity);
        }
        $this->deletedRootEntities = [];

        foreach ($this->deletedNestedEntities as $entity) {
            $this->solr->removeDocument($entity);
        }
        $this->deletedNestedEntities = [];
    }
}