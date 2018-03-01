<?php
// +----------------------------------------------------------------------
// | @Appname 应用描述：[基于ThinkPHP5开发] 
// +----------------------------------------------------------------------
// | @Copyright (c) http://www.lishaoen.com 
//+----------------------------------------------------------------------
// | @Author: lishaoenbh@qq.com
// +----------------------------------------------------------------------
// | @Last Modified by: lishaoen
// +----------------------------------------------------------------------
// | @Last Modified time: 2017-11-30 11:06:42 
// +----------------------------------------------------------------------
// | @Description 文件描述： 配置行为扩展[读取数据库的配置信息并与本地配置合并]

namespace app\common\behavior;
defined('THINK_PATH') or exit();

class InitConfig
{
    /**
     * 行为扩展的执行入口必须是run
     */
    public function run(&$params)
    {
        // 安装模式下直接返回
        if (defined('BIND_MODULE') && BIND_MODULE === 'install') {
            return;
        }

        // 获取当前模块名称
        $module = '';
        $dispatch = request()->dispatch();
        if (isset($dispatch['module'])) {
            $module = $dispatch['module'][0];
        }
        
        // 读取数据库中的配置
        $system_config = cache('db_config_data');
        if (!$system_config || config('app_debug') === true) {
            // 获取所有系统配置
           $system_config = model('admin/Config')->lists();
            
            // SESSION与COOKIE与前缀设置避免冲突
            $system_config['session_prefix'] = strtolower(ENV_PRE . MODULE_MARK . '_'); // Session前缀
            $system_config['cookie_prefix']  = strtolower(ENV_PRE . MODULE_MARK . '_'); // Cookie前缀

            // 加载模块标签库及行为扩展
            $system_config['template']        = config('template'); // 先取出配置文件中定义的否则会被覆盖
            
            // 获取所有安装的模块配置
            $module_config = array();
            if ($module_config) {
                // 合并模块配置
                $system_config = array_merge($system_config, $module_config);
            }
            
            // 获取所有安装的插件配置
            $addon_config = array();
            if ($addon_config) {
                // 合并模块配置
                $system_config = array_merge($system_config, $addon_config);
            }
            
            // 格式化加载标签库
            //$system_config['template']['taglib_pre_load'] = implode(',', $system_config['taglib_pre_load']);
            
            // 加载Formbuilder扩展类型
            $system_config['form_item_type'] = config('form_item_type');
            
            cache('db_config_data', $system_config, 3600); // 缓存配置
        }
        
        // 移动端强制后台传统视图
        if (request()->isMobile()) {
            $system_config['is_mobile']  = true;
        } else {
            $system_config['is_mobile'] = false;
        }
        
        // 如果是后台并且不是Admin模块则设置默认控制器层为Admin
        if (MODULE_MARK === 'Admin' && request()->module() !== '' && request()->module() !== 'admin') {
            $system_config['url_controller_layer']  = 'admin';
            $system_config['template']['view_path'] =  APP_PATH . request()->module() . '/view/admin/';
        }
        
        // 模版参数配置
        $system_config['view_replace_str']             = config('view_replace_str'); // 先取出配置文件中定义的否则会被覆盖
        
        $system_config['view_replace_str']['__MODULE__']  = BASE_PATH.'/public/' . request()->module();
        $system_config['view_replace_str']['__IMG__']     = BASE_PATH.'/public/' . request()->module() . '/img';
        $system_config['view_replace_str']['__CSS__']     = BASE_PATH.'/public/' . request()->module() . '/css';
        $system_config['view_replace_str']['__JS__']      = BASE_PATH.'/public/' . request()->module() . '/js';
        
        // 获取当前主题的名称
        //$current_theme = model('Theme')->where(array('current' => 1))->order('id asc')->getField('name');
        
        // 默认模块
        $system_config['default_module'] = !empty($system_config['default_module']) ? $system_config['default_module']: config('default_module');
        config($system_config); // 添加配置
        //print_r(config());
        //exit;
    }
}
