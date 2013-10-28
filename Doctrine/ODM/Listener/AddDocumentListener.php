<?php
namespace FS\SolrBundle\Doctrine\ODM\Listener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use FS\SolrBundle\Solr;
use FS\SolrBundle\SolrQueryFacade;

class AddDocumentListener
{

    /**
     * @var Solr
     */
    private $solr = null;

    /**
     * @param Solr $solr
     */
    public function __construct(Solr $solr)
    {
        $this->solr = $solr;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        try {
            $this->solr->addDocument($entity);
        } catch (\RuntimeException $e) {
        }
    }
}
