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
class index_controller extends job_controller{

	function comsearch(){

	    if($_GET['job']){//职位类别匹配

	        $job				=	explode("_",$_GET['job']);

	        $_GET['job1']		=	$job[0];
	        $_GET['job1_son']	=	$job[1];
	        $_GET['job_post']	=	$job[2];
		}

		if($_GET['ejob']){//职位类别匹配

			include PLUS_PATH.'jobename.cache.php';
			include PLUS_PATH.'jobfs.cache.php';
			include PLUS_PATH.'jobparent.cache.php';

	        $ejob	=	array_search($_GET['ejob'],$job_ename);
			if($ejob && in_array($ejob,$job_three)){

				$_GET['job1']			=	$job_parent[$job_parent[$ejob]];
				$_GET['job1_son']		=	$job_parent[$ejob];
				$_GET['job_post']		=	$ejob;
			}elseif($ejob && in_array($ejob,$job_two)){

				$_GET['job1']			=	$job_parent[$ejob];
				$_GET['job1_son']		=	$ejob;
				$_GET['job_post']		=	0;
			}else{

				$_GET['job1']			=	$ejob;
				$_GET['job1_son']		=	0;
				$_GET['job_post']		=	0;
			}
	    }

	    if($_GET['city']){//城市匹配

	        $city					=	explode("_",$_GET['city']);

	        $_GET['provinceid']		=	$city[0];
	        $_GET['cityid']			=	$city[1];
	        $_GET['three_cityid']	=	$city[2];
	    }
		if($_GET['ecity']){//城市类别匹配

			include PLUS_PATH.'cityename.cache.php';
			include PLUS_PATH.'cityfs.cache.php';
			include PLUS_PATH.'cityparent.cache.php';

	        $ecity				=	array_search($_GET['ecity'],$city_ename);
			if($ecity && in_array($ecity,$city_three)){

				$_GET['provinceid']			=	$city_parent[$city_parent[$ecity]];
				$_GET['cityid']				=	$city_parent[$ecity];
				$_GET['three_cityid']		=	$ecity;
			}elseif($ecity && in_array($ecity,$city_two)){

				$_GET['provinceid']			=	$city_parent[$ecity];
				$_GET['cityid']				=	$ecity;
				$_GET['three_cityid']		=	0;
			}else{

				$_GET['provinceid']			=	$ecity;
				$_GET['cityid']				=	0;
				$_GET['three_cityid']		=	0;
			}
	    }
		
	    if($_GET['tp']==1){
	        $_GET['urgent']	=	1;
	    }
	    if($_GET['tp']==2){
	        $_GET['rec']	=	1;
	    }
	    if($_GET['all']){//合并参数匹配

	        $allurl				=	explode("_",$_GET['all']);

	        $_GET['hy']			=	$allurl[0];
	        $_GET['edu']		=	$allurl[1];
	        $_GET['exp']		=	$allurl[2];
	        $_GET['sex']		=	$allurl[3];
	        $_GET['report']		=	$allurl[4];
	        $_GET['uptime']		=	$allurl[5];
	        $_GET['welfare']	=	$allurl[6];
			$_GET['jobtype']	=	$allurl[7];
	    }
	    if ($_GET['salary']){

	        $salary				=	explode('_', $_GET['salary']);

	        $_GET['minsalary']	=	$salary[0];
	        $_GET['maxsalary']	=	$salary[1];
	    }
	    if ($this->config['sy_web_city_one']) {
	        $_GET['provinceid'] = 	$this->config['sy_web_city_one'];
	    }
	    if ($this->config['sy_web_city_two']) {
	        $_GET['cityid'] 	= 	$this->config['sy_web_city_two'];
	    }

		if($this->config['province']){
			$_GET['provinceid'] 	= 	$this->config['province'];
		}
		if($this->config['cityid']){
			$_GET['cityid'] 		= 	$this->config['cityid'];
		}
		if($this->config['three_cityid']){
			$_GET['three_cityid']	= 	$this->config['three_cityid'];
		}

        $CacheM		=	$this->MODEL('cache');
        $CacheList	=	$CacheM->GetCache(array('job','city','com','hy','uptime'));
	    $this->yunset($CacheList);

		$FinderParams	=	array('hy','job','city','job1','job1_son','job_post','provinceid','cityid','three_cityid','minsalary','maxsalary','edu','exp','sex','type','report','uptime','keyword');
		foreach($_GET as $k=>$v){

			if(in_array($k,$FinderParams) && $v!="" && $v!="0"){
				$finder[$k]	=	$v;
			}
		}
		if (isset($finder)){

		    unset($finder['city']);
		    unset($finder['job']);
		    unset($finder['all']);
		    unset($finder['tp']);

		    $this->yunset('finder',$finder);


		    if($this->config['cityid']){
		        unset($finder['cityid']);
		    }

		    if($finder && is_array($finder)){
		        foreach($finder as $key=>$val){
		            $para[]	=	$key."=".$val;
		        }
		        $paras		=	@implode('##',$para);
		        $this->yunset("paras",$paras);
		    }
		}
		$jobM	=	$this->MODEL('job');
		if($this->uid){

			$favjob	=	$jobM->getFavJobList(array('uid'=>$this->uid),array('field'=>'`job_id`'));
			if(is_array($favjob)){
				foreach($favjob as $k=>$v){
					$favjobarr[]	=	$v['job_id'];
				}
			}
			$this->yunset("favjob",$favjobarr);
		}
		$recNum   =   $jobM -> getJobNum(array('state' => 1, 'status' => 0, 'r_status' => 1, 'rec' => 1, 'rec_time' => array('>', time())));
		if ($recNum > 0) {
		    $this->yunset('recNum', $recNum);
		}

		//关键字显示
		include PLUS_PATH."keyword.cache.php";
		if(is_array($keyword)){
		    foreach($keyword as $k=>$v){
		        if($v['type']=='3'&&$v['tuijian']=='1'){
		            $jobkeyword[]	=	$v;
		        }
		    }
		}
		$this->yunset("jobkeyword",$jobkeyword);
		// 设置用户类型
        if ($this->uid && $this->usertype && $this->usertype != 1) {
            $typename = array('2' => '企业身份');
            $this->yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">' . $this->username . '</span>，账户属于' . $typename[$this->usertype] . '。');
        }
        if ($this->uid && $this->usertype == 1) {
            $lookJobIds    =   @explode(',', $_COOKIE['lookjob']);
            $this->yunset("lookJobIds", $lookJobIds);
        }
        // 处理区分SEO,有搜索条件的和没有搜索条件的不同
        if (isset($finder)){
			$data['seacrh_class']=$_GET['keyword'];
			$this->data				=	$data;
            $this->seo("com_search");
        }else{
            $this->seo("com");
        }
		$this->yun_tpl(array('search'));
	}
	function search_action(){

		$this->comsearch();
	}
	function index_action(){


 		if($this->config['sy_default_comclass']=='1'){
			$CacheM		=	$this->MODEL('cache');
            $CacheList	=	$CacheM->GetCache(array('job','city','hy'));
            $this->yunset($CacheList);
			$this->seo("com");
			$this->yun_tpl(array('index'));
		}else{
			$this->comsearch();
		}
	}
	function addfinder_action(){
		if($this->usertype==$_POST['usertype'] && $this->uid){

			$finderM  	=  	$this -> MODEL('finder');
			$para		=	$_POST['para'];
			$data		=	array(
				'uid'	=>	$this->uid,
				'usertype'	=>	$this->usertype
			);
			if(!empty($para)){
				$para_arr	=	@explode("##",$para);
				$val_arr	=	array();
				foreach($para_arr as $key=>$val){
					$val_arr	=	@explode("=",$val);
					$data[$val_arr[0]]	=	$val_arr[1];
				}
			}
			$return   =  $finderM  ->  addInfo($data);

			$this->layer_msg($return['msg'],$return['errcode'],0);
		}else{
			if($_POST['usertype']=="1"){
				$msg	=	"只有个人用户才能添加职位搜索器";
			}elseif($_POST['usertype']=="2"){
                $msg	=	"只有企业用户才能添加人才搜索器！";
			}else{
                $msg	=	"当前会员类型不允许添加搜索器！";
            }
			$this->layer_msg($msg,8,0);
		}
	}
	function report_action(){
        session_start();

        $reportM	=	$this->MODEL('report');

		if(md5(strtolower($_POST['authcode']))!=$_SESSION['authcode']  || empty($_SESSION['authcode'])){
			unset($_SESSION['authcode']);
			echo 1;die;//验证码不正确！
		}
		$row	=	$reportM->getReportOne(array('p_uid'=>$this->uid,'eid'=>(int)$_POST['id'],'c_uid'=>(int)$_POST['r_uid'],'usertype'=>$this->usertype));

		if(is_array($row)){
			echo 2;die;//您已经举报过该用户！
		}
        $data	=	array(
			'c_uid'		=>	(int)$_POST['r_uid'],
			'inputtime'	=>	mktime(),
			'p_uid'		=>	$this->uid,
			'usertype'	=>	(int)$this->usertype,
			'eid'		=>	(int)$_POST['id'],
			'r_name'	=>	$this->stringfilter($_POST['r_name']),
			'username'	=>	$this->username,
			'r_reason'	=>	$this->stringfilter($_POST['r_reason']),
			'did'		=>	$this->userdid
		);

		$nid	=	$reportM->addJobReport($data);
		if($nid){
			echo 3;die;//举报成功！
		}else{
			echo 4;die;//举报失败！
		}
	}
	/**
	 * 职位详情
	 * 邮件推荐
	 * 2019-06-18
	 */
	function recommend_action(){

		if(!isset($this->uid) || !$this->uid){
			header('Location: '.Url('login'));die;
		}

		if(!empty($_POST)){
			if($_POST['femail'] == '' || $_POST['authcode'] == ''){
				echo "请完整填写信息！";die;
			}
			session_start();
			if(md5(strtolower($_POST['authcode'])) != $_SESSION['authcode']){
				echo "验证码不正确！";die;
			}
			unset($_SESSION['authcode']);

			if($this->config['sy_email_set']!="1"){
				echo "网站邮件服务器不可用";die;
			}

			$recomM						=	$this -> MODEL('recommend');

			//判断当天推荐职位、简历数是否超过最大次数
			if(isset($this->config['sy_recommend_day_num'])	&& $this->config['sy_recommend_day_num'] > 0){

				$num					=	$recomM -> getRecommendNum(array('uid'=>$this->uid));
				if($num >= $this->config['sy_recommend_day_num']){

					echo "每天最多推荐{$this->config['sy_recommend_day_num']}次职位/简历！";exit;
				}
			}
			//判断上一次推荐的时间间隔
			if(isset($this->config['sy_recommend_interval']) && $this->config['sy_recommend_interval'] > 0){
				$row 					=	$recomM -> getInfo(array('uid' => $this -> uid, 'orderby' => 'addtime'));
				if(!empty($row['addtime']) && (time() - $row['addtime']) < $this->config['sy_recommend_interval']){
					$needTime = $this->config['sy_recommend_interval'] - (time() - $row['addtime']);
					if($needTime > 60){
						$h 				=	floor(($needTime % (3600*24)) / 3600);
						$m				=	floor((($needTime % (3600*24)) % 3600) / 60);
						$s				=	floor((($needTime % (3600*24)) % 3600 % 60));
						if($h != 0){
							$needTime	=	$h.'时';
						}else if($m != 0){
							$needTime	=	$m.'分';
						}
					}else{
						$needTime		=	$needTime.'秒';
					}

					$recs 				=	$this->config['sy_recommend_interval'];
					if($recs>60){
						$h 				=	floor(($recs % (3600*24)) / 3600);
						$m				=	floor((($recs % (3600*24)) % 3600) / 60);
						$s				= 	floor((($recs % (3600*24)) % 3600 % 60));
						if($h != 0){
							$recs		=	$h.'时';
						}else if($m != 0){
							$recs		=	$m.'分';
						}
					}else{
						$recs			=	$recs.'秒';
					}
					echo "推荐职位、简历间隔不得少于{$recs}，请{$needTime}后再推荐";exit;
				}
			}
            $jobM  =  $this->MODEL('job');
            $Info  =  $jobM -> getInfo(array('id' => $_POST['id'], 'state' => 1, 'r_status' => array('<>', 2)), array('com' => 'yes'));

			global $phpyun;

			$phpyun->assign('Info',$Info);
			$contents  =  $phpyun->fetch(TPL_PATH.$this->config['style']."/".$this->m."/sendjob.htm",time());

			$userinfoM					=	$this -> MODEL('userinfo');
			//查询用户的昵称（个人用户 resume表，企业用户company表）
			$nickname					=	'';
			$whereData					=	array('uid' => $this -> uid);
		    if($this->usertype == 1 || $this->usertype == 2){

				$fieData				=	array('usertype' => $this->usertype, 'field' => '`name`');
				$row 					= 	$userinfoM -> getUserInfo($whereData, $fieData);
		    	if($row['name']){
		    		$nickname			=	$row['name'];
		    	}
		    }else if($this->usertype == 3){
				$fieData				=	array('usertype' => 3, 'field' => '`realname`');
				$row 					= 	$userinfoM -> getUserInfo($whereData, $fieData);
		    	if($row['realname']){
		    		$nickname			=	$row['realname'];
		    	}
		    }

			$myemail					=	$this -> stringfilter($nickname);
			$title						=	'您的好友'.$myemail.'向您推荐了职位！';

			//发送邮件并记录入库
			$emailData['email']			=	$_POST['femail'];
			$emailData['subject']		=	$title;
			$emailData['content']		=	$contents;
			$notice						=	$this -> MODEL('notice');
			$sendid						=	$notice -> sendEmail($emailData);

			if($sendid['status'] != -1){
				echo 1;
			}else{
				echo "邮件发送错误 原因：" . $sendid['msg'];die;
			}
			//保存推荐记录到数据表recommend表
			$recommend 					=	array(
				'uid' 					=>	$this->uid,
				'rec_type'				=>	1,
				'rec_id'				=>	$_POST['id'],
				'email'					=>	$_POST['femail'],
				'addtime'				=>	time()
			);
			$result						=	$recomM -> addRecommendInfo($recommend);die;
		}

		$idStr				=	intval($_GET['id']);
		$JobM				=	$this->MODEL('job');
		$jobfieData			=	array('com' => 'yes');
		$Info				=	$JobM -> getInfo(array('id' => $idStr ,'state' => 1, 'r_status' => array('<>', 2)), $jobfieData);
		$Job				=	$Info;
		$this -> yunset('Info', $Job);

		if($this->uid && $this->usertype && $this->usertype != 1){

			$typename		=	array('2' => '企业身份');
			$this -> yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，账户属于'.$typename[$this->usertype].'。');
		}

		$data['job_name']		=	$Job['jobname'];//职位名称
		$data['industry_class']	=	$Job['job_hy'];//公司行业
		$data['job_class']		=	$Job['job_class_one'].",".$Job['job_class_two'].",".$Job['job_class_three'];//职位名称
		$data['job_desc']		=	$this -> GET_content_desc($Job['description']);//描述
		$data['company_name']	=	$Job['com_name'];
		$data['job_salary']		= 	$Job['job_salary'];
		$this -> data			=	$data;
		$this -> seo('recommend');

		$this -> yun_tpl(array('recommend'));
	}

	/**
	 * 职位详情
	 * 发送职位
	 * 2019-06-18
	 */
	function sendjob_action(){
		$idStr				=	intval($_GET['id']);
		if(!empty($idStr)){
			$JobM			=	$this->MODEL('job');
			$jobfieData		=	array('com' => 'yes');
			$Info			=	$JobM -> getInfo(array('id' => $idStr, 'state' => 1, 'r_status' => array('<>', 2)), $jobfieData);

			$this -> yunset('Info', $Info);
		}
		$this -> yun_tpl(array('sendjob'));
	}

	function send_email_job_action(){
		$this->yun_tpl(array('send_email_job'));
	}

	function question_action(){
		$this->yun_tpl(array('question'));
	}


}
?>