<?php

namespace FS\SolrBundle\Doctrine;

class ClassnameResolverException extends \RuntimeException
{
    public static function fromKnownNamespaces($entityNamespaceAlias, array $knownNamespaces)
    {
        $flattenListOfAllAliases = implode(',', $knownNamespaces);

        return new ClassnameResolverException(
            sprintf('could not resolve classname for entity %s, known aliase(s) are: %s', $entityNamespaceAlias, $flattenListOfAllAliases)
        );
    }
} 