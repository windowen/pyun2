<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class jobpack_controller extends user{
	
	
	
	
	function change_action(){
		$orderM		=	$this->MODEL('companyorder');
		
		$nWhere['com_id']		=	$this->uid;
		$nWhere['usertype']		=	$this->usertype;
		$nWhere['pay_remark']	=	array('like', '转换'.$this->config['integral_pricename']);
		$nWhere['pay_time']		=	array('>=', strtotime(date("Y-m-d 00:00:00")));
		
		$changeNum 	= 	$orderM->getcompanypayNum($nWhere);	
		
		$this->getStatis();
		$this->public_action();
		$this->yunset("changeNum",$changeNum);
		$this->user_tpl('change');
	}
		
	function savechange_action(){
		
		
		$data['uid']			=	$this->uid;
		
		$data['usertype']		=	1;
		
		$data['changeprice'] 	=	$_POST['changeprice'];
		
		$data['changeintegral']	=	$_POST['changeintegral'];
		
		$packM					=	$this	->	MODEL('pack');
		$return					=	$packM	->	saveChange($data);
		
		echo json_encode($return);
		
	}
	
	function changelist_action(){
		$orderM			=	$this->MODEL('companyorder');
		
		$where['com_id']		=	$this->uid;
		$where['usertype']		=	$this->usertype;
		$where['pay_remark']	=	array('like','转换'.$this->config['integral_pricename']);
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['act']	=	$_GET['act'];
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url('member',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_pay',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			$where['orderby']	=	'pay_time,desc';
		    $where['limit']		=	$pages['limit'];
			
		    $List				=	$orderM->getPayList($where);
			$this->yunset("rows",$List);
		}
		$this->getStatis();
		$this->public_action();
		$this->user_tpl('changelist');
	}
	
	
	
  function getStatis($type=''){
		$statisM  	= 	$this->MODEL('statis');
		
		$statis		= 	$statisM->getInfo($this->uid,array('usertype'=>1));
		
		if($type=='loglist'){
			$statis['freeze'] = sprintf("%.2f", $statis['freeze']);
		}
		
		$this->yunset("statis",$statis);
	}
	
}
?>