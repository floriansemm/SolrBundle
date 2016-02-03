<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Hydration\HydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use FS\SolrBundle\Doctrine\Annotation\Index as Solr;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Update\Query\Document\Document;

class EntityMapper
{
    /**
     * @var CreateDocumentCommandInterface
     */
    private $mappingCommand = null;

    /**
     * @var DoctrineHydrator
     */
    private $doctrineHydrator;

    /**
     * @var HydratorInterface
     */
    private $indexHydrator;

    /**
     * @var string
     */
    private $hydrationMode = '';

    /**
     * @param HydratorInterface $doctrineHydrator
     * @param HydratorInterface $indexHydrator
     */
    public function __construct(HydratorInterface $doctrineHydrator, HydratorInterface $indexHydrator)
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
     * @param MetaInformationInterface $meta
     *
     * @return Document
     */
    public function toDocument(MetaInformationInterface $meta)
    {
        if ($this->mappingCommand instanceof AbstractDocumentCommand) {
            return $this->mappingCommand->createDocument($meta);
        }

        return null;
    }

    /**
     * @param \ArrayAccess    $document
     * @param MetaInformation $metaInformation
     *
     * @return object
     *
     * @throws \InvalidArgumentException if $sourceTargetEntity is null
     */
    public function toEntity(\ArrayAccess $document, MetaInformation $metaInformation)
    {
        $hydratedDocument = $this->indexHydrator->hydrate($document, $metaInformation);

        if ($this->hydrationMode == HydrationModes::HYDRATE_INDEX) {
            return $hydratedDocument;
        }

        $metaInformation->setEntity($hydratedDocument);

        if ($this->hydrationMode == HydrationModes::HYDRATE_DOCTRINE) {
            return $this->doctrineHydrator->hydrate($document, $metaInformation);
        }
    }

    /**
     * @param Result $result
     * @param object $sourceTargetEntity
     *
     * @return array
     */
    public function toEntities(Result $result, $sourceTargetEntity)
    {
        $metaInformationFactory = new MetaInformationFactory();
        $metaInformation = $metaInformationFactory->loadInformation($sourceTargetEntity);

        $hydroMode  = $this->hydrationMode;
        $this->setHydrationMode(HydrationModes::HYDRATE_INDEX);
        $entities = [];
        foreach ($result as $document) {
            array_push($entities, $this->toEntity($document, $metaInformation));
        }

        $this->setHydrationMode($hydroMode);
        if ($this->hydrationMode === HydrationModes::HYDRATE_DOCTRINE) {
            $entities = $this->doctrineHydrator->hydrateEntities($entities, $metaInformation);
        }

        return $entities;
    }

    /**
     * @param string $mode
     */
    public function setHydrationMode($mode)
    {
        $this->hydrationMode = $mode;
    }
}
