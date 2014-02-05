<?php
namespace FS\SolrBundle\Doctrine;

use Doctrine\ODM\MongoDB\Configuration as OdmConfiguration;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\Configuration as OrmConfiguration;
use Doctrine\ORM\ORMException;

class ClassnameResolver
{

    /**
     * @var OrmConfiguration[]
     */
    private $ormConfiguration = array();

    /**
     * @var OdmConfiguration[]
     */
    private $odmConfiguration = array();

    /**
     * @param string $entityAlias
     * @return string
     *
     * @throws ClassnameResolverException if the entityAlias could not find in any configured namespace
     */
    public function resolveFullQualifiedClassname($entityAlias)
    {
        $entityNamespace = $this->getNamespaceAlias($entityAlias);

        $foundNamespace = '';
        foreach ($this->ormConfiguration as $configuration) {
            try {
                $foundNamespace = $configuration->getEntityNamespace($entityNamespace);
            } catch (ORMException $e) {}
        }

        $realClassName = $this->getFullyQualifiedClassname($foundNamespace, $entityAlias);
        if (class_exists($realClassName)) {
            return $realClassName;
        }

        foreach ($this->odmConfiguration as $configuration) {
            try {
                $foundNamespace = $configuration->getDocumentNamespace($entityNamespace);
            } catch (MongoDBException $e) {}
        }

        $realClassName = $this->getFullyQualifiedClassname($foundNamespace, $entityAlias);
        if (class_exists($realClassName)) {
            return $realClassName;
        }

        throw new ClassnameResolverException(sprintf('could not resolve classname for entity %s', $entityAlias));
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

    /**
     * @param OdmConfiguration $configuration
     */
    public function addOdmConfiguration(OdmConfiguration $configuration)
    {
        $this->odmConfiguration[] = $configuration;
    }

    /**
     * @param OrmConfiguration $configuration
     */
    public function addOrmConfiguration(OrmConfiguration $configuration)
    {
        $this->ormConfiguration[] = $configuration;
    }
}
