<?php
namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

interface EventListenerInterface {
	public function notify(MetaInformation $metaInformation);
}

?>