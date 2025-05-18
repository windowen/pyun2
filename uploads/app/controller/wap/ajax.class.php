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
class ajax_controller extends common{
	// wapadmin使用
	function wap_job_action(){
		include(PLUS_PATH."job.cache.php");
		
		$data	=	"<option value=''>--请选择--</option>";
		
		if(is_array($job_type[$_POST['id']])){
			foreach($job_type[$_POST['id']] as $v){
				$data	.=	"<option value='$v'>".$job_name[$v]."</option>";
			}
		}
		echo $data;
	}
	// wapadmin使用
	function wap_city_action(){
	    include(PLUS_PATH."city.cache.php");
	    
	    if(is_array($city_type[$_POST['id']])){
	        $data	=	"<option value=''>--请选择--</option>";
	        foreach($city_type[$_POST['id']] as $v){
	            $data	.=	"<option value='$v'>".$city_name[$v]."</option>";
	        }
	    }
	    echo $data;
	}
	/**
	 * 简历详情
	 * 面试邀请
	 * 2019-06-22
	 */
	function sava_ajaxresume_action(){
		
		$jobM		=	$this -> MODEL('job');

		$_POST		=	$this -> post_trim($_POST);
		$_POST['port']	=	'2';
		$fidArr		=	array(
			'fuid'		=>	$this -> uid,
			'fusername'	=>	$this -> username,
			'fusertype'	=>	$this -> usertype
		);

		$res		=	$jobM -> addYqmsInfo(array_merge($fidArr, $_POST));
		echo json_encode($res);
		die;
	}

	private function _out($arr){
        $arr['usertype']	=	$this->usertype;
        if($arr['msgList']){
           $arr['msgList']  =   $arr['msgList']['wap'];
        }
		echo json_encode($arr);die;
	}
	

	/**
	 * 下载简历（查看联系方式）
	 * 2019-06-24
	 */
	function forlink_action(){
		$downReM		=	$this -> MODEL('downresume');
		$data			=	array(
			'eid'		=>	$_POST['eid'],
		    'uid'		=>	$this -> uid,
		    'spid'		=>	$this -> spid,
		    'usertype'	=>	$this -> usertype,
			'utype'		=>	'wap',
			'did'		=>	$this -> userdid,
		);
		$downRes		=	$downReM -> downResume($data);
		$this -> _out($downRes);

	}
	
	function talentpool_action(){//加入人才库 
		
		$data		=	array(
			'eid'		=>	$_POST['eid'],
			'cuid'		=>	$this->uid,
			'usertype'	=>	$this->usertype,
			'uid'		=>	(int)$_POST['uid'],
		);
		
		$ResumeM	=	$this->MODEL('resume');
	    $return		=	$ResumeM -> addTalent($data);

		echo json_encode($return);die;
	}
	
	
  	
    //举报简历
    function indexajaxreport_action(){
		$eid	=	intval($_POST['eid']);
        if(!empty($eid)){
			$reportM	=	$this -> MODEL('report');
            $report		=	$reportM -> getNum(array('eid' => $eid, 'p_uid' => $this -> uid));
            if($report > 0){
                echo 1; die;
            }else{
                echo 2; die;
            }
        }
    }

    // 邀请面试
    function indexajaxresume_action()
    {
        
        if ($_POST) {
            
            $M                  =   $this->MODEL('comtc');
            
            $_POST['uid']       =   $this->uid;
            $_POST['spid']      =   $this->spid;
            $_POST['username']  =   $this->username;
            $_POST['usertype']  =   $this->usertype;
            
            $return             =   $M->invite_resume($_POST);
            if ($return['status']) {
                $return['msgList']  =   $return['msgList']['wap'];
                echo json_encode($return);
                die();
            }
        }
    }
    
