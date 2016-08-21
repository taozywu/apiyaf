<?php

/**
 * V1.
 */
class V1Controller extends \Core\Controller\Api
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
        // $class = new School_Student_V1_CatModel();

        $result = array("name" => "wutao");

        echo $this->response($result, 200);
        exit;
    }

    public function showAction()
    {
        echo $this->get("id");
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
