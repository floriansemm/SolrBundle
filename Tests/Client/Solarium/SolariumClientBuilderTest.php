<?php

namespace FS\SolrBundle\Tests\Client\Solarium;

use FS\SolrBundle\Client\Solarium\SolariumClientBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SolariumClientBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $defaultEndpoints;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->defaultEndpoints = [
            'unittest' => [
                'schema' => 'http',
                'host' => '127.0.0.1',
                'port' => 8983,
                'path' => '/solr',
                'timeout' => 5,
                'core' => null
            ]
        ];
    }

    public function testCreateClientWithoutDsn()
    {
        $actual = $this->createClientWithSettings($this->defaultEndpoints);

        $endpoint = $actual->getEndpoint('unittest');
        $this->assertEquals('http://127.0.0.1:8983/solr/', $endpoint->getBaseUri());
    }

    public function testCreateClientWithoutDsnWithCore()
    {
        $this->defaultEndpoints['unittest']['core'] = 'core0';

        $actual = $this->createClientWithSettings($this->defaultEndpoints);

        $endpoint = $actual->getEndpoint('unittest');
        $this->assertEquals('http://127.0.0.1:8983/solr/core0/', $endpoint->getBaseUri());
    }

    /**
     * @param string $dsn
     * @param string $expectedBaseUri
     * @param string $message
     *
     * @dataProvider dsnProvider
     */
    public function testCreateClientWithDsn($dsn, $expectedBaseUri, $message)
    {
        $settings = $this->defaultEndpoints;
        $settings['unittest'] = [
            'dsn' => $dsn
        ];

        $actual = $this->createClientWithSettings($settings);

        $endpoint = $actual->getEndpoint('unittest');
        $this->assertEquals($expectedBaseUri, $endpoint->getBaseUri(), $message);
    }

    /**
     * @param string $dsn
     * @param string $expectedBaseUri
     * @param string $message
     *
     * @dataProvider dsnProvider
     */
    public function testCreateClientWithDsnAndCore($dsn, $expectedBaseUri, $message)
    {
        $settings = $this->defaultEndpoints;
        $settings['unittest'] = [
            'dsn' => $dsn,
            'core' => 'core0'
        ];

        $actual = $this->createClientWithSettings($settings);

        $endpoint = $actual->getEndpoint('unittest');
        $this->assertEquals($expectedBaseUri . 'core0/', $endpoint->getBaseUri(), $message . ' with core');
    }

    /**
     * @return array
     */
    public function dsnProvider()
    {
        return [
            [
                'http://example.com:1234',
                'http://example.com:1234/',
                'Test DSN without path and any authentication'
            ],
            [
                'http://example.com:1234/solr',
                'http://example.com:1234/solr/',
                'Test DSN without any authentication'
            ],
            [
                'http://user@example.com:1234/solr',
                'http://user@example.com:1234/solr/',
                'Test DSN with user-only authentication'
            ],
            [
                'http://user:secret@example.com:1234/solr',
                'http://user:secret@example.com:1234/solr/',
                'Test DSN with authentication'
            ],
            [
                'https://example.com:1234/solr',
                'https://example.com:1234/solr/',
                'Test DSN with HTTPS'
            ]
        ];
    }

    /**
     * @param array $settings
     *
     * @return \Solarium\Client
     */
    private function createClientWithSettings(array $settings)
    {
        /** @var EventDispatcherInterface $eventDispatcherMock */
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        
        return (new SolariumClientBuilder($settings, $eventDispatcherMock))->build();
    }
}