	//签到，TODO:会员中心
	function sign_action(){
		
		if($_POST['rand']){
			$IntegralM	=	$this -> MODEL('integral');
		
			$userinfoM	=	$this -> MODEL('userinfo');
			
			$date		=	date("Ymd");
			
			$member		=	$userinfoM -> getInfo(array('uid'=>$this->uid,'usertype'=>$_COOKIE['usertype']),array('field'=>"`signday`,`signdays`"));
			
			$lastreg	=	$userinfoM -> getMemberregInfo(array('uid'=>$this->uid,'usertype'=>$_COOKIE['usertype'],'orderby'=>'id,desc'));
			
			$lastregdate=	date("Ymd",$lastreg['ctime']);
			
			if($lastregdate!=$date){
				
				$yesterday	=	date("Ymd",strtotime("-1 day"));
				
				if($lastregdate==$yesterday&&intval(date("d"))>1){
					
					if($member['signday']>=5){
						
						$integral	=	$this->config['integral_signin']*2;
						
					}else{
						
						$integral	=	$this->config['integral_signin'];
						
					}
					$signday	=	$member['signday']+1;
					
					$msg		=	'连续签到'.$signday."天";
					
				}else{
					$signday	=	'1';
					
					$integral	=	$this->config['integral_signin'];
					
					$msg		=	'第一次签到';
					
				}
				$arr	=	array();
				
				$nid	=	$userinfoM -> addMemberreg(array("uid"=>$this->uid,"usertype"=>$this->usertype,'date'=>$date,"ctime"=>time(),'ip'=>fun_ip_get()));
				
				if($nid){
					
				    $IntegralM->company_invtal($this->uid,$this->usertype,$integral,true,$msg,true,2,'integral');
					
					$userinfoM -> upInfo(array('uid'=>$this->uid),array('signday'=>$signday,'signdays'=>array('+','1')));
					
					$arr['type']=date("j");
					
				}else{
					
					$arr['type']=-2;
					
				} 
				$arr['integral']	=	$integral.$this->config['integral_pricename'];
				
				$arr['signday']		=	$signday;
				
				$arr['signdays']	=	$member['signdays']+1;
				
			}
			echo json_encode($arr);die;
		}
	}
	//邮箱认证,发送邮件，TODO:会员中心
	function emailcert_action()
    {
        $ComapnyM   =   $this->MODEL('company');

        session_start();

        $code       =   $_POST['authcode'];

       if (md5(strtolower($code)) != $_SESSION['authcode'] || empty($_SESSION['authcode'])) {

            echo 4;
            die();
        }

        $data   =   array(

            'usertype'  =>  $this->usertype,

            'did'       =>  $this->userid,

            'email'     =>  $_POST['email']

        );

        $errCode    =   $ComapnyM->sendCertEmail(array( 'uid' => $this->uid, 'type' => '1'), $data);

        echo $errCode;
        
        die();
    }
	
	
	//手机认证,发送短信，TODO:会员中心
	function mobliecert_action(){
	    $logM			=	$this->MODEL('log');
		$noticeM 		= 	$this->MODEL('notice');
		
		$result			=		$noticeM->jycheck($_POST['code'],'');
		if(!empty($result)){
			$this->layer_msg($result['msg'], 9, 0, '', 2, $result['error']);
		}
	  
	    if(!$this->uid || !$this->username){
	        $this->layer_msg('请先登录', 9, 0, '', 2, 110);
	    }else{
	        $shell=$this->GET_user_shell($this->uid,$_COOKIE['shell']);
	        if(!is_array($shell)){
	            $this->layer_msg('登录有误', 9, 0, '', 2, 111);
	        }
	        $moblie = $_POST['str'];
	        $user 	= array(
	            'uid'      => $this->uid,
	            'usertype' => $this->usertype
	        );
	        
	        $result 		= $noticeM->sendCode($moblie, 'cert', 2, $user);
	        $logM -> addMemberLog($user['uid'], $user['usertype'], '手机认证验证码', 13, 1);
			
	        echo json_encode($result);exit();
	    }
	}
	//发送短信验证码，TODO:wap前台
	function regcode_action(){
		$noticeM 	= 	$this->MODEL('notice');
		$result		=	$noticeM->jycheck($_POST['code'],'注册会员');
		if(!empty($result)){
			$this->layer_msg($result['msg'], 9, 0, '', 2, $result['error']);
		}
	    $moblie		= 	trim($_POST['moblie']);
	    // 两种情况验证手机号是否被使用，改为在发送验证码时验证
	    // 1-用户名注册且实名认证，需要发送短信验证码
	    // 2-手机号注册，有极验/顶象验证码
	    if ($_POST['noblur']){
	        $registerM	=	$this->MODEL('register');
	        $return 	= 	$registerM->regMoblie(array('moblie'=>$moblie));
	        
	        if($return['errcode']==0){
	            
	            $result 	= 	$noticeM->sendCode($moblie, 'regcode',2);
	            
	            echo json_encode($result);exit();
	        }else{
	            echo json_encode($return);exit();
	        }
	    }else{
	        $result 	= 	$noticeM->sendCode($moblie, 'regcode', 2);
	        
	        echo json_encode($result);exit();
	    }
	}
	//快速申请职位入口
	function temporaryresume_action(){
		$userinfoM	=	$this->MODEL("userinfo");
		$companyM	=	$this->MODEL("company");
		$noticeM 	= 	$this->MODEL('notice');
		$jobM		=	$this->MODEL('job');
		$resumeM	=	$this->MODEL("resume");
		
		$_POST 		= 	$this->post_trim($_POST);
		
		$ismoblie	= 	$userinfoM->getMemberNum(array("moblie"=>$_POST['telphone']));

		if($ismoblie>0){
			$return['msg']	=	'当前手机号已被使用，请更换其他手机号！';
			echo json_encode($return);die;
		}else{
			
			if ($this->config['sy_msg_isopen']==1 && $this->config['sy_msg_login']==1) {
				if(!$_POST['checkcode']){
					$return['msg']	=	'请输入短信验证码！';
					echo json_encode($return);die;
				}
				
    			$cert_arr	= 	$companyM->getCertInfo(array('check'=>$_POST['telphone'],'type'=>2,'orderby'=>'ctime,desc'),array('`ctime`,`check2`'));
    			
				if (is_array($cert_arr)) {
    				
					$checkTime	= 	$noticeM->checkTime($cert_arr['ctime']);
					if ($checkTime) {
						 $res	= 	$_POST['checkcode'] == $cert_arr['check2'];

						 $udata['moblie_status']	=	1;
    				} else {
						$return['msg']	=	'验证码验证超时，请重新点击发送验证码！';
						echo json_encode($return);die;
    				}
    			} else {
					$return['msg']	=	'验证码发送不成功，请重新点击发送验证码！';
					echo json_encode($return);die;
    			}
			}else{
			    
			    $result  =  $noticeM->jycheck($_POST['authcode'],'注册会员');
			    
			    if(!empty($result)){
			        
			        $return['msg']		=  $result['msg'];
			        echo json_encode($return);die;
			    }
			}
			$res = true;
			if($res){
				$salt 	= 	substr(uniqid(rand()), -6);
				$pass 	= 	passCheck($_POST['password'],$salt);
				$ip		=	fun_ip_get();
				$data	=	array(
					'username'	=>	$_POST['telphone'],
					'password'	=>	$pass,
					'usertype'	=>	1,
					'status'	=>	$this->config['user_state'],
					'salt'		=>	$salt,
					'reg_date'	=>	time(),
					'login_date'=>	time(),
					'reg_ip'	=>	$ip,
					'login_ip'	=>	$ip,
					'source'	=>	'12',
					'moblie'	=>	$_POST['telphone'],
					'did'		=>	$this->config['did'],
				);
				// 手机号绑定同步member表
				if (!empty($udata['moblie_status'])){
				    $data['moblie_status']  =  $udata['moblie_status'];
				}
				$udata['lastupdate']	=	time();

				$sdata	=	array(
					"resume_num"	=>	"1",
					"did"			=>	$this->config['did']
				);
				$udata['r_status']	=	$this->config['user_state'];
				$userid	=	$userinfoM->addInfo(array('mdata'=>$data,'udata'=>$udata,'sdata'=>$sdata));
				if($userid['error']){
					$return['msg']		=	$userid['msg'];
					$return['errcode']	=	$userid['error'];
					echo json_encode($userid);die;
				}
			}
			if(intval($userid)){
				$this->cookie->add_cookie($userid,$_POST['telphone'],$salt,"",$pass,1,$this->config['sy_logintime'],$this->config['did']);
				//简历基本信息数据
				$rData = array(
					'name' 		=> $_POST['uname'],
					'sex' 		=> $_POST['sex'],
					'birthday' 	=> $_POST['birthday'],
					'edu' 		=> $_POST['edu'],
					'exp' 		=> $_POST['exp'],
					'telphone' 	=> $_POST['telphone']
				);
				//简历求职意向数据
				include PLUS_PATH."user.cache.php";
				include PLUS_PATH."/job.cache.php";
				$jobinfo	=	$jobM->getInfo(array('id' => intval($_POST['jobid'])),array('field'=>'`hy`,`job1`,`job1_son`,`job_post`,`provinceid`,`cityid`,`three_cityid`,`minsalary`,`maxsalary`'));
				
    			if ($jobinfo['job_post']) {
    				$job_classid	=	$jobinfo['job_post'];
    			} elseif ($jobinfo['job1_son']) {
    				$job_classid	=	$jobinfo['job1_son'];
    			} else {
    				$job_classid	=	$jobinfo['job1'];
    			}
    			if ($jobinfo['three_cityid']){
    			    $city_classid	=	$jobinfo['three_cityid'];
    			}elseif($jobinfo['cityid']){
    			    $city_classid	=	$jobinfo['cityid'];
    			}else{
    			    $city_classid	=	$jobinfo['provinceid'];
    			}
				
				$eData = array(
					'lastupdate' 	=> time(),
					'uid' 			=> $userid,
					'ctime' 		=> time(),
					'name' 			=> $job_name[$job_classid],
					'hy' 			=> $jobinfo['hy'],
					'job_classid' 	=> $job_classid,
					'city_classid' 	=> $city_classid,
					'minsalary' 	=> $jobinfo['minsalary'],
					'maxsalary' 	=> $jobinfo['maxsalary'],
					'type' 			=> $userdata['user_type'][0],
					'report' 		=> $userdata['user_report'][0],
					'jobstatus' 	=> $userdata['user_jobstatus'][0],
					'state' 		=> $this->config['resume_status'],
          			'r_status' 		=> 1,
					'edu' 			=> $_POST['edu'],
					'exp' 			=> $_POST['exp'],
					'sex' 			=> $_POST['sex'],
					'birthday' 		=> $_POST['birthday'],
					'source' 		=> 12
				);
				//简历工作经历数据
				$workData = array();
				if ($this->config['resume_create_exp'] == '1') {
					$workData[] = array(
						'uid' 		=> $userid,
						'name' 		=> $_POST['workname'],
						'sdate' 	=> strtotime($_POST['worksdate']),
						'edate' 	=> $_POST['workedate'] ? strtotime($_POST['workedate']) : 0,
						'title' 	=> $_POST['worktitle'],
						'content'	=> $_POST['workcontent']
					);
				}
				//简历教育经历数据
				$eduData = array();
				if ($this->config['resume_create_edu'] == '1') {
					$eduData[] = array(
						'uid' => $userid,
						'name' => $_POST['eduname'],
						'sdate' => strtotime($_POST['edusdate']),
						'edate' => strtotime($_POST['eduedate']),
						'title' => $_POST['edutitle'],
						'specialty' => $_POST['eduspec'],
						'education' => $_POST['education']
					);
				}
				//简历项目经历数据
				$proData = array();
				if ($this->config['resume_create_project'] == '1') {
					$proData[]	= 	array(
						'uid' 		=> $userid,
						'name' 		=> $_POST['proname'],
						'sdate' 	=> strtotime($_POST['prosdate']),
						'edate' 	=> strtotime($_POST['proedate']),
						'title' 	=> $_POST['protitle'],
						'content'	=> $_POST['procontent']
					);
				}
				
				$addArr	= 	array(
					'uid' 		=> $userid,
					'rData' 	=> $rData,
					'eData' 	=> $eData,
					'workData' 	=> $workData,
					'eduData' 	=> $eduData,
					'proData' 	=> $proData,
					'utype' 	=> 'user'
				);
				$return	= 	$resumeM->addInfo($addArr);
				if($return['errcode']!=9){
					
					echo json_encode($return);die;
				}
				if($return['id']){
					
					$eid	=	$return['id'];
					
					if($this->config['user_state']!="1"){
						$return['msg']		=	'您的账号需要通过审核，才能投递简历哦！';
						$return['errcode']	=	7;
						$return['url']		=	Url('wap',array("c"=>"job","a"=>"comapply",'id'=>intval($_POST['jobid']),'tolist'=>1));
						echo json_encode($return);die;
					}
					if(!$this->config['resume_status']){
						$return['msg']		=	'您的简历需要通过审核，才能投递简历哦！';
						$return['errcode']	=	7;
						$return['url']		=	Url('wap',array("c"=>"job","a"=>"comapply",'id'=>intval($_POST['jobid']),'tolist'=>1));
						echo json_encode($return);die;
					}
					if($this->config['user_sqintegrity']){
						$expect	=	$resumeM->getExpect(array('id'=>$eid),array('field'=>'`integrity`'));
						if($this->config['user_sqintegrity']>$expect['integrity']){
							$return['msg']		=	'该简历完整度未达到'.$this->config['user_sqintegrity'].'%，请先完善简历！';
							$return['errcode']	=	7;
							$return['url']		=	Url('wap',array("c"=>"resume","eid"=>"".$eid.""),'member');
							echo json_encode($return);die;
						}
					}
					$jobid	=	(int)$_POST['jobid'];
					$comjob	=	$jobM->getInfo(array('id'=>$jobid),array('field'=>'`com_name`,`name`,`uid`,`is_link`'));

					$value	=	array(
						'job_id'	=>	$jobid,
						'com_name'	=>	$comjob['com_name'],
						'job_name'	=>	$comjob['name'],
						'com_id'	=>	$comjob['uid'],
						'uid'		=>	$userid,
						'eid'		=>	$eid,
						'datetime'	=>	time()
					);
					$nid	=	$jobM->addSqJob($value);
					
					$historyM	= 	$this->MODEL('history');
					$historyM->addHistory('useridjob',$jobid);

					$jobM->upInfo(array('snum'=>array('+',1)), array('id' => $jobid));
					
					if($comjob['is_link']=='1'){
						$job_link	=	$companyM->getInfo($comjob['uid'],array("field"=>"`linkmail` as email,`linktel` as link_moblie"));
					}else{
						$job_link	=	$jobM->getComJobLinkInfo(array("jobid"=>$jobid,"is_email"=>"1"),array("field"=>"`email`,`link_moblie`"));
					}
					if($this->config['sy_email_set']=="1"){
						if($job_link['email']){
						    $Info       =   $resumeM -> getInfoByEid(array('eid' => $eid));
						    
						    global $phpyun;
						    $phpyun -> assign('Info',$Info);
						    
						    $contents	=	$phpyun -> fetch(TPL_PATH.'resume/sendresume.htm',time());
							$emaildata	= 	array(
                                'email' 	=> 	$job_link['email'],
                                'subject' 	=> 	"您收到一份新的求职简历！——".$this->config['sy_webname'],
                                'content' 	=> 	$contents,
                                //发送email记录到数据表email_msg
                                'uid'		=>	$comjob['uid'],
                                'name'		=>	$comjob['com_name'],
                                'cuid'		=>	'',
                                'cname'		=>	'',
                                'tbContent'	=>	'简历详情eid:' . $eid
                              );
                            $noticeM->sendEmail($emaildata);
						}
					}
					if($this->config['sy_msg_isopen']=='1'){
						if($job_link['link_moblie']){
							$data	=	array(
								'uid'		=>	$comjob['uid'],
								'name'		=>	$comjob['com_name'],
								'cuid'		=>	'',
								'cname'		=>	'',
								'type'		=>	'sqzw',
								'jobname'	=>	$comjob['name'],
								'date'		=>	date("Y-m-d"),
								'moblie'	=>	$job_link['link_moblie'],
								'port'		=>	'2'
							);
                            $noticeM->sendSMSType($data);
						}
					}
					 
					
					$logM	=	$this->MODEL('log');
					$logM->addMemberLog($this->uid,$this->usertype,"我申请了职位：".$comjob['name'],6,1);
					$return['msg']		=	'申请成功！';
					$return['errcode']	=	9;
					$return['url']		=	Url('wap',array("c"=>"job","a"=>"comapply",'id'=>intval($_POST['jobid']),'tolist'=>1));
					echo json_encode($return);die;
				}else{
					$return['msg']		=	'保存失败！';
					$return['errcode']	=	7;
					echo json_encode($return);die;
				}
			}
		}
	}
	function checkuser($username,$name=''){
		$userinfoM	=	$this -> MODEL('userinfo');
		
		$user		=	$userinfoM -> getInfo(array('username'=>$username),array('field'=>'`uid`'));
		
		if($user['uid']){
			
			$name.="_".rand(1000,9999);
			
			return $this->checkuser($name,$username);
			
		}else{
			return $username;
		}
	}
	
