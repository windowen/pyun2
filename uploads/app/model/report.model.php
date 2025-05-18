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
class report_model extends model
{
	/**
    * @desc   引用log类，添加用户日志   
    */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera,$type);  
    }
	//获取被举报职位列表
	function getReportList($whereData=array(),$data=array()){
		$ListNew		=	array();
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('report',$whereData,$data['field']);
		include PLUS_PATH."/reason.cache.php";
		if(!empty( $List )){
			
			foreach($List as $key=>$val){
				if(in_array($val['c_uid'],$uids)==false){
					$uids[]	=	$val['c_uid'];
				}
				if(in_array($val['eid'],$eids)==false){
					$eids[]	=	$val['eid'];
				}
			}
			
			//获取member
			$mWhere['uid']	=	array('in',pylode(',',$uids));
			$mdata['field']	=	'`uid`,`email`';
			$member			=	$this->getMemberList($mWhere,$mdata);
			
			//获取job
			$jWhere['id']	=	array('in',pylode(',',$eids));
			$jdata['field']	=	'`id`,`name`';
			$job			=	$this->getJobList($jWhere,$jdata);
			
			//获取question
			$qWhere['id']	=	array('in',pylode(',',$eids));
			$qdata['field']	=	'`id`,`title`';
			$question		=	$this->select_all("question",$qWhere,$qdata['field']);
			
			foreach($List as $key=>$val){
				if ($data['utype']=='admin') {
					
					if($data['type']=='0'){
						if($member&&is_array($member)){
							foreach($member as $v){
								if($v['uid']==$val['c_uid']){
									$List[$key]['email']	=	$v['email'];
								}
							}
						}
						
						if($job&&is_array($job)){
							foreach($job['list'] as $v){
								if($v['id']==$val['eid']){
									$List[$key]['name']		=	$v['name'];
								}
							}
						}
					}
					
					if($data['type']=='1'){	
						$List[$key]['c']		=	"add";
						$List[$key]['is_del']	=	'问题已被删除';
						if(!empty($question)){
							foreach($question as $qv){
								if($qv['id']==$val['eid']){
									if($qv['title']!=''){
										$List[$key]['title']	=	$qv['title'];
										$List[$key]['url']		=	"index.php?m=admin_question&id=".$val['eid'];
										$List[$key]['is_del']	=	'';
									}
								}
							}
						}
						foreach($reason as $v){
							if($val['r_reason']==$v['id']){
								$List[$key]['reason']	=	$v['name'];
							}elseif($val['r_reason']=='0'){
								$List[$key]['reason']	=	'原因已被删除';
							}
						}
					}
					
					if($data['type']=='3'){	
						$rea	=	@explode('@',$val['r_reason']);
						$List[$key]['ereason']	=	$rea[0];
						$List[$key]['rreason']	=	$rea[1];
					
					}
				}
			}
	
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
    }
    /**
     * @desc    添加report 举报列表数据
     */
    public function addReport($data = array()) {
        
       $nid	=	$this->insert_into('report',$data);
       
       return $nid;
        
    }
    
    /**
	 * @desc 投诉顾问 
	 * @param array $data
	 * @return number|string
	 */
    public function addCrmReport($data = array()){

    	$nid	=	$this->addReport($data);
    	
    	return $nid;
    }
    /**
	 * @desc 问答举报 
	 * @param array $data
	 * @return number|string
	 */
    public function addAskReport($data = array()){

    	$nid	=	$this->addReport($data);
    	
    	return $nid;
    }
    /**
	 * @desc 职位举报 
	 * @param array $data
	 * @return number|string
	 */
    public function addJobReport($data = array()){
    	$nid	=	$this->addReport($data);
    	
    	return $nid;
    }
	/**
	 * @desc 简历举报 
	 * @param array $data
	 * @return number|string
	 */
	public  function ReportResume($data = array()){
	    
        $haves  =   $this -> select_once('report', array('p_uid' => $data['p_uid'], 'c_uid' => $data['c_uid'],'eid'=>$data['eid'],'usertype' => $data['usertype']));

        if (is_array($haves)) {
            
            $return =   array(
                'msg'       =>  '您已经举报过该用户！',
                'errcode'   =>  8
            );
            
        } else {
            if(is_array($data['reason'])){
            	$data['reason'] = @implode(',', $data['reason']);
            }
            $datas  =   array(
                
                'c_uid'     =>  $data['c_uid'],
                'inputtime' =>  $data['inputtime'],
                'p_uid'     =>  $data['p_uid'],
                'did'       =>  $data['did'],
                'usertype'  =>  $data['usertype'],
                'eid'       =>  $data['eid'],
                'r_name'    =>  $data['r_name'],
                'username'  =>  $data['username'],
                'r_reason'  =>  $data['reason'] // 提交的是汉字，不能用pylode
            );

            $nid    =   $this->addReport($datas);
            
            if ($nid) {
            	$rexpect	=	$this -> select_once('resume_expect',array('id'=>$data['eid']),'`name`');
               
                $this->addMemberLog($data['p_uid'], $data['usertype'], "举报简历" . $data['r_name'], 23, 1);
                
                $return =   array(
                    'msg'       =>  '举报成功！',
                    'errcode'   =>  9
                );
            } else {
                
                $return =   array(
                    'msg'       =>  '举报失败！',
                    'errcode'   =>  8
                );
            }
        }
        return $return;
    }
    
	function upReport($whereData=array(),$data=array()){
	
	    if(!empty($whereData)){
			
			$nid	=	$this -> update_once('report',$data,$whereData);
			
		}
		return $nid;
    }
	
	function getReportOne($whereData=array(),$data=array()){
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List			=	$this -> select_once('report',$whereData,$data['field']);
		}

		return $List;
    }
	
	function delReport($whereData,$data=array()){
		
		if($whereData['id']){
			
			
			if(is_array($whereData['id'])){
				$where['id']	=	array('in',pylode(',',$whereData['id']));
				
				$return['layertype']	=	1;
				
				$del	=	pylode(',',$whereData['id']);
			}else{
				
				$return['layertype']	=	0;
				
				$where['id']	=	$whereData['id'];
				
				$del	=	$whereData['id'];
			}

			if($whereData['uid']){
				$where['p_uid']	=	$whereData['uid'];
			}
			 
			$return['id']			=	$this	->	delete_all('report',$where,'');
			
			$return['msg']			=	$data['title'].'(ID:'.$del.')';
            
            $return['errcode']		=	$return['id'] ? '9' :'8';
            $return['msg']			=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
			
		}else{
			$return['msg']			=	'请选择您要删除的'.$data['title'].'！';
			$return['layertype']	=	0;
			$return['errcode']		=	8	;
		}
		
		return	$return;
		
    }
	
	// 举报数量
	public function getNum($Where = array()){
		return $this -> select_num('report', $Where);
	}
	
	/**
     * @desc   引用userinfo类，查询member列表信息   
     */
    
    private function getMemberList($whereData = array(), $data = array()) {

        require_once ('userinfo.model.php');
        
        $UserinfoM = new userinfo_model($this->db, $this->def);
        
        return  $UserinfoM   ->  getList($whereData , $data);
        
    }
	
	/**
     * @desc   引用job类，查询job列表信息   
     */
    
    private function getJobList($whereData = array(), $data = array()) {

        require_once ('job.model.php');
        
        $jobM = new job_model($this->db, $this->def);
        
        return  $jobM   ->  getList($whereData , $data);
        
    }
	
	/**
     * @desc   引用company类，查询company列表信息   
     */
    
    private function getComList($whereData = array(), $data = array()) {

        require_once ('company.model.php');
        
        $CompanyM = new company_model($this->db, $this->def);
        
        return  $CompanyM   ->  getList($whereData , $data);
        
    }
}
?>