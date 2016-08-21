<?php

/**
 * 欢迎页.
 */
class TestController extends \Core\Controller\Api
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
        echo "test";
    }

    
}
