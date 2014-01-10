<?php

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;

class SaveEntityFeatureContext extends BehatContext
{
    /**
     * @Given /^I have a Doctrine entity$/
     */
    public function iHaveADoctrineEntity()
    {
        $solr = FeatureContext::getSolrInstance();

        $entity = new \FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity();
        $entity->setId(1235);
        $entity->setText('a Text');

        $solr->addDocument($entity);

        throw new PendingException();
    }
} 