<?php
namespace FS\SolrBundle\Doctrine\Listener;

use FS\SolrBundle\SolrQueryFacade;

use FS\SolrBundle\SolrFacade;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DeleteDocumentListener {
	
	/**
	 * 
	 * @var SolrFacade
	 */
	private $solrFacade = null;
	
	public function __construct(SolrFacade $solrFacade) {
		$this->solrFacade = $solrFacade;
	}
	
	public function preRemove(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		
		$this->solrFacade->removeDocument($entity);		
	}
}

?>