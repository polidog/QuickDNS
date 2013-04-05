<?php
require './vendor/autoload.php';
use Polidog\Pdns\Pdns;

$server = new Pdns();

$server->init(function() use ($server){
	$server->set('driver','List');
	$server->set('stdout', true);
});

try {
	$server->listen(10053);
} catch (PdnsException $pe) {
	echo $pe->getMessage()."\n";
}
