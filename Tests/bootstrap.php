<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;


$file = __DIR__.'/../vendor/autoload.php';

if (!file_exists($file)) {
	throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"'.__DIR__);
}

AnnotationDriver::registerAnnotationClasses();
AnnotationRegistry::registerLoader(array($file, 'loadClass'));

require_once $file;