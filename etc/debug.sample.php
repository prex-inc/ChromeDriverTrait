<?php
/*
 * このファイルを、debug.phpとしてコピーして使ってください。
 *
 * 使い方：
 * - テストコード内に、　$this->debug();を埋め込むと、そこで待機モードになる
 * - debug.phpを修正して保存するたびに、ブラウザ上で処理が実行される
 * - エラーやprint_rなどの表示結果は、debug.logに記録される
 * - 文法エラーは一応チェック機能がある。でも実行してみないとわからないfatal errorを起こすと終了してしまいます
 */

use Facebook\WebDriver;
use Facebook\WebDriver\Remote;
use Facebook\WebDriver\WebDriverExpectedCondition as Expect;
use Facebook\WebDriver\WebDriverSelect as Select;
use Facebook\WebDriver\WebDriverBy as By;
use Facebook\WebDriver\WebDriverKeys as Keys;

//このファイルは、debug()関数内で、includeされるので、普通に$this->driverとかができます。
$this->driver->findElement(By::name('sitem'))->sendKeys('123456');
$this->driver->findElement(By::id('searchBtn'))->click();

//この表示結果は、debug.logに記述されます。
//print_r($this->driver->findElement(By::name('sitem'))->getText());

