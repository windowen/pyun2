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
class reward_list_controller extends company{
	function index_action(){
		$this				->	public_action();
		$urlarr['c']		=	'reward_list';
		$urlarr["page"]		=	"{{page}}";
		$pageurl			=	Url('member',$urlarr);
		$where['uid']	    =	$this->uid;
		$where['usertype']	=	$this->usertype;
		$where['orderby']	=	'id,desc';
		//提取分页
		$pageM				=	$this  -> MODEL('page');
		$pages				=	$pageM -> pageList('change',$where,$pageurl,$_GET['page']);
		$redeemM		=	$this  -> MODEL('redeem');
		if($pages['total'] > 0){
		    
		    $where['limit']	=	$pages['limit'];
		    $return			=	$redeemM	->	getChangeList($where);
		    $this	->	yunset("rows",$return['list']);
		}
		$num	=	$redeemM	->	getChangeNum(array('uid'=>$this->uid));
		$this	->	yunset("num",$num);

		$this	->	company_satic();
		$this	->	com_tpl('reward_list');
	}
	
	function del_action(){
		$redeemM	=	$this		->	MODEL('redeem');
		$return		=	$redeemM	->	delChange(array('uid'=>$this->uid,'id'=>(int)$_GET['id']),array('member'=>'com','uid'=>$this->uid,'usertype'=>$this->usertype,'id'=>(int)$_GET['id']));
		$this		->	layer_msg($return['msg'],$return['cod'],0,$_SERVER['HTTP_REFERER']);
	}
}
?>