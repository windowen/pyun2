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
class ask_model extends model{
    
    /**
     * @desc   查询member会员表
     */
    private function getMemberList($where = array(), $field = '*'){
        
        $members  =   $this -> select_all('member', $where, $field);
        
        return $members;
    }
    
    
	/**
	* @desc    获取问答管理列表
	* @Desc    查训 question 表数据 ，多条数据查询
	* @param   $whereData:查询条件
	* @param   $data:自定义处理数组 
	*/
	public function getList($whereData,$data=array()) {        
       
		$select   =   $data['field'] ? $data['field'] : '*';     
      
		$List     =   $this -> select_all('question', $whereData, $select);
		
		if(is_array($List) && $List ){
		    
		    if (isset($data['utype']) && $data['utype']=='admin') {
		         
    			foreach($List  as $v){
                    
                    $uids[]		=	$v['uid'];   //作者id
                    
    				$cids[]		=	$v['cid'];   //类别id
                        
                }
    			
    			$memberList		=	$this -> select_all('member',array('uid'=>array('in',pylode(',',$uids))));
    			
    			$qclassList		=	$this -> getQclassList(array('id'=>array('in',pylode(',',$cids))));
    			
    			foreach($List as $key => $val){
    			
					$List[$key]['pic']=checkpic($val['pic'],$this->config['sy_friend_icon']);
					
    				foreach($memberList as $v){
    					
    					if($val['uid'] == $v['uid']){
    						
    						$List[$key]['username']	=	$v['username'];
    					
    					}
    				
    				}
    				
    				foreach($qclassList as $value){
    					
    					if($val['cid'] == $value['id']){
    						
    						$List[$key]['classname']	=	$value['name'];
    					
    					}
    				
    				}
    			
    			}
    			
		    }
			if(isset($data['utype']) && $data['utype'] == 'hot'){	// 推荐达人
				$recUser	=	$recUids	=	array();
				foreach ($List as $k => $v){
					$v['pic']	=	checkpic($v['pic'], $this->config['sy_friend_icon']);
					if(!in_array($v['uid'], $recUids)){
						$recUids[]	=	$v['uid'];
						$recUser[]	=	$v;
					}
				}
				$List['recUser']	=	$recUser;
			}
			return $List;  
		}
	}
	
	/**
	* @desc    获取问答详情
	* @desc    查询 question 单条记录查询
	*/
	public function getInfo($id, $data     =   array()){
		
		if(!empty($id)){
			
			$where['id']	=	intval($id);
			
		}elseif($data['where']){
			
			$where			=	$data['where'];
			
		}
		
		$select		=	$data['field'] ? $data['field'] : '*';	
		
		$question	=	$this -> select_once('question', $where, $select);
		
		if($question&&is_array($question)){
			
			$question['pic']=checkpic($question['pic'] , $this->config['sy_friend_icon']);
			
		}
		return $question;
	
	}
	
	/**
	* @desc    修改问答详情
	* @desc    修改question表数据
	*/
	public function upAskInfo($whereData, $data = array()){
		
		if(!empty($whereData)) {
			
			$PostData = array(
				
				'title'			=>	$data['title'],
				
				'cid'			=>	$data['cid'],
				
				'visit'			=>	$data['visit'],
				
				'is_recom'		=>	$data['is_recom'],
				
				'content'		=>	$data['content'],
				
			);
			
			if ($data && is_array($data)){
				
				 $nid  =  $this -> update_once('question', $PostData, array('id'=>$whereData['id']));
			
			}
		}
		return $nid;
	}
	
	/**
	* @desc   修改问答审核状态
	*/
	public function upStatusInfo($id , $whereData = array(), $data = array()){

		if(!empty($id)){
			
			if(is_array($id)){
				
				$where['id']	=	array('in',pylode(',',$id));
			
			}else{
				
				$where['id']	=	intval($id);
			
			}
			
			$nid  =  $this -> update_once('question', $data, $where);
		
		}
		
		return $nid;
	
	}
	
