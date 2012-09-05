<?php

$symfonyPath = '/www/htdocs/immobinet-asmir/vendor/symfony/src';

require_once $symfonyPath.'/Symfony/Component/ClassLoader/UniversalClassLoader.php'; 

$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();

$loader->registerNamespaces(array(
    'Symfony'          => $symfonyPath,
	'Goetas\DoctrineToXsd'      => __DIR__.'/../lib',
));

$loader->register();

