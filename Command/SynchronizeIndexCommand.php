<?php
namespace FS\SolrBundle\Command;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\EntityRepository;
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
            ->addOption('flushsize', null, InputOption::VALUE_OPTIONAL, 'Number of items to handle before flushing data', 500)
            ->addOption('source', null, InputArgument::OPTIONAL, 'specify a source from where to load entities [relational, mongodb]', null)
            ->setDescription('Index all entities');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entities = $this->getIndexableEntities($input->getArgument('entity'));
        $source = $input->getOption('source');
        if ($source !== null) {
            $output->writeln('<comment>The source option is deprecated and will be removed in version 2.0</comment>');
        }

        $batchSize = $input->getOption('flushsize');
        $solr = $this->getContainer()->get('solr.client');

        foreach ($entities as $entityCollection) {
            $objectManager = $this->getObjectManager($entityCollection);

            $output->writeln(sprintf('Indexing: <info>%s</info>', $entityCollection));

            try {
                $repository = $objectManager->getRepository($entityCollection);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>No repository found for "%s", check your input</error>', $entityCollection));

                continue;
            }

            $totalSize = $this->getTotalNumberOfEntities($entityCollection);

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
     * @param string $entityClassname
     *
     * @throws \RuntimeException if no doctrine instance is configured
     *
     * @return ObjectManager
     */
    private function getObjectManager($entityClassname)
    {
        $objectManager = $this->getContainer()->get('doctrine')->getManagerForClass($entityClassname);
        if ($objectManager) {
            return $objectManager;
        }

        $objectManager = $this->getContainer()->get('doctrine_mongodb')->getManagerForClass($entityClassname);
        if ($objectManager) {
            return $objectManager;
        }

        throw new \RuntimeException(sprintf('Class "%s" is not a managed entity', $entityClassname));
    }

    /**
     * Get a list of entities which are indexable by Solr
     *
     * @param null|string $entity
     *
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
     *
     * @return int
     *
     * @throws \Exception if no primary key was found for the given entity
     */
    private function getTotalNumberOfEntities($entity)
    {
        $objectManager = $this->getObjectManager($entity);
        $repository = $objectManager->getRepository($entity);

        if ($repository instanceof DocumentRepository) {
            $totalSize = $repository->createQueryBuilder()
                ->getQuery()
                ->count();
        } else {
            $dataStoreMetadata = $objectManager->getClassMetadata($entity);

            $identifierFieldNames = $dataStoreMetadata->getIdentifierFieldNames();

            if (!count($identifierFieldNames)) {
                throw new \Exception(sprintf('No primary key found for entity %s', $entity));
            }

            $countableColumn = reset($identifierFieldNames);

            /** @var EntityRepository $repository */
            $totalSize = $repository->createQueryBuilder('size')
                ->select(sprintf('count(size.%s)', $countableColumn))
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $totalSize;
    }
}
