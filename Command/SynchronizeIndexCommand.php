<?php
namespace FS\SolrBundle\Command;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('solr:synchronize')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity you want to index')
            ->addOption(
                'source',
                null,
                InputArgument::OPTIONAL,
                'specify a source from where to load entities [relational, mongodb]',
                'relational'
            )
            ->setDescription('Index all entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getArgument('entity');
        $source = $input->getOption('source');

        $objectManager = $this->getObjectManager($source);

        try {
            $repository = $objectManager->getRepository($entity);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>No repository found for "%s", check your input</error>', $entity));

            return;
        }

        $entities = $repository->findAll();

        if (count($entities) == 0) {
            $output->writeln('<comment>No entities found for indexing</comment>');

            return;
        }

        $solr = $this->getContainer()->get('solr.client.default');

        foreach ($entities as $entity) {
            try {
                $solr->synchronizeIndex($entity);

            } catch (\Exception $e) {}
        }

        $results = $this->getContainer()->get('solr.console.command.results');
        if ($results->hasErrors()) {
            $output->writeln('<info>Synchronization finished with errors!</info>');
        } else {
            $output->writeln('<info>Synchronization successful</info>');
        }

        $output->writeln(sprintf('<comment>Synchronized Documents: %s</comment>', $results->getSucceed()));
        $output->writeln(sprintf('<comment>Not Synchronized Documents: %s</comment>', $results->getErrored()));

    }

    /**
     * @param string $source
     * @throws \InvalidArgumentException if $source is unknown
     * @throws \RuntimeException if no doctrine instance is configured
     * @return AbstractManagerRegistry
     */
    private function getObjectManager($source)
    {
        $objectManager = null;

        if ($source == 'relational') {
            $objectManager = $this->getContainer()->get('doctrine');
        } else {
            if ($source == 'mongodb') {
                $objectManager = $this->getContainer()->get('doctrine_mongodb');
            } else {
                throw new \InvalidArgumentException(sprintf('Unknown source %s', $source));
            }
        }

        return $objectManager;
    }
}
