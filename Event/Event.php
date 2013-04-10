<?php
namespace FS\SolrBundle\Event;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class Event
{

    /**
     * @var object
     */
    private $client = null;

    /**
     * @var MetaInformation
     */
    private $metainformation = null;

    /**
     * something like 'update-solr-document'
     *
     * @var string
     */
    private $solrAction = '';

    /**
     * @param object $client
     * @param MetaInformation $metainformation
     */
    public function __construct($client = null, MetaInformation $metainformation = null, $solrAction = '')
    {
        $this->client = $client;
        $this->metainformation = $metainformation;
        $this->solrAction = $solrAction;
    }

    /**
     * @return MetaInformation
     */
    public function getMetaInformation()
    {
        return $this->metainformation;
    }

    /**
     * @return string
     */
    public function getCore()
    {
        $options = $this->client->getOptions();

        if (isset($options['path'])) {
            return $options['path'];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSolrAction()
    {
        return $this->solrAction;
    }
}
