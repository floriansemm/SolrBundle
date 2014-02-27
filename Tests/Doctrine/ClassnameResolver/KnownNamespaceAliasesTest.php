<?php

namespace FS\SolrBundle\Tests\Doctrine\ClassnameResolver;


class KnownNamespaceAliasesTest extends \PHPUnit_Framework_TestCase {

    public function test()
    {
        $config = $this->getMock('Doctrine\ORM\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getEntityNamespace')
            ->will($this->returnValue($knownNamespace));

        $config = $this->getMock('Doctrine\ODM\MongoDB\Configuration', array(), array(), '', false);
        $config->expects($this->once())
            ->method('getDocumentNamespace')
            ->will($this->returnValue($knownNamespace));
    }
}
 