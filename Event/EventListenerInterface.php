<?php
namespace FS\SolrBundle\Event;

interface EventListenerInterface {
	public function notify(\SolrInputDocument $document);
}

?>