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
/**
* 订阅管理
* @author 鑫潮-卢光露
* @date    2015-12-30 
*/
class subscribe_controller extends company{
	function index_action(){
		
		$userinfoM		=	$this -> MODEL('userinfo');

		$WxM			=	$this -> MODEL('weixin');
		
		$userInfo		=	$userinfoM -> getInfo(array('uid'=> $this -> uid), array('field' => '`wxid`, `wxopenid`'));
		if(empty($userInfo['wxid']) || empty($userInfo['wxopenid'])){
			$qrcode	=	$WxM -> applyWxQrcode($_COOKIE['wxloginid']);
			$this->yunset("qrcode",$qrcode);
		}
		$where['uid']	=  $this->uid;
		$where['type']	=  2;
		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	 	$pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this->MODEL('page');
	    $pages			=	$pageM->pageList('subscribe',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];
	        
	        $subscribeM  =  $this->MODEL('subscribe');
	        
	        $List   	=  $subscribeM->getList($where);
		}
		

		$this->yunset("rows",$List);
		$this->company_satic();
		$this->public_action();
		$this->com_tpl('subscribe');
	}
	function subscribedingyue_action(){
		
		$userinfoM	=	$this -> MODEL('userinfo');

		$WxM		=	$this -> MODEL('weixin');

		$userInfo   =  $userinfoM -> getInfo(array('uid'=> $this -> uid), array('field' => '`wxid`, `wxopenid`'));

		if(empty($userInfo)){
			
			echo 1;die;
		}
		
		if($this->usertype && $this->usertype != 1 && $this->usertype != 2){
			
			echo 2;die;//只有个人和企业用户才可订阅！
		}
		//判断微信是否开启
		if(empty($userInfo['wxid']) && empty($userInfo['wxopenid'])){
			$qrcode	=	$WxM -> applyWxQrcode($_COOKIE['wxloginid']);
			if(empty($qrcode)){
				echo 3;die;
			}else{
				echo 4;die;
			}
		}else{
			//直接跳转
			echo 5;die;
		}

	}
	function record_action(){
		$where['uid']	=  $this->uid;
		$where['type']	=  2;
		$urlarr['c']	=	$_GET['c'];
		$urlarr['act']	=	$_GET['act'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this->MODEL('page');
	    $pages			=	$pageM->pageList('subscriberecord',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];
	        
	        $subscribeM  =  $this->MODEL('subscribe');
	        
	        $List   	=  $subscribeM->getRecordList($where);
	    } 
		$this->yunset("rows",$List);
		$this->public_action();
		$this->com_tpl('subscriberecord');
	}  
	function del_action(){
		if($_POST['delid']||$_GET['id']){
			if ($_GET['id']){
				$id   =  intval($_GET['id']);
			}elseif ($_POST['delid']){
				$id   =  $_POST['delid'];
			}
			$subscribeM		=  $this->MODEL('subscribe');
			$return   		=  $subscribeM -> del($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
			$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}
	}
}
?>