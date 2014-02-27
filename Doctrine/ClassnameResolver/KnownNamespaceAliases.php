<?php

namespace FS\SolrBundle\Doctrine\ClassnameResolver;

use Doctrine\ODM\MongoDB\Configuration as OdmConfiguration;
use Doctrine\ORM\Configuration as OrmConfiguration;

class KnownNamespaceAliases
{
    /**
     * @var array Namespace-Alias => Full Entity/Documentnamespace
     */
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

    /**
     * @param string $alias
     * @return bool
     */
    public function isKnownNamespaceAlias($alias)
    {
        return isset($this->knownNamespaceAlias[$alias]);
    }

    /**
     * @param string $alias
     * @return string
     */
    public function getFullyQualifiedNamespace($alias)
    {
        if ($this->isKnownNamespaceAlias($alias)) {
            return $this->knownNamespaceAlias[$alias];
        }

        return '';
    }

    /**
     * @return array
     */
    public function getAllNamespaceAliases()
    {
        return array_keys($this->knownNamespaceAlias);
    }
} 