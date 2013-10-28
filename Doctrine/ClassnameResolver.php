<?php
namespace FS\SolrBundle\Doctrine;

use Doctrine\ODM\MongoDB\Configuration as OdmConfiguration;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\Configuration as OrmConfiguration;
use Doctrine\ORM\ORMException;

class ClassnameResolver
{

    /**
     * @var array
     */
    private $ormConfiguration = array();

    /**
     * @var array
     */
    private $odmConfiguration = array();

    /**
     * @param string $entity
     * @return string
     *
     * @throws ClassnameResolverException
     */
    public function resolveFullQualifiedClassname($entity)
    {
        $entityNamespace = $this->getNamespaceAlias($entity);

        $foundNamespace = '';
        foreach ($this->ormConfiguration as $configuration) {
            try {
                $foundNamespace = $configuration->getEntityNamespace($entityNamespace);
            } catch (ORMException $e) {}
        }

        if ($foundNamespace != '') {
            $realClassName = $foundNamespace . '\\' . $this->getClassname($entity);
            if (class_exists($realClassName)) {
                return $realClassName;
            }
        }

        foreach ($this->odmConfiguration as $configuration) {
            try {
                $foundNamespace = $configuration->getDocumentNamespace($entityNamespace);
            } catch (MongoDBException $e) {}
        }

        if ($foundNamespace != '') {
            $realClassName = $foundNamespace . '\\' . $this->getClassname($entity);
            if (class_exists($realClassName)) {
                return $realClassName;
            }
        }

        throw new ClassnameResolverException(sprintf('could not resolve classname for entity %s', $entity));
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
