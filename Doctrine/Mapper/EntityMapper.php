<?php
namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Hydration\HydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\Mapping\AbstractDocumentCommand;
use FS\SolrBundle\Doctrine\Annotation\Index as Solr;
use Solarium\QueryType\Update\Query\Document\Document;

class EntityMapper
{
    /**
     * @var AbstractDocumentCommand
     */
    private $mappingCommand = null;

    /**
     * @var HydratorInterface
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
     * @param \ArrayAccess $document
     * @param object       $sourceTargetEntity
     *
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

    /**
     * @param string $mode
     */
    public function setHydrationMode($mode)
    {
        $this->hydrationMode = $mode;
    }
}
