<?php

namespace app\api\controller;
use app\common\controller\ApiBase;

class SalaryRecord extends ApiBase
{
	public function getUserSalaryRecord(){
		$result = [];
		$this->customer_id = 23;
		if($this->customer_id){
			$db_salary_record = db('mall_agent_salary_record');
			$map = [
				'customer_id' => $this->customer_id,
				'status' => 1
			];
			$list = $db_salary_record->where($map)->page($this->page, $this->pagesize)->select();
			$count = $db_salary_record->where($map)->count();
			$page_data = $this->getPageData();
			$result['list'] = $list;
			$result['page_data'] = $page_data;
		}
		return $this->sendResult($result);
	}
}