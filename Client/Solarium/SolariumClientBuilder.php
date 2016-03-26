<?php

namespace FS\SolrBundle\Client\Solarium;

use FS\SolrBundle\Client\Builder;
use Solarium\Client;
use Solarium\Core\Plugin\AbstractPlugin;

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
     * @var AbstractPlugin
     */
    private $plugins;

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->plugins = array();
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
        $solariumClient = new Client(array('endpoint' => $this->settings));
        foreach ($this->plugins as $pluginName => $plugin) {
            $solariumClient->registerPlugin($pluginName, $plugin);
        }

        return $solariumClient;
    }
} 