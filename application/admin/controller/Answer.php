<?php

namespace app\admin\controller;

use app\service\AnswerService;

/**
 * 问答管理
 */
class Answer extends Common
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
     * 问答列表
     */
	public function Index()
	{
		// 参数
        $params = input();

        // 分页
        $number = 10;

        // 条件
        $where = AnswerService::AnswerListWhere($params);

        // 获取总数
        $total = AnswerService::AnswerTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  MyUrl('admin/order/index'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = AnswerService::AnswerList($data_params);
        $this->assign('data_list', $data['data']);

		// 状态
		$this->assign('common_is_show_list', lang('common_is_show_list'));

		// 参数
        $this->assign('params', $params);
        return $this->fetch();
	}

	/**
	 * 问答删除
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
        $params['user_type'] = 'admin';
        return AnswerService::AnswerDelete($params);
	}

	/**
	 * 问答回复处理
	 */
	public function Reply()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			return $this->error('非法访问');
		}

		// 开始处理
        $params = input();
        return AnswerService::AnswerReply($params);
	}

	/**
	 * 状态更新
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
        return AnswerService::AnswerStatusUpdate($params);
	}
}
?>