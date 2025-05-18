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
class userEntrust_model extends model{
		//获取简历信息列表resume_expect
    private function getResumeExpectList($whereData, $data = array()){
        
        require_once ('resume.model.php');
        
        $resumeM    =   new resume_model($this->db, $this->def);
        
        return  $resumeM   ->  getList($whereData , $data);
    }
	//获取简历信息列表resume
    private function getResumeList($whereData, $data = array()){
        
        require_once ('resume.model.php');
        
        $resumeM    =   new resume_model($this->db, $this->def);
        
        return  $resumeM   ->  getResumeList($whereData , $data);
    }
    /**
     * @desc   引用log类，添加用户日志
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        
        require_once ('log.model.php');
        
        $LogM = new log_model($this->db, $this->def);
        
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera,$type);
        
    }
	 
    /**
     * 委托简历,推荐列表处理查询条件
     */
    public function getRecordWhere($eid){
        
        if (!empty($eid)){
            $where   =  array();
            
            $expect  =  $this -> select_once('resume_expect',array('id'=>$eid),'`id`,`hy`,`city_classid`,`job_classid`');
            
            if (!empty($expect)){
                
                
                
                if ($expect['job_classid']){
                    
                    $where['PHPYUNBTWSTART_A']     =  '';
                    $where['job1']          =  array('in',$expect['job_classid']);
                    $where['job1_son']      =  array('in',$expect['job_classid'],'OR');
                    $where['job_post']      =  array('in',$expect['job_classid'],'OR');
                    $where['PHPYUNBTWEND_A']  =  '';
                }
                if ($expect['city_classid']){
                    
                    $where['PHPYUNBTWSTART_B']     =  '';
                    $where['provinceid']    =  array('in',$expect['city_classid']);
                    $where['cityid']        =  array('in',$expect['city_classid'],'OR');
                    $where['three_cityid']  =  array('in',$expect['city_classid'],'OR');
                    $where['PHPYUNBTWEND_B']  =  '';
                }
            }
            
            $record  =  $this -> select_all('user_entrust_record',array('eid'=>$eid),'`jobid`');
            
            $jobid   =  array();
            
            if($record){
                
                foreach($record as $v){
                    
                    $jobid[]  = $v['jobid'];
                }
            }
            if (!empty($jobid)){
                
                $where['id']  =  array('notin',pylode(',', $jobid));
            }
            
            return $where;
        }
    }
    /**
     * 委托简历-推荐列表-推荐简历
     */
    public function sendRecord($whereData,$data=array()){
        
        if(!empty($whereData['eid']) && !empty($whereData['jobid'])){
            
            $recoed  =  $this -> select_once('user_entrust_record',array('jobid'=>$whereData['jobid'],'eid'=>$whereData['eid']));
            
            if(empty($recoed)){
                
                $company  =  $this -> select_once('company',array('uid'=>$whereData['comid']),'`uid`,`linkmail`,`did`');
                
                $resume   =  $this -> select_once('resume_expect',array('id'=>$whereData['eid']),'`id`,`uid`,`name`');
                
                $rdata    =  array(
                    'jobid'  =>  $whereData['jobid'],
                    'uid'    =>  $resume['uid'],
                    'eid'    =>  $whereData['eid'],
                    'comid'  =>  $company['uid'],
                    'ctime'  =>  time(),
                    'did'    =>  $company['did']
                );
                
                $nid      =  $this -> insert_into('user_entrust_record',$rdata);
                
                if($nid){
                    //发送系统通知
                    include_once('sysmsg.model.php');
                    
                    $sysmsgM  =  new sysmsg_model($this->db, $this->def);
                    
                    $sysmsgM -> addInfo(array('uid'=>$company['uid'],'usertype'=>2, 'content'=>'管理员为您推荐了简历<a href="resumetpl,'.$resume['id'].'">《'.$resume['name'].'》</a>'));
                    //发送邮件并记录入库
                    if ($company['linkmail']){
                        
                        include_once ('resume.model.php');
                        $resumeM    =   new resume_model($this->db, $this->def);
                        
                        $Info       =   $resumeM -> getInfoByEid(array('eid' => $whereData['eid']));
                        
                        global $phpyun;
                        
                        $phpyun -> assign('Info',$Info);
                        $contents	=	$phpyun -> fetch(TPL_PATH.'resume/sendresume.htm',time());
                        
                        if ($contents){
                            
                            include_once('notice.model.php');
                            
                            $noticeM    =  new notice_model($this->db, $this->def);
                            
                            $emailData  =  array(
                                'email'    =>  $company['linkmail'],
                                'subject'  =>  $this -> config['sy_webname'].'向您推荐了简历！',
                                'content'  =>  $contents
                            );
                            
                            $noticeM -> sendEmail($emailData);
                        }
                    }
                    $return['msg']      =  '推荐简历(ID:'.$whereData['eid'].')成功';
                    $return['errcode']  =  '9';
                }else{
                    $return['msg']      ='推荐失败';
                    $return['errcode']  =  '8';
                }
            }else{
                $return['msg']      =  '请勿重复推荐';
                $return['errcode']  =  '8';
            }
        }else{
            $return['msg']      =  '请选择要推荐的简历';
            $return['errcode']  =  '8';
        }
        return $return;
    }
   
