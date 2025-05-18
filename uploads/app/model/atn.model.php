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
class atn_model extends model{
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
	//获取职位信息列表company_job
    private function getJobList($whereData, $data = array()){
        
        require_once ('job.model.php');
        
        $jobM    =   new job_model($this->db, $this->def);
        
        return  $jobM   ->  getList($whereData , $data);
    }
	//获取职位信息列表company
    private function getComList($whereData, $data = array()){
        
        require_once ('company.model.php');
        
        $companyM    =   new company_model($this->db, $this->def);
        
        return  $companyM   ->  getList($whereData , $data);
    }
	
	/**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type=''); 
    }
     /**
      * 查询全部信息
      * @param 表：atn
      * @param 功能说明：获取atn表里面信息
      * @param 引用字段：$whereData：条件  2:$data:查询字段
      *
     */
    public function getatnList($whereData,$data=array()){
        $field              =   $data['field'] ? $data['field'] : '*';
        
        $atnlist            =   $this   ->  select_all('atn', $whereData, $field);
        
        foreach($atnlist as $k=>$v){
        	if($v['time']){
        		$atnlist[$k]['time_n']	=	date('Y-m-d',$v['time']);
        	}
        }
        if(is_array($atnlist) && $atnlist){
          if($data['utype']=='user'){
            //关注企业
            $atnlist	=	$this -> getAtnUserDataList($atnlist,$data);
            
          }
          if($data['utype']=='company'){
            //关注企业
            $atnlist	=	$this -> getAtnComDataList($atnlist,$data);
          }
         
        }
		
        return $atnlist; 
    }
	/**
     * @desc    关注我的人才
     *
     * @param   array $List
     */
    private function getAtnUserDataList($List,$data=array()) {
        foreach($List as $v){
			if($v['uid'] && !in_array($v['uid'],$uids)){
				$uids[]	=	$v['uid'];
			}
		}
		//  查询个人姓名
		$rWhere['uid']            	=   array('in', pylode(',', $uids));
		$rData['field']             =   '`uid`,`name`,`nametype`,`sex`,`telphone`,`def_job`';

		$resumeList                	=   $this -> getResumeList($rWhere, $rData);

		//  查询个人简历
		$reWhere['uid']             =   array('in', pylode(',', $uids));
		$reWhere['defaults']        =   '1';
		$reData['field']            =   '`id`,`uid`,`name`,`job_classid`,`minsalary`,`maxsalary`,`exp`,`edu`,`birthday`';

		$expectList                 =   $this -> getResumeExpectList($reWhere, $reData);
		
		$userid_msg		=	$this -> select_all("userid_msg",array('fid'=>$data['uid'],'uid'=>array('in', pylode(',', $uids))),"uid");
		
		$userid_job		=   $this -> select_all('userid_job',array('com_id'=>$data['uid'],'uid'=>array('in',pylode(',',$uids))),'`uid`,`is_browse`');
				
		foreach($List  as $k=>$v){
			foreach($resumeList as $val){
					
				if($v['uid'] == $val['uid']){
					$List[$k]['name_n']		=   $val['name'];
					$List[$k]['telphone_n']	=   $val['telphone'];
					$List[$k]['username']    =	$val['name_n'];
					$List[$k]['telphone']    =	$val['telphone'];
				}
			}
			foreach ($expectList['list'] as $val){
				
				if ($v['uid']   ==  $val['uid']) {
					$List[$k]['eid']		=   $val['id'];
					$List[$k]['exp']		=   $val['exp_n'];
					$List[$k]['edu']		=   $val['edu_n'];
					$List[$k]['age']		=   $val['age_n'];
					if ($val['job_classid'] != "") {
						$List[$k]['jobname'] = $val['job_classname'];
					}
				}
			}
			foreach($userid_msg as $val)
			{
				if($val['uid']==$v['uid'])
				{
					$List[$k]['userid_msg']=1;
				}
			}
			
			foreach($userid_job as $val){
			    
				if($v['uid']==$val['uid']){
					$List[$k]['is_browse']		=	$val['is_browse'];
				}
			}
		}
        return $List;
    }
	
	
	
