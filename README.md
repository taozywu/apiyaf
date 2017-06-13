# apiyaf
this is a for api framework and support rest.

#重要说明
1. yaf里面除了lib和全局lib外，其他均只能使用自己的yaf命名空间。
2. yaf的model部分可以支持多目录结构 例如 School_Student_V1_CatModel 对应的目录 =》 Models/Schoole/Student/V1/Cat.php
3. 此框架为主要提供接口服务的框架，支持cli，http访问的可自定义路由且支持restful风格多返回格式（json,html,jsonp,xml...）

#更新说明

2017/06/13   
抱歉!暂时不做后续升级更新,如使用该框架,请自行承担程序中的意想不到的BUG!

2015/11/13   
1. 增加CI的input output format security
2. 接口校验
 * 校验format格式
 * 校验allowed method
 * 校验黑名单
 * 校验传参get，post，delete，put，patch。。
 * 校验接口class，method（次数，允许） --@todo
 * 校验百名单
 * 校验key --@todo
 * 校验ssl
 * 校验访问间隔控制 --@todo

2015/11/12   
1. 支持restful
2. 配置在config/route.php
3. 考虑到接口一般供给移动端使用，则会存在移动端版本各异的情况，则需要维护至少3个版本的api的处理。

```
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
```

* 所有子版本的的逻辑程序均会相应继承对应的上一级父程序


2015/11/11   
* cli下访问
view /data/program/php/bin/php ./apiyaf/public/index.php request_uri="/index/index" "env=dev&aaa=a&bbb=b"
获取变量阔以直接$GLOBALS这样来获取。


* http下访问
1. 原生访问 /index/cat/show/id/1  => controllers/Cat.php/showAction => $this->getRequest()->getParam("id");
2. restful访问

```
array("get", "/cat/:id", "index", "cat", "show"), // --> /cat/1   <=> /index/cat/show/id/1
array("get", "v1/school/getStudent", "index", "v1", "index"), // --> /v1/school/getStudent <=> /index/v1/index
```
