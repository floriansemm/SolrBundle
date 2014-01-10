<?php

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;

class SaveEntityFeatureContext extends BehatContext
{

    private $entity;
    private $solr;

    /**
     * @Given /^I have a Doctrine entity$/
     */
    public function iHaveADoctrineEntity()
    {
        $this->solr = $this->getMainContext()->getSolrInstance();

        $this->entity = new \FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity();
        $this->entity->setId(\FS\SolrBundle\Tests\Util\EntityIdentifier::generate());
        $this->entity->setText('a Text');
    }

    /**
     * @When /^I add this entity to solr$/
     */
    public function iAddThisEntityToSolr()
    {
        $this->solr->addDocument($this->entity);
    }

    /**
     * @Then /^should no error occurre$/
     */
    public function shouldNoErrorOccurre()
    {
        $eventDispatcher = $this->getMainContext()->getEventDispatcher();

        if ($eventDispatcher->errorsOccurred()) {
            throw new RuntimeException(sprintf('error occurred while indexing'));
        }

        $this->getMainContext()->assertInsertSuccessful();
    }

} 