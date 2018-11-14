# Index OneToOne/ManyToOne relation

Given you have the following entity with a ManyToOne relation to `Category`.

```php
<?php

// ....

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * @Solr\Document()
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Post
{
    /**
     * @var integer
     *
     * orm stuff
     *
     * @Solr\Id
     */
    private $id;

    /**
     * @var string
     *
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var Category
     *
     * @Solr\Field(type="string", getter="getTitle")
     *
     * @ORM\ManyToOne(targetEntity="Acme\DemoBundle\Entity\Category", inversedBy="posts", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    // ... some getter / setter
}
```

You have now different ways to index the `category` relation:

- flat string representation
- full object

## Flat string representation

The important configuration is `@Solr\Field(type="string", getter="getTitle")`. This tells Solr to call `Category::getTitle()` when the `Post` is indexed.
 
```php

$category = new Category();
$category->setTitle('post category #1');

$post = new Post();
$post->setTitle('a post title');
$post->setCategory($category);

$em = $this->getDoctrine()->getManager();
$em->persist($post);
$em->flush();
```

### Quering the relation 

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'category' => 'post category #1'
));
```

# Index OneToMany relation

Given you have the following `Post` entity with a OneToMany relation to `Tag`.

Again you can index the collection in two ways:

- flat strings representation
- full objects

## flat strings representation

```php
<?php

// ....

use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * Post
 *
 * @Solr\Document()
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Post
{
    /**
     * // orm stuff
     *
     * @Solr\Id
     */
    private $id;

    /**
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @Solr\Field(type="strings", getter="getName")
     * 
     * @ORM\OneToMany(targetEntity="Acme\DemoBundle\Entity\Tag", mappedBy="post", cascade={"persist"})
     */
    private $tags;

    // ... some getter / setter
}
```

All `Tag`s will be transformed to a set of strings `@Solr\Field(type="strings", getter="getName")`. 

```php
$post = new Post();
$post->setTitle($postTitle);
$post->setText('relation');
$post->setTags(array(
    new Tag('tag #1'),
    new Tag('tag #2'),
    new Tag('tag #3')
));

$em = $this->getDoctrine()->getManager();
$em->persist($post);
$em->flush();
```

Which will result in a document like this:

```json
"docs": [
  {
    "id": "post_391",
    "title_s": "post 25.03.2016",
    "text_t": "relation",
    "tags_ss": [
      "tag #1",
      "tag #2",
      "tag #3"
    ],
    "_version_": 1529771282767282200
  }
]
```

### Quering the strings collection

Now `Post` can be searched like this

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'tags' => 'tag #1'
));
```
If you want to index multiple fields you can use the following syntax for defining multiple fields:

```php

    /**
     * @var Tag[]
     * 
     * @Solr\Fields(getter="getTags", fields={
     *      @Solr\Field(type="integers", getter="getId", fieldAlias="id"),
     *      @Solr\Field(type="strings", getter="getName", fieldAlias="name")
     *      })
     * @ORM\OneToMany(targetEntity="Acme\DemoBundle\Entity\Tag", mappedBy="post", cascade={"persist"})
     */
    private $tags;
        
```

The fieldAlias is required in this format and will result in the following document:

```json
"docs": [
  {
    "id": "post_391",
    "title_s": "post 25.03.2016",
    "text_t": "relation",
    "name_ss": [
      "tag #1",
      "tag #2",
      "tag #3"
    ],
    "id_is": [
      "1",
      "2",
      "3"
    ]
    "_version_": 1529771282767282200
  }
]
```

You can now search the repository with the name or id variable:

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'id' => '1'
));

$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'name' => 'tag #1'
));
```

        
=======
   
## Index full objects

Post entity:

```php
    /**
     * @Solr\Field(type="strings", nestedClass="Acme\DemoBundle\Entity\Tag")
     * 
     * @ORM\OneToMany(targetEntity="Acme\DemoBundle\Entity\Tag", mappedBy="post", cascade={"persist"})
     */
    private $tags;
```

Mark the `Tag` entity as Nested

```php
/**
 * Tag
 *
 * @Solr\Nested()
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Tag
{
    /**
     * @var integer
     *
     * @Solr\Id
     *
     * orm stuff
     */
    private $id;

    /**
     * @var string
     *
     * @Solr\Field(type="string")
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    // getter and setter
}
```

## Querying the collection

Now `Post` can be searched like this

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'tags.name' => 'tag #1'
));
```
