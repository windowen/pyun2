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
class msg_model extends model{
	/**
     * @desc   引用log类，添加用户日志
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        
        require_once ('log.model.php');
        
        $LogM	=	new log_model($this->db, $this->def);
        
        return  $LogM -> addMemberLog($uid, $usertype, $content, $opera, $type);
        
    }
	/**
	 * 获取求职咨询列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	public function getList($whereData,$data=array()){
		
		$ListNew	=	array();
		
		$List		=	$this -> select_all('msg',$whereData);

		$rows		=	$this -> select_all('down_resume',array('comid'=>$whereData['job_uid']),'`uid`');
		
		$uids = array();
		foreach ($rows as $v) {
		    $uids[] = $v['uid'];
		}

		if(!empty( $List )){
			
			if(is_array($List)&&$List){
				
			    $uid =   array();
			    
				foreach($List as $v){
					
					$uid[]	=	$v['uid'];
				}
				$resume  =  $this -> select_all('resume_expect',array('uid' => array('in', pylode(',' , $uid)),'defaults' => 1),'`id`,`uid`');
				
				$resumeList  =  $this -> select_all('resume',array('uid'=>array('in', pylode(',' , $uid))));
				foreach($resumeList as $key=>$val){
					if(empty($val['name'])){
						$euids[]	=	$val['uid'];
					}
				}
				// 没有姓名的，显示用户名
				if (!empty($euids)){
				    $nameList  =  $this -> select_all('member',array('uid'=>array('in', pylode(',' , $euids))),'`uid`,`username`');
				    
				    foreach($resumeList as $key=>$val){
				        foreach($nameList as $k=>$v){
				            if($v['uid']==$val['uid']){
				                $resumeList[$key]['name']	=	$v['username'];
				            }
				        }
				    }
				}
				
				include_once('resume.model.php');
				$resumeM  =  new resume_model($this->db, $this->def);
				
				foreach($List as $key => $val){
				
					foreach ($resume as $va){
						
						if($val['uid'] == $va['uid']){
							
							$List[$key]['eid']	=	$va['id'];
						}
					}
					
					foreach ($resumeList as $v){
					    if ($v['uid'] == $val['uid']){
					        // 已下载的，直接显示姓名
					        if(in_array($uid, $uids)){
					            
					            $List[$key]['username']  =   $v['name'];
					            
					        }else{
					            // 未下载的，对姓名进行处理
					            $nameArr = array('nametype' => $v['nametype'], 'name' => $v['name'], 'eid' => $v['def_job'], 'sex' => $v['sex']);
					            
					            $List[$key]['username']  =   $resumeM -> setUsernameShow($nameArr);
					        }
					    }
					}

					$List[$key]['content']	    =	strip_tags(trim($val['content']));
					$List[$key]['datetime_n']	=	date('Y-m-d H:i',$val['datetime']);
      				if($v['reply_time']>0){
        				$List[$key]['reply_time_n']	=	date('Y-m-d H:i',$val['reply_time']);
      				}
				}
			}
			$ListNew['list']	=	$List;
		}
		return	$ListNew;
	}
	/**
	 * 获取单条求职咨询
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	function getInfo($whereData,$data=array()){
	    
	    $field	=	empty($data['field']) ? '*' : $data['field'];
	    
	    if (!empty($whereData)) {
	        
	        $msg  =  $this -> select_once('msg',$whereData, $field);
	        
	        if($msg && is_array($msg)){
	            
	            $msg['content']         =   strip_tags($msg['content']);
	            
	            if (isset($msg['datetime'])){
	                $msg['datetime_n']	    =	date('Y-m-d H:i',$msg['datetime']);
	            }
	            if (isset($msg['reply_time'])){
	                $msg['reply_time_n']	=	date('Y-m-d H:i',$msg['reply_time']);
	            }
	            return $msg;
	        }
	    }
	}

	/**
	 * 添加求职咨询
	 * $data 		自定义数组
	 * 应用中使用到的添加求职咨询
	 */
	public function addMsg($data = array(),$code=''){
	    $res                =   array();
	    $res['errorcode']   =   8;
	    $res['msg']         =   '';
	    
	    if(empty($data)){
	        $res['msg']         =   '参数错误，请重试！';
	        return $res;
	    }
	    if(empty($data['uid']) || empty($data['username'])){
	        $res['msg']		=	'请先登录！';
	        return	$res;
	    }
	    if($data['usertype'] != 1){
	        $res['msg']		=	'只有个人用户才可以留言！';
	        return	$res;
	    }
	    
	    //参数判断
	    if(empty($data['content'])){
	        $res['msg']			=	'留言内容不能为空！';
	        return	$res;
	    }
	    if($code == ''){
	        if(empty($data['authcode'])){
	            $res['msg']			=	'验证码不能为空！';
	            return	$res;
	        }
	        session_start();
	        if(md5(strtolower($data['authcode'])) != $_SESSION['authcode'] || empty($_SESSION['authcode'])){
	            $res['msg']			=	'验证码错误！';
	            return	$res;
	        }
	    }
	    
	    $sql  =  array(
	        'uid'       =>  $data['uid'],
	        'datetime'  =>  time(),
	        'content'   =>  $data['content'],
	        'type'      =>  1
	    );
	    if (!empty($data['jobid'])){
	        
	        $job  =  $this->select_once('company_job', array('id'=>$data['jobid']),'`id`,`uid`,`name`,`com_name`');
	        
	        $sql['jobid']     =  $job['id'];
	        $sql['job_uid']   =  $job['uid'];
	        $sql['com_name']  =  $job['com_name'];
	        $sql['job_name']  =  $job['name'];
	        
	    }else if (!empty($data['job_uid'])){
	        
	        $com  =  $this->select_once('company', array('uid'=>$data['job_uid']),'`uid`,`name`');
	        
	        $sql['job_uid']   =  $com['uid'];
	        $sql['com_name']  =  $com['name'];
	    }
	    
	    //判断是否在黑名单
	    include_once ('black.model.php');
	    $blackM            		=   new black_model($this->db, $this->def);
	    $black					=	$blackM -> getBlackNum(array(
	        'p_uid'				=>	$sql['uid'],
	        'c_uid'				=>	$sql['job_uid']
	    ));
	    if(!empty($black)){
	        $res['msg']			=	'该企业暂不接受相关咨询！';
	        return	$res;
	    }
	    
	    $resume					=	$this->select_once("resume",array('uid'=>$sql['uid']),"`name`");
	    
	    if(!empty($resume)){
	        
	        $sql['username']   =   $resume['name'];
	        
	    }
	    
	    $id						=	$this -> addInfo($sql);
	    
	    if(!empty($id)){
	        include_once ('sysmsg.model.php');
	        $sysmsgM    		=   new sysmsg_model($this->db, $this->def);
	        $sysmsgM -> addInfo(array('uid' => $sql['job_uid'],'usertype'=>2,  'content' => '您有一个留言，请及时查看'));
	        
	        $res['msg']			=	'留言成功，请等待回复！';
	        $res['errorcode']   =   9;
	        return	$res;
	    }else{
	        $res['msg']			=	'留言失败！';
	        return	$res;
	    }
	    
	}

