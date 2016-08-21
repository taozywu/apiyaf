<?php

/**
 * Class Bootstrap
 */

class Bootstrap extends \Yaf\Bootstrap_Abstract
{

    private $_conf = null;

    /**
     * 初始化配置
     */
    public function _initConfig(\Yaf\Dispatcher $dispatcher)
    {
        $this->_conf = \Yaf\Application::app()->getConfig()->toArray();
        if (isset($this->_conf['config']['file'])) {
            $this->_conf = array_merge($this->_conf, (array) include_once $this->_conf['config']['file']);
        }

        \YClient\YTextRpcClient::config($this->_conf['rpcserver']);

        \Yaf\Registry::set("config", $this->_conf);
    }


    /**
     * 注册命名空间
     */
    public function _initNamespaces()
    {
        \Yaf\Loader::getInstance()->registerLocalNameSpace(array("Core", "Smarty"));
    }

    /**
     * default
     * @param  \Yaf\Dispatcher $dispatcher [description]
     * @return [type]                      [description]
     */
    public function _initDefaultName(\Yaf\Dispatcher $dispatcher) 
    {
        $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
    }

    /**
     * 初始化路由
     * @param  \Yaf\Dispatcher $dispatcher [description]
     * @return [type]                      [description]
     */
    public function _initRoute(\Yaf\Dispatcher $dispatcher)
    {
        // 针对cli.php来使用的。
        if (strtoupper($dispatcher->getRequest()->getMethod()) == 'CLI') {
            $router = \Yaf\Dispatcher::getInstance()->getRouter();
            $router->addConfig($this->_conf['routes']);
        } else {
            $router = new \RESTfulRouter\Router;
            if ($this->_conf['route']) {
                foreach ($this->_conf['route'] as $r) {
                    if (count($r) != 5) {
                        exit("Please api route must be five params.");
                    }
                    $router->on($r[0], $r[1], $r[2], $r[3], $r[4]);
                }
            }
        }
        
    }
    
    /**
     * 初始化插件
     * @param  \Yaf\Dispatcher $dispatcher [description]
     * @return [type]                     [description]
     */
    public function _initPlugin(\Yaf\Dispatcher $dispatcher) 
    {
        if (isset($this->_conf['benchmark']['open']) && $this->_conf['benchmark']['open']) {
            $benchmark = new \BenchmarkPlugin();
            $dispatcher->registerPlugin($benchmark);
        }
    }

    /**
     * init xhprof
     * @param  Yaf_Dispatcher $dispatcher [description]
     * @return [type]                     [description]
     */
    public function _initXhprof(\Yaf\Dispatcher $dispatcher)
    {
        if (isset($this->_conf['xhprof']['open']) && $this->_conf['xhprof']['open']) {
            $xhprof = new \XhprofPlugin();
            $dispatcher->registerPlugin($xhprof);
        }
    }

}
