<?php

namespace app\admin\controller;

use app\service\ConfigService;

/**
 * 站点设置
 */
class Site extends Common
{
	/**
	 * 构造方法
	 */
	public function __construct()
	{
		// 调用父类前置方法
		parent::__construct();

		// 登录校验
		$this->IsLogin();

		// 权限校验
		$this->IsPower();
	}

	/**
     * [Index 配置列表]
     */
	public function Index()
	{
		// 时区
		$this->assign('site_timezone_list', lang('site_timezone_list'));

		// 站点状态
		$this->assign('site_site_state_list', lang('site_site_state_list'));

		// 是否开启用户注册
		$this->assign('site_user_reg_state_list', lang('site_user_reg_state_list'));

		// 是否开启用户登录
		$this->assign('site_user_login_state_list', lang('site_user_login_state_list'));

		// 获取验证码-开启图片验证码
		$this->assign('site_img_verify_state_list', lang('site_img_verify_state_list'));

		// 配置信息
		$this->assign('data', ConfigService::ConfigList());

		// 编辑器文件存放地址
        $this->assign('editor_path_type', 'common');
		
		return $this->fetch();
	}

	/**
	 * [Save 配置数据保存]
	 */
	public function Save()
	{
		// 站点状态值处理
		if(!isset($_POST['home_user_reg_state']))
		{
			$_POST['home_user_reg_state'] = '';
		}

		// 基础配置
		return ConfigService::ConfigSave($_POST);
	}
}
?>