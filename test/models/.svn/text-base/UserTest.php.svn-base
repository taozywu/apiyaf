<?php
/**
* @backupGlobals disabled
*/
class UserTest extends PHPUnit_Framework_TestCase {


    public function testUser() {
	$mysql = new Mysql();
        $db = $mysql->getHashConfig()->getDB();
        $userObj = new UserModel();;

        $username = 'test_username';
        $salt = 'test_salt_123456123456789';
        $password = 'test_password';
        
        $userObj->delUser($username);

        $data = array(
            $username,
            'test_nickname',
            $userModel->hashPassword($password, $salt),
            $salt
        );
        $userObj->registerUser($data);

        $row = $userObj->getUser($username);
        $this->assertEquals($row['username'], $username);

        //$this->assertTrue($userObj->loginUser($username, $password));
    }

}
?>
