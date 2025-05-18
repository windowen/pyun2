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
class ask_controller extends common{
	function index_action(){
		$this->get_moblie();
		
		$M	=	$this -> MODEL('ask');
        if($this->uid){ 
			$my_attention	=	$M -> getAtnInfo(array('uid'=>$this->uid,'type'=>1));
			
			$my_atten		=	@explode(',',rtrim($my_attention['ids'],",")); 
			
			$this->yunset("my_atten",$my_atten);			
		}
		
		$where['is_recom']	=	'1';
		$where['orderby']	=	'add_time';
		$where['limit']		=	'6';
		
		$recom				=	$M -> getList($where,array("field"=>"uid,nickname,pic",'utype' => 'hot'));
		
		$this->yunset('recom',$recom['recUser']);

		$this->yunset("headertitle","职场问答");
		
		$this->seo('ask_index');
		
		$this->yuntpl(array('wap/ask'));
	}
	
	function list_action(){
		$this->get_moblie();
		
		$M	=	$this -> MODEL('ask');
        if($this->uid){ 
			$my_attention	=	$M -> getAtnInfo(array('uid'=>$this->uid,'type'=>1));
			
			$my_atten		=	@explode(',',rtrim($my_attention['ids'],",")); 
			
			$this->yunset("my_atten",$my_atten);			
		} 
	    $CacheM		=	$this -> MODEL('cache');
		
        $CacheList	=	$CacheM -> GetCache(array('ask'));
		
		$this->yunset($CacheList);
		
		if(trim($_GET['keyword'])){
			
			$this->addkeywords(12,trim($_GET['keyword']));
		}
		$this->yunset("getinfo",$_GET);
		
		$this->yunset("headertitle","问答列表"); 
		if($_GET['cid']){

			$qclass = $M->getQclassInfo($_GET['cid'],array('field'=>"`pid`"));
			
			if($qclass['pid']){
				$data['seacrh_class']=$CacheList['ask_name'][$qclass['pid']];
				$data['ask_class_name']=$CacheList['ask_name'][$_GET['cid']];
			}else{
				$data['seacrh_class']=$CacheList['ask_name'][$qclass['pid']];
				$data['ask_class_name']=$CacheList['ask_name'][$_GET['cid']];
			}
			$data['ask_desc']=strip_tags($CacheList['ask_intro'][$_GET['cid']]);
		
		}else{
			$data['ask_class_name']='';
			$data['ask_desc']='';
		}
		$this->data=$data;
		
		$this->seo('ask_search');
		
		$this->yuntpl(array('wap/asklist'));
	}
	function content_action(){
		$this -> get_moblie();
		
	    $M			=	$this -> MODEL('ask');
		
	    $atnM		=	$this -> MODEL('atn');
		
        $UserInfoM	=	$this -> MODEL('userinfo');
				
        $ID		=	(int)$_GET['id'];
		
		$show	=	$M -> getInfo($ID);
		
		$where['qid']=	$ID;
		
		$pageurl=Url('wap',array("c"=>$_GET['c'],"a"=>$_GET['a'],'id'=>$ID,'orderby'=>$_GET['orderby'],"page"=>"{{page}}"));
		
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
		$this->yunset("headertitle","问题详细");
		$this->seo("ask_content");
		$this->yunset("backurl",Url('wap',array("c"=>'ask')));
		$this->yuntpl(array('wap/askcontent'));
	}
	
