# PHPでDNSの正引きをするためのライブラリ！！！
Host書き直すのだるいし、ローカル内で共有したい時なんかに使えます。
※絶対にLAN内で使用するようにしてください。。。
ネットワークに不具合が起きても責任もてないし・・・使用する時は自己責任でお願いします。

## ライセンス
MIT License

## 動作環境
php5.3以上
みなさん、php5.5使いましょうヽ(｀・ω・´)ﾉ ｳﾜｧｧﾝ!


## インストール
1. composerをget
```
$ curl -s http://getcomposer.org/installer | php
```
2. composer.jsonを用意する
```
    {
        "require": {
		    "polidog/quick-dns": "*",
        },
    }    
```
3. composer installする
```
$ composer.phar install
```

## 動かし方
examplesディレクトリの中を見てもらえれば解りますが、基本的には以下のような流れになります。
1. オートローダーを設定する
てかcomposer installとかすれば多分勝手にautoloder作られるよ！
2. サーバーインスタンス生成する
3. setStorageConfigのなかでkeyがdataの入れ鵜tにドメインとipを設定する
ドメイン名をキー、valueをIPを指定する
※FQDNじゃなくてドメイン名ね！

あとはlistenメソッドを実行するだけ！！！
引数でポート指定できるよー！！


4.実際に実行してみる

   cd examples
   sudo php example1.php
※管理者権限が必要です。

この状態だと、UDP:10053ポートで起動しているので、普通に以下のようにdigをうつ
    
    dig @localhost -p 10053 www.polidog.jp
    [info]question domain:www.polidog.jp
    [info]query type:A
    [info]ip address:133.242.145.155

こんどはdns設定してないドメインの名前解決をする

    dig @localhost -p 10053 www.yahoo.co.jp
    [info]question domain:www.yahoo.co.jp
    [info]query type:A
    [info]call lookupExternal

lookupExternalと言われているので、これで外に問い合わせに行ってると思います。
