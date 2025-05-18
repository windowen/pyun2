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
class pay_controller extends company{

	//账户管理,积分充值
	function index_action(){
		$this	->	public_action();
		$statis	=	$this	->	company_satic();
		
		$comorderM	=	$this	->	MODEL('companyorder');
		$nopayorder	=	$comorderM	->	getCompanyOrderNum(array('uid'=>$this->uid,'usertype' => $this->usertype,'order_state'=>'1'));
		$this	->	yunset("statis",$statis);
		$this	->	yunset('nopayorder',$nopayorder);
		$this	->	yunset($this->MODEL('cache')->GetCache(array('integralclass')));
		$this	->	com_tpl('pay');
	}
    /**
     * @desc 生成订单
     */
	function dingdan_action(){
		$data['price']			=	$_POST['price'];
		$data['comvip']			=	$_POST['comvip'];
		$data['comservice']		=	$_POST['comservice'];
		$data['price_int']		=	$_POST['price_int'];
		$data['integralid']		=	$_POST['integralid'];
		$data['dkjf']           =   $_POST['dkjf'];
		$data['order_remark']	=	$_POST['remark'];
		$data['uid']			=	$this->uid;
		$data['usertype']		=	$this->usertype;
		$data['did']			=	$this->userdid;
		$orderM	=	$this	->	MODEL('companyorder');
		$return	=	$orderM	->	addComOrder($data);
		$this	->	ACT_layer_msg($return['msg'],$return['errcode'],$return['url']);
	}
    /**
     * 积分抵扣全额支付
     */
	function dkzf_action(){
		$data['uid']		=	$this	->	uid;
		$data['username']	=	$this	->	username;
		$data['usertype']	=	$this	->	usertype;
		
		if($_POST['tcid']){
			$data['tcid']	=	$_POST['tcid'];
		}
		if($_POST['id']){
			$data['id']		=	$_POST['id'];
		}
		$data['server']     =   $_POST['server'];
		
		$jfdkM   =	$this -> MODEL('jfdk');
		$return  =	$jfdkM -> dkBuy($data);

		echo json_encode($return);
	}
}
?>