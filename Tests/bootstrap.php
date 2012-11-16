<?php
$file = __DIR__.'/../../../../../../app/bootstrap.php.cache';



if (!file_exists($file)) {
	throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $file;