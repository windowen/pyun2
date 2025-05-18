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
class paylist_controller extends user{
	function index_action(){
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);
		$this->public_action();
		
		$orderM					=	$this->MODEL('companyorder');
		$where['uid']			=	$this->uid;
		$where['usertype']		=	$this->usertype;
		$where['order_price']	=	array('>', 0);
		

		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"paylist";
		$pageurl		=	Url('member',$urlarr);
		

		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_order',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){
			
		    $where['orderby']		=	array('order_time,desc','order_state,asc');
		    $where['limit']			=	$pages['limit'];
			
		    $List					=	$orderM->getList($where);
			$this->yunset("rows",$List);
		}
		$this->user_tpl('paylist');
	}
	
	function del_action(){
		$orderM		=	$this->MODEL('companyorder');
		
		$return		=	$orderM->cancelOrder(array('id'=>(int)$_GET['id'], 'uid'=>$this->uid));
		
		$this->layer_msg($return['msg'],$return['errcode'],0,$return['url']);
		
	}
	function card_action(){
		$orderM				=	$this->MODEL('companyorder');
		
		$data['card']		=	intval($_POST['card']);
		
		$data['password']	=	$_POST['password'];
		
		$data['uid']		=	$this->uid;
		
		$data['did']		=	$this->userdid;
		
		$data['username']	=	$this->username;
		
		$return	 			= 	$orderM->cardOrder($data);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
		
	}
}
?>