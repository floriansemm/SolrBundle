<?php

namespace FS\SolrBundle\Client\Solarium;

use FS\SolrBundle\Client\Builder;
use Solarium\Client;
use Solarium\Core\Plugin\AbstractPlugin;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Creates an instance of the Solarium Client
 */
class SolariumClientBuilder implements Builder
{
    /**
     * @var array
     */
    private $settings = array();

    /**
     * @var AbstractPlugin[]
     */
    private $plugins = array();

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param array                    $settings
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(array $settings, EventDispatcherInterface $eventDispatcher)
    {
        $this->settings = $settings;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string         $pluginName
     * @param AbstractPlugin $plugin
     */
    public function addPlugin($pluginName, AbstractPlugin $plugin)
    {
        $this->plugins[$pluginName] = $plugin;
    }

    /**
     * {@inheritdoc}
     *
     * @return Client
     */
    public function build()
    {
        $solariumClient = new Client(array('endpoint' => $this->settings), $this->eventDispatcher);
        foreach ($this->plugins as $pluginName => $plugin) {
            $solariumClient->registerPlugin($pluginName, $plugin);
        }

        return $solariumClient;
    }
} 