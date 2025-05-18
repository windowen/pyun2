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
class evaluate_model extends model{ 
     
	 
	/*
	 * 添加管理员日志 
	 */
	function adminLog($content, $opera = '', $type = '', $opera_id=''){
		require_once('log.model.php');
		$logM	=	new log_model($this->db,$this->def);
		return	$logM	->	addAdminLog($content, $opera = '', $type = '', $opera_id='');
	}
	/**
	* @desc   获取测评试卷列表
	* @param  $whereData:查询条件
	* @param  $data:自定义处理数组 
	*/
	public function getList($whereData,$data=array()) {      
		
		$select  =   $data['field'] ? $data['field'] : '*'; 
		
		$List    =   $this -> select_all('evaluate_group',$whereData,$select);
		if(!empty($List)){
			foreach($List as $k=>$v){
				if($data['pic']){
					
					$List[$k]['pic_n']	=	checkpic($v['pic'] , $this->config['sy_cplogo']);
					
				}
			}
		}
		return $List;
	}
	
	/**
	* @desc   获取测评试卷列表详情
	*/
	public function getInfo($whereData,$data	=	array()){
		
		$select	=	$data['field'] ? $data['field'] : '*';	
		
		$Info	=	$this -> select_once('evaluate_group', $whereData, $select);
		if(!empty($Info)){
			
			if($Info['fromscore']){
				$Info['fromscore_n'] 		= 	mb_unserialize($Info['fromscore']);
			}
			if($Info['toscore']){
				$Info['toscore_n'] 		= 	mb_unserialize($Info['toscore']);
			}
			if($Info['comment']){
				$Info['comment_n'] 		= 	mb_unserialize($Info['comment']);
			}
			//结果分析
			if($Info['fromscore_n']){
				$result	=	array();
				foreach($Info['fromscore_n'] as $k=>$v){
					$result[$k]['fromscore']=$v;
					$result[$k]['toscore']=$Info['toscore_n'][$k];
					$result[$k]['comment']=$Info['comment_n'][$k];
				}
				$Info['result']=$result;
			}
			
			
			if($data['pic'] && $Info['pic']){
				
				$Info['pic_n']	=	checkpic($Info['pic'] , $this->config['sy_cplogo']);
				
			}
		}
		return $Info;
	}
	
	/**
	* @desc   添加测评试卷列表详情
	*/
	public function addInfo($data = array()){
		
		$AddData	=	array(
			
			'fromscore'		=>	serialize($data['fromscore']),
			
			'toscore'		=>	serialize($data['toscore']),
			
			'comment'		=>	serialize($data['comment']),
			
			'name'			=>	trim($data['examtitle']),
			
			'pic'			=>	$data['pic'],
			
			'description'	=>	trim($data['description']),
			
			'top'			=>	intval($data['top']),
			
			'recommend'		=>	intval($data['recommend']),
			
			'hot'			=>	intval($data['hot']),
			
			'sort'			=>	intval($data['sort']),
			
			'keyid'			=>	intval($data['selectgroup']),
			
			'ctime'			=>	time()
		
		);

		if ($AddData && is_array($AddData)){
			
			$nid	=	$this -> insert_into('evaluate_group',$AddData);

			/** 
			if($nid && $qData && is_array($qData)){

				foreach($qData as $qk => $qv){
					
					$vData[$qk]['question']			=	$qv['question'];
					$vData[$qk]['score']			=	serialize($qv['score']);
					$vData[$qk]['option']			=	serialize($qv['option']);
					$vData[$qk]['gid']				=	$nid;

					$this -> insert_into('evaluate',$vData[$qk]);
				}
			} 
			*/
			 

		}
		
		return $nid;
	
	}
	/**
	* @desc   修改测评试卷列表详情
	*/
	public function upEvaluateGroup($data = array(), $whereData = array()){
		
		return 	$this -> update_once('evaluate_group',$data,$whereData);
	}
	/**
	* @desc   修改测评试卷列表详情
	*/
	public function upInfo($whereData, $data = array()){
		
		if(!empty($whereData)) {
			
			$PostData	=	array(
				
				'fromscore'		=>	serialize($data['fromscore']),
				
				'toscore'		=>	serialize($data['toscore']),
				
				'comment'		=>	serialize($data['comment']),
				
				'name'			=>	trim($data['examtitle']),
				
				'description'	=>	trim($data['description']),
				
				'top'			=>	intval($data['top']),
				
				'recommend'		=>	intval($data['recommend']),
				
				'hot'			=>	intval($data['hot']),
				
				'sort'			=>	intval($data['sort']),
				
				'keyid'			=>	intval($data['selectgroup']),
			
			);
			if($data['pic']){

				$PostData['pic']	=	$data['pic'];

			}

			if ($PostData && is_array($PostData)){
				
				$nid	=	$this -> update_once('evaluate_group',$PostData,array('id'=>$whereData['id']));
				
				/**
				if($nid && $qData && is_array($qData)){

					foreach($qData as $qk => $qv){
						
						$vData[$qk]['question']			=	$qv['question'];
						$vData[$qk]['score']			=	serialize($qv['score']);
						$vData[$qk]['option']			=	serialize($qv['option']);
						$vData[$qk]['gid']				=	$whereData['id'];
					 
						if(!empty($qv['qid'])){
							$this -> update_once('evaluate',$vData[$qk],array('id' => $qv['qid']));
						}else{
							$this -> insert_into('evaluate',$vData[$qk]);
						}
					}
				} 
				*/

			}
			
			return $nid;
		
		}
	
	}
	
