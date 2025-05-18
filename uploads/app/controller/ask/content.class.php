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
class content_controller extends ask_controller{
	function index_action(){ 
		$M			=	$this -> MODEL('ask');
		
		$atnM		=	$this -> MODEL('atn');
		
		$UserInfoM	=	$this -> MODEL('userinfo');
		
		$ID			=	(int)$_GET['id'];
		
		$show		=	$M -> getInfo($ID);
		
		if(empty($show)){
 			$this->ACT_msg($_SERVER['HTTP_REFERER'],"问答不存在或被删除！");
		}
		
		$where['qid']=	$ID;
		
		$pageurl	=	Url('ask',array("c"=>$_GET['c'],'id'=>$ID,'orderby'=>$_GET['orderby'],"page"=>"{{page}}"));
		
		$pageM		=	$this  -> MODEL('page');
		$pages		=	$pageM -> pageList('answer',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			if($_GET['orderby']){
				$where['orderby']	=	$_GET['orderby'];
			}else{
				$where['orderby']	=	'add_time';
			}
			$where['limit']		=	$pages['limit'];
			
			$answer	=	$M -> getAnswersList($where);
			$this->yunset('total',$pages['total']);
		}
		
		$Userinfo	=	$UserInfoM -> getInfo(array('uid'=>$show['uid']),array('field'=>'username,usertype,uid'));
		
		$AnswerNum	=	$M -> getAnswersNum(array('qid'=>$show['id'])); 
		
		$M -> upStatusInfo($show['id'],'',array('answer_num'=>$AnswerNum));
		
		if($this->uid){
			$atten_ask	=	$M -> getAtnInfo(array('uid'=>$this->uid,'type'=>1));
			
			$atn		=	$atnM -> getatnList(array('uid'=>$this->uid),array('field'=>'sc_uid'));
			
		}
		
		$myinfo['pic']=checkpic('',$this->config['sy_friend_icon']);

		if(!empty($answer)){
			foreach($answer as $key=>$val){
				if($val['uid']==$this->uid){
					$answer[$key]['is_atn']='2';
				}else{
					foreach($atn as $a_v){
						if($a_v['sc_uid']==$val['uid']){
							$answer[$key]['is_atn']='1';
						}
					}
				}
			}
		}
		if($Userinfo['uid']==$this->uid){
			$Userinfo['useratn']='2';
			$show['qatn']='2';
		}else if(!empty($atn)){
			foreach($atn as $val){
				if($Userinfo['uid']==$val['sc_uid']){
					$Userinfo['useratn']='1';
				}
			}
		}
		if($atten_ask&&is_array($atten_ask)&&$show['qatn']==''){
			$ids=explode(',',rtrim($atten_ask['ids'],','));
			if(in_array($show['id'],$ids)){
				$show['qatn']='1';
			}
		}
		$data['ask_title']	=	$show['title'];
		
		$this->data			=	$data;
		
        $CacheM	=	$this -> MODEL('cache');
		
        $reason	=	$CacheM -> GetCache('reason');
		
        $M -> upStatusInfo((int)$_GET['id'],'',array("visit"=>array('+','1')));
		
 		$this->yunset("userinfo",$Userinfo);
 		$this->yunset("myinfo",$myinfo);
        $this->yunset(array("reason"=>$reason,"show"=>$show,"uid"=>$this->uid,"answer"=>$answer,"ask_title"=>$show['title'].' - '.$this->config['sy_webname'],"c"=>"index"));
		$this->seo('ask_content');
		$this->ask_tpl('content');
	}
}
?>