<?php
$file = __DIR__.'/../app/bootstrap.php.cache';



if (!file_exists($file)) {
	throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"'.__DIR__);
}

require_once $file;