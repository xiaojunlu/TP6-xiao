<?php

namespace app\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Event;
use app\biz\common\ServiceKernel;

class IndexController extends BaseController
{
    public function index()
    {
          //  $user =  Db::table('user')->where('id', 1)->find();
        //    halt($user);

        // halt($this->getCurrentUser());
        $user = $this->getUserService()->searchUsers(array(
            'ids' => [1,3] 
        ), array('created_time' => 'DESC'), 0, 2000);
        halt($user);
        Event::subscribe('app\biz\user\event\UserEventSubscriber');
        Event::trigger('user.login', $user);
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
}
