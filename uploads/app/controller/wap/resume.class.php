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
class resume_controller extends common{

	public $userInfo	=	array();

	/**
	 * @desc 简历列表
	 * 2019-06-24
	 */
	function index_action()
	{

		$this -> get_moblie();
		$CacheM		=	$this -> MODEL('cache');
        $CacheArr	=	$CacheM -> GetCache(array('user','job','city','hy'));
        $uptime		=	array(1 => '今天', 3 => '最近3天', 7 => '最近7天', 30 => '最近一个月', 90 => '最近三个月');
        $this -> yunset('uptime', $uptime);
		
        if (isset($_GET['ecity']) || isset($_GET['ejob'])){
            
            $pinyin  =  $CacheM->pinYin($_GET,array('city_index'=>$CacheArr['city_index'],'job_index'=>$CacheArr['job_index']));
            
            if (!empty($pinyin)){
                
                $_GET  =  array_merge($_GET,$pinyin);
            }
        }
		
		$searchurl  =  array();
		foreach($_GET as $k=>$v){
			if($k!=""){
				$searchurl[]  =  $k.'='.$v;
			}
		}
		
		if ($this->usertype == 2 || $this->usertype == 3) {
    		//已下载
    		$downM    =	  $this -> MODEL('downresume');
    		$down     =	  $downM -> getSimpleList(array('comid' => $this->uid,'usertype'=>$this->usertype), array('field' => '`eid`'));
    		$eid      =   array();   
    		foreach ($down as $v){
    		    $eid[]	=	$v['eid'];
    		}
    		$this -> yunset('eid',$eid);
    		
    		$lookResumeIds    =   @explode(',', $_COOKIE['lookresume']);
    		$this->yunset("lookResumeIds", $lookResumeIds);

			$userInfoM		=	$this->MODEL('userinfo');

			$this->userInfo	=	$userInfoM -> getUserInfo(array('uid' => $this->uid), array('usertype' => $this->usertype));
			
 
			$this->yunset('userInfo', $this->userInfo);
			
		}
		
		$searchurl	=	@implode('&', $searchurl);
		$this -> yunset('searchurl', $searchurl);
		$this -> yunset('backurl', Url('wap'));
		
		$this -> yunset($CacheArr);
		include(CONFIG_PATH.'db.data.php');
		$this -> yunset('integrity_name',$arr_data['integrity_name']);

		if (count($searchurl) > 1){
		    $this->seo('user_search');
		}else{
		    $this->seo('user');
		}			
		 
		$companyM    =	  $this -> MODEL('company');
		$comnum     =	  $companyM -> getCompanyNum(array('uid' => $this->uid));
		$this->yunset("comnum", $comnum);
		
		$this -> yunset('headertitle', '找人才');
		$this -> yunset('topplaceholder', '请输入简历关键字,如：服务员...');
		$this -> yuntpl(array('wap/resume'));
	}

	function search_action(){
		$this -> index_action();
	}

