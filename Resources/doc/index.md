[![Build Status](https://secure.travis-ci.org/floriansemm/SolrBundle.png?branch=master)](http://travis-ci.org/floriansemm/SolrBundle)

This Bundle provides a simple API to index and query a Solr Index. 

Please use the `development` branch for pull-request.

# Configuration

The bundle requires a working doctrine-orm or doctrine-mongodb configuration. There are no differences in the use.

## Install Solr

Solr-Server

[Tutorial]: http://davehall.com.au/blog/dave/2010/06/26/multi-core-apache-solr-ubuntu-1004-drupal-auto-provisioning

Follow the installation instructions in this [Tutorial]

## Install curl and php5-curl [Ubuntu]

	sudo aptitude update
	sudo aptitude install libcurl3 libcurl3-dev php5-curl

## Install php extension

PHP-Extension

	sudo pear install pecl/solr
	sudo sh -c 'echo "extension=solr.so" > /etc/php5/conf.d/solr.ini' # Ubuntu
	# Restart any services running php, so the new extension is loaded; i.e.: sudo service apache2 restart

## Install the Bundle

Bundle

1.  Register bundle in AppKernel.php

        # app/AppKernel.php

        $bundles = array(
            // ...
            new FS\SolrBundle\FSSolrBundle(),
            // ...
        );

2.  Add Bundle to autoload

	A. Via composer, add in your composer.json

        "require": {
            // ...  
            "floriansemm/solr-bundle": "dev-master"
        }
        
	B.  or manually, in app/autoload.php
	
	i. In symfony 2.1.4 (supposing you clone the bundle in vendor/floriansemm/solr-bundle/FS/, making available vendor/floriansemm/solr-bundle/FS/SolrBundle/FSSolrBundle.php)

        $loader->add('FS\\SolrBundle', array(__DIR__.'/../vendor/floriansemm/solr-bundle'));		

	ii. in older version it could be

        $loader->registerNamespaces(array(
            // ...
            'FS' => __DIR__.'/../vendor/bundles',
            // ...
        ));

## Multiple Indexes

You have to setup the connection options

		# app/config/config.yml
		
		fs_solr:
			solr:
				hostname: localhost
				port: 8983
                path:
                    core0: /solr/core0
                    core1: /solr/core1
            auto_index: true|false
			entity_manager: default 

# Usage #

## Annotations

To put an entity to the index, you must add some annotations to your entity:

		// your Entity

		// ....
		use FS\SolrBundle\Doctrine\Annotation as Solr;
		
		/**
		 * 
		 *
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

### Supoorted field types

Currently is a basic set of types implemented.

- string
- text
- date
- integer
- boolean

### Filter annotation

In some cases a entity should not be index. For this you have the `SynchronizationFilter` Annotation.


		/**
		 *
		 * @Solr\Document
		 * @Solr\SynchronizationFilter(callback="shouldBeIndex")
		 */
		class SomeEntity {
			/**
			 * @return boolean
			 */
			public function shouldBeIndex() {
				// put your logic here
			}
		}

The callback property specifies an callable function, which decides whether the should index or not. 	


## Solr field configuration

Solr comes with a set of predefined field-name/field-types mapping:

- title (solr-type: general_text)
- text (solr-type: general_text)
- category (solr-type: general_text)
- content_type (solr-type: string)

For details have a look into your schema.xml.

So if you have an entity with a property "category", then you don't need a type-declaration in the annotation:

			/**
			 * @Solr\Field
			 * @ORM\Column(name="category", type="text")
			 */
			private $category = '';

The field has in this case automaticaly the type "general_text".

If you persist this entity, it will put automaticlly to the index. Update and delete happens automatically too.

## Query a field of a document

To query the index you have to call some services.

		$query = $this->get('solr')->createQuery('AcmeDemoBundle:Post');
		$query->addSearchTerm('title', 'my title');
		
		$result = $result = $query->getResult();
		
The $result array contains all found entities. The solr-service does all mappings from SolrDocument
to your entity for you.

## Query all fields of a document

The pervious examples have queried only the field 'title'. You can also query all fields with a string.

    	$query = $this->get('solr')->createQuery('AcmeDemoBundle:Post');
    	$query->queryAllFields('my title);
    		
    	$result = $query->getResult();


## Define Result-Mapping

To narrow the mapping, you can use the `addField()` method.

		$query = $this->get('solr')->createQuery('AcmeDemoBundle:Post');
		$query->addSearchTerm('title', 'my title');
		$query->addField('id');
		$query->addField('text');
		
		$result = $query->getResult();

In this case only the fields id and text will be mapped (addField()), so title and created_at will be
empty. If nothing was found $result is empty.

## Index manually an entity

To index your entities manually, you can do it the following way:

		$this->get('solr')->addDocument($entity);
		$this->get('solr')->updateDocument($entity);
		$this->get('solr')->deleteDocument($entity);

`deleteDocument()` requires that the entity-id is set.

## Use document repositories

If you specify your own repository you must extend the `FS\SolrBundle\Repository\Repository` class. The useage is the same
like Doctrine-Repositories:

	$myRepository = $this->get('solr')->getRepository('AcmeDemoBundle:Post');
	$result = $myRepository->mySpecialFindMethod();
	
If you haven't declared a concrete repository in your entity and you calling `$this->get('solr')->getRepository('AcmeDemoBundle:Post')`, you will
get an instance of `FS\SolrBundle\Repository\Repository`.

## Use multiple cores

Solr supports multiple indexies. If you have different languages in your application, use can index your documents in different indexies.

The setup is easy:

Under the `path` option, you can specify your different indexies.


            path:
                    core0: /solr/core0
                    core1: /solr/core1

In this case the default core is `core0`. If you use multiple core, then the auto-index functionality should be disabled. In other case all document will index in one core. To disable use the flag `auto_index` in your config (default value is `true`). 

To index documents with the `addDocument` method requires a concrete core:

        $this->get('solr')->core('core0')->addDocument($document);


## Commands

There are comming two commands with this bundle:

* `solr:index:clear` - delete all documents in the index
* `solr:synchronize` - synchronize the db with the index

