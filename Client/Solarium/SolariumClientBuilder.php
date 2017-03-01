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
     * @param array $settings
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(array $settings, EventDispatcherInterface $eventDispatcher)
    {
        $this->settings = $settings;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $pluginName
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
        $settings = [];
        foreach ($this->settings as $name => $options) {
            if (isset($options['dsn'])) {
                unset(
                    $options['scheme'],
                    $options['host'],
                    $options['port'],
                    $options['path']
                );

                $parsedDsn = parse_url($options['dsn']);
                unset($options['dsn']);
                if ($parsedDsn) {
                    $options['scheme'] = isset($parsedDsn['scheme']) ? $parsedDsn['scheme'] : 'http';
                    if (isset($parsedDsn['host'])) {
                        $options['host'] = $parsedDsn['host'];
                    }
                    if (isset($parsedDsn['user'])) {
                        $auth = $parsedDsn['user'] . (isset($parsedDsn['pass']) ? ':' . $parsedDsn['pass'] : '');
                        $options['host'] = $auth . '@' . $options['host'];
                    }
                    $options['port'] = isset($parsedDsn['port']) ? $parsedDsn['port'] : 80;
                    $options['path'] = isset($parsedDsn['path']) ? $parsedDsn['path'] : '';
                }
            }

            $settings[$name] = $options;
        }

        $solariumClient = new Client(array('endpoint' => $settings), $this->eventDispatcher);
        foreach ($this->plugins as $pluginName => $plugin) {
            $solariumClient->registerPlugin($pluginName, $plugin);
        }

        return $solariumClient;
    }
}
