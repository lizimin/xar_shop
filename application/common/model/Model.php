<?php
// +----------------------------------------------------------------------
// | 公共模型
// +----------------------------------------------------------------------
// | @copyright (c) gd.kg.clzg.cn All rights reserved.
// +----------------------------------------------------------------------
// | @author: lishaoen <lishaoen@gmail.com>
// +----------------------------------------------------------------------
// | @version: v2.0
// +----------------------------------------------------------------------

namespace app\common\model;

define('APP_DEBUG', config('app_debug'));
define('MAGIC_QUOTES_GPC', false);

class Model extends \think\Model
{
    protected $param;
	protected $autoWriteTimestamp = true;

	protected $type = array(
		'id'  => 'integer',
	);
    
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
        $this->param = \think\Request::instance()->param();
    }
    
    /**
	 * 数据修改
	 * @return [bool] [是否成功]
	 */
	public function change(){
		$data = \think\Request::instance()->post();
		if (isset($data['id']) && $data['id']) {
			return $this->save($data, array('id'=>$data['id']));
		}else{
			return $this->save($data);
		}
	}
	
	/**
     * 返回结果
	 * 
     * @return array
     */
    public function result($data = array(), $info = '',$status = 0)
    {
		//初始化定义
		$arr = array();

		//参数变量定义
		$arr = array(
			'error'    => $status,
			'message'  => $info,
			'data'     => $data
		);
		
        return $arr;
    }
    //通过当前模型的主键ID来获取该条记录
    public function getDataByPk($pk = 0, $field = '*'){
    	$map = $result = array();
    	if($pk){
    		if($this->pk){
    			$map[$this->pk] = $pk;
    			$result = $this->where($map)->field($field)->find();
    		}else{
    			$result = array('error'=>'当前模型主键未设置');
    		}
    		
    	}
    	return $result ? $result->toArray() : $result;
    }
    //通过map获取数据。单条
    public function getDataByMap($map = array()){
        $result = array();
        if($map){
            $result = $this->where($map)->find();
        }
        return $result;
    }
    //通过map获取数据。列表
    public function getListByMap($map = array(), $field = '*', $order = '', $page = 0, $pagesize = 0){
        $result = array();
        if($map){
            $this->where($map)->field($field);
            if($pagesize) $this->page($page, $pagesize);
            if($order) $this->order($order);
            $result = $this->select();
        }
        return $result;
    }

    /**
     * 更新数据
     */
    public function updateInfo($where = [], $data = [])
    {
        $return_data = $this->allowField(true)->where($where)->update($data);
        return $return_data;
    }

    /**
     * 设置某个字段值
     */
    public function setFieldValue($where = [], $field = '', $value = '')
    {
        return $this->updateInfo($where, [$field => $value]);
    }

}
