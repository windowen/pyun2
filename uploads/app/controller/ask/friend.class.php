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
class friend_controller extends ask_controller{
	function myquestion_action(){		
		$uid=(int)$_GET['uid'];		
		
		if($uid==''){
			$uid=$this->uid;
		}
		$this->yunset("myuid",$uid);
		
		$this->yunset("navtype",'myquestion');
		
		$M			=	$this -> MODEL('ask');
		
		$userinfoM	=	$this -> MODEL('userinfo');
		
		$info		=	$M -> getInfo('',array('where'=>array('uid'=>(int)$_GET['uid'])));
		
		$username	=	$userinfoM -> getInfo(array('uid'=>$uid),array('field'=>"`uid`,`username`"));
		
		if($this->uid==$uid){
			
			$data['nickname']	=	$this->username;
			
		}elseif($info['nickname']){
			
			$data['nickname']	=	$info['nickname'];
			
		}elseif($username['username']){
			
			$data['nickname']	=	$username['username'];
			
		}
		$this->data=$data;
		
		$this->seo("myquestio");
		
		$this->ask_tpl('myquestion');
	}
	function myanswer_action(){
		$M			=	$this -> MODEL('ask');
		
		$userinfoM	=	$this -> MODEL('userinfo');
		
		$uid		=	(int)$_GET['uid'];
		
		if($uid==''){
			
			$uid=$this->uid;
		}
		$this->yunset("myuid",$uid);
		
		$where['uid']=	$uid;
		
		$pageurl	=	Url('ask',array("c"=>$_GET['c'],'a'=>$_GET['a'],'uid'=>$uid,"page"=>"{{page}}"));
		
		$pageM		=	$this  -> MODEL('page');
		$pages		=	$pageM -> pageList('answer',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			$where['orderby']	=	'add_time';
			
			$where['limit']		=	$pages['limit'];
			
			$List	=	$M -> getAnswersList($where);
			
			$this->yunset("rows" , $List);
		}
		
		$this->yunset("navtype","myquestion");
		
		
		$username	=	$userinfoM -> getInfo(array('uid'=>$uid),array('field'=>"`uid`,`username`"));
		
		$info		=	$M -> getInfo('',array('where'=>array('uid'=>(int)$_GET['uid']),'field'=>'nickname'));
		
		if($this->uid==$uid){
			
			$data['nickname']	=	$this->username;
		}elseif($info['nickname']){
			
			$data['nickname']	=	$info['nickname'];
		}elseif($username['username']){
			
			$data['nickname']	=	$username['username'];
		}
		$this->data=$data;
		
		$this->seo("myanswer");
		
		$this->ask_tpl('myanswer');
 	}
	
	function attenquestion_action(){
		$M			=	$this -> MODEL('ask');
		
		$userinfoM	=	$this -> MODEL('userinfo');
		
		$uid		=	(int)$_GET['uid'];
		
		if($uid==''){
			$uid=$this->uid;
		}
		$this->yunset("myuid",$uid);
		
        $atnlist	=	$M -> getAtnInfo(array('uid'=>$uid,'type'=>1),array('field'=>'ids'));
		
		$ids		=	array_filter(@explode(',',$atnlist['ids']));
		
		if(count($ids)){
			$where['id']=	array('in',pylode(',',$ids));
			
			$pageurl	=	Url('ask',array("c"=>$_GET['c'],'a'=>$_GET['a'],'uid'=>$uid,"page"=>"{{page}}"));
			
			$pageM		=	$this  -> MODEL('page');
			$pages		=	$pageM -> pageList('question',$where,$pageurl,$_GET['page']);
			
			if($pages['total'] > 0){
				
				$where['orderby']	=	'add_time';
				
				$where['limit']		=	$pages['limit'];
				
				$List	=	$M -> getList($where);
				
				$this->yunset("rows" , $List);
			}
			
		}
		
		$this->yunset("navtype",'myquestion');
		
		$username	=	$userinfoM -> getInfo(array('uid'=>$uid),array('field'=>"`uid`,`username`"));
		
		$info		=	$M -> getInfo('',array('where'=>array('uid'=>(int)$_GET['uid']),'field'=>'nickname'));
		
		if($this->uid==$uid){
			
			$data['nickname']	=	$this->username;
		}elseif($info['nickname']){
			
			$data['nickname']	=	$info['nickname'];
		}elseif($username['username']){
			
			$data['nickname']	=	$username['username'];
		}
		$this->data=$data;
		
		$this->seo('attenquestion');
		
		$this->ask_tpl('attenquestion');
	}
	function attention_action(){
		$M		=	$this -> MODEL('ask');
		
		if($this->uid==''||$this->username==''){
			$this->layer_msg('请先登录！',8,0,$_SERVER['HTTP_REFERER']);
		}
		$this->is_login();
		
		$id 	=	(int)$_POST['id'];
		
		$type	=	(int)$_POST['type'];
		
		if($id==''&&(int)$_GET['id']){
			
			$id	=	(int)$_GET['id'];
			
			$type=	1;
		}
		$data	=	array(
			'uid'		=>	$this->uid,
			
			'usertype'	=>	$this->usertype,
			
			'type'		=>	$type,
			
			'id'		=>	$id
		);
		$return	=	$M -> setAttention($data);
		
		if($return['id']){
			
			if($_GET['id']){
				
				$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
			}else{
				
				$this->layer_msg($return['msg'],$return['errcode'],0,$return['atnnum'],$return['isadd']);
			}
		}else{
			
			$this->layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
		}
	}
	function delask_action(){
		$id	=	(int)$_GET['id'];
		if($id){
			$AskM	=	$this	->	MODEL('ask');

			if($_GET['type']==1){
			
				$result	=	$AskM -> delAnswer(array("id"=>$id,"uid"=>$this->uid));
			}else{
			
				$result	=	$AskM -> delquestion($id,array('uid'=>$this -> uid));
			}
			$result?$this->layer_msg('操作成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('操作失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
}
?>