	/**
	* @desc   删除测评试卷
	*/
	public function delevaluate($delId){
		
		if($delId){
 
			$nid	=	$this -> delete_all('evaluate_group',array('id'=>$delId), '');
			
			if($nid){
				
				$this -> delete_all('evaluate_leave_message',array('examidid'=>$delId), '');
				
				$this -> delete_all('evaluate_log',array('examidid'=>$delId), '');
				
				$this -> delete_all('evaluate',array('gid'=>$delId), '');
			
			}
		
		}
		
		return $nid;
	
	}
	
	/**
	* @desc   添加测评问题
	*/
	public function addEvaQuestion($data = array()){
			
			$AddData	=	array(
				
				'question'		=>	$data['question'],
				
				'option'		=>	$data['option'],
				
				'score'			=>	serialize($data['score']),
				
				'gid'			=>	$data['examid'],
				
				'sort'			=>	0
			
			);
			
			if ($AddData && is_array($AddData)){
				
				$nid	=	$this -> insert_into('evaluate',$AddData);
			
			}
			
			return $nid;
	
	}
	
	/**
	* @desc   修改测评问题
	*/
	public function upEvaQuestion($whereData, $data = array()){
		
		if(!empty($whereData)) {
			
			$PostData	=	array(
				
				'question'		=>	$data['question'],
				
				'option'		=>	serialize($data['option']),
				
				'score'			=>	serialize($data['score']),
			
			);
			
			if ($PostData && is_array($PostData)){
				
				$nid	=	$this -> update_once('evaluate',$PostData,array('id'=>$whereData['id']));
			
			}
			
			return $nid;
		
		}
	
	}
	
	/**
	* @desc   删除测评问题
	*/
	public function delEvaQuestion($delId){
		
		if($delId){
			
			$nid	=	$this -> delete_all('evaluate',array('id'=>$delId), '');
		
		}
		
		return $nid;
	
	}
	
	/**
	* @desc   获取问题、选项、试卷总分
	*/
	public function getEvaluateList($where = array(), $data =array()){
	    $select  =   $data['field'] ? $data['field'] : '*'; 
	    $List  =   $this -> select_all('evaluate', $where, $select);
	    if(!empty($List)){
			foreach($List as $k=>$v){
				if($v['option']){
					$List[$k]['option']		= 	mb_unserialize($v['option']);
				}
				if($v['score']){
					$List[$k]['score']		= 	mb_unserialize($v['score']);
				}
				if($data['letters']){
					$List[$k]['letters']	=	array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
				}
			}
		}
		return $List;
	}
	/**
	* @desc   获取测评题目数量
	*/
	function getEvaluateNum($whereData=array()){
		
		$List	=	$this -> select_num('evaluate',$whereData);
		
		return	$List;
	
	}
	/**
	* @desc   获取测评类别数量
	*/
	function getEvaluateGroupNum($whereData=array()){
		
		$List	=	$this -> select_num('evaluate_group',$whereData);
		
		return	$List;
	
	}
	/**
	* @desc   添加分组
	*/
	public function addEvaluateGroupInfo($data = array()){
		
		$AddData	=	array(
			
			'name'	=> 	$data['classname'],
			
			'keyid'	=>0,
		
		);
		
		if ($data && is_array($data)){
			
			$nid	=	$this -> insert_into('evaluate_group', $AddData);
		
		}
		
		return $nid;
	
	}
	
