<?php

namespace app;

use think\Request;
use app\biz\user\AnonymousUser;
use app\biz\common\ServiceKernel;


class AppKernel
{
    private $isServiceKernelInit = false;

    public function handle(Request $request)
    {
        //初始化核心
        if (!$this->isServiceKernelInit) {

            //初始化项目时，获取邀请码
            $serviceKernel = ServiceKernel::create();
            try {
                $invitedCode = session('invitedCode');
            } catch (\Exception $e) {
                $invitedCode = '';
            }

            $currentUser = array(
                'current_ip' => $request->ip() ?: '127.0.0.1',
                'is_secure' => $request->isSsl(),
                'invited_code' => $invitedCode,
            );

            $currentUser = new AnonymousUser($currentUser);
            $serviceKernel->setCurrentUser($currentUser);

            $this->isServiceKernelInit = true;
        }
    }
}