	function pl_action(){
		$msgM	=	$this -> MODEL('msg');
		
		$blackM	=	$this -> MODEL('black');
		
		if($this->uid==''||$this->username==''){
			echo 3;die;
		}
		if($this->usertype!= '1'){
			echo 0;die;
		}
		$black	=	$blackM -> getBlackInfo(array('p_uid'=>$this->uid,'c_uid'=>(int)$_POST['job_uid']));
		if(!empty($black)){
			echo 7;die;
		}
		if(trim($_POST['content'])==""){
			echo 2;die;
		}
		if(trim($_POST['authcode'])==""){
			echo 4;die;
		}
		session_start();
		if(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
			echo 5;die;
		}
		$id	=	$msgM -> addInfo(array('uid'=>$this->uid,'username'=>$this->username,'jobid'=>trim($_POST['jobid']),'job_uid'=>trim($_POST['job_uid']),'content'=>trim($_POST['content']),'com_name'=>trim($_POST['com_name']),'job_name'=>trim($_POST['job_name']),'type'=>'1','datetime'=>time()));
		if($id){
			echo 1;die;
		}else{
			echo 6;die;
		}
	}
	
	
	function atncompany_action(){//关注企业
		$data	=	array(
			'id'			=>	(int)$_POST['id'],
			'uid'			=>	$this->uid,
			'usertype'		=>	$this->usertype,
			'username'		=>	$this->username,
			'utype'			=>	'teacher',
			'sc_usertype'	=>	2
		);
		 
		$atnM	=	$this->MODEL('atn');
		$return	=	$atnM->addAtnLt($data);
		echo json_encode($return);die;
	}
	//职位类别
	function getjob_action(){
		include(PLUS_PATH."job.cache.php");
		if(is_array($job_type[$_POST['id']])){	
		    if($_POST['type']=="jobone_son"){				
				$data	.=	'<li onclick="check_job_li(\''.$_POST['id'].'\',\'job1\');"><a href="javascript:;">全部</a></li>';
			}
			if($_POST['type']=="job_post"){				
				$data	.=	'<li onclick="check_job_li(\''.$_POST['id'].'\',\'job1_son\');"><a href="javascript:;">全部</a></li>';
			}			
			foreach($job_type[$_POST['id']] as $v){				
				if($_POST['type']=="jobone_son"){
					if(!empty($job_type[$v])){

						$data	.=	'<li class="qCategoryt'.$v.'" onclick="Categoryt(\''.$v.'\',\''.$job_name[$v].'\',\'job_post\');"><a href="javascript:;">'.$job_name[$v].'</a></li>';
					}else{
						
						$data	.=	'<li onclick="check_job_li(\''.$v.'\',\'job1_son\');"><a href="javascript:;">'.$job_name[$v].'</a></li>';
					}
						
					}else{
						$data	.=	'<li onclick="check_job_li(\''.$v.'\',\'job_post\');"><a href="javascript:;">'.$job_name[$v].'</a></li>';
				    }				
			}			
		}else{
			if($_POST['type']=="jobone_son"){				
				$data	.=	'<li onclick="check_job_li(\''.$_POST['id'].'\',\'job1\');"><a href="javascript:;">全部</a></li>';
			}
			if($_POST['type']=="job_post"){				
				$data	.=	'<li onclick="check_job_li(\''.$_POST['id'].'\',\'job1_son\');"><a href="javascript:;">全部</a></li>';
			}
		}
		echo $data;
	}
	
	
	
