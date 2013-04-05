<?php
require './vendor/autoload.php';
use Polidog\Pdns\Pdns;


$pdns = new Pdns();
try {
	$pdns->listen();
} catch (PdnsException $pe) {
	echo $pe->getMessage()."\n";
}
