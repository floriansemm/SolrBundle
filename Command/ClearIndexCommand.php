<?php
namespace FS\SolrBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('solr:index:clear')
            ->setDescription('Clear the whole index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $solr = $this->getContainer()->get('solr.client.default');

        try {
            $solr->clearIndex();

            $output->writeln('<info>Index successful cleared</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