	//城市类别
	function getcity_action(){
		include(PLUS_PATH."city.cache.php");
		if(is_array($city_type[$_POST['id']])){	
			if($_POST['type']=="cityid"){				
				$data	.=	'<li onclick="check_city_li(\''.$_POST['id'].'\',\'provinceid\');"><a href="javascript:;">全部</a></li>';
			}
			if($_POST['type']=="three_cityid"){				
				$data	.=	'<li onclick="check_city_li(\''.$_POST['id'].'\',\'cityid\');"><a href="javascript:;">全部</a></li>';
			}		
			foreach($city_type[$_POST['id']] as $v){				
				if($_POST['type']=="cityid"){
					if(!empty($city_type[$v])){
						$data	.=	'<li class="qgradet'.$v.'" onclick="gradet(\''.$v.'\',\''.$city_name[$v].'\',\'three_cityid\');"><a href="javascript:;">'.$city_name[$v].'</a></li>';
					}else{
						$data	.=	'<li onclick="check_city_li(\''.$v.'\',\'cityid\');"><a href="javascript:;">'.$city_name[$v].'</a></li>';
					}
					
				}else{
					$data	.=	'<li onclick="check_city_li(\''.$v.'\',\'three_cityid\');"><a href="javascript:;">'.$city_name[$v].'</a></li>';	
				}
			}		
		}else{
			if($_POST['type']=="cityid"){				
				$data	.=	'<li onclick="check_city_li(\''.$_POST['id'].'\',\'provinceid\');"><a href="javascript:;">全部</a></li>';
			}
			if($_POST['type']=="three_cityid"){				
				$data	.=	'<li onclick="check_city_li(\''.$_POST['id'].'\',\'cityid\');"><a href="javascript:;">全部</a></li>';
			}
		}
		echo $data;
	}	
	
