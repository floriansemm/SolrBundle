<?php
namespace FS\SolrBundle\Event;

class EventManager {
	
	private $listener = array();

	const UPDATE = 'update';
	const INSERT = 'insert';
	const DELETE = 'delete';	
	
	public function addListener($event, EventListenerInterface $listener) {
		$this->listener[$event][] = $listener;
	}
	
	public function getListeners() {
		return $this->listener;
	}
	
	public function handle($event, \SolrInputDocument $document) {
		if (array_key_exists($event, $this->listener)) {
			foreach ($this->listener[$event] as $listener) {
				if ($listener instanceof EventListenerInterface) {
					$listener->notify($document);
				}
			}
		}
	}
}

?>