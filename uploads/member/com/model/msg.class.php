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
class msg_controller extends company{	
	
	function index_action(){
		
		$MsgM	=	$this -> MODEL('msg');
		
		$where['job_uid']		=	$this -> uid;
		
		$where['del_status']	=	array('<>','1');
		
		$urlarr		=	array("c" => "msg","page" => "{{page}}");
		
		$pageurl	=	Url('member',$urlarr);
		
		$pageM		=	$this -> MODEL('page');
		
		$pages		=	$pageM -> pageList('msg',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);
		
		if($pages['total'] > 0){
			
			
			$where['orderby']	=	'datetime';
			
			
			$where['limit']		=	$pages['limit'];
			
			
			$rows	=	$MsgM -> getList($where);
			
		}
		
		$this -> yunset("rows",$rows['list']);
		
		$this -> public_action();
		
		$this -> company_satic();
		
		$this -> com_tpl('msg');
	
	}
	
	function del_action(){
		
		$MsgM	=	$this -> MODEL('msg');
		
		if($_GET['id']){
			
			$where['id']		=	(int)$_GET['id'];
			
			$where['job_uid']	=	$this -> uid;
			
			$nid	=	$MsgM -> upInfo($where);
			
			if($nid){
 				
				$this ->MODEL('log')-> addMemberLog($this -> uid, 2,"删除求职咨询",18,3);//会员日志
				
				$this -> layer_msg('删除成功！',9,0,"index.php?c=msg");
 			
			}else{
 				
				$this -> layer_msg('删除失败！',8,0,"index.php?c=msg");
 			
			}
		}
	}
	
	function save_action(){
		
		$MsgM	=	$this -> MODEL('msg');
		
		if($_POST['submit']){
			
			$data['reply']					=	$_POST['reply'];
			
			$data['reply_time']				=	time();
			
			$data['user_remind_status']		='0';
			
			$id		=	$MsgM -> upReplyInfo(array('id' => $_POST['id'], 'job_uid' => $this -> uid),$data);
			
			if($id){
				
				$this ->MODEL('log')-> addMemberLog($this -> uid, 2,"回复求职咨询",18,1);//会员日志
 				
				$this->ACT_layer_msg("回复成功！",9,"index.php?c=msg");
 			
			}else{
 				
				$this->ACT_layer_msg("添加失败！",8,"index.php?c=msg");
 			
			}
		}
	}
	
	function getContent_action(){
	    
	    $MsgM  =  $this -> MODEL('msg');
	    
	    $msg   =  $MsgM->getInfo(array('id'=>$_POST['id'],'job_uid'=>$this -> uid),array('field'=>'`content`'));
	    
	    echo json_encode($msg);
	}
}
?>