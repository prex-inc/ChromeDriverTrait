<?php

use Facebook\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\Remote;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 * trait ChromeDriverTrait
 * ブラウザの開閉処理、データの保存処理、レポートの表示などを担う
 */
trait ChromeDriverTrait
{

    /** @var RemoteWebDriver $driver 動的に保持するRemoteWebDriver  */
    protected $driver;

    /** @var string $scrollEndFlagId スクロール終了を告げるオブジェクトのID */
    static $scrollEndFlagId = 'scroll_end';

    /** @var array $chromeOptions chromeの起動オプション */
    static $chromeOptions = [];

    /** @var RemoteWebDriver $baseDriver staticに保持するRemoteWebDriver  */
    static $baseDriver;

    /** @var string $testTime テストの開始時間  */
    static $testTime;

    /** @var array $allTests テスト名の配列  */
    static $allTests = [];

    /** @var array $passed 通過したテスト名の配列 */
    static $passed = [];

    /** @var string $reportsDir reportの保存先 */
    static $reportsDir = '';

    /** @var string $scriptBeforeScrollForSaveScreenShot 最初のスクロール前に実行したいスクリプト */
    static $scriptBeforeScrollForSaveScreenShot = '';

    /** @var Object $testResultObject テスト結果のオブジェクト  */
    static $testResultObject;

    /**
     * テスト開始時に一回だけ実行される初期化処理
     */
    public static function setUpBeforeClassOfChromeDriver()
    {
        //タイムゾーン設定
        date_default_timezone_set('Asia/Tokyo');

        //テスト実行時間を保持
        static::$testTime = date('ymdHis');

        //ブラウザの設定
        $capabilities =  DesiredCapabilities::chrome();
        $capabilities->setCapability("chromeOptions", static::$chromeOptions);

        //ブラウザを開く
        $host = 'http://localhost:4444/wd/hub';
        static::$baseDriver = RemoteWebDriver::create($host, $capabilities);

        //スクリーンショットを保存するディレクトリ指定
        static::$reportsDir = realpath(__DIR__ . '/../temporary/') . '/';
    }

    /**
     * テスト結果をまとめた配列を返す
     * @return array
     */
    static function getTestResultsArray(){

        $temp = [];
        //今回、生成したスクリーンショットのファイルリストを取得する
        $glob_files = glob(static::$reportsDir . static::$testTime .'*.jpg');
        foreach($glob_files as $f){
            $segment = explode('-', basename($f));
            $testName = __CLASS__. '::'.$segment[1];

            $temp[$testName]['screen_shots'][] = $f;
        }

        //今回、生成したHTMLのファイルリストを取得する
        $glob_files = glob(static::$reportsDir . static::$testTime .'*.html');
        foreach($glob_files as $f){

            $segment = explode('-', basename($f));
            $testName = __CLASS__. '::'.$segment[1];

            $temp[$testName]['html'][] = $f;
        }

        //$allTests順に並び替える
        $results = [];
        foreach(static::$allTests as $testName){
            $results[$testName] = $temp[$testName];
        }


        $passed = array_keys(static::$testResultObject->passed());

        //テスト結果を配列にマージ
        foreach($results as $testName=>$dummy){
            $results[$testName]['flag'] = in_array($testName, $passed) ? 'true' : 'false';
        }

        return $results;
    }

    /**
     * 結果報告HTMLを生成して、URLを返す
     * @return string
     */
    static function makeResultHtml(){

        $results = static::getTestResultsArray();

        ob_start();
        include(__DIR__. '/../templates/template.php');
        $str = ob_get_contents();
        ob_end_clean();

        $path = static::$reportsDir . 'result.html';
        file_put_contents($path, $str);

        return 'file://'. $path;
    }

    /**
     * 画面のスクロールscript
     */
    function getScrollJavaScript()
    {
        return sprintf('
    
            //スクロール前の位置を取得する
            preScrollTop = document.documentElement.scrollTop || document.body.scrollTop;
    
            window.scrollBy(0, window.innerHeight);
    
            //スクロール後の位置を取得する
            scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            
            //スクロールの前後で変わらないなら一番下である
            if(preScrollTop == scrollTop) {
            
                var element = document.createElement("span"); 
                element.id = "%s"; 
                document.body.appendChild(element);
            }
            ',
            static::$scrollEndFlagId);
    }

