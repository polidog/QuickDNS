<?php
require './vendor/autoload.php';
use Polidog\Pdns\Pdns;

$server = new Pdns();

$server->init(function() use ($server){
	$server->set('driver','List');
	$server->set('port', 53);
});

try {
	$server->listen();
} catch (PdnsException $pe) {
	echo $pe->getMessage()."\n";
}
