<?php
// +----------------------------------------------------------------------
// | 公共控制器
// +----------------------------------------------------------------------
// | @copyright (c) gd.kg.clzg.cn All rights reserved.
// +----------------------------------------------------------------------
// | @author: lishaoen <lishaoen@gmail.com>
// +----------------------------------------------------------------------
// | @version: v2.0
// +----------------------------------------------------------------------

namespace app\common\controller;

use think\Controller;
use think\Config;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;
use think\Url;

class Common extends Controller
{
    
	protected $url;
	protected $request;
	protected $module;
	protected $controller;
	protected $action;
    public    $site_url;
    public    $uid;
    public    $user_info;
    
    /**
     * 初始化方法
     */
	protected function _initialize()
    {
		//if (!is_file(APP_PATH . 'database.php') || !is_file(APP_PATH . 'install.lock')) {
			//return $this->redirect('install/index/index');
		//}
        //定义网站地址
        if(config('home_page')){
            $this->site_url = config('home_page');
        }else{
            $this->site_url = request()->domain().'/';
            config('home_page',$this->site_url);
        }
        define('SITE_URL', $this->site_url);
        //print_r(config());
        //exit;
		//获取request信息
		$this->requestInfo();
        //用户登录信息
        if(is_login()){
            $this->uid = is_login();
            $this->user_info = get_user_info($this->uid);
        }
        $this->assign('uid', $this->uid);
        $this->assign('user_info', $this->user_info);
        
        $this->assign('meta_keywords', config('web_site_keyword'));
        $this->assign('meta_description', config('web_site_description'));
        $this->assign('_page_name', strtolower(request()->module() . '_' . request()->controller() . '_' . request()->action()));
        
    }
    
    
    /**
     * 设置Meta
     */
    protected function setMeta($title = '')
	{
		$this->assign('meta_title', $title);
	}
    /**
	 * 验证码
	 * @param  integer $id 验证码ID
	 */
	public function verify($id = 1) {
		$option = array(
			// 验证码字符集合
			'codeSet'  => '2345678',
			// 是否画混淆曲线
			'useCurve' => false,
			'length' => 4
		);
		$verify = new \org\Verify($option);
		$verify->entry($id);
	}

	/**
	 * 检测验证码
	 * @param  integer $id 验证码ID
	 * @return boolean     检测结果
	 */
	public function checkVerify($code, $id = 1) {
		if ($code) {
			$verify = new \org\Verify();
			$result = $verify->check($code, $id);
			if (!$result) {
				return $this->error("验证码错误！", "");
			}
		} else {
			return $this->error("验证码为空！", "");
		}
	}
    
    /**
     * 设置一条或者多条数据的状态
     * @param $strict 严格模式要求处理的纪录的uid等于当前登录用户UID
     */
    public function setstatus($model = '', $strict = false)
    {
        if ($model =='') {
            $model = request()->controller();
        }
        $ids    = array_unique((array) input('ids/a', 0));
        $status = input('status');
        $setfield = input('setfield','status');
        
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        // 获取主键
        $status_model      = model($model);
        $model_primary_key = $status_model->getPk();

        // 获取id
        $ids                     = is_array($ids) ? implode(',', $ids) : $ids;
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map[$model_primary_key] = array('in', $ids);
        // 严格模式
        if ($strict) {
            $map['uid'] = array('eq', is_login());
        }
        switch ($status) {
            case 'forbid': // 禁用条目
                $data = array($setfield => 0);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success' => '禁用成功', 'error' => '禁用失败')
                );
                break;
            case 'resume': // 启用条目
                $data = array($setfield => 1);
                //$map  = array_merge(array($setfield => 0), $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success' => '启用成功', 'error' => '启用失败')
                );
                break;
            case 'recycle': // 移动至回收站
                // 查询当前删除的项目是否有子代
                if (in_array('pid', $status_model->getTableFields())) {
                    $count = $status_model->where(array('pid' => array('in', $ids)))->count();
                    if ($count > 0) {
                        $this->error('无法删除，存在子项目！');
                    }
                }

