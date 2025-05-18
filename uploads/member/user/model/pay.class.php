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
class pay_controller extends user{
	function index_action(){
		$orderM		=	$this->MODEL('companyorder');
	
		$nopayorder	=	$orderM->getCompanyOrderNum(array('uid'=>$this->uid,'usertype' => $this->usertype, 'order_state'=>1));
		
		$this -> yunset('nopayorder',$nopayorder);
		$this -> public_action();
		$this -> yunset($this->MODEL('cache')->GetCache(array('integralclass')));
		$this -> user_tpl('pay');
	}
	
	//生成订单
	function dingdan_action(){
		$orderM				   =  $this->MODEL('companyorder');
	
		$data['price_int']	   =  intval($_POST['price_int']);
		$data['integralid']	   =  intval($_POST['integralid']);
		$data['order_remark']  =  $_POST['remark'];
		$data['uid']		   =  $this->uid;
		$data['did']		   =  $this->userdid;
		$data['usertype']	   =  $this->usertype;
		
		$return				   =  $orderM->addComOrder($data);
		
		$this->ACT_layer_msg($return['msg'], $return['errcode'], $return['url']);
	}
}
?>