<?php
namespace FS\SolrBundle\Doctrine\MongoDB\Listener;

use FS\SolrBundle\SolrQueryFacade;

use FS\SolrBundle\SolrFacade;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

class AddDocumentListener {
    
    /**
     * @var SolrFacade
     */
    private $solrFacade = null;
    
    /**
     * @param SolrFacade $solrFacade
     */
    public function __construct(SolrFacade $solrFacade) {
        $this->solrFacade = $solrFacade;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args) {
        $entity = $args->getDocument();
        
        try {
            $this->solrFacade->addDocument($entity);
        } catch (\RuntimeException $e) {}
    }
}

?>