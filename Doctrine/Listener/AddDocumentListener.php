<?php
namespace FS\SolrBundle\Doctrine\Listener;

use FS\SolrBundle\SolrQueryFacade;

use FS\SolrBundle\SolrFacade;
use Doctrine\ORM\Event\LifecycleEventArgs;

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
		$entity = $args->getEntity();
		
		try {
			$this->solrFacade->addDocument($entity);
		} catch (\RuntimeException $e) {}
	}
}

?>