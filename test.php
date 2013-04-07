<?php
require './vendor/autoload.php';
use Polidog\Pdns\Pdns;

$server = new Pdns();

$server->init(function() use ($server){
	$server->setStorageConfig(array(
		'type' => 'List',
		'data' => array(
			'www.polidog.jp' => '133.242.145.155'
		),
	));
	$server->set('stdout', true);
});

try {
	$server->listen(10053);
} catch (PdnsException $pe) {
	echo $pe->getMessage()."\n";
}
