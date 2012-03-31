<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Event\EventListenerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

abstract class AbstractLogListener implements EventListenerInterface {
	/**
	 *
	 * @var LoggerInterface
	 */
	protected $logger = null;
	
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	protected function createDocumentNameWithId(MetaInformation $metaInformation) {
		return $metaInformation->getDocumentName().':'.$metaInformation->getEntityId();
	}
	
	protected function createFieldList(MetaInformation $metaInformation) {
		return implode(', ', $metaInformation->getFields());
	}
}