    /**
     * 获取简历推送记录
     * $whereData 	查询条件
     * $data 		自定义处理数组
     */
    
    public function getRecordInfo($whereData,$data=array()){
        
        $select     =   $data['field'] ? $data['field'] : '*';
        
        $info		=	$this -> select_once('user_entrust_record',$whereData,$select);
        
        if (is_array($info)) {
            
            return	$info;
            
        }    
    }
    
    /**
    * 获取简历推送记录列表
    * $whereData 	查询条件
    * $data 		自定义处理数组
    */
    
    public function getRecordList($whereData,$data=array()){
        $ListNew	=	array();
        $List		=	$this -> select_all('user_entrust_record',$whereData);
        
        if(!empty( $List )){
            $List  =  $this -> getDataRecord($List);
            if ($data['utype'] == 'admin'){
                if ($whereData['comid']){
                    
                    $com  =  $this->select_once('company',array('uid'=>$whereData['comid']),'`name`');
                    
                    if ($com){
                        $ListNew['com']  =  $com;
                    }
                }
            }
            
            $ListNew['list']	=	$List;
        }
        
        return	$ListNew;
    }
    /**
     * 简历记录管理-简历推送记录-删除推送
     */
    public function delRecord($id, $data = array()){
        
        $limit                =  'limit 1';
        if (!empty($id)){
            
            if(is_array($id)){
                
                $id  =  pylode(',', $id);
                $return['layertype']  =  1;
                $limit                =  '';
            }else{
				$return['layertype']  =  0;
			}
			if($data['uid']){
				$delWhere = array('id'=>array('in',$id),'uid'=>$data['uid']);
			}else{
				$delWhere = array('id'=>array('in',$id));
			}
            $nid      =  $this->delete_all('user_entrust_record',$delWhere,$limit);
            
            if ($nid){
                $this -> addMemberLog($data['uid'],$data['usertype'],"删除推送的人才",25,3);
				
                $return['msg']      =  '简历推送(ID:'.$id.')删除成功';
                $return['errcode']  =  '9';
            }else{
                $return['msg']      =  '简历推送(ID:'.$id.')删除成功';
                $return['errcode']  =  '8';
            }
        }else{
            $return['msg']      =  '请选择您要删除的简历推送';
            $return['errcode']  =  '8';
        }
        return $return;
    }
   
    /**
    * 推送简历列表,后台显示数据处理
    */
    private function getDataRecord($List){
        
        foreach($List as $v){
            $uids[]    =  $v['uid'];
            $eids[]    =  $v['eid'];
            $comids[]  =  $v['comid'];
            $jobids[]  =  $v['jobid'];
        }
		//  查询个人姓名
		$rWhere['uid']    =   array('in', pylode(',', $uids));
        $rData['field']   =   '`uid`,`name`,`nametype`,`sex`,`def_job`';
        
        $resume           =   $this -> getResumeList($rWhere, $rData);
		
		 //  查询个人简历名称
        $reWhere['id']    =   array('in', pylode(',', $eids));
        $reData['field']  =   '`id`,`name`,`job_classid`,`minsalary`,`maxsalary`,`exp`';
        
        $expect  =   $this -> getResumeExpectList($reWhere, $reData);
        
        $com     =   $this -> select_all('company',array('uid'=>array('in',pylode(',', $comids))),'`uid`,`name`');
        
        $job     =   $this -> select_all('company_job',array('id'=>array('in',pylode(',', $jobids))),'`id`,`name`');
		
		$userid_msg  =	$this -> select_all("userid_msg",array('fid'=>$where['comid'],'uid'=>array('in',pylode(",",$uids))),'`uid`');
		
        foreach($List as $k=>$v){
            foreach($resume as $val){
	            
	            if($v['uid'] == $val['uid']){
	                $List[$k]['user_name']      =  $val['name_n'];
	            }
	        }
			foreach($expect['list'] as $val){
	            
	            if( $v['eid'] == $val['id']){
					$List[$k]['exp']			=	$val['exp_n'];
					$List[$k]['salary']			=	$val['salary'];
					if ($val['job_classid'] != "") {
	                    $List[$k]['jobclassidname']	= $val['job_classname'];
	                }
	                $List[$k]['resume_name']	=  $val['name'];
	            }
	        }
            foreach($com as $val){
                
                if($val['uid'] == $v['comid']){
                    
                    $List[$k]['com_name']  =  $val['name'];
                }
            }
            foreach($job as $val){
                
                if($val['id'] == $v['jobid']){
                    
                    $List[$k]['job_name']  =  $val['name'];
                }
            }
			foreach($userid_msg as $val){
				if($v['uid']==$val['uid']){
					$List[$k]['userid_msg']		=	1;
				}
			}
        }
        return $List;
    }
	 
	 
}
?>