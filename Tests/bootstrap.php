<?php



$file = __DIR__.'/../vendor/autoload.php';

if (!file_exists($file)) {
	throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"'.__DIR__);
}

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
\Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

require_once $file;