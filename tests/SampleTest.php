<?php

include_once(__DIR__. '/../../vendor/autoload.php');

include_once(__DIR__. '/../../traits/ChromeDriverTrait.php');

include_once(__DIR__. '/../../etc/Helper.php');


class SampleTest extends PHPUnit_Framework_TestCase
{

    use ChromeDriverTrait;
    use BuyPcTrait;

    /**
     * テスト開始時に一回だけ実行される初期化処理
     */
    public static function setUpBeforeClass()
    {
        $env = env();

        $options = [
            'pc' => array(
                "args" => array(
                    "--user-agent=", //iPhone
                    "--window-size=1500,1000", //500,1000
                ),
            ),
            'sp' =>array(
                "args" => array(
                    "--user-agent=iPhone",
                    "--window-size=500,1000",
                ),
            )
        ];

        static $scriptBeforeScrollForSaveScreenShot = '
        //固定のヘッダー要素を取り除く
        var header = document.getElementById("header");
        if(header){
            header.parentNode.removeChild(header);
        }
    ';


        self::$chromeOptions = Config::chromeOptions('pc');

        if(in_array($env['db_init_flag'], $_SERVER['argv'])){
            file_get_contents($env['db_host'].'/CI/index.php?/init/init_webui_test_db/'.$env['dev_flag']);
            echo 'init_webui_test_db';
        }

        self::$baseAssets = Config::assets();
        self::setUpBeforeClassOfChromeDriver();

        file_get_contents($env['default_host'].'/webui_test/set_own_domain/0');
        file_get_contents($env['default_host'].'/webui_test/set_kisekae/0');
    }

    /**
     * 各テストメソッドの初期化処理
     */
    public function setUp()
    {
        parent::setUp();
        $this->driver = self::$baseDriver;
        $this->env = env();
        $this->assets = self::$baseAssets;

        //メール書き出しファイルを初期化する
        file_put_contents($this->env['mailtest_path'], '');
        file_put_contents($this->env['mailtest_decode_path'], '');
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

    public function test_company_webui_test_baibai_search_ByAddress(){ $this->get_company_webui_test_baibai_search_ByAddress($this->env['default_host'].'/company/webui_test/baibai/search'); }
    public function test_company_webui_test_baibai_search_ByStation(){ $this->get_company_webui_test_baibai_search_ByStation($this->env['default_host'].'/company/webui_test/baibai/search'); }
    public function test_company_webui_test_baibai_bl_AdditionalSearch(){ $this->get_company_webui_test_baibai_bl_AdditionalSearch($this->env['default_host'].'/company/webui_test/baibai/bl/%E5%B7%9D%E5%8F%A3%E5%B8%82/'); }
    public function test_company_webui_test_baibai_bk_dt_CheckLoan(){ $this->get_company_webui_test_baibai_bk_dt_CheckLoan($this->env['default_host'].'/company/webui_test/baibai/bk/detail/dt_c132777.html'); }
    public function test_company_webui_test_baibai_bk_dt_CheckContact(){ $this->get_company_webui_test_baibai_bk_dt_CheckContact($this->env['default_host'].'/company/webui_test/baibai/bk/detail/dt_c132777.html'); }

}