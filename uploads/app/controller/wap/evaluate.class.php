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
class evaluate_controller extends common{
	function index_action(){
		$evaluateM=$this->MODEL('evaluate');
		
		$where['keyid']			=	array('<>',0);
		//分页链接
		$urlarr['c'] 		= 	$_GET['c']; 
		$urlarr['page'] 	= 	"{{page}}";
		$pageurl			=	Url('wap',$urlarr);
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('evaluate_group',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			
			$where['orderby']	=	'ctime,desc';
			
		    $where['limit']		=	$pages['limit'];
			
		    $List				=	$evaluateM->getList($where,array('field'=>'`id`,`pic`,`name`,`description`,`visits`','pic'=>1));
			
			$this->yunset("rows",$List);
		}
		$this->seo("evaluate");
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluatelist'));
	}
	function show_action(){
		$evaluateM 	= 	$this->MODEL('evaluate'); 
		
		$id 		= 	intval($_GET['id']); 
		$info		=	$evaluateM->getInfo(array('id'=>$id),array('field'=>'`id`,`name`,`description`,`visits`')); 
		$this->yunset('info',$info);
		
		if($info['id']==''){
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"没有找到相关测评哦！");
		}
		//题目数
		$questions 	= 	$evaluateM->getEvaluateNum(array('gid'=>$id));
		$this->yunset('questions',$questions);
		
		$evaluateM->upEvaluateGroup(array('visits'=>array('+',1)),array('id'=>$info['id']));
		
		$data['exampaper']	=	$info['name'];		
		$this->data			=	$data;
		$this->seo('exampaper');
		
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluateshow'));
	}
	function exampaper_action(){
		$evaluateM 	= 	$this->MODEL('evaluate'); 
		$id 		= 	intval($_GET['id']); 
		$info		=	$evaluateM->getInfo(array('id'=>$id),array('field'=>'`id`,`name`,`description`'));
		$this->yunset('info',$info);
		
		if($info['id']==''){
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"没有找到相关测评哦！");
		}
		
		$arr=array();
		$questions 	= 	$evaluateM->getEvaluateList(array('gid'=>$id),array('letters'=>1));
		$this->yunset('questions',$questions);	
		
		$data['exampaper']	=	$info['name'];		
		$this->data			=	$data;
		$this->seo('exampaper');
		
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluatepaper'));
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
			'utype'		=>	'wap',
		);
		$return=$evaluateM->addEvaluateLog($data);
		if($return['errcode']==10){
			$this->ACT_msg_wap($return['url'],$return['msg']);
		}else{
			$this->layer_msg($return['msg'],$return['errcode'],0,$return['url'],2);
		}
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
			$this->ACT_msg_wap(Url("wap",array('c'=>"evaluate")),"试卷不存在哦！");
		}
		$exambase	=	$evaluateM->getInfo(array('id'=>$info['examid']),array('field'=>'`id`,`name`,`toscore`,`fromscore`,`comment`'));
		$this->yunset('exambase',$exambase); 
		
		$data['exampaper']	=	$exambase['name'];		
		$this->data			=	$data;
		$this->seo('gradeshow');
		
		$this->yunset("headertitle","职业测评");
		$this->yuntpl(array('wap/evaluategradeshow'));
	}
	function create_uuid($prefix = "yun"){    //可以指定前缀
		$str 	= 	md5(uniqid(mt_rand(), true));   
		$uuid  	= 	substr($str,0,12);   
		return $prefix.$uuid; 
	}
}
?>