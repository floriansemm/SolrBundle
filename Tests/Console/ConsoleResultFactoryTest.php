<?php

namespace FS\Console;


use FS\SolrBundle\Console\ConsoleResultFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Event\ErrorEvent;
use FS\SolrBundle\Event\Event;
use FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity;

class ConsoleResultFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function resultFromErrorEventContainsExceptionMessage()
    {
        $error = new ErrorEvent();
        $error->setException(new \Exception('message'));

        $factory = new ConsoleResultFactory();
        $result = $factory->fromEvent($error);

        $this->assertEquals('message', $result->getErrorMessage());
    }

    /**
     * @test
     */
    public function resultNotContainsIdAndEntityWhenMetaInformationNull()
    {
        $event = new Event(null, null, '');

        $factory = new ConsoleResultFactory();
        $result = $factory->fromEvent($event);

        $this->assertEquals(null, $result->getResultId());
        $this->assertEquals('', $result->getEntityClassname());
        $this->assertEquals('', $result->getErrorMessage());
    }

    /**
     * @test
     */
    public function resultFromSuccessEventContainsNoMessage()
    {
        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metaInformation = new MetaInformation();
        $metaInformation->setClassName('an entity');
        $metaInformation->setEntity($entity);

        $event = new Event(null, $metaInformation, '');

        $factory = new ConsoleResultFactory();
        $result = $factory->fromEvent($event);

        $this->assertEquals(1, $result->getResultId());
        $this->assertEquals('an entity', $result->getEntityClassname());
        $this->assertEquals('', $result->getErrorMessage());
    }
}
 