	/**
	* @desc   修改测评类别
	*/
	function upEvaluateGroupInfo($addData=array(),$whereData=array()){
		
		if($addData['name']){
			
			$type	=	'名称';
		
		}else{
			
			unset($addData['name']);
		
		}
		
		if($addData['sort']){
			
			$type	=	'排序';
		
		}else{
			
			unset($addData['sort']);
		
		}
		
		$nid	=	$this -> update_once('evaluate_group',$addData,$whereData);
		
		$this -> adminLog($type."修改成功");
	
	}
	
	/**
	* @desc   删除测评类别
	*/
	public function delEvaluateGroup($delId){
		
		if($delId){
			
			$nid		=$this -> delete_all('evaluate_group' , array('id' => array('in',$delId)),'');
		
		}
		
		return $nid;
	
	}
	
	public function delEvaluateGroups($delId){
		
		if($delId){

			$this -> delete_all('evaluate_group' , array('id' => array('in',$delId)),'');
			
			$this -> delete_all('evaluate_leave_message' , array('examidid' => array('in',$delId)),'');
			
			$this -> delete_all('evaluate_log' , array('examidid' => array('in',$delId)),'');
			
			$this -> delete_all('evaluate' , array('gid' => array('in',$delId)),'');
		}
	}
	
	/**
	* @desc   查询member会员表
	*/
	public function getMemberList($where = array(), $field = '*'){
	    
	    $members  =   $this -> select_all('member', $where, $field);
	    
	    return $members;
	}
	/**
	* @desc   获取测评留言管理列表
	*/
	public function getMessageList($whereData,$data=array()) {  
		$select  =   $data['field'] ? $data['field'] : '*'; 
		$List    =   $this -> select_all('evaluate_leave_message',$whereData,$select);
		
		if($List&&is_array($List)){
			
			$uid=array();
			foreach($List as $key=>$val){
				$List[$key]['name']	=	'访客';
				
				if($val['uid'] && in_array($val['uid'],$uid)==false && $val['usertype']){
					
					$uid[]			=	$val['uid'];
				}
			}
			
			$mwhere['uid']	=	array('in',pylode(',',$uid));
			$member			=	$this->getMemberList($mwhere);
			
			require_once ('userinfo.model.php');
			$userinfoM 		= new userinfo_model($this->db, $this->def);
			$userList		=	$userinfoM->getUserList(array('uid'=>array('in',pylode(',',$uid))));
			
			foreach($List as $key=>$val){
				
				foreach($member as $v){
					
					if($val['uid']==$v['uid']){
						
						$List[$key]['name']	=	$v['username'];
					}
				}
				foreach($userList as $v){
					
					if($val['uid'] == $v['uid']){
						
						$List[$key]['pic']		=	$v['pic']; 
					}
				}
				
			}
		}
		return $List;
	}
	
	/**
	* @desc   获取测评留言管理列表详细信息
	*/
	public function getMessageInfo($whereData,$data	=	array()){
		
		$select	=	$data['field'] ? $data['field'] : '*';	
		
		$MessageInfo	=	$this -> select_once('evaluate_leave_message', $whereData, $select);
		
		return $MessageInfo;
	
	}
	/**
	* @desc   查询评论数量
	*/
	function getMessageNum($whereData=array()){
		
		$List	=	$this -> select_num('evaluate_leave_message',$whereData);
		
		return	$List;
	
	}
	/**
	* @desc   测评留言
	*/
	public function addMessage($data = array()){
		
		$examid		=	(int)$data['examid'];
		$message	=	$data['message'];
		
		if($message==''){
			return array('msg'=>'评论内容不能为空！','errcode'=>8);
		}
		if($data['uid'] &&$data['username']){
			
			$uid		=	$data['uid'];
			$usertype	=	$data['usertype'];
		}else{
			$uid		=	$data['nuid'];
		}
		$post		=	array(
			'uid'		=>	$uid,
			'usertype'	=>	$usertype,
			'examid'	=>	$examid,
			'message'	=>	$message,
			'ctime'		=>	time()
		);
		$return['id']=$this->insert_into('evaluate_leave_message',$post);
		if($return['id']){
			return array('msg'=>'评论成功！','errcode'=>9);
		}else{
			return array('msg'=>'评论失败！','errcode'=>8);
		}
	}
	/**
	* @desc   删除测评留言管理列表
	*/
	public function delMessage($delId){
		
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
			 
			$delid=$this -> delete_all('evaluate_leave_message',array('id' => array('in',$delId)),'');
  			
			if($delid){
				
				$return['msg']		=	'评论';
				
				$return['errcode']	=	$delid ? '9' :'8';
				
				$return['msg']		=	$delid ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
			
			}
		
		}
		
