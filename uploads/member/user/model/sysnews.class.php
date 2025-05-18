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
class sysnews_controller extends user{
	
	function index_action(){
		
		$SysmsgM			=	$this -> MODEL('sysmsg');
		
		$where['fa_uid']	=	$this -> uid;
		
		$where['usertype']	=	$this->usertype;
		
		$urlarr				=	array("c"=>"sysnews","page"=>"{{page}}");
		
		$pageurl			=	Url('member',$urlarr);
		
		$pageM				=	$this  -> MODEL('page');
		
		$pages				=	$pageM -> pageList('sysmsg',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);
		
		if($pages['total'] > 0){
			
			$where['orderby']	=	'id';
			
			$where['limit']		=	$pages['limit'];
			
			$rows	=	$SysmsgM -> getList($where);

		}
		
		$this -> yunset("rows",$rows);
		
		$this -> public_action();
		
		$this -> user_tpl('sysnews');
	}
	
	function del_action(){
		
		$SysmsgM	=	$this->MODEL('sysmsg');	
    
    $LogM     =  $this->MODEL('log');
		
		if($_GET['del'] || $_GET['id']){
			
			if ($_GET['id']){
				
				$id   =  intval($_GET['id']);
			
			}elseif ($_GET['del']){
				
				$id   =  $_GET['del'];
			
			}
			
			$return		=	$SysmsgM -> delSysmsg($id,array('fa_uid'=>$this->uid));
			
			$LogM-> addMemberLog($this -> uid, $this->usertype,"删除系统消息",18,3);
			
			$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
		
		}
		
	}
	
	function set_action(){
		
		$SysmsgM	=	$this->MODEL('sysmsg');
		
		if(intval($_POST['id'])){
			
			$SysmsgM -> upSysmsg(array('id'=>intval($_POST['id']),'fa_uid'=>$this->uid,'remind_status'=>'0'),array('remind_status'=>'1'));
		
		}
	
	}
	
}
?>