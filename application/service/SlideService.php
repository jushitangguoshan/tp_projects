<?php

namespace app\service;

use think\Db;
use app\service\ResourcesService;

/**
 * 轮播图服务层
 */
class SlideService
{
    /**
     * 数据列表
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function SlideList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;

        $data = Db::name('Slide')->field($field)->where($where)->order('sort asc')->limit($m, $n)->select();
        if(!empty($data))
        {
            $common_platform_type = lang('common_platform_type');
            $common_is_enable_tips = lang('common_is_enable_tips');
            $common_app_event_type = lang('common_app_event_type');
            foreach($data as &$v)
            {
                // 是否启用
                if(isset($v['is_enable']))
                {
                    $v['is_enable_text'] = $common_is_enable_tips[$v['is_enable']]['name'];
                }

                // 平台类型
                if(isset($v['platform']))
                {
                    $v['platform_text'] = $common_platform_type[$v['platform']]['name'];
                }

                // 事件类型
                if(isset($v['event_type']) && $v['event_type'] != -1)
                {
                    $v['event_type_text'] = $common_app_event_type[$v['event_type']]['name'];
                }

                // 图片地址
                if(isset($v['images_url']))
                {
                    $v['images_url_old'] = $v['images_url'];
                    $v['images_url'] = ResourcesService::AttachmentPathViewHandle($v['images_url']);
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
    public static function SlideTotal($where = [])
    {
        return (int) Db::name('Slide')->where($where)->count();
    }

    /**
     * 列表条件
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function SlideListWhere($params = [])
    {
        $where = [];

        if(!empty($params['keywords']))
        {
            $where[] = ['name', 'like', '%'.$params['keywords'].'%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 等值
            if(isset($params['is_enable']) && $params['is_enable'] > -1)
            {
                $where[] = ['is_enable', '=', intval($params['is_enable'])];
            }
            if(isset($params['event_type']) && $params['event_type'] > -1)
            {
                $where[] = ['event_type', '=', intval($params['event_type'])];
            }
            if(!empty($params['platform']))
            {
                $where[] = ['platform', '=', $params['platform']];
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
     * 数据保存
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function SlideSave($params = [])
    {
        // 请求类型
        $p = [
            [
                'checked_type'      => 'length',
                'key_name'          => 'name',
                'checked_data'      => '2,60',
                'error_msg'         => '名称长度 2~60 个字符',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'platform',
                'checked_data'      => array_column(lang('common_platform_type'), 'value'),
                'error_msg'         => '平台类型有误',
            ],
            [
                'checked_type'      => 'in',
                'key_name'          => 'event_type',
                'checked_data'      => array_column(lang('common_app_event_type'), 'value'),
                'is_checked'        => 2,
                'error_msg'         => '事件值类型有误',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'event_value',
                'checked_data'      => '255',
                'error_msg'         => '事件值最多 255 个字符',
            ],
            [
                'checked_type'      => 'empty',
                'key_name'          => 'images_url',
                'checked_data'      => '255',
                'error_msg'         => '请上传图片',
            ],
            [
                'checked_type'      => 'length',
                'key_name'          => 'sort',
                'checked_data'      => '3',
                'error_msg'         => '顺序 0~255 之间的数值',
            ],
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 附件
        $data_fields = ['images_url'];
        $attachment = ResourcesService::AttachmentParams($params, $data_fields);

        // 数据
        $data = [
            'name'          => $params['name'],
            'platform'      => $params['platform'],
            'event_type'    => isset($params['event_type']) ? intval($params['event_type']) : -1,
            'event_value'   => $params['event_value'],
            'images_url'    => $attachment['data']['images_url'],
            'bg_color'      => isset($params['bg_color']) ? $params['bg_color'] : '',
            'sort'          => intval($params['sort']),
            'is_enable'     => isset($params['is_enable']) ? intval($params['is_enable']) : 0,
        ];

        if(empty($params['id']))
        {
            $data['add_time'] = time();
            $new_id = Db::name('Slide')->insertGetId($data);
            if($new_id > 0)
            {
                $params['name'] = Db::name('Slide')->where('id',$new_id)->value('name');
                $logs = [
                    'admin_id'=>$params['admin']['id'],
                    'title'=>'添加',
                    'detail'=>$params['admin']['username'].'添加了'.$params['name']."信息",
                    'add_time'=>time()
                ];
                $log = System::AdminLogSave($logs);
                return DataReturn('添加成功', 0);
            }
            return DataReturn('添加失败', -100);
        } else {
            $data['upd_time'] = time();
            if(Db::name('Slide')->where(['id'=>intval($params['id'])])->update($data))
            {
                $logs = [
                    'admin_id'=>$params['admin']['id'],
                    'title'=>'修改',
                    'detail'=>$params['admin']['username'].'修改了'.$params['name']."信息",
                    'add_time'=>time()
                ];
                $log = System::AdminLogSave($logs);
                return DataReturn('编辑成功', 0);
            }
            return DataReturn('编辑失败', -100); 
        }
    }

    /**
     * 删除
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function SlideDelete($params = [])
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
        $params['name'] = Db::name('Slide')->where(['id'=>$params['id']])->value('name');
        // 删除操作
        if(Db::name('Slide')->where(['id'=>$params['id']])->delete())
        {
            $logs = [
                'admin_id'=>$params['admin']['id'],
                'title'=>'删除',
                'detail'=>$params['admin']['username'].'删除了'.$params['name'],
                'add_time'=>time()
            ];
            $log = System::AdminLogSave($logs);
            return DataReturn('删除成功');
        }

        return DataReturn('删除失败或资源不存在', -100);
    }

    /**
     * 状态更新
     * @param    [array]          $params [输入参数]
     */
    public static function SlideStatusUpdate($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '操作id有误',
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
        if(Db::name('Slide')->where(['id'=>intval($params['id'])])->update(['is_enable'=>intval($params['state'])]))
        {
           return DataReturn('编辑成功');
        }
        return DataReturn('编辑失败或数据未改变', -100);
    }
}
?>