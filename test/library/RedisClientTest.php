<?php
/**
* @backupGlobals disabled
*/
class RedisClientTest extends PHPUnit_Framework_TestCase {
    private $redis;
    
    public function testHashRedis() {
	$redisClient = new RedisClient('default');
        $this->redis = $redisClient->getHashConfig("hash")->getRedis();
        $this->redis->set("foo", "bar");
        $this->assertTrue($this->redis->get("foo") == "bar");
    }

    public function testMSRedis() {
	$redisClient = new RedisClient('default');
        $this->redis = $redisClient->getHashConfig("hash")->getRedis();
        $this->redis->set("foo1", "bar1");
        $this->assertTrue($this->redis->get("foo1") == "bar1");
    }
}
?>
