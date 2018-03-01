<?php
// +----------------------------------------------------------------------
// | 设置模型验证器
// +----------------------------------------------------------------------
// | @copyright (c) gd.kg.clzg.cn All rights reserved.
// +----------------------------------------------------------------------
// | @author: lishaoen <lishaoen@gmail.com>
// +----------------------------------------------------------------------
// | @version: v2.0
// +----------------------------------------------------------------------

namespace app\common\validate;


class Validate extends \think\Validate{
    
    
    protected function requireIn($value, $rule, $data){
		if (is_string($rule)) {
			$rule = explode(',', $rule);
		}else{
			return true;
		}
		$field = array_shift($rule);
		$val = $this->getDataValue($data, $field);
		if (!in_array($val, $rule) && $value == '') {
			return false;
		} else {
			return true;
		}
	}
    
}