		return $return;
	
	}
	/**
	* @desc   获取测评试卷列表详情
	*/
	public function getEvaluateLogInfo($whereData,$data	=	array()){
		
		$select	=	$data['field'] ? $data['field'] : '*';	
		
		$Info	=	$this -> select_once('evaluate_log', $whereData, $select);
		if(!empty($Info)){
			
			if($Info['examid']){
				//得分对应的评语
				$exambase	=	$this->getInfo(array('id'=>$Info['examid']),array('field'=>'`toscore`,`fromscore`,`comment`'));
				
				foreach($exambase['result'] as $v){
					
					if($v['fromscore']<=$Info['grade'] && $Info['grade']<= $v['toscore']){
						
						$comment 	= 	$v['comment'];break;
					}
				}
				$Info['comment_n']=$comment;
			}
			
		}
		return $Info;
	}
	
	/**
	* @desc   获取测评用户记录列表
	*/
	public function getEvaluateLogList($whereData,$data=array()) {      
		
		$select  =   $data['field'] ? $data['field'] : '*'; 
		
		$List    =   $this -> select_all('evaluate_log',$whereData,$select);
		
		if($List&&is_array($List)){
			
			$uid	=	array();
			
			foreach($List as $key => $val){
				
				$List[$key]['name']	=	'访客';
				if(in_array($val['uid'],$uid)==false && $val['uid']){
					
					$uid[]			=	$val['uid'];
				}
				if($val['examid'] && !in_array($val['examid'],$uid)){
					
					$examid[]		=	$val['examid'];
				}
			}
			$member			=	$this -> select_all('member',array('uid'=>array('in',pylode(',',$uid))),'`username`,`uid`');
			
			require_once ('userinfo.model.php');
			$userinfoM 		= new userinfo_model($this->db, $this->def);
			$userList		=	$userinfoM->getUserList(array('uid'=>array('in',pylode(',',$uid))));
			if($data['utype']=='index'){
				$gwhere['id']		=	array('in',pylode(',',$examid));
			}else{
				$gwhere['keyid']	=	array('<>',0);
			}
			$exame			=	$this->getList($gwhere);
			
			foreach($List as $key => $val){
				
				foreach($member as $v){
					
					if($val['uid'] == $v['uid']){
						
						$List[$key]['name']		=	$v['username']; 
					}
				}
				foreach($userList as $v){
					
					if($val['uid'] == $v['uid']){
						
						$List[$key]['pic']		=	$v['pic']; 
					}
				}
				foreach($exame as $v){
					
					if($val['examid'] == $v['id']){
						
						$List[$key]['title']	=	$v['name'];
						$List[$key]['keyid'] 	=	$v['keyid'];
					}
				}
			}
		}
		return $List;
	}
	public function addEvaluateLog($data=array()){
		
		if($data['examid']){
			$gid 		= 	$data['hidGid'];/*分组id*/
			$examid 	= 	(int)$data['examid'];/*试卷id*/
			
			$result 	= 	$this->getEvaluateLogInfo(array($data['type']=>$data['uid'],'examid'=>$examid),array('field'=>'`id`'));
			
			if($result['id']){
				
				$this->update_once('evaluate_log',array('grade'=>$data['scores'],'ctime'=>time()),array('id'=>$result['id']));
			}else{
				
				$result['id']	=	$this->insert_into('evaluate_log',array($data['type']=>$data['uid'],'examid'=>$examid,'grade'=>$data['scores'],'ctime'=>time()));
			}
			if($data['utype']=='pc'){
				$url	=	Url("evaluate",array('c'=>"exampaper","a"=>'gradeshow',"id"=>$result['id']));
			}
			if($data['utype']=='wap'){
				$url	=	Url("wap",array('c'=>"evaluate","a"=>'gradeshow',"id"=>$result['id']));
			}
			return array('msg'=>'提交成功！','errcode'=>9,'url'=>$url); 
		}else{
			if($data['utype']=='wap'){
				$url	=	Url("wap",array('c'=>"evaluate"));
			}
			if($data['utype']=='pc'){
				$url	=	Url('evaluate');
			}
			return array('msg'=>'没有找到相关测评哦！','errcode'=>10,'url'=>$url);
		}
	}
	/**
	* @desc   删除测评用户记录列表
	*/
	public function delEvaluateLog($delId){
		
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
			
			$delid=$this -> delete_all('evaluate_log',array('id' => array('in',$delId)),'');
			
			if($delid){
				
				$return['msg']		=	'测评记录';
				
				$return['errcode']	=	$delid ? '9' :'8';
				
				$return['msg']		=	$delid ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
			
			}
		
		}
		
		return $return;
	
	}
}
?>