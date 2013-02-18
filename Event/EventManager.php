<?php
namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class EventManager {
	
	/**
	 * @var array
	 */
	private $listener = array();

	const UPDATE = 'update';
	const INSERT = 'insert';
	const DELETE = 'delete';
	const ERROR = 'error';
	
	/**
	 * @param string $event
	 * @param EventListenerInterface $listener
	 */
	public function addListener($event, EventListenerInterface $listener) {
		$this->listener[$event][] = $listener;
	}
	
	/**
	 * @return array
	 */
	public function getListeners() {
		return $this->listener;
	}
	
	/**
	 * @param string $eventName
	 * @param Event $event
	 */
	public function handle($eventName, Event $event) {
		if (!array_key_exists($eventName, $this->listener)) {
			return;
		}
		
		foreach ($this->listener[$eventName] as $listener) {
			if ($listener instanceof EventListenerInterface) {
				$listener->notify($event);
			}
		}
	}
}

?>