	/**
	 * 添加求职咨询
	 * $data 		自定义数组
	 */
	public function addInfo($data=array()){	
		
		return $this -> insert_into('msg', $data);		
	
	}
	/**
	 * 删除求职咨询
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	public function delInfo($id,$data=array()){
		
	    $limit                =  'limit 1';
	    
		$return['layertype']  =	 0;
	    
	    if (!empty($id)){
	        
	        if(is_array($id)){
	            
	            $id  =  pylode(',', $id);
	           
				$return['layertype']  =  1;
	            
				$limit                =  '';
	        }
	        if($data['uid']){
				$delWhere = array('uid'=>$data['uid'],'id'=>array('in',$id));
			}else{
				$delWhere = array('id'=>array('in',$id));
			}
			 
	        $nid      =  $this->delete_all('msg',array('id'=>array('in',$id)),$limit);
	        
	        if ($nid){
				
				$this -> addMemberLog($data['uid'],$data['usertype'],'删除求职咨询',18,3);
	           
				$return['msg']      =  '求职咨询(ID:'.$id.')删除成功';
	            
				$return['errcode']  =  '9';
	      
			}else{
	           
			    $return['msg']      =  '求职咨询(ID:'.$id.')删除成功';
	            
				$return['errcode']  =  '8';
	        
			}
	    
		}else{
	        
			$return['msg']      =  '请选择您要删除的求职咨询';
	       
			$return['errcode']  =  '8';
	    
		}
	   
		return $return;
	
	}
	
	
	
	/**
	 * 删除求职咨询
	*/
	public function upInfo($whereData, $data = array()){
		
		if(!empty($whereData)) {
			if($data['usertype']=='1'){
				//个人会员提醒出来，未读状态改成已读状态
				$nid  =  $this -> update_once('msg', array('user_remind_status'=>$data['user_remind_status']),$whereData);
			}else{
				$PostData	=	array(
					
					'del_status'	=>	'1',
				
				);
				
				$nid  =  $this -> update_once('msg', $PostData, array('id'=>$whereData['id'],'job_uid'=>$whereData['job_uid']));
			}
		}
		
		return $nid;
	
	}
	
	/**
	 * 回复状态
	*/
	public function upReplyInfo($whereData, $data = array()){
		
		if(!empty($whereData)) {
			
			$PostData	=	array(
				
				'reply'					=>$data['reply'],
				
				'reply_time'			=>$data['reply_time'],
				
				'user_remind_status'	=>$data['user_remind_status'],
			
			);
			
			$nid  =  $this -> update_once('msg', $PostData, array('id'=>$whereData['id'],'job_uid'=>$whereData['job_uid']));
		
		}
		
		return $nid;
	
	}
	
	public function delMsg($id, $data = array()){
		
		if(!empty($id)){
            
            if(is_array($id)){
                
                $ids    =	$id;
                
                $return['layertype']	=	1;
                
            }else{
                
                $ids    =   @explode(',', $id);
				
				$return['layertype']	=	0;
                
            }
            
            $id            		=   pylode(',', $ids);

            $return['id']		=	$this -> delete_all('msg',array('id' => array('in',$id),'job_uid'=>$data['job_uid']),'');
            
            $return['msg']		=	'求职咨询';
            
            $return['errcode']	=	$return['id'] ? '9' :'8';
           
		    $return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
       
	    }else{
            
			$return['msg']		=	'请选择您要删除的数据！';
            
			$return['errcode']	=	8;
        
		}
        
        return	$return;
	}
	/**
     * @desc    获取职位咨询数目
     */
	function getMsgNum($WhereData = array()){
		
		$Msgnum	=	$this->select_num('msg', $WhereData);
		
		return $Msgnum;
	}
	
}
?>