<?php
$file = __DIR__.'/../vendor/autoload.php';



if (!file_exists($file)) {
	throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"'.__DIR__);
}

require_once $file;