	public function upRecommend($whereData,$data=array()){
		
		if(!empty($whereData)){
			
			$nid	=  $this -> update_once('question', $data, $whereData);
			
		}
		
		return $nid;
	
	}
	
	/**
	* @desc    发布问答
	*/
	public function addAskInfo($data = array()){
		if(!empty($data)){
			if($data['title']==""){
				$return['msg']		=	"标题不能为空！";
				
				$return['errcode']	=	8;
				
				return $return;
			}
			
			include_once ('notice.model.php');
				
			$noticeM		=		new notice_model($this->db, $this->def);
			
			$result			=		$noticeM->jycheck($_POST['authcode'],'职场提问');
			
			if(!empty($result)){
				$return['errcode']		=	8;
				$return['msg']			=	$result['msg']?$result['msg']:'';
				if($result['error']==107){
					$return['waperrcode']	=	4;
				}else{
					$return['waperrcode']	=	0;
				}
				
				return $return;
			}
			

			//判断每日最多发布问答数配置项，判断当日已发布问答数
			if(isset($this->config['sy_day_ask_num']) && $this->config['sy_day_ask_num'] > 0) {
				
				$dayAskNum = $this->getQuestionNum(array("uid"=>$data['uid'],'add_time'=>array('>=',strtotime(date("Y-m-d"))),'add_time'=>array('<=',time())));
				
				if($dayAskNum >= $this->config['sy_day_ask_num']){
					
					$return['msg']		=	"您今天已发布".$dayAskNum."个问答了，请明日再发！";
					
					$return['errcode']	=	8;
					
					return $return;
				}
			}
			
			$auto=true;
			
			if($data['usertype']&&$data['uid']){
				include_once ('userinfo.model.php');
				
				$userinfoM	=	new userinfo_model($this->db, $this->def);
				
				$minfo		=	$userinfoM -> getUserInfo(array("uid"=>$data['uid']),array("usertype"=>$data['usertype']));
				
 				$addData['nickname']	=	trim($data['username']);

				if($data['usertype']==2||$data['usertype']==4 ){
					
					$addData['nickname']	=	$minfo['name'];
					$addData['pic']			=	$minfo['logo'];
				}elseif($data['usertype']==1 ||$data['usertype']==5){
					
					$addData['nickname']	=	$minfo['name'];
					$addData['pic']			=	$minfo['photo'];
				}elseif($data['usertype']==3){
					
					$addData['nickname']	=	$minfo['realname'];
					$addData['pic']			=	$minfo['photo'];
				}
			}
			
			$addData['title']	=	$data['title'];
			
			$addData['cid']		=	$data['cid'];
			
			$addData['content'] =	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'','',''),$data["content"]);
			
			$addData['content']	=	strip_tags(trim($addData['content']),"<p> <br> <b>");
			
			$addData['uid']		=	$data['uid'];
			
			$addData['add_time']=	time();

			//后台开启问答审核，则默认为未审核；关闭审核，则默认为审核通过
			$addData['state'] = $this->config['ask_check'] == 1 ? 0 : 1;

			//审核未通过的企业用户，发布问答为“未审核”状态
			if($addData['state'] != 0 && $data['usertype'] == 2){

				$member = $this->select_once('member',array('uid'=>$data['uid']),'`status`');
				
				if($member['status'] != 1){
					
					$addData['state'] = 0;
				}
			}
			
