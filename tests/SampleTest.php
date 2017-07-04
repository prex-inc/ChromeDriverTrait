<?php

include_once(__DIR__. '/../vendor/autoload.php');

include_once(__DIR__. '/../traits/ChromeDriverTrait.php');


use Facebook\WebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition as Expect;
use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\Remote;
use Facebook\WebDriver\WebDriverSelect as Select;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class SampleTest extends PHPUnit_Framework_TestCase
{

    use ChromeDriverTrait;

    /**
     * テスト開始時に一回だけ実行される初期化処理
     */
    public static function setUpBeforeClass()
    {
        $options = [
            'pc' => array(
                "args" => array(
                    "--user-agent=",
                    "--window-size=1500,1000",
                ),
            ),
            'sp' =>array(
                "args" => array(
                    "--user-agent=iPhone",
                    "--window-size=500,1000",
                ),
            )
        ];

        static::$scriptBeforeScrollForSaveScreenShot = '
        //設定例：固定のヘッダー要素を取り除く
        var header = document.getElementById("header");
        if(header){
            header.parentNode.removeChild(header);
        }
    ';

        self::$chromeOptions = $options['pc'];
        self::setUpBeforeClassOfChromeDriver();
    }

    /**
     * 各テストメソッドの初期化処理
     */
    public function setUp()
    {
        parent::setUp();
        $this->driver = self::$baseDriver;
    }

    public function tearDown()
    {
        $this->tearDownOfChromeDriver();
    }

    /**
     * 全テスト終了時の処理
     */
    public static function tearDownAfterClass()
    {
        $url = static::makeResultHtml();
        static::$baseDriver->get($url);
    }

    /**
     * yahooの表示
     */
    public function test_yahoo(){

        $this->driver->get('http://yahoo.co.jp');

        $this->assertRegExp('/yahoo/', $this->driver->getPageSource());
    }

    /**
     * yahooの検索
     */
    public function test_yahoo_post(){

        $this->driver->get('http://yahoo.co.jp');

        //1.5秒まつ
        $this->driver->wait(100, 100)->until(self::sleep(1500));

        //検索する
        $this->driver->findElement(By::name('p'))->clear()->sendKeys(uniqid().'test');
        $this->driver->findElement(By::name('search'))->click();

        //ヘッダーの表示を待つ
        $this->driver->wait(100, 100)->until(Expect::presenceOfElementLocated(By::cssSelector('#hd,#ygma')));

        $this->assertRegExp('/yahoo/', $this->driver->getPageSource());
    }

    /**
     * rakutenの表示
     */
    public function test_rakuten(){

        $this->driver->get('http://www.rakuten.co.jp/');

        // /etc/debug.phpに記述された処理が、ファイル保存されるたびに実行されます。
        // フォームの動きをリアルタイムで確認しながら、テストコードを書くことができます。
        //$this->debug();

        $this->assertRegExp('/rakuten/', $this->driver->getPageSource());
    }

}