	/**
	 * 企业报名招聘会
	 */
	function ajaxzphjob_action(){
		$data	=	array(
			'usertype'	=>	$this->usertype,
			'uid'		=>	$this->uid,
			'spid'		=>	$this->spid,
			'jobid'		=>	$_POST['jobid'],
			'id'		=>	intval($_POST['id']),
			'zid'		=>	intval($_POST['zid']),
		);
		$zphM	=	$this->MODEL('zph');
		$arr	=	$zphM->ajaxZph($data);
		
		if ($arr['status'] == 2 && !empty($_POST['jobid'])){
		    // 套餐不足，记录jobid的cookie来备用
		    $this->cookie->setcookie('zphjobid', $_POST['jobid'], time()+86400);
		}
		
	    echo json_encode($arr);die;
	}
	//天眼查工商数据获取
	function getbusiness_action(){
		if($_POST['name']){
			$noticeM	=	$this -> MODEL('notice');
			$reurn		=	$noticeM -> getBusinessInfo($_POST['name']);
			
			if(!empty($reurn['content'])){
				$comGsInfo	=	$reurn['content'];

				echo json_encode($comGsInfo);
			}
		}
	}
	
	
	
	//修改密码，TODO:会员中心
	function setpwd_action(){
		if($_POST['password']){
			$UserinfoM  =   $this->MODEL('userinfo');
			$data   	=   array(
                
                'uid'           =>  $this->uid,
                'usertype'      =>  $this->usertype,
                'oldpassword'   =>  $_POST['password'],
                'password'      =>  $_POST['passwordnew'],
                'repassword'    =>  $_POST['passwordconfirm']
                
            );
			$err    =   $UserinfoM -> savePassword($data);
			
			$arr['type']=$err['errcode'];
			$arr['msg']=$err['msg'];
			if($err['errcode'] == '9'){
                $this -> cookie -> unset_cookie();     
            }
            echo json_encode($arr);die;
		}
	}
	
