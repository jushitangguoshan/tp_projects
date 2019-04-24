<?php

namespace app\index\controller;

use app\service\SafetyService;

/**
 * 安全
 */
class Safety extends Common
{
	/**
     * 构造方法
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();

        // 是否登录
        $this->IsLogin();
    }

	/**
	 * [Index 首页]
	 */
	public function Index()
	{
		// 安全信息列表
		$this->assign('safety_panel_list', lang('safety_panel_list'));

		// 数据列表
		$data = array(
				'mobile'	=>	$this->user['mobile_security'],
				'email'		=>	$this->user['email_security'],
			);
		$this->assign('data', $data);
		return $this->fetch();
	}

	/**
	 * [LoginPwdInfo 登录密码修改页面]
	 */
	public function LoginPwdInfo()
	{
		return $this->fetch();
	}

	/**
	 * [MobileInfo 原手机号码修改页面]
	 */
	public function MobileInfo()
	{
		if(empty($this->user['mobile']))
		{
			return redirect(MyUrl('index/safety/newmobileinfo'));
		}
		return $this->fetch();
	}

	/**
	 * [NewMobileInfo 新手机号码修改页面]
	 */
	public function NewMobileInfo()
	{
		if(session('safety_sms') == null && !empty($this->user['mobile']))
		{
			return $this->error('原帐号校验失败', MyUrl('index/safety/mobileinfo'));
		}
		return $this->fetch();
	}

	/**
	 * [EmailInfo 电子邮箱修改页面]
	 */
	public function EmailInfo()
	{
		if(empty($this->user['email']))
		{
			return redirect(MyUrl('index/safety/newemailinfo'));
		}
		return $this->fetch();
	}

	/**
	 * [NewEmailInfo 新电子邮箱修改页面]
	 */
	public function NewEmailInfo()
	{
		if(session('safety_email') == null && !empty($this->user['email']))
		{
			return $this->error('原帐号校验失败', MyUrl('index/safety/emailinfo'));
		}
		return $this->fetch();
	}

	/**
	 * [VerifyEntry 验证码显示]
	 */
	public function VerifyEntry()
	{
		$params = array(
                'width' => 100,
                'height' => 32,
                'key_prefix' => 'safety',
            );
        $verify = new \base\Verify($params);
        $verify->Entry();
	}

	/**
	 * [LoginPwdUpdate 登录密码修改]
	 */
	public function LoginPwdUpdate()
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            $this->error('非法访问');
        }

        // 开始处理
        $params = input('post.');
        $params['user'] = $this->user;
        return SafetyService::LoginPwdUpdate($params);
	}

	/**
	 * [VerifySend 验证码发送]
	 */
	public function VerifySend()
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 开始处理
        $params = input('post.');
        $params['user'] = $this->user;
        return SafetyService::VerifySend($params);
	}


	/**
	 * [VerifyCheck 原账户验证码校验]
	 */
	public function VerifyCheck()
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 开始处理
        $params = input('post.');
        $params['user'] = $this->user;
        return SafetyService::VerifyCheck($params);
	}

	/**
	 * [AccountsUpdate 账户更新] 
	 */
	public function AccountsUpdate()
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 开始处理
        $params = input('post.');
        $params['user'] = $this->user;
        return SafetyService::AccountsUpdate($params);
	}
}
?>