	function answer_action(){
		$this->get_moblie();
				
		$M		=	$this -> MODEL('ask');
		
		$data	=	array(
			
			'uid'		=>	$this->uid,
				
			'username'	=>	$this->username,
			
			'usertype'	=>	$this->usertype,
				
			'id'		=>	(int)$_POST['id'],
				
			'content'	=>	$_POST["content"],
			
			'authcode'	=>	$_POST["authcode"],
			
			'utype'		=>	'wap'
		);
		$return	=	$M -> addAnswerInfo($data);
		
		echo json_encode($return);die;
		
	}		
	function topic_action(){
		$this->get_moblie();
		
	    $M=$this->MODEL('ask');
		
        if($this->uid){ 
			$my_attention	=	$M -> getAtnInfo(array('uid'=>$this->uid,'type'=>1));
			
			$my_atten		=	@explode(',',rtrim($my_attention['ids'],",")); 
			
			$this->yunset("my_atten",$my_atten);			
		} 
		$this->yunset("headertitle","职场话题");

		$this->data['ask_class_name'] = '';
		
		$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : '';
		
		if($pid){
			include(PLUS_PATH.'/ask.cache.php');
			$this->data['ask_class_name'] = isset($ask_name[$pid]) ? $ask_name[$pid] . '-' : '';
		}
		
		$this->seo("ask_topic");
		
		$this->yuntpl(array('wap/asktopic'));
	}	
	function myquestion_action(){
		$this->get_moblie();
		$uid=(int)$_GET['uid'];				
		if($uid==''){
			$uid=$this->uid;
		}					
	    $M			=	$this -> MODEL('ask');     
		$CacheM		=	$this -> MODEL('cache');
        $CacheList	=	$CacheM -> GetCache(array('ask'));
		$this->yunset($CacheList);				
		/* $urlarr['c']='ask';
		$urlarr['a']='myquestion';
		$urlarr['page']='{{page}}';
		$pageurl=Url('wap',$urlarr);
		$rows=$M->get_page('question',"`uid`='".$uid."'",$pageurl,'10');		
		$this->yunset($rows); */
		
		if($uid){
			$MemberM	=	$this -> MODEL("userinfo");	
			$member		=	$MemberM -> getInfo(array('uid'=>$uid),array('field'=>"`uid`,`usertype`"));
			$usertype	=	$member['usertype'];
			$minfo		=	$MemberM->getUserInfo(array("uid"=>$uid),array("usertype"=>$usertype));
			if($usertype==2||$usertype==4){
				
			        $pic= $minfo['logo'];
			    
			}elseif($usertype==1||$usertype==3){
			    
			        $pic= $minfo['photo'];
			    
			}
			$row['pic']=checkpic($pic);
		    if($usertype==1||$usertype==2||$usertype==4){
 				$row['name']=$minfo['name'];
			}elseif($usertype==3){
 				$row['name']=$minfo['realname'];
			}
		}
 		$this->yunset("row",$row);
 		//seo数据
 		$info=$M->getInfo(array('uid'=>$uid),array('field'=>'nickname'));
 		if($info['nickname']){
 		    $data['nickname']=$info['nickname'];
 		}else{
 		    $data['nickname']=$row['name'];
 		}
 		$this->data=$data;
 		
		$q_num = $M->getQuestionNum(array('uid'=>$uid));
		$this->yunset("q_num",$q_num?$q_num:0);
		
		$qw_num = $M->getQuestionNum(array('uid'=>$uid,'state'=>'1'));
		$this->yunset("qw_num",$qw_num);
			
		$a_num = $M->getAnswersNum(array('uid'=>$uid));
		$this->yunset("a_num",$a_num?$a_num:0);
			
 		$atnlist=$M->getAtnInfo(array('uid'=>$uid,'type'=>1),array('field'=>'ids'));
		$ids=array_filter(@explode(',',$atnlist['ids']));
			
		if(count($ids)){
 			$g_num=$M->getQuestionNum(array('id'=>array('in',pylode(',',$ids))));
		}
	 		
 		$this->yunset("g_num",$g_num?$g_num:0);
		
		$this->yunset("myuid",$uid);
		$this->yunset("backurl",Url('wap',array("c"=>'ask')));
		$this->seo("myquestio");
		$this->yunset("headertitle","个人主页");
		$this->yuntpl(array('wap/question'));
	}
	function delask_action(){
		$id=(int)$_GET['id'];
		if($id){
			$AskM	=	$this -> MODEL('ask');
			//删除问题、回答、评论
			if($_GET['type']==1){//删除回答
			
				$result	=	$AskM -> delAnswer(array("id"=>$id,"uid"=>$this->uid));
			}else{//删除问题
			
				$result	=	$AskM -> delquestion($id);
			}
			$result?$this->layer_msg('操作成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('操作失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	function attention_action(){			
	    $M		=	$this -> MODEL('ask');
		
		$uid	=	$this->uid;
		
		$id		=	(int)$_POST['id'];
		
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
	function myanswer_action(){
		$this->get_moblie();
		
	    $M		=	$this -> MODEL('ask');
		
		$uid	=	(int)$_GET['uid'];
		
		if($uid==''){
			$uid=	$this->uid;
		}
		$where['uid']=	$uid;
		
		$pageurl	=	Url('wap',array("c"=>$_GET['c'],'a'=>$_GET['a'],'uid'=>$uid,"page"=>"{{page}}"));
		
		$pageM		=	$this  -> MODEL('page');
		$pages		=	$pageM -> pageList('answer',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			$where['orderby']	=	'add_time';
			
			$where['limit']		=	$pages['limit'];
			
			$List	=	$M -> getAnswersList($where);
			
			$this->yunset("rows" , $List);
		}
		if($uid){
			$MemberM	=	$this -> MODEL("userinfo");
			
			$member		=	$MemberM -> getInfo(array('uid'=>$uid),array('field'=>"`uid`,`usertype`"));
			
			$usertype	=	$member['usertype'];
			
			$minfo		=	$MemberM->getUserInfo(array("uid"=>$uid),array("usertype"=>$usertype));
			
			if($usertype==2||$usertype==4){
				
			    $pic	=	$minfo['logo'];
			    
			}elseif($usertype==1||$usertype==3){
			    
			    $pic	=	$minfo['photo'];
			    
			}
			$row['pic']	=	checkpic($pic);
			if($usertype==1||$usertype==2||$usertype==4){
 				$row['name']=$minfo['name'];
			}elseif($usertype==3){
 				$row['name']=$minfo['realname'];
			}
		}
		
 		$this->yunset("row",$row);
 			
 		$q_num = $M->getQuestionNum(array('uid'=>$uid));
		
		$this->yunset("q_num",$q_num?$q_num:0);
		
		$qw_num = $M->getQuestionNum(array('uid'=>$uid,'state'=>'1'));
		
		$this->yunset("qw_num",$qw_num);
			
		$a_num = $M->getAnswersNum(array('uid'=>$uid));
		
		$this->yunset("a_num",$a_num?$a_num:0);
			
 		$atnlist=$M->getAtnInfo(array('uid'=>$uid,'type'=>1),array('field'=>'ids'));
		
		$ids=array_filter(@explode(',',$atnlist['ids']));
		
		if(count($ids)){
			
 			$g_num=$M->getQuestionNum(array('id'=>array('in',pylode(',',$ids))));
		}
	 		
 		$this->yunset("g_num",$g_num?$g_num:0);
 		
		$info=$M->getInfo(array('uid'=>(int)$_GET['uid']),array('field'=>'nickname'));
		if($info['nickname']){
			$data['nickname']=$info['nickname'];
		}else{
		    $data['nickname']=$row['name'];
		}
		$this->data=$data;
		
		$this->seo("myanswer");
		
		$this->yunset("myuid",$uid);
		
		$this->yunset("backurl",Url('wap',array("c"=>'ask')));
		
		$this->yunset("headertitle","个人主页");
		
		$this->yuntpl(array('wap/answer'));
	}
	
	function attenquestion_action(){
		$this->get_moblie();
		
	    $M		=	$this -> MODEL('ask');	
		
		$uid	=	(int)$_GET['uid'];
		
		if($uid==''){
			$uid=	$this->uid;
		}
        $atnlist=	$M -> getAtnInfo(array('uid'=>$uid,'type'=>1),array('field'=>'ids'));
		
		$ids=array_filter(@explode(',',$atnlist['ids']));
		
		if(count($ids)){
			$where['id']=	array('in',pylode(',',$ids));
			
			$pageurl	=	Url('wap',array("c"=>$_GET['c'],'a'=>$_GET['a'],'uid'=>$uid,"page"=>"{{page}}"));
			
			$pageM		=	$this  -> MODEL('page');
			$pages		=	$pageM -> pageList('question',$where,$pageurl,$_GET['page']);
			
			if($pages['total'] > 0){
				
				$where['orderby']	=	'add_time';
				
				$where['limit']		=	$pages['limit'];
				
				$List	=	$M -> getList($where);
				
				$this->yunset("rows" , $List);
			}
		}
		
		if($uid){
			$MemberM	=	$this -> MODEL("userinfo");
			
			$member		=	$MemberM -> getInfo(array('uid'=>$uid),array('field'=>"`uid`,`usertype`"));
			
			$usertype	=	$member['usertype'];
			
			$minfo		=	$MemberM->getUserInfo(array("uid"=>$uid),array("usertype"=>$usertype));
			
			if($usertype==2||$usertype==4){
				
			    $pic	=	$minfo['logo'];
			    
			}elseif($usertype==1||$usertype==3){
			    
			    $pic	=	$minfo['photo'];
			    
			}
			$row['pic']	=	checkpic($pic);
		    if($usertype==1||$usertype==2||$usertype==4){
				
 				$row['name']=$minfo['name'];
			}elseif($usertype==3){
				
 				$row['name']=$minfo['realname'];
			}
		}
 		$this->yunset("row",$row);
		
 		$q_num = $M->getQuestionNum(array('uid'=>$uid));
		
		$this->yunset("q_num",$q_num?$q_num:0);
		
		$qw_num = $M->getQuestionNum(array('uid'=>$uid,'state'=>'1'));
		
		$this->yunset("qw_num",$qw_num);
			
		$a_num = $M->getAnswersNum(array('uid'=>$uid));
		
		$this->yunset("a_num",$a_num?$a_num:0);
			
 		$atnlist=$M->getAtnInfo(array('uid'=>$uid,'type'=>1),array('field'=>'ids'));
		
		$ids=array_filter(@explode(',',$atnlist['ids']));
		
		if(count($ids)){
			
 			$g_num=$M->getQuestionNum(array('id'=>array('in',pylode(',',$ids))));
		}
	 		
 		$this->yunset("g_num",$g_num?$g_num:0);
 		
 		
 		
		$this->yunset("headertitle","个人主页");
		
		$info=$M->getInfo(array('uid'=>(int)$_GET['uid']),array('field'=>'nickname'));
		
		if($info['nickname']){
			$data['nickname']=$info['nickname'];
		}else{
		    $data['nickname']=$row['name'];
		}
		$this->data=$data;
		
		$this->seo("attenquestion");
		
		$this->yunset("myuid",$uid);
		
		$this->yunset("backurl",Url('wap',array("c"=>'ask')));
		
		$this->yuntpl(array('wap/attenquestion'));
	}
	function hotweek_action(){
		$this->get_moblie();
		
	    $M			=	$this -> MODEL('ask'); 
		     
		$CacheM		=	$this -> MODEL('cache');
		
        $CacheList	=	$CacheM -> GetCache(array('ask'));
		
		$this->yunset($CacheList);
		 if($this->uid){
			$my_attention	=	$M -> getAtnInfo(array('uid'=>$this->uid,'type'=>1));
			
			$my_atten		=	@explode(',',rtrim($my_attention['ids'],",")); 
			
			$this->yunset("my_atten",$my_atten);			
		} 		
		$this->yunset("headertitle","一周热点");
		
		$this->seo("ask_hot_week");
		
		$this->yuntpl(array('wap/askhotweek'));
	}
	function addquestion_action(){
		if($this->uid==''){				
			$data['msg']	=	'请先登录！';
			
			$data['url']	=	$this->config['sy_weburl'].'/wap/index.php?c=login';
			
			$this->yunset("layer",$data);
		 }
	    $CacheM		=	$this -> MODEL('cache');
		
        $CacheList	=	$CacheM -> GetCache(array('ask'));
		
		$this->yunset($CacheList);
		
		$this->yunset("headertitle","发布问答");
		
		$this->seo("ask_add_question");
		
		$this->yuntpl(array('wap/addquestion'));
	}
	function addquestions_action(){
		$M		=	$this -> MODEL('ask');
		
		$data	=	array(
			'uid'		=>	$this->uid,
				
			'usertype'	=>	$this->usertype,

			'username'	=>	$this->username,
				
			'cid'		=>	(int)$_POST['cid'],
				
			'title'		=>	trim($_POST['title']),
				
			'authcode'	=>	$_POST['authcode'],
				
			'content'	=>	$_POST["content"]
		);
		$return	=	$M -> addAskInfo($data);
		
		echo json_encode($return);die; 
	}
	function qclass_action(){
		$CacheM	=	$this -> MODEL('cache');
		
        $info	=	$CacheM -> GetCache(array('ask'));
		
		$rows	=	array();
		
		$id		=	(int)$_POST['id'];
		foreach($info['ask_type'][$id] as $v){
			$rows[$v]=urlencode($info['ask_name'][$v]);
		}
		$rows = $this->JSON($rows);
		//$rows = json_encode($rows);
		echo urldecode($rows);die;
	}

	function qrepost_action(){
		$M			=	$this->MODEL('ask');
		
		$logM		=	$this -> MODEL('log');
		
		$reportM	=	$this -> MODEL('report');
			
        $userinfoM	=	$this -> MODEL('userinfo');
		
		if($this->uid==""||$this->username==''){
			echo 'no_login';die;
		}
		$eid	=	(int)$_POST['eid'];
		
		$reason	=	$_POST['reason'];
		
		$is_set	=	$reportM -> getReportOne(array('type'=>1,'r_type'=>1,'eid'=>$eid),array('field'=>'`p_uid`'));
		if(empty($is_set)){
            $question	=	$M -> getInfo($eid,array('field'=>'`uid`'));
			
			$Userinfo	=	$userinfoM -> getInfo(array('uid'=>$question['uid']),array('field'=>'`username`'));
			
            $my_nickname=	$userinfoM -> getInfo(array('uid'=>$this->uid),array('field'=>'`username`'));
			
			$data['did']		=	$this->userdid;
			
			$data['p_uid']		=	$this->uid;
			
			$data['c_uid']		=	$question['uid'];
			
			$data['eid']		=	(int)$_POST['eid'];
			
			$data['usertype']	=	$this->usertype;
			
			$data['inputtime']	=	time();
			
			$data['username']	=	$my_nickname['username'];
			
			$data['r_name']		=	$Userinfo['username'];
			
			$data['r_reason']	=	$_POST['reason'];
			
			$data['type']		=	1;
			
			$data['r_type']		=	1;
			
			$new_id=$reportM -> addAskReport($data);
			
			if($new_id){
				
			    $logM -> addMemberLog($this->uid,$this->usertype,'举报问答问题',23,1);
				echo '1';
			}else{
				echo '0';
			}
		}else{
			if($is_set['p_uid']==$this->uid){
				echo '2';
			}else{
				echo '3';
			}
		}
	}
	function getcomment_action(){
		$M		=	$this -> MODEL('ask');
		
		$aid	=	(int)$_POST['aid'];
		
		$comment=	$M -> getCommentsList(array('aid'=>$aid,'orderby'=>'add_time,asc'));
		
		if(is_array($comment)){
			
			foreach($comment as $k=>$v){
				

				$comment[$k]['pic']			=	checkpic($v['pic'],$this->config['sy_friend_icon']);

				$comment[$k]['errorpic']	=	checkpic('',$this->config['sy_friend_icon']);
				
				$comment[$k]['url']			=	Url('wap',array("c"=>'ask','a'=>"myquestion",'uid'=>$v['uid']));
				
				$comment[$k]['nickname']	=	urlencode($v['nickname']);
				
				$comment[$k]['content']		=	urlencode($v['content']);
				
				$comment[$k]['date']		=	date("Y-m-d H:i",$v['add_time']);
				
				if($v['uid']==$this->uid){
					
					$comment[$k]['myself']='1';
				}
			}
			$comment_json = $this->JSON($comment);
			
			echo urldecode($comment_json);die;
		}
	}
	function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
		static $recursive_counter = 0;
		
		if (++$recursive_counter > 1000) {
			die('possible deep recursion attack');
		}
		
		foreach ($array as $key => $value){
			if (is_array($value)) {
				$this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
			} else {
				$array[$key] = $function($value);
			}
	  
			if ($apply_to_keys_also && is_string($key)) {
				$new_key = $function($key);
				if ($new_key != $key) {
					$array[$new_key] = $array[$key];
					unset($array[$key]);
				}
			}
		}
		$recursive_counter--;
	}

	
	function JSON($array) {
	    $this->arrayRecursive($array, 'urlencode', true);
	    $json = json_encode($array);
	    return urldecode($json);
	}
	function forcomment_action(){
		$M		=	$this -> MODEL('ask');
		if($this->uid==""||$this->username==''){
			echo 'no_login';die;
		}
		$data	=	array(
			'uid'		=>	$this->uid,
			
			'usertype'	=>	$this->usertype,
			
			'aid'		=>	(int)$_POST['aid'],
			
			'qid'		=>	(int)$_POST['qid'],
			
			'content'	=>	$_POST['content']
		);
		
		echo $M -> addReview($data);
	}
	function forsupport_action(){
		$M		=	$this -> MODEL('ask');
		
		$data	=	array(
			'uid'		=>	$this->uid,
			
			'usertype'	=>	$this->usertype,
			
			'aid'		=>	$_POST['aid']
		);
		
		echo $M -> upSupportInfo($data);die;
	}	
}
?>