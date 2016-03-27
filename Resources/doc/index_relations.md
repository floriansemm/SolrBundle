# Index OneToOne/ManyToOne relation

Given you have the following entity with a ManyToOne relation to `Category`.

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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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

The index data would look something like this:

```json
"docs": [
  {
    "id": "post_1",
    "title_s": "a post title",
    "category_s": "post category #1",
    "_version_": 1529771282767282200
  }
]
```

The result of search-queries like this 

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'category' => 'post category #1'
));
```

contain a `Post` entity with a `Category` entity. The indexed data `post category #1` was replaced by DB reference.

# Index OneToMany relation

Given you have the following `Post` entity with a OneToMany relation to `Tag`.

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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @var Tag[]
     *
     * @Solr\Field(type="strings", getter="getName")
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

Now `Post` can be searched like this

```php
$posts = $this->get('solr.client')->getRepository('AcmeDemoBundle:Post')->findOneBy(array(
    'tags' => 'tag #1'
));
```
        