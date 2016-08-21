# apiyaf
this is a for api framework and support rest.

##重要说明
1.yaf里面除了lib和全局lib外，其他均只能使用自己的yaf命名空间。<br>
2.yaf的model部分可以支持多目录结构 例如 School_Student_V1_CatModel 对应的目录 =》 Models/Schoole/Student/V1/Cat.php<br>
3.此框架为主要提供接口服务的框架，支持cli，http访问的可自定义路由且支持restful风格多返回格式（json,html,jsonp,xml...）<br>

###更新说明
```
2015/11/13
1.增加CI的input output format security
2.接口校验
（1）校验format格式
（2）校验allowed method
（3）校验黑名单
（4）校验传参get，post，delete，put，patch。。
（5）校验接口class，method（次数，允许） --@todo
（6）校验百名单
（7）校验key --@todo
（8）校验ssl
（9）校验访问间隔控制 --@todo

2015/11/12
支持restful
1.配置在config/route.php
2.考虑到接口一般供给移动端使用，则会存在移动端版本各异的情况，则需要维护至少3个版本的api的处理。

Models
    School                      // 模块
        Student.php             // 子模块
        V1
            Student.php         // V1 子模块

        V2
            Student.php         // V2 子模块

        Teacher
            Index.php
            V1
                Index.php

说明一点，所有子版本的的逻辑程序均会相应继承对应的上一级父程序。<br>


2015/11/11
cli下访问
view /data/program/php/bin/php ./apiyaf/public/index.php request_uri="/index/index" "env=dev&aaa=a&bbb=b"<br>
获取变量 直接$GLOBALS这样来获取。


http下访问<br>
1.原生访问 /index/cat/show/id/1  => controllers/Cat.php/showAction => $this->getRequest()->getParam("id");<br>
2.restful访问<br>
array("get", "/cat/:id", "index", "cat", "show"), // --> /cat/1   <=> /index/cat/show/id/1<br>
array("get", "v1/school/getStudent", "index", "v1", "index"), // --> /v1/school/getStudent <=> /index/v1/index <br>
```
