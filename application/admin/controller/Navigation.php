<?php

namespace app\admin\controller;

use app\service\ArticleService;
use app\service\NavigationService;
use app\service\GoodsService;

/**
 * 导航管理
 */
class Navigation extends Common
{
	private $nav_type;

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

		// 导航类型
		$this->nav_type = input('nav_type', 'header');
	}

	/**
     * [Index 导航列表]
     */
	public function Index()
	{
		// 获取导航列表
		$this->assign('data_list', NavigationService::NavList(['nav_type'=>$this->nav_type]));

		// 一级分类
		$this->assign('nav_header_pid_list', NavigationService::LevelOneNav(['nav_type'=>$this->nav_type]));

		// 获取分类和文章
		$article_category_content = ArticleService::ArticleCategoryListContent();
        $this->assign('article_list', $article_category_content['data']);

		// 商品分类
		$this->assign('goods_category_list', GoodsService::GoodsCategory());

		// 自定义页面
		$this->assign('customview_list', db('CustomView')->field(array('id', 'title'))->where(array('is_enable'=>1))->select());

		// 是否新窗口打开
		$this->assign('common_is_new_window_open_list', lang('common_is_new_window_open_list'));

		// 是否显示
		$this->assign('common_is_show_list', lang('common_is_show_list'));

		$this->assign('nav_type', $this->nav_type);
		return $this->fetch();
	}

	/**
     * [Save 添加/编辑]
     */
	public function Save()
	{
		// 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 开始处理
        $params = input();
        $params['nav_type'] = $this->nav_type;
        return NavigationService::NavSave($params);
	}

	

	/**
	 * [Delete 删除]
	 */
	public function Delete()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			return $this->error('非法访问');
		}

		// 开始处理
        $params = input();
        return NavigationService::NavDelete($params);
	}

	/**
	 * [StatusUpdate 状态更新]
	 */
	public function StatusUpdate()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			return $this->error('非法访问');
		}

		// 开始处理
        $params = input();
        return NavigationService::NavStatusUpdate($params);
	}
}
?>