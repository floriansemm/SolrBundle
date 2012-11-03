<?php

namespace FS\SolrBundle\Tests\Solr\Event;

use FS\SolrBundle\Event\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

use FS\SolrBundle\Event\EventManager;

/**
 * 
 * @author fs
 * @group eventmanager
 */
class EventManagerTest extends \PHPUnit_Framework_TestCase {
	
	public function testAddListener_OneAdded() {
		$manager = new EventManager();
		
		$listener = $this->getMock('FS\SolrBundle\Event\EventListenerInterface', array(), array(), '', false);
		
		$manager->addListener(EventManager::INSERT, $listener);
		
		$listener = $manager->getListeners();

		$this->assertTrue(array_key_exists(EventManager::INSERT, $listener), 'listener for insert event registered');
		$this->assertEquals(1, count($listener[EventManager::INSERT]), 'on listener for insert registered');
	}
	
	public function testHandle_ListenerForEventIsRegistred() {
		$manager = new EventManager();
		
		$listener = $this->getMock('FS\SolrBundle\Event\EventListenerInterface', array(), array(), '', false);
		$listener->expects($this->once())
				 ->method('notify');
		
		$manager->addListener(EventManager::INSERT, $listener);
		
		$client = $this->getMock('\SolrClient', array(), array(), '', false);
		
		$manager->handle(EventManager::INSERT, new Event($client, new MetaInformation()));
	}
	
}

