<?php

namespace FS\SolrBundle\Tests\Integration;

use FS\SolrBundle\Event\ErrorEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcherFake implements EventDispatcherInterface
{

    /**
     * @var ErrorEvent[]
     */
    private $errorEvents = array();

    /**
     * @var \FS\SolrBundle\Event\Event[]
     */
    private $events = array();

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     * @param Event $event The event to pass to the event handlers/listeners.
     *                          If not supplied, an empty Event instance is created.
     *
     * @return Event
     *
     * @api
     */
    public function dispatch($eventName, Event $event = null)
    {
        if ($event instanceof ErrorEvent) {
            $this->errorEvents[$eventName] = $event;

            return;
        }

        $this->events[$eventName] = $event;

        return;
    }

    /**
     * @return bool
     */
    public function errorsOccurred()
    {
        if (count($this->errorEvents) > 0) {
            return true;
        }

        return false;
    }

    public function getOccurredErrors()
    {
        $errors = '';
        foreach ($this->errorEvents as $error) {
            $errors .= $error->getExceptionMessage() . PHP_EOL;
        }

        return $errors;
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function eventOccurred($eventName)
    {
        if (isset($this->errorEvents[$eventName])) {
            return true;
        }

        if (isset($this->events[$eventName])) {
            return true;
        }

        return false;
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string $eventName The event to listen on
     * @param callable $listener The listener
     * @param integer $priority The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     *
     * @api
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        // TODO: Implement addListener() method.
    }

    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events he is
     * interested in and added as a listener for these events.
     *
     * @param EventSubscriberInterface $subscriber The subscriber.
     *
     * @api
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement addSubscriber() method.
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param string|array $eventName The event(s) to remove a listener from
     * @param callable $listener The listener to remove
     */
    public function removeListener($eventName, $listener)
    {
        // TODO: Implement removeListener() method.
    }

    /**
     * Removes an event subscriber.
     *
     * @param EventSubscriberInterface $subscriber The subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        // TODO: Implement removeSubscriber() method.
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return array The event listeners for the specified event, or all event listeners by event name
     */
    public function getListeners($eventName = null)
    {
        // TODO: Implement getListeners() method.
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return Boolean true if the specified event has any listeners, false otherwise
     */
    public function hasListeners($eventName = null)
    {
        // TODO: Implement hasListeners() method.
    }

} 