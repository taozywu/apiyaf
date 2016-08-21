<?php

/**
 * 入口
 */
define("DEBUG", true);
error_reporting(DEBUG ? E_ALL : 0);
ini_set('display_errors', DEBUG ? 'On' : 'Off');
// 根目录
define("ROOT_PATH", realpath(dirname(__FILE__) . "/../"));

isset($env) || $env = DEBUG ? "dev" : "product";
$_SERVER['env'] = $env;

// 检查yaf扩展
if (!extension_loaded("yaf")) {
    die("Please install yaf extension.");
}
// 设定yaf全局library
ini_set("yaf.library", ROOT_PATH . "/../YBase");
// 设定yaf全局environ
ini_set('yaf.environ', $env);
// 如果开启, 则会把类名中的路径部分全部小写
ini_set('yaf.lowcase_path', 0);
$app = new \Yaf\Application(ROOT_PATH . "/conf/conf.ini", ini_get('yaf.environ'));
$app->bootstrap()->run();
