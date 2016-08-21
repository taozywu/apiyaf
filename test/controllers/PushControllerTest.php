<?php
/**
* @backupGlobals disabled
*/
Class PushControllerTest extends PHPUnit_Framework_TestCase {
 
    private $application = NULL;

    // 初始化实例化YAF应用，YAF application只能实例化一次
    public function __construct() {
        if (!$this->application = Yaf_Registry::get('Application')) {
            $this->application = new Yaf_Application(APPLICATION_PATH . '/conf/phpunit/application.ini', 'common');
            Yaf_Registry::set('Application', $this->application);
        }
    }
 
    // 创建一个简单请求，并利用调度器接受Repsonse信息，指定分发请求。
    private function requestActionAndParseBody($action, $params=array()) {
        $request = new Yaf_Request_Simple("CLI", "An", "Push", $action, $params);
        $response = $this->application->getDispatcher()->returnResponse(true)
            ->dispatch($request);
        return $response->getBody();
    }
 
    public function testPushAction() {
    	$m2 = md5('test');
        $mobile = 13800000000;
        $userid = sha1('test');
        $pt = 2; //使用信鸽push
        
        $response = $this->requestActionAndParseBody('register', 
            array('mb'=>$mobile, 'm2' => $m2, 'pushid' => $userid));

        $data  = json_decode($response, true);
        $vcode = $data['content'];
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        
        //新建会话
        $params = [
            'm2' => $m2,
            'mb' => $mobile,
            'type' => 1,
            'content' => 'test',
            'from' => $mobile,
            'to' => 13401069598,
            'create' => $mobile,
            'nick' => "nick",
            'avatar' => "avatar",
            'tonick' => "tonick",
        ];
        
        $response = $this->requestActionAndParseBody('send', $params);
        $data  = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        $content = $data['content'];
        $this->assertTrue($content['mid'] > 0);
        $this->assertTrue($content['sid'] > 0);

        $params = [
            'm2' => $m2,
            'mb' => $mobile,
            'sid' => $content['sid'],
            'type' => 1,
            'content' => 'test',
            'from' => $mobile,
            'to' => 13401069598,
            'create' => $mobile,
        ];

        $response = $this->requestActionAndParseBody('send', $params);
        $data  = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        $content = $data['content'];
        $this->assertTrue($content['mid'] > 0);
        $this->assertTrue($content['sid'] > 0);

        //新建会话
        $params = [
            'm2' => $m2,
            'mb' => $mobile,
            'type' => 1,
            'content' => 'test',
            'from' => $mobile,
            'to' => 13401069597,
            'create' => $mobile,
            'tonick' => "tonick",
        ];
        
        $response = $this->requestActionAndParseBody('send', $params);
        $data  = json_decode($response, true);
        $this->assertTrue(0 != $data['errno']);
    }
}
