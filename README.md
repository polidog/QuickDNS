PHPでDNSの正引きをするためのライブラリ！！！
==========

Host書き直すのだるいし、ローカル内で共有したい時なんかに使えます。
※絶対にLAN内で使用するようにしてください。。。
ネットワークに不具合が起きても責任もてないし・・・使用する時は自己責任でお願いします。


動作環境
------------
php5.3以上  
みなさん、php5.4使いましょうヽ(｀・ω・´)ﾉ ｳﾜｧｧﾝ!

動かし方
------------
examplesディレクトリの中を見てもらえれば解りますが、基本的には以下のような流れになります。
1. オートローダーを設定する
てかcomposer installとかすれば多分勝手にautoloder作られるよ！
2. コマンドクラスのインスタンスを生成する
3. executeする
4. CLIで実行するときに使いたいコマンドのに合わせて引数を渡す

例:~/bin/command
    
    #!/usr/bin/env php
    <?php
        require('../vendor/autoload.php');
        $command = new Polidog\Console\Console($argv);
        $command->addPath(__DIR__.DIRECTORY_SEPARATOR.'Command2/');
        $command->execute();	 


こんな感じのクラスがあったとします。
デフォルトでStringクラスがあるのでStringクラスを実行する事を考えます。

     polidog$ cd ~/bin
     polidog$ ./command string urlencode テスト!
     %E3%83%86%E3%82%B9%E3%83%88%21


って感じでurlencodeされた値が表示されます。



例えば、引数にurlencodeを指定しない場合、つまりクラス名しか指定していない場合は
mainメソッドをオーバーライドしなければ、メソッド一覧がでる使用になっています。

    polidog$ cd ~/bin
    polidog$ ./command string
        base64	string　指定した文字列をbase64エンコードする
        urlencode	string　URLエンコードをする
        urldecode	string　URLデコードする
        unserialize	string　指定したシリアライズされた配列を普通の配列に戻して出力する
        serialize	string　配列なシンタックスの文字列をシリアライズした値に変更する

ちなみに標準で搭載されている、listメソッドを使っても一覧を出す事が出来ます。

    polidog$ ./command string list
        base64	string　指定した文字列をbase64エンコードする
        urlencode	string　URLエンコードをする
        urldecode	string　URLデコードする
        unserialize	string　指定したシリアライズされた配列を普通の配列に戻して出力する
        serialize	string　配列なシンタックスの文字列をシリアライズした値に変更する


コマンドの拡張の仕方
------------
拡張したいコマンド用クラスはどこに置いてもかまいません。
Consoleクラスが生成されたあとにaddPathで使用したいクラスのパスを追加することができます。
examplesのcommandファイルには以下のようなコードが記載されています。
    
    $command->addPath(__DIR__.DIRECTORY_SEPARATOR.'Command2/');

これにより、examples/Command2いかのディレクトリにCommandAbstractを継承したクラスを用意しておけば、そのクラスを使用できます。
また、あたりまえですが、クラス名=ファイル名になっていないと利用できません。

で、例えばCommand2/には言ってるTestクラスを呼び出すときには以下のようにすればいいです

    ./command test
    Hello extend command

これだけで、Command2にあるテストクラスが実行されます。
