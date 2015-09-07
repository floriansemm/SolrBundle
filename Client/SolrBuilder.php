<?php

namespace FS\SolrBundle\Client;

use Solarium\Client;
use Solarium\Core\Plugin\Plugin;

/**
 * Creates an instance of the Solarium Client
 */
class SolrBuilder implements Builder
{
    /**
     * @var array
     */
    private $settings = array();

    /**
     * @var Plugin
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
     * @param string $pluginName
     * @param Plugin $plugin
     */
    public function addPlugin($pluginName, Plugin $plugin)
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