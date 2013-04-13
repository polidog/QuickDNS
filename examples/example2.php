<?php
// ファイルを使用したDNSサーバの作成
require '../vendor/autoload.php';
use Polidog\QuickDns\QuickDns;
use Polidog\QuickDns\Exception\QuickDnsDomainException;

$server = new QuickDns();
$server->init(function() use ($server) {
	$server->setStorage(new \Polidog\QuickDns\Storage\FileStorage(array(
		'./list.ini',
		'./list2.ini'
	)));
	$server->set('stdout', true);
});


try {
	$server->listen(10053);
} catch (QuickDnsDomainException $pe) {
	echo $pe->getMessage();
}