<?php

namespace FS\SolrBundle\Command;

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
            ->setDescription('Index all entities');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $namespaces = $this->getContainer()->get('solr.doctrine.classnameresolver.known_entity_namespaces');
        $metaInformationFactory = $this->getContainer()->get('solr.meta.information.factory');

        foreach ($namespaces->getEntityClassnames() as $classname) {
            $metaInformation = $metaInformationFactory->loadInformation($classname);

            $output->writeln(sprintf('<comment>%s</comment>', $classname));
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