	/**
	 * 简历详情
	 * 2019-06-21
	 */
	function show_action(){
		$this -> get_moblie();
		
		$resumeM					=	$this -> MODEL('resume');
		$JobM						=	$this -> MODEL('job');
		
		$getUid						=	intval($_GET['uid']);
		$getType					=	intval($_GET['type']);
		$getId						=	intval($_GET['id']);
		$returnUrl					=	Url('wap',array('c'=>'login'));
		
		//记录游客浏览量
		if($this->uid == '' && $this->config['sy_resume_visitors'] > 0){ 
		    
			if($_COOKIE['resumevisitors'] >= $this->config['sy_resume_visitors']){ 				
			     
			    $this->ACT_msg_wap($returnUrl,'游客用户，每天只能访问'.$this->config['sy_resume_visitors'].'份简历，请登录后继续访问！', 2, 5);
			}else{
			    
				if ($_COOKIE['resumevisitors']==''){
				
				    $resumevisitors	=	1;
				}else{
					
				    $resumevisitors	=	$_COOKIE['resumevisitors']+1;
				}
				$this -> cookie -> SetCookie('resumevisitors', $resumevisitors, strtotime(date("Y-m-d",strtotime("+1 day"))));
			}
		}
		
		if(!empty($getUid)){
			
			    
			    $def_job     =	$resumeM -> getResumeInfo(array('uid' => $getUid, 'r_status' => 1), array('field' => '`def_job`'));
				
				if(empty($def_job)){
				
				    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'],'没有找到该人才！', 2, 5);
				    
				}else if($def_job['def_job'] < 1){
				    
				    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'],'还没有创建简历！', 2, 5);
				}else if($def_job['def_job']){
				    
					$id      =	$def_job['def_job'];
				}
		
 		}else{
			$id			     =	$getId;
		}

		$this->yunset('eid', $id);
		
		$resume_expect	     =	$resumeM -> getInfoByEid(array('eid' => $id, 'uid' => $this->uid, 'usertype' => $this->usertype));
		
		if(empty($resume_expect)){
		    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'],'没有找到该人才！', 2, 5);
		}

		//简历状态判断
		if($resume_expect['state'] == 0 && $resume_expect['uid'] != $this->uid){		    

		    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'],'简历正在审核中！', 2, 5);
			
		}elseif($resume_expect['r_status'] == 2 && $resume_expect['uid'] != $this->uid){
		    
		    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'],'简历暂被锁定，请稍后查看！', 2, 5);

		}elseif($resume_expect['state'] == 3 && $resume_expect['uid'] != $this->uid){

		    $this->ACT_msg_wap($_SERVER['HTTP_REFERER'],'简历审核暂未通过！', 2, 5);
		}
		if ($this->config['com_search'] == 1 && !$this->uid){
            $this->ACT_msg_wap($returnUrl, '请先登录', 2, 5);
        }
		//个人用户无法查看简历
		if($this->config['sy_user_visit_resume'] == '0' && $this->usertype == 1 && $this->uid != $resume_expect['uid']){
			
		    $this->ACT_msg_wap($returnUrl,'个人用户无权限查看！', 2, 5);
		}
		// 查询黑名单
        $blackM             =   $this->MODEL('black'); 
        $blackInfo          =   $blackM -> getBlackInfo(array('p_uid' => $this->uid, 'c_uid'=> $resume_expect['uid']));
        
        if(!empty($blackInfo)){
            
            $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '该用户已关闭简历!', 2, 5);
            
        }
		//人才收藏库
		$talent_pool				  =	  $resumeM -> getTalentList(array('eid' => $id, 'cuid' => $this -> uid));

		//已邀请面试数量
		$userid_msg					  =	  $JobM -> getYqmsNum(array('fid' => $this->uid, 'uid' => $resume_expect['uid']));

		$resume_expect['talent_pool'] =	  $talent_pool;
		$resume_expect['userid_msg']  =	  $userid_msg;
		$resume_expect['euid']		  =	  $resume_expect['uid'];
		$resume_expect['rec_resume']  =	  $resume_expect['rec_resume'];
        
		//根据是否登录展示不同联系方式
		if($this->uid){
			if(isset($resume_expect['showcontactflag']) && $resume_expect['showcontactflag']){
				
				  $resume_expect['link_wapmsg']		=	"<a href='javascript:void(0)' onclick=\"isDownResume('$id',{$resume_expect['downresumes']},'".Url("ajax",array('c'=>'forlink'))."')\"><span>查看联系方式</span></a>";
			}else{
				if($this->usertype==2||$this->usertype==3){
					$resume_expect['link_wapmsg']	=	"<a href='javascript:void(0)' onclick=\"for_link('$id','".Url("ajax",array('c'=>'forlink'))."')\"><span>查看联系方式</span></a>";
					
				}else{
					if($this->config['sy_user_change']==1){
						$resume_expect['link_wapmsg']	=	"<a href='javascript:void(0)' onclick=\"layermsg('请先申请企业账户')\"><span>查看联系方式</span></a>";
				
					}else{
						$resume_expect['link_wapmsg']	=	"<a href='javascript:void(0)' onclick=\"layermsg('只有企业用户才能查看联系方式')\"><span>查看联系方式</span></a>";
						
					}
				}
			}
		}
		$cData['uid']		=	$this->uid;
		$cData['usertype']	=	$this->usertype;
		$cData['eid']		=	intval($_GET['id']);
		$cData['ruid']		=	$resume_expect['uid'];
		$resumeCkeck		=	$resumeM->openResumeCheck($cData);
		$this ->yunset('resumeCkeck',$resumeCkeck);
		
