<?php

namespace app\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Event;
use app\biz\common\ServiceKernel;

class Index extends BaseController
{
    public function index()
    {
    //    $user =  Db::table('user')->where('id', 1)->find();
    //    halt($user);

        //halt(9999);
        $user = $this->getUserService()->getUser(1);
        halt($user);
     //   Event::subscribe('app\biz\user\event\UserEventSubscriber');
     //   Event::trigger('user.login');
       // event('user.login', array());
       // Event::listen('UserLogin', 'app\listener\UserLogin');

        //app('html_helper')->purify();
      //  $this->app->html_helper->purify();
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('user.UserService');
    }

    protected function createService($service)
    {
        return $this->getServiceKernel()->createService($service);
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