    //消息数
    function msgNum_action()
    {
		$M    =  $this->MODEL('msgNum');
		$arr  =  $M->getmsgNum($this->uid, $this->usertype);
		echo json_encode($arr);
    }
    function ajax_url_action(){
        if($_POST){
            if($_POST['url']!=""){
                $urls=@explode("&",$_POST['url']);
                foreach($urls as $v){
                    if($_POST['type']=="provinceid"||$_POST['type']=="cityid"||$_POST['type']=="three_cityid"){
                        if(strpos($v,"provinceid")===false && strpos($v,"cityid")===false&& strpos($v,"three_cityid")===false){
                            $gourl[]=$v;
                        }
                    }elseif($_POST['type']=="job1"||$_POST['type']=="job1_son"||$_POST['type']=="job_post"){
                        if(strpos($v,"job1")===false && strpos($v,"job1_son")===false&& strpos($v,"job_post")===false){
                            $gourl[]=$v;
                        }
                    }elseif($_POST['type']=="nid"||$_POST['type']=="tnid"){
                        if(strpos($v,"tnid")===false&&strpos($v,"nid")===false){
                            $gourl[]=$v;
                        }
                    }else{
                        if(strpos($v,$_POST['type'])===false){
                            $gourl[]=$v;
                        }
                    }
                }
                if($_POST['id']>0){
                    $gourl=@implode("&",$gourl)."&".$_POST['type']."=".$_POST['id'];
                }else{
                    $gourl=@implode("&",$gourl);
                }
            }else{
                $gourl=$_POST['type']."=".$_POST['id'];
            }
            echo "?".$gourl;die;
        }
    }
	//wap前台商城商品类别
	function getredeem_action(){
		include(PLUS_PATH."redeem.cache.php");
		$data.='<li onclick="check_redeem_li(\''.$_POST['id'].'\',\'nid\');"><a href="javascript:;">全部</a></li>';
		if(is_array($redeem_type[$_POST['id']])){
			foreach($redeem_type[$_POST['id']] as $v){				
				if($_POST['type']=="tnid"){
						$data.='<li onclick="check_redeem_li(\''.$v.'\',\'tnid\');"><a href="javascript:;">'.$redeem_name[$v].'</a></li>';
					}			
			}			
		}
		echo $data;
	}

