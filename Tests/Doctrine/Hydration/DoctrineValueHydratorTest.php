<?php


namespace FS\SolrBundle\Tests\Doctrine\Hydration;


use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Hydration\DoctrineValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

class DoctrineValueHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function skipArrays()
    {
        $hydrator = new DoctrineValueHydrator();

        $this->assertFalse($hydrator->mapValue('createdAt', array(), new MetaInformation()));
    }

    /**
     * @test
     */
    public function skipObjects()
    {
        $hydrator = new DoctrineValueHydrator();

        $field = new Field(array('type' => 'datetime'));
        $field->name = 'createdAt';
        $field->getter = 'format(\'Y-m-d\TH:i:s.z\Z\')';

        $metaInformation = new MetaInformation();
        $metaInformation->setFields(array($field));

        $this->assertFalse($hydrator->mapValue('createdAt', new \DateTime(), $metaInformation));
    }

    /**
     * @test
     */
    public function mapCommonType()
    {
        $hydrator = new DoctrineValueHydrator();

        $field = new Field(array('type' => 'string'));
        $field->name = 'title';

        $metaInformation = new MetaInformation();
        $metaInformation->setFields(array($field));

        $this->assertTrue($hydrator->mapValue('title_s', 'a title', $metaInformation));
    }
}
