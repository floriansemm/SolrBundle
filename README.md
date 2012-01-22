This Bundle provides a simple API to index and query a Solr Index. 

Installtion
============

Solr-Server

[Tutorial]: http://davehall.com.au/blog/dave/2010/06/26/multi-core-apache-solr-ubuntu-1004-drupal-auto-provisioning

Follow the installation instructions in this [Tutorial]

PHP-Extension

		sudo pecl install -n solr-beta

Bundle

1.  Register bundle in AppKernel.php

        # app/AppKernel.php

        $bundles = array(
            // ...
            new FS\SolrBundle\FSSolrBundle(),
            // ...
        );

2.  Add Bundle to autoload

        # app/autoload.php

        $loader->registerNamespaces(array(
            // ...
            'FS' => __DIR__.'/../vendor/bundles',
            // ...
        ));


Usage
=====

To put an entity to the index, you must add some annotations to your entity:

		// your Entity

		// ....
		use FS\SolrBundle\Doctrine\Annotation as Solr;
		
		/**
		 * FS\BlogBundle\Entity\Post
		 *
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
	
If you persist this entity, it will put automaticlly to the index.

Update and delete is not implemented yet.

To query the index you have to call some services.

		$query = $this->get('solr.query')->createQuery('AcmeDemoBundle:Post');
		$query->addSearchTerm('title', 'my title');
		$query->addField('id');
		$query->addField('text');
		
		$result = $this->get('solr')->query($query);
		
The $result array contains all found entities. The solr-service does all mappings from SolrDocument
to your entity for you. In this case only the fields id and text will be mapped (addField()), so title and created_at will be
empty. If nothing was found $result is empty.

If no field was explict add, all fields will be mapped.

		$query = $this->get('solr.query')->createQuery('AcmeDemoBundle:Post');
		$query->addSearchTerm('title', 'my title');
		
		$result = $this->get('solr')->query($query);

The pervious examples have queried only the field 'title'. You can also query all fields with a string.

    	$query = $this->get('solr.query')->createQuery('AcmeDemoBundle:Post');
    	$query->queryAllFields('my title);
    		
    	$result = $this->get('solr')->query($query); 	
		