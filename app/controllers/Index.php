<?php

/**
 * 欢迎页.
 */
class IndexController extends \Core\Controller\Api
{

    /**
     * 构造
     * @return [type] [description]
     */
    public function init()
    {
        parent::init();
    }

    /**
     * 首页
     * @return [type] [description]
     */
    public function indexAction()
    {
        // $user = $this->getRequest()->get('debug');
        // $this->output(11, "error", null);
        echo "h";
    }

    public function showAction()
    {
        echo "test";exit;
    }

    public function testLoginAction()
    {
        $options = [
            "cost" => 10, 
            "salt" => "12312312312345672222890"
        ];
        echo \password_hash("123456", PASSWORD_BCRYPT, $options);
    }

    public function taoFilmAction()
    {
        $result = \YClient\Text::inst("TaoFilm")->setClass("User")->testDb();
        print_r($result);
    }

    public function opSysAction()
    {
        $result = \YClient\Text::inst("OpSys")->setClass("User")->testDb();
        print_r($result);
    }
}
