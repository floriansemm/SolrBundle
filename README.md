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

Configuration
=============

You have to setup the connection options

		# app/config/config.yml
		
		fs_solr:
			solr:
				hostname: localhost
				port: 8983
			entity_manager: default 

Multiple connections are planed.

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
	
If you persist this entity, it will put automaticlly to the index. Update and delete happens automatically too.

To query the index you have to call some services.

		$query = $this->get('solr')->createQuery('AcmeDemoBundle:Post');
		$query->addSearchTerm('title', 'my title');
		$query->addField('id');
		$query->addField('text');
		
		$result = $query->getResult();
		
The $result array contains all found entities. The solr-service does all mappings from SolrDocument
to your entity for you. In this case only the fields id and text will be mapped (addField()), so title and created_at will be
empty. If nothing was found $result is empty.

If no field was explict add, all fields will be mapped.

		$query = $this->get('solr')->createQuery('AcmeDemoBundle:Post');
		$query->addSearchTerm('title', 'my title');
		
		$result = $result = $query->getResult();

The pervious examples have queried only the field 'title'. You can also query all fields with a string.

    	$query = $this->get('solr')->createQuery('AcmeDemoBundle:Post');
    	$query->queryAllFields('my title);
    		
    	$result = $query->getResult();

To index your entities manually, you can do it the following way:

		$this->get('solr')->addDocument($entity);
		$this->get('solr')->updateDocument($entity);
		$this->get('solr')->deleteDocument($entity);

The delete action needs the id of the entity.		

Commands
========

There are comming two commands with this bundle:

* `solr:index:clear` - delete all documents in the index
* `solr:synchronize` - synchronize the db with the index. You have to specify an entity.