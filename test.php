<?php
require './vendor/autoload.php';
use Polidog\QuickDns\QuickDns;

$server = new QuickDns();

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
} catch (QuickDnsException $pe) {
	echo $pe->getMessage()."\n";
}