                // 标记删除
                $data[$setfield] = -1;
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success' => '成功移至回收站', 'error' => '回收失败')
                );
                break;
            case 'restore': // 从回收站还原
                $data = array($setfield => 1);
                $map  = array_merge(array($setfield => -1), $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success' => '恢复成功', 'error' => '恢复失败')
                );
                break;
            case 'delete': // 删除记录
                // 查询当前删除的项目是否有子代
                // 查询当前删除的项目是否有子代
                if (in_array('pid', $status_model->getTableFields())) {
                    $count = $status_model->where(array('pid' => array('in', $ids)))->count();
                    if ($count > 0) {
                        $this->error('无法删除，存在子项目！');
                    }
                }

                // 删除记录
                $result = $status_model->where($map)->delete();
                if ($result) {
                    $this->success('删除成功，不可恢复！');
                } else {
                    $this->error('删除失败');
                }
                break;
            default:
                $this->error('参数错误');
                break;
        }
    }
    
    /**
     * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
     * @param string $model 数据模型
     * @param array  $data  修改的数据
     * @param array  $map   查询时的where()方法的参数
     * @param array  $msg   执行正确和错误的消息
     *                       array(
     *                           'success' => '',
     *                           'error'   => '',
     *                           'url'     => '',   // url为跳转页面
     *                           'ajax'    => false //是否ajax(数字则为倒数计时)
     *                       )
     */
    protected function editRow($model, $data, $map, $msg)
    {
        $msg = array_merge(
            array(
                'success' => '操作成功！',
                'error'   => '操作失败！',
                'url'     => '',
                'ajax'    => request()->isAjax(),
            ),
            (array) $msg
        );
        $model  = model($model);
//        $result = $model->isUpdate(false)->save($data,$map);
	    $field = array_keys($data);
	    $result = $model->where($map)->setField($field[0], $data[$field[0]]);
        if ($result != false) {
            $this->success($msg['success'] . $model->getError(), $msg['url'], $msg['ajax']);
        } else {
            $this->error($msg['error'] . $model->getError(), $msg['url'], $msg['ajax']);
        }
    }
    
    /**
	 * request信息
	 */
	protected function requestInfo()
    {
		$this->param = $this->request->param();
		defined('MODULE_NAME') or define('MODULE_NAME', $this->request->module());
		defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $this->request->controller());
		defined('ACTION_NAME') or define('ACTION_NAME', $this->request->action());
		defined('IS_POST') or define('IS_POST', $this->request->isPost());
		defined('IS_GET') or define('IS_GET', $this->request->isGet());
		$this->url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
		$this->assign('request', $this->request);
		$this->assign('param', $this->param);
	}

	/**
	 * 获取单个参数的数组形式
	 */
	protected function getArrayParam($param)
    {
		if (isset($this->param['id'])) {
			return array_unique((array) $this->param[$param]);
		} else {
			return array();
		}
	}

	/**
	 * 是否为手机访问
	 * @return boolean [description]
	 */
	public function isMobile()
    {
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		}
        
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset($_SERVER['HTTP_VIA'])) {
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}

		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}
    
    /**
	 * 是否为微信访问
	 * @return boolean [description]
	 */
	public function is_wechat()
    {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
			return true;
		}
		return false;
	}
    
    /**
	 * 权限检测
	 * @param string  $rule    检测的规则
	 * @param string  $mode    check模式
	 * @return boolean
	 */
	final protected function checkRule($rule, $type = AuthRule::rule_url, $mode = 'url') {
		static $Auth = null;
		if (!$Auth) {
			$Auth = new \com\Auth();
		}
		if (!$Auth->check($rule, session('user_auth.uid'), $type, $mode)) {
			return false;
		}
		return true;
	}
    
    /**
	 * 检测是否是需要动态判断的权限
	 * @return boolean|null
	 *      返回true则表示当前访问有权限
	 *      返回false则表示当前访问无权限
	 *      返回null，则表示权限不明
	 *
	 */
	protected function checkDynamic() {
		if (IS_ROOT) {
			return true; //管理员允许访问任何页面
		}
		return null; //不明,需checkRule
	}
    
    /**
	 * action访问控制,在 **登录成功** 后执行的第一项权限检测任务
	 *
	 * @return boolean|null  返回值必须使用 `===` 进行判断
	 *
	 *   返回 **false**, 不允许任何人访问(超管除外)
	 *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
	 *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
	 */
	final protected function accessControl() {
		$allow = \think\Config::get('allow_visit');
		$deny  = \think\Config::get('deny_visit');
		$check = strtolower($this->request->controller() . '/' . $this->request->action());
		if (!empty($deny) && in_array_case($check, $deny)) {
			return false; //非超管禁止访问deny中的方法
		}
		if (!empty($allow) && in_array_case($check, $allow)) {
			return true;
		}
		return null; //需要检测节点权限
	}
    
    
}
