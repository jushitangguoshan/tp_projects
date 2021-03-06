<?php

namespace app\index\controller;

use app\service\BuyService;

/**
 * 购物车
 */
class Cart extends Common
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
     * 首页
     */
    public function Index()
    {
        $cart_list = BuyService::CartList(['user'=>$this->user]);
        $this->assign('cart_list', $cart_list['data']);

        $base = [
            'total_price'   => empty($cart_list['data']) ? 0 : array_sum(array_column($cart_list['data'], 'total_price')),
            'total_stock'   => empty($cart_list['data']) ? 0 : array_sum(array_column($cart_list['data'], 'stock')),
            'ids'           => empty($cart_list['data']) ? '' : implode(',', array_column($cart_list['data'], 'id')),
        ];
        $this->assign('base', $base);
        return $this->fetch();
    }

    /**
     * 购物车保存
     * @desc    description
     */
    public function Save()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            $this->error('非法访问');
        }

        $params = $_POST;
        $params['user'] = $this->user;
        return BuyService::CartAdd($params);
    }

    /**
     * 购物车删除
     * @desc    description
     */
    public function Delete()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        $params = $_POST;
        $params['user'] = $this->user;
        return BuyService::CartDelete($params);
    }

    /**
     * 数量保存
     * @desc    description
     */
    public function Stock()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            $this->error('非法访问');
        }

        $params = $_POST;
        $params['user'] = $this->user;
        return BuyService::CartStock($params);
    }
}
?>