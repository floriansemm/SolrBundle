<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Hydration\Hydrator;
use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use FS\SolrBundle\Doctrine\Annotation\Index as Solr;
use Solarium\QueryType\Update\Query\Document\Document;

class EntityMapper
{
    /**
     * @var CreateDocumentCommandInterface
     */
    private $mappingCommand = null;

    /**
     * @var Hydrator
     */
    private $doctrineHydrator;

    /**
     * @var Hydrator
     */
    private $indexHydrator;

    private $hydrationMode = '';

    public function __construct(Hydrator $doctrineHydrator, Hydrator $indexHydrator)
    {
        $this->doctrineHydrator = $doctrineHydrator;
        $this->indexHydrator = $indexHydrator;

        $this->hydrationMode = HydrationModes::HYDRATE_DOCTRINE;
    }

    /**
     * @param AbstractDocumentCommand $command
     */
    public function setMappingCommand(AbstractDocumentCommand $command)
    {
        $this->mappingCommand = $command;
    }

    /**
     * @param MetaInformation $meta
     * @return Document
     */
    public function toDocument(MetaInformation $meta)
    {
        if ($this->mappingCommand instanceof AbstractDocumentCommand) {
            return $this->mappingCommand->createDocument($meta);
        }

        return null;
    }

    /**
     * @param \ArrayAccess $document
     * @param object $sourceTargetEntity
     * @return object
     *
     * @throws \InvalidArgumentException if $sourceTargetEntity is null
     */
    public function toEntity(\ArrayAccess $document, $sourceTargetEntity)
    {
        if (null === $sourceTargetEntity) {
            throw new \InvalidArgumentException('$sourceTargetEntity should not be null');
        }

        $metaInformationFactory = new MetaInformationFactory();
        $metaInformation = $metaInformationFactory->loadInformation($sourceTargetEntity);

        $hydratedDocument = $this->indexHydrator->hydrate($document, $metaInformation);
        if ($this->hydrationMode == HydrationModes::HYDRATE_INDEX) {
            return $hydratedDocument;
        }

        $metaInformation->setEntity($hydratedDocument);

        if ($this->hydrationMode == HydrationModes::HYDRATE_DOCTRINE) {
            return $this->doctrineHydrator->hydrate($document, $metaInformation);
        }
    }

    public function setHydrationMode($mode)
    {
        $this->hydrationMode = $mode;
    }
}
