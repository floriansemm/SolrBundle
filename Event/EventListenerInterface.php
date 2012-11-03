<?php
namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

interface EventListenerInterface {
	
	/**
	 * @param Event $event
	 */
	public function notify(Event $event);
}

?>