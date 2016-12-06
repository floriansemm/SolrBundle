<?php

namespace FS\SolrBundle\Tests\Doctrine\ClassnameResolver;


use Doctrine\ORM\Configuration as OrmConfiguration;
use Doctrine\ODM\MongoDB\Configuration as OdmConfiguration;
use FS\SolrBundle\Doctrine\ClassnameResolver\KnownNamespaceAliases;

class KnownNamespaceAliasesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function addAliasFromMultipleOrmConfigurations()
    {
        $config1 = $this->createMock(OrmConfiguration::class);
        $config1->expects($this->once())
            ->method('getEntityNamespaces')
            ->will($this->returnValue(array('AcmeDemoBundle')));

        $config2 = $this->createMock(OrmConfiguration::class);
        $config2->expects($this->once())
            ->method('getEntityNamespaces')
            ->will($this->returnValue(array('AcmeBlogBundle')));

        $knownAliases = new KnownNamespaceAliases();
        $knownAliases->addEntityNamespaces($config1);
        $knownAliases->addEntityNamespaces($config2);

        $this->assertTrue(in_array('AcmeDemoBundle', $knownAliases->getAllNamespaceAliases()));
        $this->assertTrue(in_array('AcmeBlogBundle', $knownAliases->getAllNamespaceAliases()));
    }

    /**
     * @test
     */
    public function addAliasFromMultipleOdmConfigurations()
    {
        $config1 = $this->createMock(OdmConfiguration::class);
        $config1->expects($this->once())
            ->method('getDocumentNamespaces')
            ->will($this->returnValue(array('AcmeDemoBundle')));

        $config2 = $this->createMock(OdmConfiguration::class);
        $config2->expects($this->once())
            ->method('getDocumentNamespaces')
            ->will($this->returnValue(array('AcmeBlogBundle')));

        $knownAliases = new KnownNamespaceAliases();
        $knownAliases->addDocumentNamespaces($config1);
        $knownAliases->addDocumentNamespaces($config2);

        $this->assertTrue(in_array('AcmeDemoBundle', $knownAliases->getAllNamespaceAliases()));
        $this->assertTrue(in_array('AcmeBlogBundle', $knownAliases->getAllNamespaceAliases()));
    }

    /**
     * @test
     */
    public function knowAliasHasAValidNamespace()
    {
        $config1 = $this->createMock(OdmConfiguration::class);
        $config1->expects($this->once())
            ->method('getDocumentNamespaces')
            ->will($this->returnValue(array('AcmeDemoBundle' => 'Acme\DemoBundle\Document')));

        $config2 = $this->createMock(OdmConfiguration::class);
        $config2->expects($this->once())
            ->method('getDocumentNamespaces')
            ->will($this->returnValue(array('AcmeBlogBundle' => 'Acme\BlogBundle\Document')));

        $knownAliases = new KnownNamespaceAliases();
        $knownAliases->addDocumentNamespaces($config1);
        $knownAliases->addDocumentNamespaces($config2);

        $this->assertEquals('Acme\DemoBundle\Document', $knownAliases->getFullyQualifiedNamespace('AcmeDemoBundle'));
    }
}
 