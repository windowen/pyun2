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
class exampaper_controller extends evaluate_controller{ 
 
	function index_action(){ 	
		$evaluateM 	= 	$this->MODEL('evaluate'); 
		$examid 	= 	intval($_GET['titleid']); 
		
		$info		=	$evaluateM->getInfo(array('id'=>$examid),array('pic'=>1,'field'=>'`id`,`name`,`pic`,`description`,`visits`,`comnum`,`ctime`')); 
		$this->yunset('info',$info);
		
		if($info['id']==''){
			$this->ACT_msg($this->config['sy_weburl'],"没有找到相关测评！");
		} 
		
		
		$questions	= 	$evaluateM->getEvaluateList(array('gid'=>$examid),array('letters'=>1));
		$this->yunset('questions',$questions);	
		
		$arr		=	array();
		foreach($questions as $key=>$val){
			$arr[]=$val['id'];
 		}   
		$this->yunset('arr',"['".@implode("','",$arr)."']");
		
		$evaluateM->upEvaluateGroup(array('visits'=>array('+',1)),array('id'=>$info['id']));	 
		 
		$data['exampaper']	=	$info['name'];		
		$this->data			=	$data;
		$this->seo('exampaper');
		$this->evaluate_tpl('exampaper');
	}
	
	/*展现得分*/
	function grade_action(){
		$evaluateM	=	$this->MODEL('evaluate');
		
		$questions 	= 	$evaluateM->getEvaluateList(array('gid'=>(int)$_POST['examid']),array('field'=>'`id`,`score`'));
			
		$score		=	$pid	=array();
		foreach($questions as $val){
			$pid[]	=	$val['id'];
			$score['q'.$val['id']]	= 	$val['score'];
		}  
		$scores		=	0;
		foreach($pid as $val){  
			$scores	+=	$score['q'.$val][$_POST['q'.$val]]; 
		}
		if($this->uid && $this->username){
			$uid	=	$this->uid;
			$type	=	'uid';
		}else if($_COOKIE['nuid']){ 
			$uid	=	$_COOKIE['nuid']; 
			$type	=	'nuid';
		}else{
			$uid	=	$this->create_uuid();
			$type	=	'nuid';
			$this->cookie->setcookie("nuid",$uid,time()+3600); 
		}
		
		$data=array(
			'examid'	=>	$_POST['examid'],
			'uid'		=>	$uid,
			'type'		=>	$type,
			'hidGid'	=>	$_POST['hidGid'],
			'scores'	=>	$scores,
			'utype'		=>	'pc',
		);
		$return=$evaluateM->addEvaluateLog($data);
		header("location:".$return['url']); 
	}
	function gradeshow_action(){ 
		$evaluateM	=	$this->MODEL('evaluate');
		
		$id			=	(int)$_GET['id']; 
		
		if($this->uid &&$this->username){
			$where['id']	=	$id;
			$where['uid']	=	$this->uid;
		}else{
			$where['id']	=	$id;
			$where['nuid']	=	$_COOKIE['nuid'];
		}
		//测试结果
		$info		=	$evaluateM->getEvaluateLogInfo($where,array('field'=>'`grade`,`examid`,`id`'));
		$this->yunset('info',$info);
		
		if($info['id']==''){
			$this->ACT_msg(Url('evaluate'),"试卷不存在哦！");
		}
		//试卷信息
		$exambase	=	$evaluateM->getInfo(array('id'=>$info['examid']),array('field'=>'`id`,`name`,`num`,`comnum`,`toscore`,`fromscore`,`comment`,`visits`'));
		$this->yunset('exambase',$exambase); 
		
		/*测热门评*/
		$recom 		= 	$evaluateM->getList(array('keyid'=>array('<>',0),'recommend'=>1,'limit'=>'8'),array('field'=>'`id`,`keyid`,`name`,`visits`'));
		$this->yunset('recom',$recom);
		
		/*他们也参加了测评*/
		$examinee 			= 	$evaluateM->getEvaluateLogList(array('uid'=>array('<>',''),'orderby'=>'ctime,desc',"groupby"=>'uid','limit'=>'12'));
		$this->yunset('examinee',$examinee);
		
		//留言
		$mwhere['examid']	=	$info['examid'];
		
		$urlarr['c'] 		= 	$_GET['c']; 
		$urlarr['a'] 		= 	$_GET['a']; 
		$urlarr['id'] 		= 	(int)$_GET['id']; 
		$urlarr['page'] 	= 	"{{page}}";
		$pageurl			=	Url('evaluate',$urlarr);
		
		$pageM				=	$this  -> MODEL('page');
		$pages				=	$pageM -> pageList('evaluate_leave_message',$mwhere,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			$mwhere['orderby']	=	'ctime,desc';
		    $mwhere['limit']	=	$pages['limit'];
			
		    $rows				=	$evaluateM->getMessageList($mwhere);
			$this->yunset("rows",$rows);
		}
		//测评记录
		if($this->uid){
			$lwhere['uid']		=	$this->uid;
		}else if($_COOKIE['nuid']){
			$lwhere['nuid']		=	$_COOKIE['nuid'];
		}
		$lwhere['orderby']		=	'id,desc';
		$lwhere['limit']		=	8;
		
		$list 					= 	$evaluateM->getEvaluateLogList($lwhere,array('utype'=>'index'));
		$this->yunset('list',$list); 
		
		$data['exampaper']	=	$exambase['name'];		
		$this->data			=	$data;
		$this->seo('gradeshow');
		$this->evaluate_tpl('gradeshare');
	}
	
	function message_action(){
		$evaluateM	=	$this->MODEL('evaluate');
		$data		=	array(
			'examid'	=>	(int)$_POST['examid'],
			'message'	=>	trim($_POST['message']),
			'uid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
			'username'	=>	$this->username,
			'nuid'		=>	$_COOKIE['nuid'],
			
		);
		$return 	=	$evaluateM->addMessage($data);
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
	}
}
?>