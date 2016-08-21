<?php

return array(
    // 测试 ==> get/post/put/delete ""  module controller action
    // array("get", "/cat/:id", "index", "cat", "show"),    # /cat/1 => /index/cat/show/id/1
    // array("post", "/cat", "index", "cat", "add"),    # /cat => /index/cat/add
    // array("put", "/cat/:name", "index", "cat", "put"),   # /cat/test => /index/cat/put/name/test
    // array("delete", "/cat/:id", "index", "cat", "del"),  # /cat/1 => /index/cat/del/id/1
    // 
    // V1,V2 针对不同版本的情况处理 ==> get/post/put/delete "" module controller action
    array("get", "v1/school/getStudent", "index", "v1", "index"),   # /v1/school/getStudent => /index/v1/index
);
