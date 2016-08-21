<?php

/**
 * api cli
 * 1.view /data/program/php/bin/php ./apiyaf/public/index.php request_uri="/index/index" "env=dev&aaa=a&bbb=b"
 */

define("DEBUG", true);
error_reporting(DEBUG ? E_ALL : 0);
// 根目录
define("ROOT_PATH", realpath(dirname(__FILE__) . "/../"));

// 这样的处理后后面的获取变量 直接可以 $GLOBALS['aaa'] = 111.
if (isset($argv[2])) {
    foreach (explode("&", $argv[2]) as $item) {
        $value       = explode("=", $item);
        ${$value[0]} = $value[1];
    }
}

isset($env) || $env = DEBUG ? "dev" : "product";
$_SERVER['env'] = $env;

// 检查yaf扩展
if (!extension_loaded("yaf")) {
    die("Please install yaf extension.");
}
// 设定yaf全局library
// ini_set("yaf.library", ROOT_PATH . "/core/YBase/");
ini_set("yaf.library", ROOT_PATH . "/../YBase");
// 设定yaf全局environ
ini_set('yaf.environ', $env);
// 如果开启, 则会把类名中的路径部分全部小写
ini_set('yaf.lowcase_path', 0);

$app = new \Yaf\Application(ROOT_PATH . "/conf/conf.ini", ini_get('yaf.environ'));
$app->bootstrap()->getDispatcher()->dispatch(new \Yaf\Request\Simple());