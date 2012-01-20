<?php
namespace FS\SolrBundle\Doctrine\Listener;

use FS\SolrBundle\SolrFacade;
use Doctrine\ORM\Event\LifecycleEventArgs;

class IndexListener {
	
	/**
	 * 
	 * @var SolrFacade
	 */
	private $solrFacade = null;
	
	public function __construct(SolrFacade $solrFacade) {
		$this->solrFacade = $solrFacade;
	}
	
	public function postPersist(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		
		$this->solrFacade->addDocument($entity);
	}
	
	public function postUpdate(LifecycleEventArgs $args) {}
	
	
	public function postRemove(LifecycleEventArgs $args) {}	
}

?>