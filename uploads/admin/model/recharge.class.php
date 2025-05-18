<?php
/*
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class recharge_controller extends adminCommon{
	function index_action(){
		if(isset($_POST['insert'])){
			$OrderM		=	$this -> MODEL('companyorder');
			
			$userarr	=	str_replace('，', ',', trim($_POST['userarr']));
			$userarr	=	@explode(',',trim($userarr));
			
			$post		=	array(
					'fs' 			=>  intval($_POST['fs']),
					'price_int'		=>  trim($_POST['price_int']),
					'remark'		=>  trim($_POST['remark'])
			);
		   
			$return		=	$OrderM -> PayMember($userarr,$post);

			$this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
		}else{
			$RatingM		=	$this -> MODEL('rating');
		    $rating_list	=	$RatingM -> getList(array('category'=>1,'display'=>1),array('field'=>'id,name,service_price'));
			
		    $this -> yunset("rating_list",$rating_list);
		}
		$this -> yuntpl(array('admin/admin_recharge'));
	}
	//后台开通会员
	function comvip_action(){
	    $OrderM		=	$this -> MODEL('companyorder');

		$post		=	array(
				'username'		=>  trim($_POST['username']),
				'comname' 		=>  trim($_POST['comname']),
				'ratingid'		=>	intval($_POST['ratingid']),
				'vipprice'		=>	$_POST['vipprice'],
				'leijia'		=>	intval($_POST['leijia']),
				'vipprice'		=>	$_POST['vipprice']	
	    );
	   
		$return    =  $OrderM -> ComVip($post);
		$this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	function searchname_action(){
		$name		=	$this -> post_trim($_POST['username']);
		$OrderM		=	$this -> MODEL('companyorder');
		$return		=	$OrderM -> SearchName($name);
		echo json_encode($return);die;
	}
	function searchcom_action(){
		$name		=	$this -> post_trim($_POST['comname']);
		$OrderM		=	$this -> MODEL('companyorder');
		$return		=	$OrderM -> SearchCom($name);
		echo json_encode($return);die;
	}
}
?>