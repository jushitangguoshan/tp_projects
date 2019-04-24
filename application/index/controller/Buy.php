<?php

namespace app\index\controller;

use app\service\GoodsService;
use app\service\UserService;
use app\service\PaymentService;
use app\service\BuyService;

/**
 * 购买
 */
class Buy extends Common
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
        if(input('post.'))
        {
            session('buy_post_data', $_POST);
            return redirect(MyUrl('index/buy/index'));
        } else {
            // 获取商品列表
            $params = session('buy_post_data');
            $params['user'] = $this->user;
            $ret = BuyService::BuyTypeGoodsList($params);

            // 商品校验
            if(isset($ret['code']) && $ret['code'] == 0)
            {
                // 用户地址
                $this->assign('user_address_list', UserService::UserAddressList(['user'=>$this->user])['data']);

                // 支付方式
                $this->assign('payment_list', PaymentService::BuyPaymentList(['is_enable'=>1, 'is_open_user'=>1]));
                
                // 商品/基础信息
                $base = [
                    'total_price'   => empty($ret['data']) ? 0 : array_sum(array_column($ret['data'], 'total_price')),
                    'total_stock'   => empty($ret['data']) ? 0 : array_sum(array_column($ret['data'], 'stock')),
                    'address'       => UserService::UserDefaultAddress(['user'=>$this->user])['data'],
                ];
                $this->assign('base', $base);
                $this->assign('goods_list', $ret['data']);
                
                $this->assign('params', $params);
                return $this->fetch();
            } else {
                $this->assign('msg', isset($ret['msg']) ? $ret['msg'] : '参数错误');
                return $this->fetch('public/tips_error');
            }
        }
    }

    /**
     * 订单添加
     * @desc    description
     */
    public function Add()
    {
        if(input('post.'))
        {
            $params = $_POST;
            $params['user'] = $this->user;
            return BuyService::OrderAdd($params);
        } else {
            $this->assign('msg', '非法访问');
            return $this->fetch('public/tips_error');
        }
    }
}
?>