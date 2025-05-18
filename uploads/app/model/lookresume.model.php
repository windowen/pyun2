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
class lookresume_model extends model{
	/**
     * @desc   引用log类，添加用户日志
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type='');
    }
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
	 * 获取浏览简历列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组 utype:admin后台；  uid； usertype
	 */

	public function getList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('look_resume',$whereData);

		if(!empty( $List )){

			$List  =  $this -> getDataList($List,$data);

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
	 * 获取浏览简历详情
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	public function getInfo($whereData,$data=array()){
		$data['field']		=	empty($data['field']) ? '*' : $data['field'];
        $resumeInfo			=   $this -> select_once('look_resume', $whereData, $data['field']);
		return $resumeInfo;
	}


	/**
	 * 修改浏览简历详情
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	function upInfo($where = array(),$upData = array()){

	    $nid    =   $this -> update_once('look_resume', $upData, $where);

		return $nid;
	}

    /**
     * @desc     新增浏览简历记录
     * @param    array $data
     * @return   $return
     */
    function addInfo($data = array()){
        return $this->insert_into('look_resume', $data);
    }

	/**
	 * 删除浏览简历
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	public function delInfo($where){

	    $limit                =  'limit 1';
	    $return['layertype']  =	 0;

	    if (!empty($where['id'])){

	        $id  =  $where['id'];
	        if(is_array($id)){

	            $id  =  pylode(',', $id);
	            $return['layertype']  =  1;
	            $limit                =  '';
	        }else{
	            $return['layertype']  =  0;
            }
            if($where['usertype']==1){
                // 个人操作
                $nid  =  $this -> update_once("look_resume",array('status'=>1),array('uid'=>$where['uid'],'id'=>array('in',$id)));
            }elseif($where['usertype']==2){
                // 企业
				$nid  =  $this -> update_once("look_resume",array('com_status'=>1),array('com_id'=>$where['uid'],'id'=>array('in',$id)));
            }else{
                // 管理员操作
				$nid  =  $this -> delete_all('look_resume',array('id'=>array('in',$id)),$limit);
			}
	        if ($nid){
	            if (!empty($where['usertype']) && !empty($where['uid'])){

	                $this->addMemberLog($where['uid'], $where['usertype'], '删除浏览过的简历',26,3);
	            }
	            $return['msg']      =  '简历浏览记录(ID:'.$id.')删除成功';
	            $return['errcode']  =  '9';

	        }else{
	            $return['msg']      =  '简历浏览记录(ID:'.$id.')删除失败';
	            $return['errcode']  =  '8';

	        }
	    }elseif($data['where']){

			$where	=	$data['where'];

			if($data['norecycle'] == '1'){	//	数据库清理，不插入回收站

				$nid  =  $this -> delete_all('look_resume', $where, '', '', '1');
			}else{

				$nid  =  $this -> delete_all('look_resume', $where, '');
			}

			return $nid;
	    }else{

	        $return['msg']      =  '请选择您要删除的简历浏览';
	        $return['errcode']  =  '8';
	    }
	    return $return;
	}

	/**
	 * @desc 简历浏览列表处理数据
	 */
	private function getDataList($List = array(), $data = array()){

	    if (!empty($data['uid']) && !empty($data['usertype'])) {
	        $uid       =   intval($data['uid']);
	        $usertype  =   intval($data['usertype']);
	    }

	    foreach($List as $v){

	        $eid[]		=   $v['resume_id'];
			$comid[]	=   $v['com_id'];
	    }

	    // 查询个人简历
	    $reWhere['id']     =   array('in', pylode(',', $eid));
	    $reData['utype']   =   'user';
	    $reData['field']   =   '`id`,`uid`,`name`,`job_classid`,`minsalary`,`maxsalary`,`exp`,`city_classid`,`hy`,`lastupdate`,`edu`,`birthday`,`sex`,`uname`';
	    $expect	           =   $this -> getResumeExpectList($reWhere, $reData);
	    if (!empty($expect['list']) && is_array($expect['list'])) {
	        foreach ($expect['list'] as $ev) {
	            $userid[$ev['uid']]    =   $ev['uid']; // 个人UID
	        }
	    }

		// 查询个人姓名
	    $rWhere['uid']            	=   array('in', pylode(',', $userid));
        $rData['field']             =   '`uid`,`name`,`nametype`,`sex`,`def_job`';
        $resume                		=   $this -> getResumeList($rWhere, $rData);

        if ($usertype !=2 ) {
        	
			$com    =	$this -> select_all('company',array('uid'=>array('in',pylode(',', $comid))),'`uid`,`name`,`pr`,`mun`,`sdate`,`linkman`,`linkjob`,`hy`,`logo_status`,`logo`');
			$comJob	=   $this -> select_all('company_job',array('uid'=>array('in',pylode(',',$comid)), 'state' => '1', 'r_status' => '1', 'status' => '0'),'`uid`,`name`');
	    }
	    if ($usertype == 2) {

    		$userid_msg   =	  $this -> select_all('userid_msg',array('fid' => $uid, 'uid'=>array('in',pylode(",",$userid))),'`uid`');
    		$userid_job   =   $this -> select_all('userid_job',array('com_id' => $uid, 'uid'=>array('in',pylode(',',$userid))),'`uid`,`is_browse`');
	    }
		include_once ('cache.model.php');
		$cacheM   =	  new cache_model($this->db, $this->def);
		$cache    =	  $cacheM -> GetCache(array('com'));

	    foreach($List as $k=>$v){

			$List[$k]['datetime_n']   =   date('Y-m-d',$v['datetime']);


	        foreach($com as $val){

	            if($v['com_id'] == $val['uid']){

	                $List[$k]['com_name']	=	$val['name'];
	                $List[$k]['linkman']	=	$val['linkman'];
	                $List[$k]['linkjob']	=	$val['linkjob'];
	                $List[$k]['hy']	        =	$val['hy'];
					$List[$k]['pr_n']		=	$cache['comclass_name'][$val['pr']];
					$List[$k]['mun_n']		=	$cache['comclass_name'][$val['mun']];
					$sdate					=	explode('-',$val['sdate']);
					$List[$k]['sdate']		=	$sdate[0];
					if(empty($val['logo']) || $val['logo_status']==1){

                        $List[$k]['logo_n'] =  checkpic($this->config['sy_unit_icon']);
                    }else{
                        $List[$k]['logo_n']	=  checkpic($val['logo']);
					}
	            }
	        }
			if($comJob) {
				foreach($comJob as $cv){
					if($v['com_id'] == $cv['uid']){
						if(!$List[$k]['com_job']){
							$List[$k]['com_job']	=	$cv['name'];
						}else{
							$List[$k]['com_job']	=	$List[$k]['com_job'].'，'.$cv['name'];
						}
					}
				}
			}	
	        foreach($expect['list'] as $val){

	            if( $v['resume_id'] == $val['id']){
	                $List[$k]['name']			=	$data['utype'] == 'admin' ? $val['uname'] : $val['uname_n'];
	                $List[$k]['age']			=	$val['age_n'];
	                $List[$k]['exp']			=	$val['exp_n'];
					$List[$k]['edu']			=	$val['edu_n'];
					$List[$k]['sex']			=	$val['sex_n'];
					$List[$k]['salary']			=	$val['salary'];
					if ($val['job_classid'] != "") {
	                    $List[$k]['jobname']	= $val['job_classname'];
	                }
	                $List[$k]['resume_name']	=  $val['name'];
	            }
	        }

			foreach($resume as $val){

	            if($v['uid'] == $val['uid'] && empty($List[$k]['name'])){

	                $List[$k]['name']      =  $val['name_n'];
	            }
	        }
			if($usertype == 3){
				foreach($expect['list'] as $val){

					if( $v['resume_id'] == $val['id']){

						$List[$k]['hy']				=	$val['hy_n'];
						if ($val['city_classid'] != "") {
							$List[$k]['cityname']   =   $val['city_classname'];
						}
						$List[$k]['lastupdate']		=  $val['lastupdate'];
					}
				}
			}

			if ($usertype == 2) {

    			foreach($userid_msg as $val){
    				if($v['uid']==$val['uid']){
    					$List[$k]['userid_msg']		=	1;
    				}
    			}
    			foreach($userid_job as $val){

    				if($v['uid']==$val['uid']){
    					$List[$k]['is_browse']		=	$val['is_browse'];
    				}
    			}
			}
	    }

	    return $List;
	}
	//  浏览简历数目
	function getLookNum($Where = array()){

		return $this->select_num('look_resume', $Where);

	}


	/**
	 * 应用浏览简历
	 * $data
	 * uid => 浏览人uid
	 * 返回参数暂时没做特殊处理
	 */
	public function browseResume($data = array()){
		if(empty($data['eid'])){
			return false;
		}
		if(!in_array($data['usertype'], array(2, 3))){
			return false;
		}

		//简历浏览记录
		$look_resume					=	$this -> getInfo(array('com_id' => $data['uid'], 'resume_id' => $data['eid'], 'usertype' => $data['usertype']));

		if(!empty($look_resume)){

			$this -> upInfo(array('resume_id' => $data['eid'], 'com_id' => $data['uid']), array('datetime' => time()));

		}else{
		    // 企业基本信息没有完善的，不记录浏览记录
		    if ($data['usertype'] == 2){
		        
		        $row  =  $this->select_once('company',array('uid'=>$data['uid']),'`name`');
		        
		    }

		    if (empty($row['name'])){
		        return false;
		    }
			$lookData					=	array();
			$lookData['uid']			=	$data['euid'];
			$lookData['resume_id']		=	$data['eid'];
			$lookData['com_id']			=	$data['uid'];
			$lookData['did']			=	$data['did'];
			$lookData['usertype']		=	$data['usertype'];
			$lookData['datetime']		=	time();
			$this -> addInfo($lookData);

			require_once ('history.model.php');
			$historyM					=	new history_model($this->db, $this->def);
			$historyM -> addHistory('lookresume', $data['eid']);

			if($data['usertype'] == 2){
				//增加系统记录
				$company					=	$this -> select_once('company', array('uid' => $data['uid']), '`name`');
				require_once ('sysmsg.model.php');
				$sysmsgM					=	new sysmsg_model($this->db, $this->def);
				$sysmsgM -> addInfo(array('uid' => $data['euid'],'usertype'=>1,'content' => '企业 <a href="comtpl,'.$data['uid'].'">'.$company['name'].'</a> 查看了您的简历'));
			}
		}
		
		require_once ('job.model.php');
		$JobM = new job_model($this->db, $this->def);
		
		//登录的企业，查看简历，设置为已查看
		$userIdWhere					=	array(
		    'com_id'					=>	$data['uid'],
		    'eid'						=>	$data['eid'],
		    'is_browse'					=>	1
		);
		
		$JobM -> updSqJob($userIdWhere, array('is_browse' => 2));
		
		return true;
	}
}
?>