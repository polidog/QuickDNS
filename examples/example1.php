<?php
// listタイプのストレージを使ったDNSサーバの起動
require '../vendor/autoload.php';
use Polidog\QuickDns\QuickDns;
use Polidog\QuickDns\Exception\QuickDnsDomainException;

$server = new QuickDns();
$server->init(function() use ($server) {
	$server->setStorage(new \Polidog\QuickDns\Storage\ListStorage(array(
		'www.polidog.jp' => '133.242.145.155'
	)));
	$server->set('stdout', true);
});


try {
	$server->listen(10053);
} catch (QuickDnsDomainException $pe) {
	echo $pe->getMessage();
}