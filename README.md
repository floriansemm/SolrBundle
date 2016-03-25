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

Finally, enable the bundle in the kernel

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

See section `Specify cores` for more detaild information about how to setup cores.

## Usage

### Annotations

To put an entity to the index, you must add some annotations to your entity:

```php
// your Entity

// ....
use FS\SolrBundle\Doctrine\Annotation as Solr;
    
/**
* @Solr\Document(repository="Full\Qualified\Class\Name")
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
     *
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title = '';

    /**
     * 
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text = '';

   /**
    * @Solr\Field(type="date")
    *
    * @ORM\Column(name="created_at", type="datetime")
    */
    private $created_at = null;
}
```

## `@Solr\Document` annotation

Entities must have this annotation to mark them as document.

### Setup custom repository class

If you specify your own repository you must extend the `FS\SolrBundle\Repository\Repository` class.

```php
/**
 * @Solr\Document(repository="My/Custom/Repository")
 */
class SomeEntity
{
    // ...
}
```


### Specify a core for a document

It is possible to specify a core dedicated to a document

```php
/**
 * @Solr\Document(index="core0")
 */
class SomeEntity
{
    // ...
}
```

All documents will be indexed in the core `core0`. If your entities/document have different languages then you can setup
a callback method, which returns the preferred core for the entity.

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

Each core must setup up in the config.yml under `endpoints`. If you leave the `index` or `indexHandler` property empty,
then a default core will be used (first in the `endpoints` list). To index a document in all cores use `*` as index value:

## `@Solr\Field` annotation

Add this annotation with a type to a property an the value will be indexed. 

### Supported simple field types

Currently is a basic set of types implemented.

- string(s)
- text(s)
- date(s)
- integer(s)
- float(s)
- double(s)
- long(s)
- boolean(s)

### Object relations

Indexing of relations works in simplified way. Related entities will not indexed as a new document only a searchable value.
Related entity do not need a `@Solr\Document` annotation.

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

### `@Solr\SynchronizationFilter(callback="shouldBeIndex")` annotation

In some cases a entity should not be index. For this you have the `SynchronizationFilter` annotation to run a filter-callback.

```php
/**
 * // ....
 * @Solr\SynchronizationFilter(callback="shouldBeIndex")
 */
class SomeEntity
{
    /**
     * @return boolean
    */
    public function shouldBeIndex()
    {
        // put your logic here
    }
}
```

The callback property specifies an callable function, which decides whether the should index or not.    

## Queries

### Query a field of a document

To query the index you have to call some services.

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->addSearchTerm('title', 'my title');

$result = $query->getResult();
```

or 

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'title' => 'my title'
));
```

### Query all fields of a document

The pervious examples have queried only the field 'title'. You can also query all fields with a string.

```php
$query = $this->get('solr.client')->createQuery('AcmeDemoBundle:Post');
$query->queryAllFields('my title');

$result = $query->getResult();
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

In this case only the fields id and text will be mapped (addField()), so title and created_at will be
empty. If nothing was found $result is empty.

The result contains by default 10 rows. You can increase this value:

```php
$query->setRows(1000000);
```

### Configure HydrationModes

HydrationMode tells the Bundle how to create an entity from a document.

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

## Commands

There are two commands with this bundle:

* `solr:index:clear` - delete all documents in the index
* `solr:index:populate` - synchronize the db with the index
* `solr:schema:show` - shows your configured documents
