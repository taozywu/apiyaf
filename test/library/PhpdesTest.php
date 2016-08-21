<?php
/**
* @backupGlobals disabled
*/
class PhpdesTest extends PHPUnit_Framework_TestCase {
    
    public function testDes() {
        $str = 'mMknXDxSVyF/AILk/U4o940+U+3DZDju0mkQ33R97bUlhTCa2Cplz4Fibyy6wbq5E/AyUEiulyeYoxGgXJCRL7ksPz9W+Lflv3Lp5ifPIe+kxTWwdYvNU6AYtr7QnhYrQOEzUqaMl3LFhRi8LNYP+A==';
        $phpdes = new Phpdes(Yaf_Registry::get('config')->application->pushkey);
        //var_dump($phpdes->decrypt($str));
    }

    public function testDesLen() {
    	$content = '什阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发中国阿斯顿发生地方阿什顿发阿什顿发打发斯蒂芬阿什顿发阿什顿发斯蒂芬阿什顿发阿什顿发阿什顿发阿什顿发阿斯顿发送阿什顿发撒打发阿什顿发阿什顿发阿什顿发阿什顿发';
    	var_dump(mb_strlen($content, 'UTF-8'));
    	$json = [
            'sid' => 1,
            'mid' => 1,
            'mtime' => Utility::microtime(),
            'to' => 13401069598,
            'type' => 1,
            'content' => base64_encode($content),
            'unread' => 1,
        ];
        //var_dump($json);
        $phpdes = new Phpdes(Yaf_Registry::get('config')->application->pushkey);
        $desContent = $phpdes->encrypt(json_encode($json));
        var_dump(strlen($desContent));
    }
}
?>
