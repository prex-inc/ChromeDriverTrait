# ChromeDriverTrait

Facebook\WebDriver\Remote\RemoteWebDriver
によるブラウザテストをより便利にするクラスです。
RemoteWebDriverの基本的な使い方は、を参照ください。

http://qiita.com/zaburo/items/f11357170953a3c34b8f
https://facebook.github.io/php-webdriver/1.2.0/Facebook/WebDriver.html

#主な機能

- 各テストをChromeのタブとして実行します
- 各テストの結果レポートをhtmlで書き出します
- 長い画面も簡易的にスクロールして全画面のスクリーンショットをとります
- テストをステップ実行する機能があります
- テストを◯マイクロ秒止める機能があります


#実行サンプル

```
phpunit --colors path/to/tests/SampleTest.php
```

この結果、３つのブラウザテストが３つのChromeタブで実行され、その結果が4番目のタブにHTMLの形で出力されます。



#ディレクトリ構成

```
etc/  
    debug.log   ...    
    debug.php   ...このファイルを編集することで、ブラウザテストをステップ実行できます。
    debug.sample.php  ...上記ファイルのサンプルです。
templates/  
    template.php  ...レポートはこの形式で生成されます。
temporary/  ...ファイルが書き出されます。定期的に削除してください。
tests/  
    SampleTest.php  ...テストのサンプルです
traits/  
    ChromeDriverTrait.php  ...テストを便利にするトレイトです
composer.json  
README.md  
```

#特殊機能の使い方

##ステップ実行機能

テストメソッド中に、

```
$this->debug();
```

と記述すると、その場でテストが待機状態になります。
このとき、etc/debug.php（debug.sample.phpをコピーしてください）を編集して保存すると、その中身が実行されます。
これにより、ブラウザテスト動作をステップ実行して実験することができます。

##テストの時間待機機能

時間指定の待機は推奨されませんが、やはり便利です。このように記述できます。

```
//1.5秒待機
$this->driver->wait(100, 100)->until(self::sleep(1500));
```