    // 企业每日最大操作次数检查
    public function ajax_day_action_check_action()
    {
        $type   =   isset($_POST['type']) ? $_POST['type'] : '';
        
        $comM   =   $this -> MODEL('company');
        $com_id =   $this->uid;
        $result =   $comM -> comVipDayActionCheck($type, $com_id);
        
        echo json_encode($result);
        die();
    }
    
     //切换身份
    function changeusertype_action(){
      $memberM		=	$this -> MODEL('userinfo');
		
		$uid			=	$this->uid;
		
		if($this->usertype==2)
		{
			$changeType	=	1;
		}else{
			$changeType	=	2;
		}
		$res			=	$memberM->checkChangeApply($this->uid,$changeType);

		echo json_encode(array('msg'=>$res['msg'],'errcode'=>$res['errcode']));die;
    }
    // 切换账号
    function notuserout_action(){
        $jobid  =   intval($_POST['jobid']);
        
        $this->cookie->unset_cookie();
        

        if ($jobid) {
        
            $url    =   Url('wap', array('c' => 'login', 'job' => $jobid));
        } else {
        
            $url    =   Url('wap', array('c' => 'login'));
        }
        
        echo $url;
        die();
    }
    
    function pubqrcode_action(){//公共二维码跳转
        $wapUrl = Url('wap');
        if($this -> config['sy_ewm_type'] == 'weixin'){//微信公众号带参数二维码
		
			if($_GET['toc'] == 'job'){
				$scene_str = "jobid_".$_GET['toid'];
			}elseif($_GET['toc'] == 'resume'){
				$scene_str = "resumeid_".$_GET['toid'];
			}elseif($_GET['toc'] == 'company'){
				$scene_str = "companyid_".$_GET['toid'];
			}
            
          
            $WxM  =  $this -> MODEL('weixin');
            
			$qrcode = $WxM->pubWxQrcode($scene_str);
			if($qrcode){
			    
			    $imgStr =   CurlGet($qrcode);
			    
			    header("Content-Type:image/png");
			    
			    echo $imgStr;
			    
			}
		    
		

		}else{
			if( isset($_GET['toid']) && $_GET['toid'] != ''){
				$wapUrl = Url('wap',array('c'=>$_GET['toc'],'a'=>$_GET['toa'],'id'=>(int)$_GET['toid']));
			}
			include_once LIB_PATH."yunqrcode.class.php";
			YunQrcode::generatePng2($wapUrl,4);
		}
    }
	
