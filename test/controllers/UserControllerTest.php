<?php
/**
* @backupGlobals disabled
*/
Class UserControllerTest extends PHPUnit_Framework_TestCase {
 
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
        $params = array_merge($params, ['phpunit'=>1]);
        $request = new Yaf_Request_Simple("CLI", "An", "User", $action, $params);
        $response = $this->application->getDispatcher()->returnResponse(true)
            ->dispatch($request);
        return $response->getBody();
    }
 
    public function testUserAction() {
    	$m2 = md5('test1');
    	$mobile = 13800000001;

    	$response = $this->requestActionAndParseBody('delUser', 
        	array('mb'=>$mobile, 'm2' => $m2));

        $data     = json_decode($response, true);
        $vcode = $data['content'];
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);

        $response = $this->requestActionAndParseBody('regVcode', 
        	array('mb'=>$mobile, 'm2' => $m2));
        $data     = json_decode($response, true);
        $vcode = $data['content'];
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        $this->assertEquals(6, strlen($data['content']));
        
        $response = $this->requestActionAndParseBody('register', 
        	array('mb'=>$mobile, 'm2' => $m2, 'sms' => $vcode));
        $data = json_decode($response, true);

        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        $this->assertTrue($data['content']['uid'] > 0);
        
        $pw = '123456';
        $response = $this->requestActionAndParseBody('setPw', 
            array('mb'=>$mobile, 'm2' => $m2, 'pw' => $pw));
        $data = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
       
        $response = $this->requestActionAndParseBody('login', 
            array('mb'=>$mobile, 'm2' => $m2, 'pw' => $pw));
        $data = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);

        $response = $this->requestActionAndParseBody('logout', 
            array('mb'=>$mobile, 'm2' => $m2));
        $data = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);

        $response = $this->requestActionAndParseBody('login', 
            array('mb'=>$mobile, 'm2' => $m2, 'pw' => $pw));
        $data = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);

        $response = $this->requestActionAndParseBody('findPwVcode', 
        	array('mb'=>$mobile, 'm2' => $m2));
        $data     = json_decode($response, true);
        $vcode = $data['content'];
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        $this->assertEquals(6, strlen($data['content']));
        
        $pw = '654321';
        $response = $this->requestActionAndParseBody('findPw', 
            array('mb'=>$mobile, 'm2' => $m2, 'sms' => $vcode, 'pw' => $pw));
        $data = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
        $response = $this->requestActionAndParseBody('login', 
            array('mb'=>$mobile, 'm2' => $m2, 'pw' => $pw));
        $data = json_decode($response, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals('0', $data['errno']);
    }
}
