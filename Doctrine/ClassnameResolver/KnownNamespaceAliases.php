<?php

namespace FS\SolrBundle\Doctrine\ClassnameResolver;

use Doctrine\ODM\MongoDB\Configuration as OdmConfiguration;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\Configuration as OrmConfiguration;
use Doctrine\ORM\ORMException;

class KnownNamespaceAliases
{

    private $knownNamespaceAlias = array();

    /**
     * @param OdmConfiguration $configuration
     */
    public function addDocumentNamespaces(OdmConfiguration $configuration)
    {
        $this->knownNamespaceAlias = array_merge($this->knownNamespaceAlias, $configuration->getDocumentNamespaces());
    }

    /**
     * @param OrmConfiguration $configuration
     */
    public function addEntityNamespaces(OrmConfiguration $configuration)
    {
        $this->knownNamespaceAlias = array_merge($this->knownNamespaceAlias, $configuration->getEntityNamespaces());
    }

    public function isKnownNamespaceAlias($alias)
    {
        return isset($this->knownNamespaceAlias[$alias]);
    }

    public function getFullyQualifiedNamespace($alias)
    {
        if ($this->isKnownNamespaceAlias($alias)) {
            return $this->knownNamespaceAlias[$alias];
        }

        return '';
    }

    public function getAllNamespaceAliases()
    {
        return array_keys($this->knownNamespaceAlias);
    }
} 