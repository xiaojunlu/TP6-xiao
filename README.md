ThinkPHPXiao
===============

> 运行环境要求PHP7.1+。

## ThinkPHP 6.0学习项目

# BIZ三层架构开发流程


<a name="c222403a"></a>
### 目录结构
以下为含 User, Article两个业务模块的推荐的目录结构示例：<br />
![image.png](https://cdn.nlark.com/yuque/0/2019/png/149414/1552362032487-6d955b38-1b4e-4526-8230-d2d8a40798c8.png#align=left&display=inline&height=521&name=image.png&originHeight=521&originWidth=768&size=35734&status=done&width=768)

<a name="38dc368d"></a>
### 命名约定
* 约定应用级业务层的顶级命名空间为 app\biz，命名空间的第二级为模块名；
* 约定 _Service 接口_的接口名以 Service 作为后缀，命名空间为 app\biz\模块名\service, 上述例子中 UserService的完整类名为 app\biz\user\service\UserService；
* 约定 _Service 实现类_的类名以 ServiceImpl 作为后缀，命名空间为 app\biz\模块名\service\impl, 上述例子中 UserServiceImpl的完整类名为 app\biz\user\service\imp\UserServiceImpl；
* Dao 接口、类名的命名约定，同 Sevice 接口、类名的命名约定。

<a name="2677668c"></a>
### 编写Dao
以编写 User Dao 为例，我们首先需要创建 UserDao接口：

```php
<?php

namespace app\biz\user\dao;

use app\biz\common\GeneralDaoInterface;

interface UserDao extends GeneralDaoInterface
{

    public function getByUsername($username);
}

```

这里我们直接继承了 GeneralDaoInterface，在 GeneralDaoInterface中，我们声明了常用的 Dao 接口：<br /><br />
```php
<?php

namespace app\biz\common;

interface GeneralDaoInterface
{
    public function create($fields);

    public function update($identifier, array $fields);

    public function delete($id);

    public function get($id, array $options = array());

    public function search($conditions, $orderBys, $start, $limit, $columns = array());

    public function count($conditions);

    public function wave(array $ids, array $diffs);

    public function table();

    public function searchPage($conditions, $orderBys, $pageSize, $columns = array());
}

```

同样我们的 UserDao 实现类，也可继承自 GeneralDaoImpl：<br /><br />
```php
<?php

namespace app\biz\user\dao\impl;

use app\biz\user\dao\UserDao;
use app\biz\common\GeneralDaoImpl;

class UserDaoImpl extends GeneralDaoImpl implements UserDao
{
    protected $table = 'user';

    public function getByUsername($username)
    {
        return $this->getByFields(array('username' => $username));
    }

}
```

这样我们就拥有了 GeneralDaoInterface接口所定义的所有方法功能。<br /><br />
<a name="e768b296"></a>
### 编写Service
以编写 UserService 为例，我们首先需创建 UserService接口：

```php
<?php

namespace app\biz\user\service;

interface UserService
{

    public function getUser($id, $lock = false);

    //根据用户名精确查找用户
    public function getUserByUsername($username);

}
```

然后创建 User Service 的实现类：

```php
<?php

namespace app\biz\user\service\impl;

use app\biz\common\BaseService;
use app\biz\user\service\UserService;
use toolkit\SimpleValidator;
use app\biz\user\UserException;

class UserServiceImpl extends BaseService implements UserService
{

    public function getUser($id, $lock = false)
    {
        $user = $this->getUserDao()->get($id, array('lock' => $lock));

        return !$user ? null : $user;
    }

    public function getUserByUsername($username)
    {
        $user = $this->getUserDao()->getByUsername($username);

        return !$user ? null : $user;
    }

    /**
     * @return UserDaoImpl
     */
    protected function getUserDao()
    {
        return $this->createDao('user.UserDao');
    }
}
```


这里我们 UserServiceImpl 继承了 BaseService ，使得 UserServiceImpl 可以自动被注入`Biz`容器对象。<br />在 getUserDao() 方法中，我们通过 $this->createDao('user.UserDao')，获得了 User Dao 对象实例biz\user\dao\impl\UserDaoImpl

<a name="28b677ed"></a>
### 使用Service
以实现_显示用户的个人主页_为例，我们的 `Controller` 代码大致为：

```php
<?php
namespace app\admin\controller;

use think\Request;
use think\facade\Session;
use app\facade\UserService;
use app\admin\validate\Login as LoginValidate;
use app\biz\user\UserProvider;

class Login extends Base
{

    /**
     * 登录
     *
     * @param Request $request
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public function index(Request $request)
    {
        $this->getUserService()->getUser($id);
        
    }



    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('user.UserService');
    }

}

```

其中 getArticleService() 同上个例子中的 getUserDao() 原理类似，通过调用 getArticleService() 我们获得了biz\article\service\impl\ArticleServiceImpl 对象实例。

# 编写模块异常流程

每一个模块都应该有对应的异常，例如UserException

<a name="38dc368d"></a>
### 命名约定
* 约定应用级业务层的顶级命名空间为 app\biz，命名空间的第二级为模块名；
* 约定 _User 模块抛出的异常_以 Exception作为后缀，命名空间为 app\biz\模块名, 上述中UserException的完整类名为 app\biz\user\UserException；

<a name="1c54639a"></a>
### 编写Exception
以编写 UserException 为例

```php
<?php

namespace app\biz\user;

use app\exception\AbstractException;

class UserException extends AbstractException
{

    const NOTFOUND_USER = 4040101;

    public $messages = array(
        4040101 => '找不到该用户！',
    );
}

```


这里我们直接继承了 AbstractException。
1. 使用const定义异常常量，命名规范为大写字母并采用下划线分割。
1. 常量值由3为HTTP状态码404/500/403+2位模块编号+2位序号组成，例如上例NOTFOUND_USER代表'找不到该用户'异常，异常编号4040101的404代表请求的用户（资源）不存在，01代表是user模块的异常，后两位01代表的是序号。

综上所述，4040101代表的属于user模块下请求的资源不存在的第一个异常。

<a name="2ca317de"></a>
### Controller抛出Exception

```php


use app\biz\user\UserException;

$this->createNewException(UserException::NOTFOUND_USER());
```

这里我们直接调用了createNewException()方法。<br /><br />
```php
  
// app\admin\controller\Base.php;

use app\exception\AbstractException;
  
  /**
     * 抛出异常
     *
     * @param [type] $e
     * @return void
     * @description 
     * @author
     */
    protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }
        throw new \Exception();
    }
```


<a name="b67cc781"></a>
### Service的实现类抛出Exception

```php

 use app\biz\user\UserException;

$this->createNewException(UserException::NOTFOUND_USER());
```

这里我们直接调用了createNewException()方法。

```php

 // app\biz\common\BaseService;
 
 use app\exception\AbstractException;
 
  protected function createNewException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
    }

```


