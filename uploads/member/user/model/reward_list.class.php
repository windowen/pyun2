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
class reward_list_controller extends user{
	function index_action(){		
		$this->public_action();
		
		$redeemM		=	$this->MODEL('redeem');
		
		$statisM		=	$this->MODEL('statis');
		
		$where['uid']	    =	$this->uid;
		$where['usertype']	=	$this->usertype;

		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	'reward_list';
		$pageurl		=	Url('member',$urlarr);
		

		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('change',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){
			
			$where['orderby']		=	'id,desc';
		    $where['limit']			=	$pages['limit'];
			
		    $List					=	$redeemM->getChangeList($where);
			
			$this->yunset("rows",$List['list']);
		}
		
		$statis				=	$statisM->getInfo($this->uid,array('usertype'=>1));
		$statis['integral']	=	number_format($statis['integral']);
		
		$num				=	$redeemM->getChangeNum(array('uid'=>$this->uid));
		
		$this	->	yunset("num",$num);
		$this	->	yunset("statis",$statis);
		$this	->	user_tpl('reward_list');
	}	
	
	function del_action(){
		$redeemM	=	$this->MODEL('redeem');
		
		$return		=	$redeemM->delChange('',array('uid'=>$this->uid, 'id'=>(int)$_GET['id'], 'usertype'=>$this->usertype,'member'=>1));
		
		$this->layer_msg($return['msg'],$return['cod'],0,$_SERVER['HTTP_REFERER']);
		
	}
	
	
}
?>