	/**
     * @desc    关注企业，查询company、company_job相关数据
     *
     * @param   array $List
     */
    private function getAtnComDataList($List,$data=array()) {
        
        $sids  =  array();
        foreach($List as $v){
			if($v['sc_uid'] && !in_array($v['sc_uid'],$sids)){
				$sids[]	=	$v['sc_uid'];
			}
		}
		//  职位信息company_job
		$jobWhere['uid']            =   array('in', pylode(',', $sids));
		$jobWhere['status']         =   array('<>', 1);
		$jobData['field']          	=   '`uid`,`name`,`id`,`pr`,`mun`';
		$joblist				   	=	$this->getJobList($jobWhere,$jobData);
		foreach($joblist['list'] as $v){
			if($data['wap']){
				$url					=	Url('wap',array("c"=>"job",'a'=>'comapply',"id"=>$v['id']));
			}else{
				$url					=	Url('job',array("c"=>"comapply","id"=>$v['id']));
			}
			
			$jobname[$v['uid']][]	=	"<a href='".$url."' target='_bank'>".$v['name']."</a>";
		}
		//  查询企业company	
		$comWhere['uid']			=	array('in', pylode(',', $sids));
		$comData['field']           =   '`uid`,`name`,`sdate`,`ant_num`,`logo`,`hy`,`pr`,`mun`,`logo_status`';
		$comData['logo']           	=   '1';
		$comlist				   	=	$this->getComList($comWhere,$comData);

		foreach($List  as $k=>$v){
			
			$List[$k]['time_n']		=	date('Y-m-d H:i',$v['time']);
			
			foreach($comlist['list'] as $val){
				if($v['sc_uid']==$val['uid']){
					$List[$k]['com_name']	=	$val['name'];
					$List[$k]['hy_n']		=	$val['hy_n'];
					$List[$k]['pr_n']		=	$val['pr_n'];
					$List[$k]['mun_n']		=	$val['mun_n'];
					$List[$k]['logo']		=	$val['logo'];
					
					$sdate					=	explode('-',$val['sdate']);
					$List[$k]['com_sdate']	=	$sdate[0];
				}
			}
			
			foreach($joblist['list'] as $val){
				if($v['sc_uid']==$val['uid']){
					$List[$k]['com_pr']		=	$val['job_pr'];
					$List[$k]['com_mun']	=	$val['job_mun'];
					
					$List[$k]['joblist_wx'][$val['id']]	=	$val['name'];
				}
			}		
			foreach($jobname as $kk=>$val){
				if($v['sc_uid']==$kk){
					$List[$k]['jobnum']		=	count($val);
					$i=0;
					foreach($val as $value){
						if($i<2){
							$joblist[$kk][]		=	$value;
						}
						$i++;
					}
					$List[$k]['jobname']		=	@implode(",",$joblist[$kk]);
				}
			}
			
		}
        return $List;
    }
	
	/**
     * @desc 取消关注企业
     * @param 表：atn
     * @param 引用字段：$data:字段 sc_usertype : 1个人、2企业
     */
	public function delAtnAll($id,$data=array()){
     
		if(!empty($id)){
 
		    $return   =   array();
		    
		    if ($data['type'] == 'admin') {    
		        
		        if(is_array($id)){
		        
		            $ids    				=	$id;
		            $return['layertype']	=	1;
		        }else{
		            
		            $ids    				=   @explode(',', $id);
		            $return['layertype']	=	0;
		        }
		        $id       =   pylode(',', $ids);
				
		        
		        $return['id']     =   $this->delete_all('atn', array('id' => array('in', $id)),'');
		        
		        $return['msg']    =   $return['id'] ? '删除成功' : '删除失败';
		        $return['errcode']=   $return['id'] ? 9 : 8;
		        
		        return $return;
		        
		    }else{
		        
		        $return['layertype']	=	0;
		        
		        $id       =   intval($id);
		        $uid      =   intval($data['uid']);
		        $usertype =   intval($data['usertype']);
		        $type     =   intval($data['sc_usertype']);
		        
				if($type==2){
		            
	                $where    =   array(
	                    'id'          =>  $id,
	                    'uid'         =>  $uid,
	                    'usertype'    =>  $usertype,
	                    'sc_usertype' =>  2
	                );
		        }
		        
		        $return['id']     =   $this -> delete_all('atn', $where, '');
		        
		        if($return['id']){
		            
		            //取消关注企业
		            if($type==2){
		                $this -> update_once('company', array('ant_num' => array('-',1)),array('uid' => intval($data['cuid'])));
		                $this -> addMemberLog($data['uid'], $data['usertype'], '取消关注企业',5,3);
		            }
		           
		            $return['msg']      =  '取消成功';
		            $return['errcode']  =  '9';
		        } else{
		            $return['msg']      =  '取消失败';
		            $return['errcode']  =  '8';
		        }
		    }
			
		}else{

			$return['msg']      	=  '系统繁忙';
			$return['errcode']  	=  '8';
			$return['layertype']	=	0;
		}
		return $return;  
	}
	
