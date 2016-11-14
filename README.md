SolrBundle
==========
[![Build Status](https://secure.travis-ci.org/floriansemm/SolrBundle.png?branch=master)](http://travis-ci.org/floriansemm/SolrBundle) 
[![Latest Stable Version](https://poser.pugx.org/floriansemm/solr-bundle/v/stable.svg)](https://packagist.org/packages/floriansemm/solr-bundle)
[![Total Downloads](https://poser.pugx.org/floriansemm/solr-bundle/downloads.svg)](https://packagist.org/packages/floriansemm/solr-bundle)

Introduction
------------

This Bundle provides a simple API to index and query a Solr Index. 

## Installation

Installation is a quick (I promise!) 3 step process:

1. Download SolrBundle
2. Enable the Bundle
3. Configure the SolrBundle

### Step 1: Download SolrBundle

This bundle is available on Packagist. You can install it using Composer:

```bash
$ composer require floriansemm/solr-bundle
```

### Step 2: Enable the bundle

Next, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FS\SolrBundle\FSSolrBundle(),
    );
}
```

### Step 3: Configure the SolrBundle

Finally, configure the bundle:

``` yaml
# app/config/config.yml
fs_solr:
    endpoints:
        core0:
            host: host
            port: 8983
            path: /solr/core0
            core: corename
            timeout: 5
```

### Step 4: Configure your entities

To make an entity indexed, you must add some annotations to your entity. Basic configuration requires two annotations: 
`@Solr\Document()`, `@Solr\Id()`. To index data add `@Solr\Field()` to your properties.

```php
// ....
use FS\SolrBundle\Doctrine\Annotation as Solr;
    
/**
* @Solr\Document()
* @ORM\Table()
*/
class Post
{
    /**
     * @Solr\Id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title = '';

    /**
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text = '';

   /**
    * @Solr\Field(type="date", getter="format('Y-m-d\TH:i:s.z\Z')")
    *
    * @ORM\Column(name="created_at", type="datetime")
    */
    private $created_at = null;
}
```

# Annotation reference

## `@Solr\Document` annotation

This annotation denotes that an entity should be indexed as a document. It has several optional properties: 

* `repository`
* `index`
* `indexHandler`

### Setting custom repository class with `repository` option

If you specify your own repository, the repository must extend the `FS\SolrBundle\Repository\Repository` class.

```php
/**
 * @Solr\Document(repository="My/Custom/Repository")
 */
class SomeEntity
{
    // ...
}
```

### `index` property

It is possible to specify a core the document will be indexed in:

```php
/**
 * @Solr\Document(index="core0")
 */
class SomeEntity
{
    // ...
}
```

### `indexHandler` property

By default, all documents will be indexed in the core `core0`. If your entities/documents have different languages, then you can setup
a callback method, which should return the core the entity will be indexed in.

```php
/**
 * @Solr\Document(indexHandler="indexHandler")
 */
class SomeEntity
{
    public function indexHandler()
    {
        if ($this->language == 'en') {
            return 'core0';
        }
    }
}
```

Each core must be set up in `config.yml` under `endpoints`. If you leave the `index` or `indexHandler` property empty,
then the default core will be used (first one in the `endpoints` list). To index a document in all cores, use `*` as index value.

## `@Solr\Id` annotation

This annotation is required to index an entity. The annotation has no properties. You should add this annotation to the field that will be
used as the primary identifier for the entity/document.

```php
class Post
{
    /**
     * @Solr\Id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */

    private $id;
}
```

## `@Solr\Field` annotation

This annotation should be added to properties that should be indexed. You should specify the `type` option for the annotation.

### Supported simple field types

Currently, a basic set of types is implemented:

- string(s)
- text(s)
- date(s)
- integer(s)
- float(s)
- double(s)
- long(s)
- boolean(s)

### Object relations

Indexing relations works in simplified way. Related entities will not be indexed as a new document, but only as a searchable value.
Related entities do not need a `@Solr\Document` annotation.

#### ManyToOne relation

```php
/**
 * @var Category
 *
 * @Solr\Field(type="string", getter="getTitle")
 *
 * @ORM\ManyToOne(targetEntity="Acme\DemoBundle\Entity\Category", inversedBy="posts", cascade={"persist"})
 * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
 */
private $category;
```

Related entity:

```php
class Category
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
```

#### OneToMany relation

To index a set of objects it is important to use the fieldtype `strings`.

```php
/**
 * @var Tag[]
 *
 * @Solr\Field(type="strings", getter="getName")
 *
 * @ORM\OneToMany(targetEntity="Acme\DemoBundle\Entity\Tag", mappedBy="post", cascade={"persist"})
 */
private $tags;
```

Related entity:

```php
class Tag
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
```

[For more information read the more detailed "How to index relation" guide](Resources/doc/index_relations.md)

### `@Solr\SynchronizationFilter(callback="shouldBeIndexed")` annotation

In some cases, an entity should not be indexed. For this, you have the `SynchronizationFilter` annotation to run a filter-callback.

```php
/**
 * // ....
 * @Solr\SynchronizationFilter(callback="shouldBeIndexed")
 */
class SomeEntity
{
    /**
     * @return boolean
    */
    public function shouldBeIndexed()
    {
        // put your logic here
    }
}
```

The callback property specifies an callable function, which should return a boolean value, specifying whether a concrete 
entity should be indexed.

## Queries

### Query a field of a document

Querying the index is done via the `solr.client` service:

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->addSearchTerm('title', 'my title');
$query->addSearchTerm('collection_field', array('value1', 'value2'));

$result = $query->getResult();
```

or 

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'title' => 'my title',
    'collection_field' => array('value1', 'value2')
));
```

### Query all fields of a document

The previous examples were only querying the `title` field. You can also query all fields with a string.

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->queryAllFields('my title');

$result = $query->getResult();
```

### Define a custom query string

If you need more flexiblity in your queries you can define your own query strings:

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->setCustomQuery('id:post_* AND (author_s:Name1 OR author_s:Name2)');

$result = $query->getResult();
```

### The QueryBuilder

The query-builder based on [https://github.com/minimalcode-org/search](minimalcode-org/search) Criteria API. 

```php
$queryBuilder = $this->get('solr.client')->getQueryBuilder('AcmeDemoBundle:Post');
$result = $queryBuilder
    ->where('author')
        ->is('Name1')
    ->orWhere('author')
        ->is('Name2')
    ->getQuery()
    ->getResult();

```

To keep your code clean you should move the select-criteria in a repository-class:

```php

class YourRepository extends Repository
{
    public function findAuthor($name1, $name2)
    {
        return $this->getQueryBuilder()
            ->where('author')
                ->is($name1)
            ->orWhere('author')
                ->is($name2)
            ->getQuery()
            ->getResult();
    }
}

```


### Define Result-Mapping

To narrow the mapping, you can use the `addField()` method.

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->addSearchTerm('title', 'my title');
$query->addField('id');
$query->addField('text');

$result = $query->getResult();
```

In this case, only the `id` and `text` fields will be mapped (addField()), `title` and created_at` fields will be
empty. If nothing was found $result is empty.

By default, the result set contains 10 rows. You can increase this value:

```php
$query->setRows(1000000);
```

### Configure HydrationModes

HydrationMode tells the bundle how to create an entity from a document.

1. `FS\SolrBundle\Doctrine\Hydration\HydrationModes::HYDRATE_INDEX` - use only the data from solr
2. `FS\SolrBundle\Doctrine\Hydration\HydrationModes::HYDRATE_DOCTRINE` - merge the data from solr with the entire doctrine-entity

With a custom query:

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->setHydrationMode($mode)
```

With a custom document-repository you have to set the property `$hydrationMode` itself:

```php
public function find($id)
{
    $this->hydrationMode = HydrationModes::HYDRATE_INDEX;
    
    return parent::find($id);
}
```

## Repositories

Your should define your own repository-class to make your custom queries reuseable. How to configure a repository for a document have a look at [the annotation section](https://github.com/floriansemm/SolrBundle#setting-custom-repository-class-with-repository-option)

```php
namespace AppBundle\Search;

use FS\SolrBundle\Repository\Repository;

class ProviderRepository extends Repository
{
    public function findPost($what)
    {
        $query = $this->solr->createQuery('AcmeDemoBundle:Post');
        // some query-magic here

        return $query->getResult();
    }
}
```

In your repository you have full access to the querybuilder.

## Commands

Here's all the commands provided by this bundle:

* `solr:index:clear` - delete all documents in the index
* `solr:index:populate` - synchronize the db with the index
* `solr:schema:show` - shows your configured documents

## Extend Solarium

To extend Solarium with your own plugins, create a tagged service:

```xml
<tag name="solarium.client.plugin" plugin-name="yourPluginName"/>
```

To hook into the [Solarium events](http://solarium.readthedocs.io/en/stable/customizing-solarium/#plugin-system) create a common Symfony event-listener:

```xml
<tag name="kernel.event_listener" event="solarium.core.preExecuteRequest" method="preExecuteRequest" />
```
