<?php
namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class DeleteLogListener implements EventListenerInterface {

	/**
	 * 
	 * @var LoggerInterface
	 */
	private $logger = null;
	
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	/* (non-PHPdoc)
	 * @see FS\SolrBundle\Event.EventListenerInterface::notify()
	 */
	public function notify(MetaInformation $metaInformation) {
		$this->logger->info('document was deleted');
		
	}


}

?>