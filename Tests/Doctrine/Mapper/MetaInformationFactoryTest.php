<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

/**
 *
 * @group mapper
 */
class MetaInformationFactoryTest extends \PHPUnit_Framework_TestCase
{
    private function getClassnameResolver($namespace)
    {
        $doctrineConfiguration = $this->getMock('FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver', array(), array(), '', false);
        $doctrineConfiguration->expects($this->any())
            ->method('resolveFullQualifiedClassname')
            ->will($this->returnValue($namespace));

        return $doctrineConfiguration;
    }

    private function getClassnameResolverCouldNotResolveClassname()
    {
        $doctrineConfiguration = $this->getMock('FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolver', array(), array(), '', false);
        $doctrineConfiguration->expects($this->any())
            ->method('resolveFullQualifiedClassname')
            ->will($this->throwException(new ClassnameResolverException('could not resolve classname for entity')));

        return $doctrineConfiguration;
    }

    public function testLoadInformation_ShouldLoadAll()
    {
        $testEntity = new ValidTestEntity();
        $expectedClassName = get_class($testEntity);

        $expectedDocumentName = 'validtestentity';

        $classnameResolver = $this->getClassnameResolver('FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity');

        $factory = new MetaInformationFactory();
        $factory->setClassnameResolver($classnameResolver);
        $actual = $factory->loadInformation('FSBlogBundle:ValidTestEntity');

        $this->assertTrue($actual instanceof MetaInformation);
        $this->assertEquals($expectedClassName, $actual->getClassName(), 'wrong classname');
        $this->assertEquals($expectedDocumentName, $actual->getDocumentName(), 'wrong documentname');
        $this->assertEquals(4, count($actual->getFields()), '4 fields are set');
        $this->assertEquals(5, count($actual->getFieldMapping()), '5 fields are mapped');
    }

    public function testLoadInformation_LoadInformationFromObject()
    {
        $testEntity = new ValidTestEntity();
        $expectedClassName = get_class($testEntity);

        $expectedDocumentName = 'validtestentity';

        $doctrineConfiguration = $this->getClassnameResolver('FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity');

        $factory = new MetaInformationFactory();
        $factory->setClassnameResolver($doctrineConfiguration);
        $actual = $factory->loadInformation($testEntity);

        $this->assertTrue($actual instanceof MetaInformation);
        $this->assertEquals($expectedClassName, $actual->getClassName(), 'wrong classname');
        $this->assertEquals($expectedDocumentName, $actual->getDocumentName(), 'wrong documentname');
        $this->assertEquals(4, count($actual->getFields()), '4 fields are mapped');

        $this->assertTrue($actual->hasField('title'), 'field should be able to located by field-name');
        $this->assertTrue($actual->hasField('text_t'), 'field should be able to located by field-name with suffix');

        $this->assertTrue($actual->getField('title') instanceof Field);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage no declaration for document found in entity
     */
    public function testLoadInformation_EntityHasNoDocumentDeclaration_ShouldThrowException()
    {
        $doctrineConfiguration = $this->getClassnameResolver('FS\SolrBundle\Tests\Doctrine\Mapper\NotIndexedEntity');

        $factory = new MetaInformationFactory();
        $factory->setClassnameResolver($doctrineConfiguration);
        $factory->loadInformation('FSBlogBundle:NotIndexedEntity');
    }

    /**
     * @expectedException FS\SolrBundle\Doctrine\ClassnameResolver\ClassnameResolverException
     * @expectedExceptionMessage could not resolve classname for entity
     */
    public function testLoadInformation_EntityDoesNoExists()
    {
        $doctrineConfiguration = $this->getClassnameResolverCouldNotResolveClassname();

        $factory = new MetaInformationFactory();
        $factory->setClassnameResolver($doctrineConfiguration);
        $factory->loadInformation('FSBlogBundle:UnknownEntity');
    }

    public function testLoadInformation_FromObject()
    {
        $doctrineConfiguration = $this->getClassnameResolver('FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity');

        $factory = new MetaInformationFactory();
        $factory->setClassnameResolver($doctrineConfiguration);

        $testEntity = new ValidTestEntity();
        $informations = $factory->loadInformation($testEntity);

        $expected = get_class($testEntity);
        $this->assertEquals($expected, $informations->getClassName(), 'class from object not discovered');
    }

    public function testLoadInformation_FromFullClassname()
    {
        $doctrineConfiguration = $this->getClassnameResolver('FS\SolrBundle\Tests\Doctrine\Mapper\ValidTestEntity');

        $factory = new MetaInformationFactory();
        $factory->setClassnameResolver($doctrineConfiguration);

        $entityClassname = get_class(new ValidTestEntity());
        $informations = $factory->loadInformation($entityClassname);

        $expected = $entityClassname;
        $this->assertEquals($expected, $informations->getClassName(), 'class from fullclassname not discovered');
    }
}