    /**
     * 表示画像を保存する
     */
    function saveScreenShoot(){

        $i = 0;
        $paths = [];

        $this->driver->executeScript('window.scroll(0, 0)');

        do{
            //スクリーンショットを保存する
            $path = static::$reportsDir.sprintf('%s-%s-%02d.jpg',
                    static::$testTime,
                    $this->getName(),
                    $i++);
            $this->driver->takeScreenshot($path);

            //ヘッダーの除去など、スクロール後に処理したいscriptを定義できる
            $this->driver->executeScript(static::$scriptBeforeScrollForSaveScreenShot);
            $this->driver->executeScript($this->getScrollJavaScript());

            $paths[] = $path;

            try{
                //scroll_endが存在しないうちは、例外が発生してbreakしない
                $this->driver->findElement(WebDriverBy::id(static::$scrollEndFlagId));
                break;

            }catch (Exception $e){ }

        } while(1);

        return $paths;
    }

    /**
     * HTMLを保存する
     */
    function saveHtml(){

        $path = sprintf(static::$reportsDir.'%s-%s-0.html', static::$testTime, $this->getName());
        $body = $this->driver->getPageSource();

        file_put_contents($path, $body);
    }

    /**
     * 遅延を行うクロージャを返す。下記のように使う
     *
     * 100マイクロ秒周期で100秒待機、待機時間は1500マイクロ秒（1.5秒）
     * $this->driver->wait(100, 100)->until(self::sleep(1500));
     *
     * @param $micro_second 遅延したいマイクロ秒
     * @return Closure 指定の遅延時間でtrueを返すクロージャ
     */
    static function sleep($micro_second){

        $start = microtime(true);

        $micro_second = $micro_second / 1000;

        return function() use ($start, $micro_second){

            return microtime(true) > $start + $micro_second;

        };
    }

    /**
     * タブを開く
     */
    function openNewTab(){
        $this->driver->ExecuteScript("window.open('','_blank');");
        $tab = $this->driver->getWindowHandles();
        $this->driver->switchTo()->window(end($tab));
    }

    /**
     * 各テストメソッドの終了処理
     */
    public function tearDownOfChromeDriver()
    {
        $this->saveScreenShoot();
        $this->saveHtml();
        $this->openNewTab();

        //テストリストを保持する
        static::$allTests[] = __CLASS__. '::'.$this->getName();
        //テスト結果を保持する
        static::$testResultObject = $this->getTestResultObject();
    }


    /**
     * ターミナルコマンドを実行する
     * @param $command 実行コマンド
     * @return string 実行結果
     */
    protected function exec($command){

        exec($command, $op, $rv);
        return implode("\n", $op);
    }

    /**
     * debug.phpで書いたコードの実行結果が、debug.logに書き出されて確認できる
     */
    protected function debug(){

        $debug_code = __DIR__ . '/../etc/debug.php';
        $debug_log = __DIR__ . '/../etc/debug.log';
        $pre_modified = self::exec('stat -f %m '.$debug_code);

        //インターバール0.5秒で１時間待機する
        $this->driver->wait(3600, 500)->until(function() use (&$pre_modified, $debug_code, $debug_log){

            //debug.phpが更新されるたびに再実行される。filemtime()は最新の値を返さない

            $modified = self::exec('stat -f %m '.$debug_code);
            if($pre_modified != $modified){

                //表示結果をdebug.logに保存
                ob_start();

                echo 'Debug: ' . date('Y-m-d H:i:s') . "\n";

                //文法エラーがないか事前にチェック
                $syntax_error = self::exec('php -l '.$debug_code);
                if(preg_match('/^No syntax/', $syntax_error)){
                    try{
                        include($debug_code);
                    }catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }else{
                    echo $syntax_error;
                }
                $cont = ob_get_contents();
                file_put_contents($debug_log, $cont);
                ob_end_clean();
            }

            $pre_modified = $modified;

            return false;
        }
        );
    }
}