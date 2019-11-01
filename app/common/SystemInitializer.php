<?php

namespace app\common;

use think\console\Input;
use think\console\Output;
use app\biz\user\CurrentUser;
use app\biz\common\ServiceKernel;
use app\biz\crontab\SystemCrontabInitializer;

class SystemInitializer
{
    protected $output;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    public function init()
    {
        // $this->_initTag();
        // $this->_initCategory();
        // $this->_initFile();
        // $this->_initPages();
        // $this->_initCoin();
        $this->_initJob();
        //  $this->_initRole();
        // $this->_initUserBalance();

        // $this->_initDefaultSetting();
        // $this->_initMagicSetting();
        // $this->_initMailerSetting();
        // $this->_initPaymentSetting();
        // $this->_initRefundSetting();
        // $this->_initSiteSetting();
        //   $this->_initSystemUsers();
    }

    public function initAdminUser($fields)
    {
        $this->output->write("  创建管理员帐号:{$fields['email']}, 密码：{$fields['password']}   ");

        $user = $this->getUserService()->getUserByEmail($fields['email']);

        if (empty($user)) {
            $user = $this->getUserService()->register($fields);
        }

        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN');
        $user['current_ip'] = '127.0.0.1';

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        ServiceKernel::instance()->setCurrentUser($currentUser);

        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER', 'ROLE_SUPER_ADMIN'));

        $this->output->writeln(' ...<info>成功</info>');

        return $this->getUserService()->getUser($user['id']);
    }

    protected function _initTag()
    {
        $this->output->write('  初始化标签');
        $defaultTag = $this->getTagService()->getTagByName('默认标签');

        if (!$defaultTag) {
            $this->getTagService()->addTag(array('name' => '默认标签'));
        }

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initCategory()
    {
        $this->output->write('  初始化分类分组');

        $activityGroup = $this->getCategoryService()->getGroupByCode('activity');

        if (empty($activityGroup)) {
            $activityGroup = $this->getCategoryService()->addGroup(array(
                'name' => '活动分类',
                'code' => 'activity',
                'depth' => 3,
            ));
        }

        $activityCategory = $this->getCategoryService()->getCategoryByCode('default');

        if (empty($activityCategory)) {
            $this->getCategoryService()->createCategory(array(
                'name' => '默认分类',
                'code' => 'default',
                'weight' => 100,
                'group_id' => $activityGroup['id'],
                'parent_id' => 0,
            ));
        }

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initFile()
    {
        $this->output->write('  初始化文件分组');

        $groups = $this->getFileService()->getAllFileGroups();

        foreach ($groups as $group) {
            $this->getFileService()->deleteFileGroup($group['id']);
        }

        $this->getFileService()->addFileGroup(array(
            'name' => '默认文件组',
            'code' => 'default',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '缩略图',
            'code' => 'thumb',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '活动',
            'code' => 'activity',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '用户',
            'code' => 'user',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '资讯',
            'code' => 'article',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '全局设置文件',
            'code' => 'system',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '小组',
            'code' => 'group',
            'public' => 1,
        ));

        $this->getFileService()->addFileGroup(array(
            'name' => '编辑区',
            'code' => 'block',
            'public' => 1,
        ));

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initPages()
    {
        $this->getContentService()->createContent(array(
            'title' => '关于我们',
            'type' => 'page',
            'alias' => 'aboutus',
            'body' => '',
            'template' => 'default',
            'status' => 'published',
        ));

        $this->getContentService()->createContent(array(
            'title' => '常见问题',
            'type' => 'page',
            'alias' => 'questions',
            'body' => '',
            'template' => 'default',
            'status' => 'published',
        ));
    }

    protected function _initCoin()
    {
        $this->output->write('  初始化虚拟币');

        $default = array(
            'cash_model' => 'none',
            'cash_rate' => 1,
            'coin_enabled' => 0,
        );

        $this->getSettingService()->set('coin', $default);

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initJob()
    {
        $this->output->write('  初始化CrontabJob');

        SystemCrontabInitializer::init();

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initRole()
    {
        $this->output->write('  初始化角色');
        $this->getRoleService()->refreshRoles();
        $this->output->writeln(' ...<info>成功</info>');
    }


    /**
     * 创建系统用户
     */
    private function _initUserBalance()
    {
        $this->getAccountService()->createUserBalance(array('user_id' => 0));
    }

    private function _initDefaultSetting()
    {
        $this->output->write('  初始化网站的默认设置');
        // $settingService = $this->getSettingService();

        $this->output->writeln(' ...<info>成功</info>');
    }

    private function _initMagicSetting()
    {
        $this->output->write('  初始化magic设置');
        $default = array(
            'export_allow_count' => 100000,
            'export_limit' => 10000,
            'enable_org' => 0,
        );

        $this->getSettingService()->set('magic', $default);

        $this->output->writeln(' ...<info>成功</info>');
    }

    private function _initMailerSetting()
    {
        $this->output->write('  初始化邮件服务器设置');

        $default = array(
            'enabled' => 0,
            'host' => 'smtp.exmail.qq.com',
            'port' => '25',
            'username' => 'user@example.com',
            'password' => '',
            'from' => 'user@example.com',
            'name' => '',
        );
        $this->getSettingService()->set('mailer', $default);

        $this->output->writeln(' ...<info>成功</info>');
    }

    private function _initPaymentSetting()
    {
        $this->output->write('  初始化支付设置');

        $default = array(
            'enabled' => 0,
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_accessKey' => '',
            'alipay_secretKey' => '',
        );

        $this->getSettingService()->set('payment', $default);

        $this->output->writeln(' ...<info>成功</info>');
    }

    private function _initRefundSetting()
    {
        $this->output->write('  初始化退款设置');

        $setting = array(
            'maxRefundDays' => 10,
            'applyNotification' => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification' => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。',
        );
        $this->getSettingService()->set('refund', $setting);
        $this->output->writeln(' ...<info>成功</info>');
    }

    private function _initSiteSetting()
    {
        $this->output->write('  初始化站点设置');

        $default = array(
            'name' => '测试站',
            'slogan' => '强大的网站解决方案',
            'url' => 'http://xxx.com',
            'logo' => '',
            'seo_keywords' => 'xxx, 后台管理软件, 在线在线解决方案',
            'seo_description' => 'xxx是强大的后台管理软件',
            'master_email' => 'test@xxx.com',
            'icp' => ' xxxxx',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
        );

        $this->getSettingService()->set('site', $default);
        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initSystemUsers()
    {
        $this->getUserService()->initSystemUsers();
    }


    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return ServiceKernel::instance()->createService('taxonomy.TagService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return ServiceKernel::instance()->createService('user.UserService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return ServiceKernel::instance()->createService('taxonomy.categoryService');
    }

    /**
     * @return FileService
     */
    private function getFileService()
    {
        return ServiceKernel::instance()->createService('content.FileService');
    }

    /**
     * @return ContentService
     */
    protected function getContentService()
    {
        return ServiceKernel::instance()->createService('content.ContentService');
    }

    /**
     * @return TagService
     */
    protected function getAccountService()
    {
        return ServiceKernel::instance()->createService('pay.AccountService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('system.SettingService');
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('role.RoleService');
    }
}
