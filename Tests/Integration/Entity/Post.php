<?php

namespace FS\SolrBundle\Tests\Integration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FS\SolrBundle\Doctrine\Annotation as Solr;

/**
 * Post
 *
 * @Solr\Document(index="core0")
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
     * @var string
     *
     * @Solr\Field(type="text")
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var Category
     *
     * @Solr\Field(nestedClass="Acme\DemoBundle\Entity\Category")
     *
     * @ORM\ManyToOne(targetEntity="Acme\DemoBundle\Entity\Category", inversedBy="posts", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var Tag[]
     *
     * @Solr\Field(nestedClass="Acme\DemoBundle\Entity\Tag")
     *
     * Solr\Fields(getter="getTags", fields={
     *      Solr\Field(type="integers", getter="getId", fieldAlias="tag_ids"),
     *      Solr\Field(type="strings", getter="getName", fieldAlias="tag_names")
     *      })
     *
     * @ORM\OneToMany(targetEntity="Acme\DemoBundle\Entity\Tag", mappedBy="post", cascade={"persist", "remove"})
     */
    private $tags;

    /**
     * @var string
     */
    private $lang = 'de';

    /**
     * @var int
     *
     * Solr\Field(type="string", fieldModifier="inc")
     */
    private $intField;

    /**
     * @var \DateTime
     *
     * @Solr\Field(type="datetime")
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var array
     *
     * @Solr\Field(type="strings", fieldModifier="remove")
     *
     * @ORM\Column(name="multivalues", type="json_array", nullable=true)
     */
    private $multivalues;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", nullable=true)
     */
    private $slug;

    /**
     * @var bool
     *
     * @Solr\Field(type="boolean")
     */
    private $isParent;

    public function __construct($test = null)
    {
        $this->intField = 3;
        $this->tags = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Post
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Post
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function selectCore()
    {
        if ($this->lang == 'en') {
            return 'core0';
        }

        return 'core1';
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return array
     */
    public function getMultivalues()
    {
        return $this->multivalues;
    }

    /**
     * @param array $multivalues
     */
    public function setMultivalues($multivalues)
    {
        $this->multivalues = $multivalues;
    }

    /**
     * @return bool
     */
    public function isIsParent() : bool
    {
        return $this->isParent;
    }

    /**
     * @param bool $isParent
     */
    public function setIsParent(bool $isParent)
    {
        $this->isParent = $isParent;
    }
}
