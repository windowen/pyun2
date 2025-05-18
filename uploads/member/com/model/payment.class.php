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
class payment_controller extends company{
	function index_action(){
		$orderM	=	$this	->	MODEL('companyorder');
		$rows	=	$orderM	->	getBankList();
		$this	->	yunset('rows',$rows);
		$order	=	$orderM	->	getInfo(array('uid'=>$this->uid,'id'=>(int)$_GET['id']),array('bank'=>1));
		if(empty($order)){ 

			$this	->	ACT_msg($_SERVER['HTTP_REFERER'],'订单不存在！'); 
		}elseif($order['order_state']!='1'){ 

			header('Location:index.php?c=paylog');
		}else{
			$this	->	company_satic();
			$companyM		=	$this	->	MODEL('company');
			$company		=	$companyM	->	getInfo(array('uid'=>$this->uid),array('field'=>'`linkman`,`linktel`,`address`,`name`'));
			
			$order_remark	=	"我所汇款的银行：\n我汇入的帐号：\n汇款金额：\n汇款时间：\n其他：\n";

			if($company['linkman']==''||$company['linktel']==''||$company['address']==''){
				$company['link_null']	=	'1';
			}

			
			$this	->	yunset("company",$company);
			$this	->	yunset("order",$order);
			$this	->	yunset("order_remark",$order_remark);
			$this	->	public_action();
			$this	->	com_tpl('payment');
		}
	}
	 
	/**
	 * 微信扫码支付
	 */
	function wxurl_action()
	{
		$comorderM  =  $this->MODEL('companyorder');
		
		$data  =  array(
		    'uid'      =>  $this->uid,
		    'usertype' =>  $this->usertype,
		    'orderId'  =>  (int)$_POST['orderId']
		);
		$return  =  $comorderM->payComOrderByWXPC($data);
		
		echo $return['msg'];die;
	}
	/**
	 * 银行转账
	 */
	function paybank_action()
	{
	    $comorderM	=	$this->MODEL('companyorder');
	    
	    $data['id']				=	$_POST['oid'];
	    $data['file']			=	$_FILES['file'];
	    $data['uid']			=	$this->uid;
	    $data['usertype']		=	$this->usertype;
		$data['did']			=	$this->userdid;
	    $data['bank_name']		=	$_POST['bank_name'];
	    $data['bank_number']	=	$_POST['bank_number'];
	    $data['bank_price']		=	$_POST['bank_price'];
	    $data['bank_time']		=	$_POST['bank_time'];
	    $data['order_remark']	=	$_POST['order_remark'];
	    
	    $return  =  $comorderM->payComOrderByBank($data);
	    
	    $this->ACT_layer_msg($return['msg'],$return['errcode'],$return['url']);
	}
}
?>