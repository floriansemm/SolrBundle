<?php
namespace FS\SolrBundle\Event\Listener;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use Psr\Log\LoggerInterface;

abstract class AbstractLogListener
{

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param MetaInformation $metaInformation
     *
     * @return string
     */
    protected function createDocumentNameWithId(MetaInformation $metaInformation)
    {
        return $metaInformation->getDocumentName() . ':' . $metaInformation->getEntityId();
    }

    /**
     * @param MetaInformation $metaInformation
     *
     * @return string
     */
    protected function createFieldList(MetaInformation $metaInformation)
    {
        return implode(', ', $metaInformation->getFields());
    }
}
