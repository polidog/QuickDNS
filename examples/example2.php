<?php
// ファイルを使用したDNSサーバの作成
require '../vendor/autoload.php';
use Polidog\Pdns\Pdns;
use Polidog\Pdns\Exception\PdnsDomainException;

$server = new Pdns();
$server->init(function() use ($server) {
	$server->setStorage(new \Polidog\Pdns\Storage\FileStorage(array(
		'./list.ini',
		'./list2.ini'
	)));
	$server->set('stdout', true);
});


try {
	$server->listen(10053);
} catch (PdnsDomainException $pe) {
	echo $pe->getMessage();
}