			$return['id']  =  $this -> insert_into('question', $addData);
			if($return['id']){
				include_once ('integral.model.php');
				
				$integralM	=	new integral_model($this->db, $this->def);
				
				$result=$integralM->max_time('发布问题',$data['uid'],$data['usertype']);	
				
				if($result==true||$auto==false){
					
					$integralM->company_invtal($data['uid'],$data['usertype'],$this->config['integral_question'],$auto,"发布问题",true,2,'integral');
				}
				
				include_once ('friend.model.php');
				
				
				$this -> addMemberLog($data['uid'],$data['usertype'],"发布了问答《".$data['title']."》",19,1);
				
				unset($_SESSION['authcode']);

				if($data['state'] == 0){
					$return['msg']		=	"已发布，正在审核中！";
					
					$return['errcode']	=	9;
				}
				else{
					$return['msg']		=	"发布成功！";
					
					$return['errcode']	=	9;
				}
			}else{
				$return['msg']		=	"提问失败！";
				
				$return['errcode']	=	8;
			}
			
		}
		return $return;
	}
	
	
	/**
	* @desc   查询问答类别表
	*/
	public function getQclassList($where = array()){
	   
		$select		=	$data['field'] ? $data['field'] : '*'; 
	   
		$q_class  	=   $this -> select_all('q_class', $where, $select);
	    
	    return $q_class;
	}
	
	/**
	* @desc   获取问答类别详情
	*/
	public function getQclassInfo($id,$data	=	array()){
		
		if(!empty($id)){
			
			$select		=   $data['field'] ? $data['field'] : '*';	
			
			$QclassInfo	=	$this -> select_once('q_class', array('id'=>intval($id)), $select);

			if($QclassInfo['pic']){
				$QclassInfo['pic']	=	checkpic($QclassInfo['pic']);
			}
		
		}
		
		return $QclassInfo;
	
	}
	
	/**
	* @desc   修改问答类别
	*/
	public function upQclassInfo($whereData,$data = array()){
		
		if(!empty($whereData)) {
			
			$PostData	=	array(
				
				'name'		=>	$data['name'],
				
				'pid'		=>	$data['pid'],
				
				'sort'		=>	$data['sort'],
				
				'intro'		=>	$data['intro'],
				
				'content'	=>	$data['content'],
				
			);

			if($data['pic']){
				$PostData['pic']	=	$data['pic'];
			}
			
			if ($data && is_array($data)){
				
				 $nid  =  $this -> update_once('q_class', $PostData, array('id'=>$whereData['id']));
			
			}
		
		}
		
		return $nid;
	
	}
	
	/**
	* @desc   添加问答类别
	*/
	public function addQclassInfo($data = array()){	
		
		$PostData	=	array(		
			
			'name'		=>	$data['name'],
			
			'pid'		=>	$data['pid'],
			
			'sort'		=>	$data['sort'],
			
			'intro'		=>	$data['intro'],
			
			'content'	=>	$data['content'],
			
		);	

		if($data['pic']){
			$PostData['pic']	=	$data['pic'];
		}
		
		if ($data && is_array($data)){	
			
			$nid	=	$this -> insert_into('q_class', $PostData);
		
		}
		
		return $nid;
	
	}
	
	/**
	* @desc   查询问答话题
	*/
	public function getAnswersList($where = array(), $data=array()){
		
	    $field  =	empty($data['field']) ? '*' : $data['field'];
	    
	    $answers  =   $this -> select_all('answer', $where, $field);
		if(is_array($answers) && $answers ){
			
			foreach($answers  as $v){
				
				$a_uid[]	=	$v['uid'];
				
				$qid[]		=	$v['qid'];
			
			}
			
			$awhere['uid']	=	array('in',pylode(',',$a_uid));
			
			$member		=	$this -> select_all('member',$awhere);
			$resume		=	$this -> select_all('resume',$awhere,'`uid`,`photo`');
			
			$company	=	$this -> select_all('company',$awhere,'`uid`,`logo`');
			$question	=	$this -> getList(array('id'=>array('in',pylode(',',$qid))),array('field'=>'`id` as `qid`, `title`'));
			foreach($answers as $key => $val){
        if(strlen($val['nickname'])>10){
          $answers[$key]['nickname']   =  mb_substr($val['nickname'],0,10,'utf-8').'...';
        }
				foreach($member as $v){
					
					if($val['uid'] == $v['uid']){
						
						$answers[$key]['username']	=	$v['username'];
						
						if(!$val['nickname']){
							
							$answers[$key]['nickname']	=	$v['username'];
						}
					
					}
				
				}
				foreach($question as $v){
					
					if($val['qid']==$v['qid']){
						
						$answers[$key]['title']	=	$v['title'];
						
						$answers[$key]['qid']	=	$v['qid'];
					}
				}
				$answers[$key]['content'] = str_replace("\r\n","<br />",$val["content"]);
				
				if($val['pic']){
					
					$pic=checkpic($val['pic']);
				}else{
					foreach($resume as $va){
						
						if($va['uid']==$val['uid']){
							
							$pic=checkpic($va['photo']);
								
						}
					}
					foreach($company as $va){
						
						if($va['uid']==$val['uid']){
							
							$pic=checkpic($va['logo']);
							
						}
					}
					
				}
				
				
				if($pic){
					
					$answers[$key]['pic']=$pic;
				}else{
					
					$answers[$key]['pic']=checkpic('',$this->config['sy_friend_icon']);
				}
			}
		
		}

	    return $answers;
	
	}	
	
	/**
	* @desc   修改回答
	*/
	public function upAnswerInfo($whereData ,$data = array()){
		
		if(!empty($whereData)) {
			
			if ($data && is_array($data)){
				
				 $nid  =  $this -> update_once('answer', $data, $whereData);
			
			}
		
		}
		
		return $nid;
	}
	/**
	* @desc   发布回答
	*/
	public function addAnswerInfo($data = array()){
		
		if(!empty($data)) {
			if($data['content']&&$data['id']){
				if($data['utype']=='wap'){
					session_cache_limiter('private, must-revalidate');
					
					session_start();
					
					$authcode=md5(strtolower($data['authcode']));	
					
					if($authcode!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
						
						unset($_SESSION['authcode']);
						
						$arr['msg']		=	"验证码错误！";
						
						return $arr;	
					}
				}
				
				$auto=true;
				 
				$info	=	$this -> getInfo($data['id'],array('field'=>'`id`,`uid`,`title`,`content`'));
				
				$content	=	$data["content"];
				
				$content	=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'','',''),$content);
				
				$addData=array();
				
				if($data['usertype']&&$data['uid']){
					include_once ('userinfo.model.php');
					
					$userinfoM	=	new userinfo_model($this->db, $this->def);
					
					$minfo		=	$userinfoM -> getUserInfo(array("uid"=>$data['uid']),array("usertype"=>$data['usertype']));
					
					if($data['usertype']==2||$data['usertype']==4){
						
						$addData['nickname']	=	$minfo['name'];
						
						$addData['pic']			=	$minfo['logo'];
					}elseif($data['usertype']==1){
						
						$addData['nickname']	=	$minfo['name'];
						
						$addData['pic']			=	$minfo['photo'];
					}elseif($data['usertype']==3){
						
						$addData['nickname']	=	$minfo['realname'];
						
						$addData['pic']			=	$minfo['photo'];
					}
				}
				
				$addData['usertype']			=	$data['usertype'];
				
				$addData['qid']		=	$data['id'];
				
				$addData['content']	=	trim(strip_tags($content));
				
				$addData['uid']		=	$data['uid'];
				
				$addData['comment']	=	0;
				
				$addData['support']	=	0;
				
				$addData['oppose']	=	0;
				
				$addData['add_time']=	time();
				
				$arr['id']=$this -> insert_into('answer', $addData);
				
				if($arr['id']){
					include_once ('integral.model.php');
				
					$integralM	=	new integral_model($this->db, $this->def);
					
					$result		=	$integralM->max_time('回答问题',$data['uid'],$data['usertype']);		
					if($result==true||$auto==false){
						
						$integralM->company_invtal($data['uid'],$data['usertype'],$this->config['integral_answer'],$auto,"回答问题",true,2,'integral');
					}
					
					$this -> update_once('question',array('answer_num'=>array('+','1'),"lastupdate"=>time()),array('id'=>$info['id']));
					
					include_once ('friend.model.php');
					
					$this -> addMemberLog($data['uid'],$data['usertype'],"回答了问答《".$info['title']."》",19,1);
					
					$arr['msg']		=	"回答成功！";
					
					$arr['errcode']	=	9;
				}else{
					$arr['msg']		=	"回答失败！";
					
					$arr['errcode']	=	8;
				}
			}else{
				$arr['msg']		=	"非法操作！";
				
				$arr['errcode']	=	8;
			}
			return $arr;
		}
	}
	
	/**
	* @desc   评论列表
	*/
	public function getCommentsList ($whereData,$data=array()) {       
		
		$select  =   $data['field'] ? $data['field'] : '*'; 
		
		$CommentList    =   $this -> select_all('answer_review',$whereData,$select);
		
		if(is_array($CommentList) && $CommentList ){
			
			foreach($CommentList  as $v){
				
				$uids[]		=	$v['uid'];
			
			}
			$where['uid']	=	array('in',pylode(',',$uids));
			
			$memberList		=	$this -> select_all('member',$where,"`username`,`uid`");
			
			$userList		=	$this -> select_all('resume',$where,"`photo`,`uid`,`name`");
			
			$comList		=	$this -> select_all('company',$where,"`logo`,`uid`");
			
			
			
			foreach($CommentList as $key => $val){
				
				if($val['usertype']==1){
					foreach($memberList as $v){
					
						if($v['uid']==$val['uid']){
							
							$CommentList[$key]['username']	=	$v['username'];
							
							// $CommentList[$key]['nickname']	=	$v['username'];
						}
					
					}
					foreach($userList as $v){
						
						if($v['uid']==$val['uid']){
							
							$CommentList[$key]['nickname']	=	$v['name'];
							
							$v['photo']?$CommentList[$key]['pic']=$v['photo']:$CommentList[$key]['pic']=$this->config['sy_friend_icon'];
						}
					}
				}
				
				if($val['usertype']==2){
					foreach($comList as $v){
						
						if($v['uid']==$val['uid']){
							
							$v['logo']?$CommentList[$key]['pic']=$v['logo']:$CommentList[$key]['pic']=$this->config['sy_friend_icon'];
						}
					}
				}
			
			}
		
		}
		
		return $CommentList;
	
	}
	
	/**
	* @desc   评论返回回答列表
	*/
	public function getCommentBackQuestion($id,$data=array()){
		
		if(!empty($id)){
			
			$select  		  =   $data['field'] ? $data['field'] : '*';	
			
			$QuestionInfo	  =	  $this -> select_once('answer', array('id'=>intval($id)), $select);
		
		}
		
		return $QuestionInfo;
	
	}
	/**
	* @desc   评论数量
	*/
	public function getAnswersNum($where=array()){
		
		return $this -> select_num('answer', $where);
	
	}
	
	/**
	* @desc  获取评论详细信息
	*/
	public function getReviewInfo($id,$data=array()){
		
		if(!empty($id)){
			
			$select   	  =   $data['field'] ? $data['field'] : '*';	
			
			$ReviewInfo	  =	 $this -> select_once('answer_review', array('id'=>intval($id)), $select);
		
		}
		
		return $ReviewInfo;
	
	}
	
	/**
	* @desc   修改评论
	*/
	public function upReview($whereData ,$data = array()){
		
		if(!empty($whereData)) {
			
			$PostData	=	array(
				
				'content'	=>	$data['content'],
			
			);
			
			if ($data && is_array($data)){
				
				 $nid  =  $this -> update_once('answer_review', $PostData, array('id'=>$whereData['id']));
			
			}
		
		}
		
		return $nid;
	}
	/**
	* @desc   发布评论
	*/
	public function addReview($addData = array()){
		
		if(!empty($addData)) {
			
			$auto=true;
			 
			$data['aid']		=	(int)$addData['aid'];
			
			$data['qid']		=	(int)$addData['qid'];
			
			$data['content']	=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'background-color:','background-color:','white-space:'),$addData['content']);
			 
			$data['uid']		=	$addData['uid'];
			
			$data['add_time']	=	time();
			
			$data['usertype']	=	$addData['usertype'];
			
			$new_id=$this -> insert_into('answer_review', $data);
			
			if($new_id){
				include_once ('integral.model.php');
				
				$integralM	=	new integral_model($this->db, $this->def);
				
				$result		=	$integralM -> max_time('评论问答',$addData['uid'],$data['usertype']);
				
				if($result==true||$auto==false){
					
					$integralM->company_invtal($data['uid'],$data['usertype'],$this->config['integral_answerpl'],$auto,"评论问答",true,2,'integral');
				}
				
				$this -> addMemberLog($addData['uid'],$addData['usertype'],"评论问答",19,1);
				
				$this -> upAnswerInfo(array('id'=>$addData['aid']),array('comment'=>array('+','1')));
				
				return '1';
			}else{
				return '0';
			}
		
		}
	}
	
	/**
	* @desc   删除评论
	*/
	public function delReview($delId){
		
		if(empty($delId)){
			
			return array(
				
				'errcode'	=>	8,
				
				'msg'		=>	'请选择要删除的数据！',
			
			);
		
		}else{
			
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$return['layertype']	=	1;
			
			}else{
				
				$return['layertype']	=	0;
			}
			
			$nid	=	$this -> delete_all('answer_review',array('id' => array('in',$delId)),'');
			
			if($nid){
								
				$this -> update_once("answer",array('comment'=>$comment),array('id'=>$_GET['aid']));
				
				$return['errcode']	=	$nid?'9':'8';
				
				$return['msg']		=	$nid ?'问答评论删除成功！' : '问答评论删除失败！';
			
			}
		
		}
		
		return	$return;
	
	}
	
	/**
	* @desc   删除试卷
	*/
	public function delquestion($delId,$data = array()){
		
		if($delId){
			if($data['utype'] = 'admin'){
				$delQuesWhere	=	array('id' => array('in',$delId));
				$delAnWhere		=	array('qid' => array('in',$delId));
			}else{
				$delQuesWhere	=	array('uid'=>$data['uid'],'id' => array('in',$delId));
				$delAnWhere		=	array('uid'=>$data['uid'],'qid' => array('in',$delId));
			}
			$result=$this -> delete_all('question' , $delQuesWhere,'');
			
			$this -> delete_all('answer_review' , $delAnWhere,'');
			
			$this -> delete_all('answer' , $delAnWhere,'');
			
		}
		return $result;
	}
	
	/**
	* @desc   删除问答类别
	*/
	public function delQclass($delId){
		
		if(empty($delId)){
           
			return	array(
              
			  'errcode' => 8,
               
			  'msg' 	=> '请选择要删除的数据！',                
            );
        
		}else{
			
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$return['layertype']	=	1;
			
			}else{
				
				$return['layertype']	=	0;
			}

			 
			$nid	=	$this -> delete_all('q_class',array('id' => array('in',$delId)),'');
			
			if($nid){
				
				$return['msg']		=	'问答分类';
				
				$return['errcode']	=	$nid ? '9' :'8';
				
				$return['msg']		=	$nid ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
			
			}
		
		}
		
		return	$return;
		
	}
	
	public function delquestiongroups($delId){
		
		if($delId){
			
			$this -> delete_all('question' , array('id' => array('in',$delId)),'');
			
			$this -> delete_all('answer' , array('qid' => array('in',$delId)),'');
			
			$this -> delete_all('answer_review' , array('qid' => array('in',$delId)),'');
			
		}
	}
	
	/**
	* @desc   删除回答
	*/
	public function delAnswer($where=array(),$delId){
	 
		if($where){
			
			$result	=	$this -> delete_all('answer' , $where,'');
			
		}else{
			$result['layertype']		=	0;
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$result['layertype']	=	1;
			}
			$result['id']	=	$this -> delete_all('answer' , array('id'=>array('in',$delId)),'');
			
			$this -> delete_all("answer_review",array('aid'=>array('in',$delId)),'');
			
			$result['msg']		=	'用户回答(ID:'.$delId.')';
			
			$result['errcode']	=	$result['id']?9:8;
			
			$result['msg']		=	$result['id']?'删除成功！':'删除失败！';
		}
		return $result;
	}
	
	/**
	* @desc    获取问答数量
	* @desc    查询 question 数量
	*/
	public function getQuestionNum($wheredata = array()){
		
		return $this -> select_num('question',$wheredata);
	
	}
	
	/**
	 * @desc 查询关注信息表单条记录
	 * 
	 */
	public function getAtnInfo($whereData = array(), $data = array()){
	    
	    $select    =   $data['field'] ? $data['field'] : '*';
	    
	    $atnInfo   =   $this -> select_once('attention', $whereData, $select);
	    
	    return $atnInfo; 
	    
	}
	
	/**
	 * @desc   attention 表数据删除
	 * @param  array $whereData
	 * @return boolean
	 */
	function delAttention($whereData = array() ,$data = array()){
	    
	    if($data['type']=='one'){//单个删除
	        
	        $limit =	'limit 1';
	        
	    }
	    
	    if($data['type']=='all'){//多个删除
	        
	        $limit =	'';
	        
	    }
 
	    $result    =	$this	->	delete_all('attention', $whereData, $limit);
	    
	    return	$result;
	}
	
	/**
	 * @desc   修改attention表数据
	 * @param  array $whereData
	 * @param  array $data
	 * @return boolean
	 */
	function upAttention( $data = array(), $whereData = array()){
	    
	    if(!empty($whereData) && !empty($data)){
	        
	        $nid   =   $this -> update_once('attention', $data, $whereData);
	        
	    }
	    
	    return $nid;
	    
	}
	/**
	 * @desc   添加attention表数据
	 * @param  array $whereData
	 * @param  array $data
	 * @return boolean
	 */
	function addAttention($data = array()){
	    
	    if(!empty($data)){
	        
	        $nid   =   $this -> insert_into('attention', $data);
	        
	    }
	    
	    return $nid;
	    
	}
	
	function setAttention($data = array()){
		
		$isset=$this->getAtnInfo(array('uid'=>$data['uid'],'type'=>$data['type']));
		
		$ids=@explode(',',$isset['ids']);
		
		if($data['type']=='1'){
			$info		=	$this -> getInfo($data['id'],array('field'=>"`id`,`title`,`uid`,`atnnum`"));
			
			$gourl		=	Url('ask',array("c"=>"content","id"=>$info['id']));
			
			$content	=	"关注了<a href=\"".$gourl."\" target=\"_blank\">《".$info['title']."》</a>。";
			
			$n_contemt	=	"取消了对<a href=\"".$gourl."\" target=\"_blank\">《".$info['title']."》</a>的关注。";
			
			$log		=	"关注了《".$info['title']."》";
			
			$n_log		=	"取消了对《".$info['title']."》的关注";
		}else{
			$info		=	$this -> getQclassInfo($data['id'],array('field'=>"`id`,`name`,`atnnum`"));
			
			$content	=	"关注了《".$info['name']."》。";
			
			$n_contemt	=	"取消了《".$info['name']."》。";
			
			$log		=	"关注了".$info['name'];
			
			$n_log		=	"取消了对".$info['name']."</a>的关注。";
		}
		if($info['uid']==$data['uid']){
			$return['msg']		=	'不能关注自己发布的问题！';
			
			$return['errcode']	=	8;
			
			return $return;
		}else{
			$adata['uid']	=	$data['uid'];
			
			$adata['type']	=	$data['type'];
			
			$arr['isadd']	=	1;

			if($ids[0]==''||$ids[0]=='0'){
				$adata['ids']=	$data['id'];
				if(is_array($isset)&&$isset){
					$nid = $this -> upAttention($adata,array("id"=>$isset['id']));
				}else{
					$nid = $this -> addAttention($adata);
				}

				$this -> addMemberLog($data['uid'],$data['usertype'],$log,5,1);//会员日志

			} else if(in_array($data['id'],$ids)&&$ids[0]){
				$nids=array();
				foreach($ids as $val){
					if($val!=$data['id'] && $val && in_array($val,$nids)==false){
						$nids[]=$val;
					}
				}
				$arr['isadd']	=	0;
				
				$adata['ids']	=	pylode(',',$nids);
				
				$nid = $this -> upAttention($adata,array("id"=>$isset['id']));
				
				$this -> addMemberLog($data['uid'],$data['usertype'],$n_log,5,1);//会员日志

			}else if(in_array($data['id'],$ids)==false&&$ids[0]>0){

				$ids[]			=	$data['id'];
				
				$adata['ids']	=	pylode(',',$ids);
				
				$nid = $this -> upAttention($adata,array("id"=>$isset['id']));

				$this -> addMemberLog($data['uid'],$data['usertype'],$log,5,1);//会员日志

			}else if(in_array($data['id'],$ids)==false&&$ids[0]<1){

				$nid = $this -> upAttention(array("ids"=>$id),array("id"=>$isset['id']));

				$this -> addMemberLog($data['uid'],$data['usertype'],$log,5,1);//会员日志

			}
			if($nid){
				if($data['type']=='1'){
					if($arr['isadd']){
						$atnnum	=	$info['atnnum']+1;
						
						$this -> upStatusInfo($info['id'],'',array("atnnum"=>$atnnum));
				
						$sql['content']	=	$content;
					}else{
						$atnnum	=	$info['atnnum']-1;
						
						$this -> upStatusInfo($info['id'],'',array("atnnum"=>$atnnum));
				
						$sql['content']	=	$n_contemt;
					}
				}
				if($data['type']=='2'){
					include(LIB_PATH."cache.class.php");
					
					$cacheclass	=	new cache(PLUS_PATH,$this->obj);
					
					$makecache	=	$cacheclass->ask_cache("ask.cache.php");
				}
				
				if($atnnum<0){
					$atnnum=0;
				}
				
				$return['id']		=	$nid;
				
				$return['msg']		=	'操作成功！';
				
				$return['errcode']	=	9;
				
				$return['atnnum']	=	$atnnum;
				
				$return['isadd']	=	$arr['isadd'];
				
				return $return;
			}else{
				$return['msg']		=	'操作失败！';
				
				$return['errcode']	=	8;
				
				return $return;
			}
		}
	}
	
	function upSupportInfo($data=array()){
		if(!empty($data)){
			
			$cookid	=	explode(',', $_COOKIE['support']);
			
			if(in_array($data['aid'],$cookid)){
				
				echo '2';die;
			}else{
				$id	=	$this -> upAnswerInfo(array('id'=>$data['aid']),array('support'=>array('+','1')));
				
				if($id){
					$this -> addMemberLog($data['uid'],$data['usertype'],"给问题回答点赞",19,1);
					
					$sendid		=	array();
					
					$sendid[]	=	$data['aid'];
					
					if($_COOKIE['support']){
						$support=	$_COOKIE['support'].','.pylode(',',$sendid);
					}else{
						$support=	pylode(',',$sendid);
					}
					require_once ('cookie.model.php');
					
					$cookieM	=	new cookie_model($this->db, $this->def);
					
					$cookieM -> SetCookie("support",$support,time() + 86400);
					
					echo '1';
				}else{
					echo '0';die;
				}
			}
		}
	}
	
    /**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        
        require_once ('log.model.php');
        
        $LogM = new log_model($this->db, $this->def);
        
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera,$type); 
        
    }
	
}
?>