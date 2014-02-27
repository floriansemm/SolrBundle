<?php
namespace FS\SolrBundle\Doctrine\ClassnameResolver;

class ClassnameResolver
{

    /**
     * @var KnownNamespaceAliases
     */
    private $knownNamespaceAliases;

    /**
     * @param KnownNamespaceAliases $knownNamespaceAliases
     */
    public function __construct(KnownNamespaceAliases $knownNamespaceAliases)
    {
        $this->knownNamespaceAliases = $knownNamespaceAliases;
    }

    /**
     * @param string $entityAlias
     * @return string
     *
     * @throws ClassnameResolverException if the entityAlias could not find in any configured namespace or the class
     * does not exist
     */
    public function resolveFullQualifiedClassname($entityAlias)
    {
        $entityNamespaceAlias = $this->getNamespaceAlias($entityAlias);

        if ($this->knownNamespaceAliases->isKnownNamespaceAlias($entityNamespaceAlias) === false) {
            $e = ClassnameResolverException::fromKnownNamespaces(
                $entityNamespaceAlias,
                $this->knownNamespaceAliases->getAllNamespaceAliases()
            );

            throw $e;
        }

        $foundNamespace = $this->knownNamespaceAliases->getFullyQualifiedNamespace($entityNamespaceAlias);

        $realClassName = $this->getFullyQualifiedClassname($foundNamespace, $entityAlias);
        if (class_exists($realClassName) === false) {
            throw new ClassnameResolverException(sprintf('class %s does not exist', $realClassName));
        }

        return $realClassName;
    }

    /**
     * @param string $entity
     * @return string
     */
    public function getNamespaceAlias($entity)
    {
        list($namespaceAlias, $simpleClassName) = explode(':', $entity);

        return $namespaceAlias;
    }

    /**
     * @param string $entity
     * @return string
     */
    public function getClassname($entity)
    {
        list($namespaceAlias, $simpleClassName) = explode(':', $entity);

        return $simpleClassName;
    }

    /**
     * @param string $namespace
     * @param string $entityAlias
     * @return string
     */
    private function getFullyQualifiedClassname($namespace, $entityAlias)
    {
        $realClassName = $namespace . '\\' . $this->getClassname($entityAlias);

        return $realClassName;
    }


}
