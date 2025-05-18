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
 
class subscribe_controller extends user{ 
	function index_action(){ 
		
		$this->public_action();
		
		$subscribeM  =  $this->MODEL('subscribe');
		
		$userinfoM		=	$this -> MODEL('userinfo');
		
		$WxM			=	$this -> MODEL('weixin');
		
		$userInfo		=	$userinfoM -> getInfo(array('uid'=> $this -> uid), array('field' => '`wxid`, `wxopenid`'));
		
		if(empty($userInfo['wxid']) || empty($userInfo['wxopenid'])){
			
			$qrcode	=	$WxM -> applyWxQrcode($_COOKIE['wxloginid']);
			
			$this->yunset("qrcode",$qrcode);
		
		}
		
		$where['uid']	=  $this->uid;
		
		$where['type']	=  1;
		
		$urlarr['c']	=	$_GET['c'];
		
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url('member',$urlarr);
		
		$pageM			=	$this->MODEL('page');
		
		$pages			=	$pageM->pageList('subscribe',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];
	        $List   	=  $subscribeM->getList($where);
	    }
		$this->yunset("rows",$List);
		$this->yunset("js_def",3);
		$this->user_tpl('subscribe');
	} 
	function record_action(){
		$this->public_action();
		$where['uid']	=  $this->uid;
		$where['type']	=  1;
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
		$this->yunset("js_def",3);
		$this->user_tpl('subscriberecord');
	} 
	function del_action(){
		$subscribeM		=  $this->MODEL('subscribe');
		if($_GET['del']||$_GET['id']){
			if ($_GET['id']){
				$id   =  intval($_GET['id']);
			}elseif ($_POST['del']){
				$id   =  $_POST['del'];
			}
			$return   		=  $subscribeM -> del($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
			$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		}
	}

	function subscribedingyue_action(){
		$userinfoM	=	$this -> MODEL('userinfo');

		$WxM		=	$this -> MODEL('weixin');
    
	    $userInfo		=	$userinfoM -> getInfo(array('uid'=> $this -> uid), array('field' => '`wxid`, `wxopenid`'));
    
		if(empty($userInfo)){
			
			echo 1;die;
		
		}
		
		if($this->usertype && $this->usertype != 1 && $this->usertype != 2){
			
			echo 2;die; 
			
		
		}
		if(empty($userInfo['wxid']) && empty($userInfo['wxopenid'])){
			$qrcode	=	$WxM -> applyWxQrcode($_COOKIE['wxloginid']);
			if(empty($qrcode)){
				echo 3;die;
			}else{
				echo 4;die;
			}
		}else{
			echo 5;die;
		}
    
	}
}
?>