<?php

namespace FS\SolrBundle\Command;

use FS\SolrBundle\Doctrine\Mapper\SolrMappingException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowSchemaCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('solr:schema:show')
            ->setDescription('Show configured entities and their fields');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $namespaces = $this->getContainer()->get('solr.doctrine.classnameresolver.known_entity_namespaces');
        $metaInformationFactory = $this->getContainer()->get('solr.meta.information.factory');

        foreach ($namespaces->getEntityClassnames() as $classname) {
            try {
                $metaInformation = $metaInformationFactory->loadInformation($classname);
            } catch (SolrMappingException $e) {
                $output->writeln(sprintf('<info>%s</info>', $e->getMessage()));
                continue;
            }

            $nested = '';
            if ($metaInformation->isNested()) {
                $nested = '(nested)';
            }
            $output->writeln(sprintf('<comment>%s</comment> %s', $classname, $nested));
            $output->writeln(sprintf('Documentname: %s', $metaInformation->getDocumentName()));
            $output->writeln(sprintf('Document Boost: %s', $metaInformation->getBoost()?$metaInformation->getBoost(): '-'));

            $table = new Table($output);
            $table->setHeaders(array('Property', 'Document Fieldname', 'Boost'));

            foreach ($metaInformation->getFieldMapping() as $documentField => $property) {
                $field = $metaInformation->getField($documentField);

                if ($field === null) {
                    continue;
                }

                $table->addRow(array($property, $documentField, $field->boost));
            }
            $table->render();
        }

    }


}