    public  function getatnInfo($where=array(),$data=array()){
       
          $select     =   $data['field'] ? $data['field'] : '*';
               
          $atnInfo    =   $this -> select_once('atn', $where, $select);
               
          return $atnInfo;
     }
    /**
     * @desc 添加信息
     * @param 表：atn
     * @param 引用字段：$data:字段
     */
    public  function addAtnInfo($data=array()){
        
        $nid    =   $this -> insert_into("atn", $data);
        
        return $nid;
    }
	/**
	 * @desc 关注数量
	 */
    function getAtnNum($whereData = array()) {
	    
	    return $this -> select_num('atn',$whereData);
	}
	
	/**
	 * @desc 关注
	 * @param array $data
	 * @return array
	 */
	public function addAtnLt($data = array())
    {
        $return     =   array();
        $id         =   (int) $data['id'];

        if ($id > 0) {
            
            if ($data['uid'] && $data['username']) {
                
                
                if ($data['usertype'] != '1') {
                    $return['msg']      =   '只有个人用户才可以关注！';
                    $return['errcode']  =   2;
                }
                

                if ($_POST['id'] == $data['uid']) {
                    $return['msg']      =   '自己不能关注自己！';
                    $return['errcode']  =   2;
                }
                
                $where                  =   array();
                $where['uid']           =   $data['uid'];   // 关注人UID
                $where['sc_uid']        =   $id;            // 被关注人UID
                
                $where['sc_usertype']   =   $data['sc_usertype'];
                
                if ($data['utype'] == 'teacher') {
                    
                    $table          =   'company';
                    $company        =   $this->select_once('company', array('uid' => $id), "`name`");
                    $name           =   $company['name'];
                    $utype          =   2;
                    
                }else{
                	$table      =   'lt_info';
	                $row        =   $this->select_once('lt_info', array('uid' => $id), "realname");
	                $name       =   $row['realname'];
	                $utype      =   3;
                }

                $atninfo    =   $this->select_once('atn', $where, '`id`');  // 查询已关注信息
                 
                if (is_array($atninfo) && ! empty($atninfo)) {
 
                    $this -> delete_all('atn', $where, '');
                    
                    if (!empty($table)) {
                        $this -> update_once($table, array('ant_num' => array('-', 1)), array('uid' => $id));
                    }
                    
                    if (!empty($utype)) {
                        include_once ('sysmsg.model.php');
                        $sysmsgM    =   new sysmsg_model($this->db, $this->def);
                        
                        $userurl    =   '<a href="usertpl,' . $data['uid'] . '">' . sub_string($data['username']) . '</a>';
                        $content    =   "用户 " . $userurl . " 取消了对你(" . $name . ")的关注！";
                        $sysmsgM -> addInfo(array('uid' => $id, 'usertype' => $utype, 'content' => $content));
                    }
                    $this->addMemberLog($data['uid'], $data['usertype'], "取消了对" . $name . "的关注！", 5, 3);
                    $return['msg']      =   '取消关注成功！';
                    $return['cancel']   =   1;
                    $return['errcode']  =   1;
                } else {
                    
                    $adata  = array(
                        'uid'       =>  $data['uid'],
                        'sc_uid'    =>  $id,
                        'usertype'  =>  $data['usertype'],
                        'time'      => time()
                    );

                    $adata['sc_usertype']   =   $data['sc_usertype'];

                    $this -> insert_into('atn', $adata);
                    
                    if (!empty($table)) {
                        $this -> update_once($table, array('ant_num' => array('+', 1)), array('uid' => $id));
                    }
                    
                    if (!empty($utype)) {
                        include_once ('sysmsg.model.php');
                        $sysmsgM    =   new sysmsg_model($this->db, $this->def);
                        
                        $userurl    =   '<a href="usertpl,' . $data['uid'] . '">' . sub_string($data['username']) . '</a>';
                        $content    =   "用户 " . $userurl . " 关注了你(".$name.")";
                        $sysmsgM -> addInfo(array('uid' => $id, 'usertype' => $utype, 'content' => $content));
                    }
                    
                    $this -> addMemberLog($data['uid'], $data['usertype'], "关注了" . $name, 5, 1);
                    
                    $return['msg']      =   '关注成功！';
                    $return['errcode']  =   1;
                }
            } else {
                
                $return['msg']      =   '您还没有登录！';
                $return['errcode']  =   2;
            }
        }
        return $return;
    }
}
?>