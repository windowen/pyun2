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
class index_controller extends evaluate_controller{ 

	//列出 试卷分组、试卷名称列表
	function index_action(){
		
		if(!isset($_COOKIE['nuid'])){
			$this->cookie->setcookie("nuid",$this->create_uuid(),3600);
		}
		
		$evaluateM			=	$this->MODEL('evaluate');

		$rows				=	$evaluateM->getList(array('hot'=>1,'keyid'=>array('>',0),"orderby"=>"sort,desc",'limit'=>10),array('field'=>'`id`,`keyid`,`pic`,`name`,`description`,`visits`','pic'=>1));
		 $this->yunset("rows",$rows); 
		

		$evaluateRecommend 	= 	$evaluateM->getList(array('keyid'=>array('<>',0),'recommend'=>1,'limit'=>'8'),array('field'=>'`id`,`keyid`,`name`,`visits`'));
		$this->yunset('evaluateRecommend',$evaluateRecommend);

		$examinee 			= 	$evaluateM->getEvaluateLogList(array('uid'=>array('<>',''),'orderby'=>'ctime,desc',"groupby"=>'uid','limit'=>'12'));
		$this->yunset('examinee',$examinee);
		
		$this->seo('evaluate');
		$this->evaluate_tpl('index'); 
	} 
	function morelist_action(){
		$evaluateM	=	$this->MODEL('evaluate'); 
		$group		=	$evaluateM->getList(array('keyid'=>'0'),array('field'=>"`id`,`name`"));
		 $this->yunset("group",$group); 
		 
		if(isset($_GET['gid']) && is_numeric($_GET['gid'])){
			$gid	=	(int)$_GET['gid'];
		}else{
			$gid	=	$group[0]['id'];
		}
		$this->yunset("gid",$gid);
		
		$where['keyid']		=	$gid;

		$urlarr['c'] 		= 	$_GET['c']; 
		$urlarr['gid'] 		= 	$gid; 
		$urlarr['page'] 	= 	"{{page}}";
		$pageurl			=	Url('evaluate',$urlarr);

		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('evaluate_group',$where,$pageurl,$_GET['page']);
		

		if($pages['total'] > 0){
			
			
			$where['orderby']	=	'sort,desc';
			
		    $where['limit']		=	$pages['limit'];
			
		    $List				=	$evaluateM->getList($where,array('field'=>'`id`,`keyid`,`pic`,`name`,`description`,`visits`','pic'=>1));
			
			$this->yunset("rows",$List);
		}
		
		if(!isset($_COOKIE['nuid'])){
			$this->cookie->setcookie("nuid",$this->create_uuid(),time()+3600);
		}
		
		$this->seo("morelist");
		$this->evaluate_tpl('morelist');
	}  
}
?>