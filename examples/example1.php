<?php
// listタイプのストレージを使ったDNSサーバの起動
require '../vendor/autoload.php';
use Polidog\Pdns\Pdns;
use Polidog\Pdns\Exception\PdnsDomainException;

$server = new Pdns();
$server->init(function() use ($server) {
	$server->setStorage(new \Polidog\Pdns\Storage\ListStorage(array(
		'www.polidog.jp' => '133.242.145.155'
	)));
	$server->set('stdout', true);
});


try {
	$server->listen(10053);
} catch (PdnsDomainException $pe) {
	echo $pe->getMessage();
}