	function index_ajaxjob_action(){

        $JobM	=	$this->MODEL('job');

		$arr	=	array();
		
		$arr['status'] = 2;

		if(empty($_POST['jobid'])){

			$arr['msg']		=	'参数错误，请重试！';

		}else if(!$this->uid || !$this->username || $this->usertype != 1){
			
			$arr['msg']		=	'请登录个人用户！';
		}else{
			
			 
			$jobid			=	intval($_POST['jobid']);
			 
			$sqJobNum		=	$JobM -> getSqJobNum(array('uid' => $this -> uid, 'job_id' => $jobid));
			
			
			if($sqJobNum > 0){	//已投递过

				$arr['msg']	=	'您已申请过该职位！';
			}else{

				$yqmsNum	=	$JobM -> getYqmsNum(array('uid' => $this -> uid, 'jobid' => $jobid));
				
				if($yqmsNum > 0){
					
					$arr['msg']	=	'该职位已邀请您面试，无需再投简历！';
				}else{
					$ResumeM	=	$this -> MODEL('resume');
			
					$resumeList	=	$ResumeM -> getSimpleList(array('uid' => $this -> uid, 'r_status' => 1, 'state' => 1, 'orderby' => 'defaults, desc'), array('field' => '`id`,`name`,`defaults`'));
					
					if(!empty($resumeList)){

						$arr['status']		=	1;
						
						$html		=	'';
						
						foreach($resumeList as $key=>$val){
							if($val['defaults']==1){
								$html .=	"<div class='job_prompt_sendresume_list job_prompt_sendresume_list_cur' id='resume_$val[id]' data_did='$val[id]' onclick='sqjobclic($val[id]);'><i class='job_prompt_sendresume_radio'></i><i class='job_prompt_sendresume_radio_q'></i>$val[name](默认简历)</div>";
							}else{
								$html .=	"<div class='job_prompt_sendresume_list' id='resume_$val[id]' data_did='$val[id]' onclick='sqjobclic($val[id]);'><i class='job_prompt_sendresume_radio'></i><i class='job_prompt_sendresume_radio_q'></i>$val[name]</div>";
							}
						}
						
						$arr['html']	=	$html;

					}else{
						
						$resume			=	$ResumeM -> getExpect(array('uid' => $this -> uid,'defaults'=>1),array('field'=>'`id`,`status`'));

						$resuemNum		=	$ResumeM -> getExpectNum(array('uid' => $this -> uid));

						if(intval($resuemNum) > 0){
						
							$arr['msg']	=	'您的简历尚未完成审核，请联系管理员加快审核进度！';
						}else{
							$arr['msg']		=	'您还没有合适的简历';
						}

					}
				}
			}
		}
		echo json_encode($arr);die;
	}
	//报名招聘会条件判断
	function ajaxComjob_action(){
	    
	    if ($_POST['zph']){
	        
	        $zphM  =  $this->MODEL('zph');
	        $comrow   =  $zphM->getZphComInfo(array('uid'=>$this->uid,'zid'=>$_POST['id']));
	        
	    }
	    if (!empty($comrow)){
	        
	        $data['status']	= 2;
	        
	        if($comrow['status']==0){
	            
	            $data['msg']	= "您已报名,请等待审核！";
	            
	        }else if($comrow['status']==1){
	            
	            $data['msg']	= "您已报名了，请不要重复报名！";
	            
	        }else if($comrow['status']==2){
	            
	            $data['msg']	= "您已报名,且审核未通过！";
	        }
	        
	    }else{
	        
	        $where	=	array(
	            'uid'		=>	$this->uid,
	            'did'		=>	$this->config['did'],
	            'state'		=>	1,
	            'status'	=>	0,
	            'r_status'	=>	array('<>',2),
	        );
	        $jobM	=	$this->MODEL('job');
	        $arr	=	$jobM->getList($where, array('field'=>'`id`,`name`'));
	        $list	=	$arr['list'];
	        
	        if(!empty($list)){
	            
	            $html				=	'';
	            
	            foreach($list as $v){
	                
	                
	                $html			.=	'<div class="mui-input-row mui-checkbox">';
	                $html			.=	'	<label>'.$v[name].'</label>';
	                $html			.=	'	<input id="status_'.$v[id].'" name="checkbox_job" value="'.$v[id].'" type="checkbox" class="lang" />';
	                $html			.=	'</div>';
	                
	            }
	            $data['html']	= $html;
	            
	            $data['status']	= 1;
	            
	        }else{
	            
	            $data['status']	= 2;
	            
	            $data['msg']	= "您还没有发布职位，请先发布职位！";
	        }
	    }
	    
	    echo json_encode($data);die;
	}
	function applytype_action(){
	    
		$memberM		=	$this -> MODEL('userinfo');
		
		$res			=	$memberM->checkChangeApply($this->uid,$_POST['applyusertype'],$_POST['applybody']);
		echo json_encode(array('msg'=>$res['msg'],'url'=>$res['url'],'wxopenid'=>$res['wxopenid'],'wxid'=>$res['wxid'],'errcode'=>$res['errcode']));die;
	}

}	
?>