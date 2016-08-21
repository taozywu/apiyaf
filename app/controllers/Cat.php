<?php

/**
 * Cat.
 */
class CatController extends \Core\Controller\Api
{

    /**
     * 构造
     * @return [type] [description]
     */
    public function init()
    {
        parent::init();
    }

    public function createAction()
    {
        echo "postt";
        exit;
    }

    public function showAction()
    {
        echo $this->getRequest()->getParam("id");
        echo "---OK";
        exit;
    }

    /**
     * http://apiyaf.dev/index/cat/test
     * @return [type] [description]
     */
    public function testAction()
    {
        $obj = new School_Student_CatModel();
        echo $obj->getStudent();
    }

    /**
     * http://apiyaf.dev/index/cat/testV1
     * @return [type] [description]
     */
    public function testV1Action()
    {
        $obj = new School_Student_V1_CatModel();
        echo $obj->getStudent();
    }
}
