<?php
error_reporting(E_ALL ^ E_NOTICE);
$_SERVER['env'] = 'phpunit'; //phpunit测试环境
define('APPLICATION_PATH', dirname(__FILE__).'/../');
$app = new Yaf_Application(APPLICATION_PATH . '/conf/phpunit/application.ini', 'common');
$app->bootstrap();

Yaf_Registry::set('Application', $app);
?>
