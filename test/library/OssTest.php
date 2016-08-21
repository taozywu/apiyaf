<?php
/**
* @backupGlobals disabled
*/
class OsstTest extends PHPUnit_Framework_TestCase {
    
    public function testOss() {
	    $oss = new Oss();
        //$oss->listBucket();
        //$oss->createBucket('biaobaiapphz-tm');
        //$oss->setBucketAcl('biaobaiapphz-tm', ALIOSS::OSS_ACL_TYPE_PUBLIC_READ_WRITE);
        //$respone = $oss->getBucketAcl('biaobaiapphz-tm');
        
        //$oss->setBucketCors('osstest');
        var_dump($oss->getSignUrl('biaobaiapphz-tm', '20140911155944_45cdb6c388534839b6c5f6db6298416b')); exit;
        //var_dump($oss->uploadByFile('osstest', '/tmp/123.jpg'));
    }
}
?>
