<?php
namespace FS\SolrBundle\Command;

use Symfony\Bundle\DoctrineBundle\Registry;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeIndexCommand extends ContainerAwareCommand {
	protected function configure() {
		$this->setName('solr:synchronize')
			 ->addArgument('entity', InputArgument::REQUIRED, 'The entity you want to index')
			 ->setDescription('Index all entities');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$entity = $input->getArgument('entity');

		$doctrine = $this->getContainer()->get('doctrine');
		$entities = $doctrine->getRepository($entity)->findAll();
		
		if (count($entities) == 0) {
			$output->writeln('<comment>No entities found for indexing</comment>');
		} else {
			$solr = $this->getContainer()->get('solr');
			
			$synchronicedEntities = 0;
			$notSynchronicedEntities = 0;
			foreach ($entities as $entity) {
				try {
					$solr->synchronizeIndex($entity);
					
					$synchronicedEntities++;
				} catch(\Exception $e) {
					$notSynchronicedEntities++;
				}					
			}
			$output->writeln('<info>Synchronization successful</info>');
			
			$output->writeln('<comment>Synchronized Documents: '.$synchronicedEntities.'</comment>');
			$output->writeln('<comment>Not Synchronized Documents: '.$notSynchronicedEntities.'</comment>');
		}
	}
}

?>