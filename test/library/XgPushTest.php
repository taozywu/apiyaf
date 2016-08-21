<?php
/**
* @backupGlobals disabled
*/
class XgPushTest extends PHPUnit_Framework_TestCase {
    
    public function testPush() {
        $mobile = 13401069598;
        $pushBindInfo = PushBindModel::getInstance()
              ->selectTable($mobile)->get($mobile);
        $pushid = $pushBindInfo['pushid'];
        $content = '测试用的';
        $json = [
            'sid' => 56,
            'mid' => 628,
            'mtime' => Utility::microtime(),
            'to' => $mobile,
            'type' => 1,
            'content' => base64_encode($content),
            'unread' => 0,
        ];
        $phpdes = new Phpdes(Yaf_Registry::get('config')->application->pushkey);
        $desContent = $phpdes->encrypt(json_encode($json));
        $ret = XgPush::getInstance()->push($desContent, $pushid);
        $this->assertEquals($ret['ret_code'], 0);
    }
}
?>
