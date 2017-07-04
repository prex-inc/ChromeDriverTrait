<?php

class Helper {

    //ターミナルコマンドを実行
    static function exec($command){

        exec($command, $op, $rv);
        return implode("\n", $op);
    }

    /**
     * 遅延を行うクロージャを返す。下記のように使う
     *
     * 100マイクロ秒周期で100秒待機、待機時間は1500マイクロ秒（1.5秒）
     * $this->driver->wait(100, 100)->until(Helper::sleep(1500));
     *
     * @param $micro_second　遅延したいマイクロ秒
     * @return Closure　指定の遅延時間でtrueを返すクロージャ
     */
    static function sleep($micro_second){

        $start = microtime(true);

        $micro_second = $micro_second / 1000;

        return function() use ($start, $micro_second){

            return microtime(true) > $start + $micro_second;

        };
    }


    
}