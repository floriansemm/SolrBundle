<?php

namespace FS\SolrBundle\Doctrine\ClassnameResolver;

class ClassnameResolverException extends \RuntimeException
{
    /**
     * @param string $entityNamespaceAlias
     * @param array $knownNamespaces
     *
     * @return ClassnameResolverException
     */
    public static function fromKnownNamespaces($entityNamespaceAlias, array $knownNamespaces)
    {
        $flattenListOfAllAliases = implode(',', $knownNamespaces);

        return new ClassnameResolverException(
            sprintf('could not resolve classname for entity %s, known aliase(s) are: %s', $entityNamespaceAlias, $flattenListOfAllAliases)
        );
    }
} 