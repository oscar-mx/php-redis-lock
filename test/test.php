<?php

use Mx\RedisLock\RedisLock;

require_once "../vendor/autoload.php";


//$redis = new Redis();
//$redis->connect('127.0.0.1');
//$lock = new RedisLock($redis, 'lock_mx', 10);
//$res = $lock->get(function () {
//    sleep(5);
//    return [123];
//});
//var_dump($res);