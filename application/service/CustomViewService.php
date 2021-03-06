<?php

namespace app\service;

use think\Db;
use app\service\ResourcesService;

/**
 * 自定义页面服务层
 */
class CustomViewService
{
    /**
     * 获取自定义列表
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CustomViewList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? 'id,title,content,is_header,is_footer,is_full_screen,access_count,is_enable' : $params['field'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        $data = Db::name('CustomView')->field($field)->where($where)->order('id desc')->limit($m, $n)->select();
        if(!empty($data))
        {
            $common_is_enable_list = lang('common_is_enable_list');
            foreach($data as &$v)
            {
                // 是否启用
                if(isset($v['is_enable']))
                {
                    $v['is_enable_text'] = $common_is_enable_list[$v['is_enable']]['name'];
                }

                // 内容
                if(isset($v['content']))
                {
                    $v['content'] = ResourcesService::ContentStaticReplace($v['content'], 'get');
                }

                // 时间
                if(isset($v['add_time']))
                {
                    $v['add_time_time'] = date('Y-m-d H:i:s', $v['add_time']);
                    $v['add_time_date'] = date('Y-m-d', $v['add_time']);
                }
                if(isset($v['upd_time']))
                {
                    $v['upd_time_time'] = date('Y-m-d H:i:s', $v['upd_time']);
                    $v['upd_time_date'] = date('Y-m-d', $v['upd_time']);
                }
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 总数
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function CustomViewTotal($where = [])
    {
        return (int) Db::name('CustomView')->where($where)->count();
    }

    /**
     * 列表条件
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CustomViewListWhere($params = [])
    {
        $where = [];

        // id
        if(!empty($params['id']))
        {
            $where[] = ['id', '=', $params['id']];
        }

        if(!empty($params['keywords']))
        {
            $where[] = ['title', 'like', '%'.$params['keywords'].'%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 等值
            if(isset($params['is_enable']) && $params['is_enable'] > -1)
            {
                $where[] = ['is_enable', '=', intval($params['is_enable'])];
            }
            if(isset($params['is_header']) && $params['is_header'] > -1)
            {
                $where[] = ['is_header', '=', intval($params['is_header'])];
            }
            if(isset($params['is_footer']) && $params['is_footer'] > -1)
            {
                $where[] = ['is_footer', '=', intval($params['is_footer'])];
            }

            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 自定义页面访问统计加1
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CustomViewAccessCountInc($params = [])
    {
        if(!empty($params['id']))
        {
            return Db::name('CustomView')->where(array('id'=>intval($params['id'])))->setInc('access_count');
        }
        return false;
    }

    /**
     * 保存
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CustomViewSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'title',
                'checked_data'      => '2,60',
                'error_msg'         => '标题长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'content',
                'checked_data'      => '50,105000',
                'error_msg'         => '内容长度最少 50~105000 个字符',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 编辑器内容
        $content = isset($_POST['content']) ? $_POST['content'] : '';

        // 数据
        $content = ResourcesService::ContentStaticReplace($content, 'add');
        $image = self::MatchContentImage($content);
        $data = [
            'title'         => $params['title'],
            'content'       => $content,
            'image'         => empty($image) ? '' : json_encode($image),
            'image_count'   => count($image),
            'is_enable'     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
            'is_header'     => isset($params['is_header']) ? intval($params['is_header']) : 0,
            'is_footer'     => isset($params['is_footer']) ? intval($params['is_footer']) : 0,
            'is_full_screen'=> isset($params['is_full_screen']) ? intval($params['is_full_screen']) : 0,
        ];

        if(empty($params['id']))
        {
            $data['add_time'] = time();
            if(Db::name('CustomView')->insertGetId($data) > 0)
            {
                return DataReturn('添加成功', 0);
            }
            return DataReturn('添加失败', -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('CustomView')->where(['id'=>intval($params['id'])])->update($data))
            {
                return DataReturn('编辑成功', 0);
            }
            return DataReturn('编辑失败', -100); 
        }
    }

    /**
     * 正则匹配文章图片
     * @param    [string]         $content [文章内容]
     * @return   [array]                   [文章图片数组（一维）]
     */
    private static function MatchContentImage($content)
    {
        if(!empty($content))
        {
            $pattern = '/<img.*?src=[\'|\"](\/static\/upload\/customview\/image\/.*?[\.gif|\.jpg|\.jpeg|\.png|\.bmp])[\'|\"].*?[\/]?>/';
            preg_match_all($pattern, $content, $match);
            return empty($match[1]) ? [] : $match[1];
        }
        return [];
    }

    /**
     * 删除
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function CustomViewDelete($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 删除操作
        if(Db::name('CustomView')->where(['id'=>$params['id']])->delete())
        {
            return DataReturn('删除成功');
        }

        return DataReturn('删除失败或资源不存在', -100);
    }

    /**
     * 状态更新
     * @param    [array]          $params [输入参数]
     */
    public static function CustomViewStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'field',
                'error_msg'         => '字段有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'state',
                'checked_data'      => [0,1],
                'error_msg'         => '状态有误',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 数据更新
        if(Db::name('CustomView')->where(['id'=>intval($params['id'])])->update([$params['field']=>intval($params['state'])]))
        {
           return DataReturn('编辑成功');
        }
        return DataReturn('编辑失败或数据未改变', -100);
    }
}
?>