		/* 模糊字段 */
		$this ->yunset('tj',$resume_expect['tj']);

		$data['resume_username']	=	$resume_expect['username_n'];//简历人姓名
		$data['resume_city']		=	$resume_expect['cityname'];//城市
		$data['resume_job']			=	$resume_expect['customjob'];//行业
		$this -> data				=	$data;
		$this -> seo('resume');
		
		if($_GET['down']){
		    if($this->usertype == '2'){
		      $this->yunset('backurl', Url('wap',array('c' => 'down'),'member'));
		    }elseif ($this->usertype == '3'){
		      $this->yunset('backurl', Url('wap',array('c' => 'down_resume'),'member'));
		    }
		}
		$this -> yunset('uid', $this->uid);
 		$this -> yunset('Info', $resume_expect);
  		$this -> yunset('headertitle', '个人简历');
		if($this->config['sy_h5_share']==1){
			$this -> yunset('shareurl', Url('wap',array('c'=>'resume','a'=>'share', 'id' => $id)));
		}else{
			$this->yunset("shareurl",Url('wap',array('c'=>'resume','a'=>'show','id'=>$id)));
		}
		$this -> yuntpl(array('wap/resume_show'));
	}


	/**
	 * 简历详情
	 * 分享简历
	 * 2019-06-21
	 */
	function share_action(){
		$this -> get_moblie();
		
		$id							=	intval($_GET['id']);
		$ResumeM					=	$this -> MODEL('resume');
		$user						=	$ResumeM -> getInfoByEid(array('eid' => $id, 'uid' => $this -> uid, 'usertype' => $this -> usertype));
		$this -> yunset('Info', $user);
		$cData['uid']		=	$this->uid;
		$cData['usertype']	=	$this->usertype;
		$cData['eid']		=	intval($_GET['id']);
		$cData['ruid']		=	$user['uid'];
		$resumeCkeck		=	$ResumeM->openResumeCheck($cData);
		$this ->yunset('resumeCkeck',$resumeCkeck);
		/* 模糊字段 */
		$this ->yunset('tj',$user['tj']);
		$data['resume_username']	=	$user['username_n'];
		$data['resume_city']		=	$user['city_one'].','.$user['city_two'];
		$this -> data				=	$data;
		$this -> seo('resume_share');
		$this -> yunset('resume_style', $this->config['sy_weburl'].'/app/template/wap/resume');
		$this -> yuntpl(array('wap/resume/index'));
	}
	
	/**
	 * 简历详情
	 * 面试邀请
	 * 2019-06-21
	 */
	function invite_action(){
		$this -> get_moblie();
		$uid					=	intval($_GET['uid']);
		$fuid					=	$this->uid;
		
		if(!empty($uid)){
			$resumeM			=	$this -> MODEL('resume');
			$user_resume			=	$resumeM -> getResumeInfo(array('uid' => $uid, 'r_status' => 1), array('field' => '`def_job`'));
			$userrows	     	=	$resumeM -> getInfoByEid(array('eid' => $user_resume['def_job'], 'uid' => $this->uid, 'usertype' => $this->usertype,'spid'=>$this->spid));
			
			$data               =   array();
			$data['name']		=	$userrows['m_status']==1 ? $userrows['name'] : $userrows['username_n'];
			$data['uid']		=	$userrows['uid'];
			$data['photo']		=	$userrows['photo'];
			
			$expect				=	$resumeM -> getExpect(array('uid' => $uid, 'defaults' => 1), array('field' => '`id`, `job_classid`', 'needCache' => 1));
			$data['id']			=	$expect['id'];
			$data['jobname']	=	$expect['jobnameArr'];
			$this -> yunset('usermsg', $data);

			//企业联系信息(默认联系方式)
			$comM				=	$this -> MODEL('company');
			$company 			=	$comM -> getInfo($fuid, array('field' => '`linktel`, `linkphone`, `linkman`, `address`'));
 			
			$jobid				=	intval($_GET['jobid']);
			$jobM 				=	$this -> MODEL('job');
			
			if(!empty($jobid)){
				
			    $job			=	$jobM -> getInfo(array('uid' =>	$fuid, 'status' => 0, 'state' => 1, 'id' => $jobid));				
					
				if(!empty($job)){
					
				    $job_link	=	$jobM -> getComJobLinkInfo(array('uid' => $fuid, 'jobid' => $jobid), array('field' => '`link_man`, `link_moblie`,`link_address`'));					
					 
					if($job['is_link'] == 1){
					    
						$job['link_man']      =	  $company['linkman'];
						$job['link_moblie']   =	  $company['linktel'] ? $company['linktel'] : $company['linkphone'];
						$job['address']       =	  $company['address'];
					}elseif($job['is_link'] == 2){
					    
						$job['link_man']      =	  $job_link['link_man'];
						$job['link_moblie']   =	  $job_link['link_moblie'];
						$job['address']       =	  $job_link['link_address'];
					}
					
				}
				
				
			}else{
                $job['link_man']      =	  $company['linkman'];
				$job['link_moblie']   =	  $company['linktel'] ? $company['linktel'] : $company['linkphone'];
				$job['address']       =	  $company['address'];
            }
     
            $this -> yunset('job', $job);
			//公司旗下职位信息（包含职位联系方式）
			$joblistA    =   $jobM -> getList(array('uid' => $fuid, 'status' => 0, 'state' => 1, 'r_status' => 1), array('link'=>'yes', 'field' => '`id`, `name`, `is_link`'));
			$joblist     =   $joblistA['list'];
			    
			$this -> yunset('joblist', $joblist);
		

			//邀请模板
            $yqmbM  =   $this->MODEL('yqmb');
            $ymlist = $yqmbM  ->getList(array('uid'=>$this->uid));
            $ymnum  = $yqmbM  ->getNum(array('uid'=>$this->uid));
            $ymcan  = $ymnum<$this->config['com_yqmb_num'] ? true : false; 
            
            $this->yunset('ymlist', $ymlist);
            $this->yunset('ymcan', $ymcan);
            
 			$this -> seo('invite_resume');
		}
		
		if($_GET['invite']){
		
		    $this->yunset('backurl', Url('wap',array('c' => 'look_resume'),'member'));
		}
		$this -> yunset('headertitle', '面试邀请');
		$this -> yuntpl(array('wap/invite'));
	}
	
	function history_action(){
	    
	    if ($_POST['eid'] && ($this->usertype == 2 || $this->usertype == 3)) {
	        
	        $resumeM        =   $this->MODEL('resume');
	        
	        $eid            =   intval($_POST['eid']);
	        
	        $resume_expect  =   $resumeM->getExpect(array('id' => $eid), array('field' => '`uid`'));
	        
	        $time           =   time();
	        
	        $cookieM        =   $this->MODEL('cookie');
	        
	        $cookieEids     =   $_COOKIE['lookresume'];
	        
	        if ($cookieEids) {
	            
	            $resumeArr  =   @explode(',', $cookieEids);
	            
	            if (!in_array($eid, $resumeArr)) {
	                
	                $lookResumeIds  =   $cookieEids.",".$eid;
	            }else{
	                
	                $lookResumeIds  =   $cookieEids;
	            }
	        }else{
	            
	            $lookResumeIds  =   $eid;
	        }
	        
	        $cookieM -> setcookie('lookresume', $lookResumeIds, $time + 3600);
	        
	        $lookM          =   $this->MODEL('lookresume');
	        
	        // 浏览记录处理
	        $lookM -> browseResume(array(
	            'euid'      =>  $resume_expect['uid'],
	            'uid'       =>  $this->uid,
	            'usertype'  =>  $this->usertype,
	            'did'       =>  $this->config['did'],
	            'eid'       =>  $eid
	        ));
	        
	    }
	}
}
?>