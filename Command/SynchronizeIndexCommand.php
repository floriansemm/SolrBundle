<?php
namespace FS\SolrBundle\Command;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use FS\SolrBundle\Console\ConsoleErrorListOutput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command synchronizes the DB with solr
 */
class SynchronizeIndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('solr:index:populate')
            ->addArgument('entity', InputArgument::OPTIONAL, 'The entity you want to index', null)
            ->addOption(
                'flushsize',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of items to handle before flushing data',
                500
            )
            ->addOption(
                'source',
                null,
                InputArgument::OPTIONAL,
                'specify a source from where to load entities [relational, mongodb]',
                'relational'
            )
            ->setDescription('Index all entities');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entities = $this->getIndexableEntities($input->getArgument('entity'));
        $source = $input->getOption('source');
        $batchSize = $input->getOption('flushsize');
        $solr = $this->getContainer()->get('solr.client');

        $objectManager = $this->getObjectManager($source);

        foreach ($entities as $entityCollection) {
            $output->writeln(sprintf('Indexing: <info>%s</info>', $entityCollection));

            try {
                $repository = $objectManager->getRepository($entityCollection);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>No repository found for "%s", check your input</error>', $entityCollection));

                continue;
            }

            $totalSize = $this->getTotalNumberOfEntities($entityCollection, $source);

            if ($totalSize === 0) {
                $output->writeln('<comment>No entities found for indexing</comment>');

                continue;
            }

            $output->writeln(sprintf('Synchronize <info>%s</info> entities', $totalSize));

            $batchLoops = ceil($totalSize / $batchSize);

            for ($i = 0; $i <= $batchLoops; $i++) {
                $entities = $repository->findBy(array(), null, $batchSize, $i * $batchSize);
                try {
                    $solr->synchronizeIndex($entities);
                } catch (\Exception $e) {
                    $output->writeln(sprintf('A error occurs: %s', $e->getMessage()));
                }
            }

            $output->writeln('<info>Synchronization finished</info>');
            $output->writeln('');
        }
    }

    /**
     * @param string $source
     *
     * @throws \InvalidArgumentException if $source is unknown
     * @throws \RuntimeException if no doctrine instance is configured
     *
     * @return AbstractManagerRegistry
     */
    private function getObjectManager($source)
    {
        $objectManager = null;

        if ($source === 'relational') {
            $objectManager = $this->getContainer()->get('doctrine');
        } else {
            if ($source === 'mongodb') {
                $objectManager = $this->getContainer()->get('doctrine_mongodb');
            } else {
                throw new \InvalidArgumentException(sprintf('Unknown source %s', $source));
            }
        }

        return $objectManager;
    }

    /**
     * Get a list of entities which are indexable by Solr
     *
     * @param null|string $entity
     * @return array
     */
    private function getIndexableEntities($entity = null)
    {
        if ($entity) {
            return array($entity);
        }

        $entities = array();
        $namespaces = $this->getContainer()->get('solr.doctrine.classnameresolver.known_entity_namespaces');
        $metaInformationFactory = $this->getContainer()->get('solr.meta.information.factory');

        foreach ($namespaces->getEntityClassnames() as $classname) {
            try {
                $metaInformation = $metaInformationFactory->loadInformation($classname);
                array_push($entities, $metaInformation->getClassName());
            } catch (\RuntimeException $e) {
                continue;
            }
        }

        return $entities;
    }

    /**
     * Get the total number of entities in a repository
     *
     * @param string $entity
     * @param string $source
     *
     * @return int
     * @throws \Exception
     */
    private function getTotalNumberOfEntities($entity, $source)
    {
        $objectManager = $this->getObjectManager($source);
        $repository = $objectManager->getRepository($entity);
        $dataStoreMetadata = $objectManager->getManager()->getClassMetadata($entity);

        $identifierColumns = $dataStoreMetadata->getIdentifierColumnNames();

        if (!count($identifierColumns)) {
            throw new \Exception(sprintf('No primary key found for entity %s', $entity));
        }

        $countableColumn = reset($identifierColumns);

        $totalSize = $repository->createQueryBuilder('size')
            ->select(sprintf('count(size.%s)', $countableColumn))
            ->getQuery()
            ->getSingleScalarResult();

        return $totalSize;
    }
}
