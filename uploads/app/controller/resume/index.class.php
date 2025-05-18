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
class index_controller extends resume_controller{
	function usersearch(){
		if($_GET[job]){//职位类别匹配
			$job				=	explode("_",$_GET[job]);
			$_GET['job1']		=	$job[0];
			$_GET['job1_son']	=	$job[1];
			$_GET['job_post']	=	$job[2];
		}
		if($_GET['ejob']){//职位类别匹配

			include PLUS_PATH.'jobename.cache.php';
			include PLUS_PATH.'jobfs.cache.php';
			include PLUS_PATH.'jobparent.cache.php';

	        $ejob				=	array_search($_GET['ejob'],$job_ename);
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
		if($_GET[city]){//城市匹配
			$city					=	explode("_",$_GET[city]);
			$_GET['provinceid']		=	$city[0];
			$_GET['cityid']			=	$city[1];
			$_GET['three_cityid']	=	$city[2];
		}
		if($_GET['ecity']){//城市类别匹配

			include PLUS_PATH.'cityename.cache.php';
			include PLUS_PATH.'cityfs.cache.php';
			include PLUS_PATH.'cityparent.cache.php';

	        //$ecity				=	array_search($_GET['ecity'],$city_ename);
	        $ecity				=	0;
	        foreach($city_ename as $ek=>$ev){
	        	if($_GET['ecity'] == $ev && $ek > $ecity){
	        		$ecity	=	$ek;
	        	}
	        }

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
		if($_GET[tp]==1){
			$_GET['pic']			=	1;
		}

		if($_GET[all]){//合并参数匹配
			$allurl				=	explode("_",$_GET[all]);
			$_GET['hy']			=	$allurl[0];
			$_GET['edu']		=	$allurl[1];
			$_GET['exp']		=	$allurl[2];
			$_GET['sex']		=	$allurl[3];
			$_GET['report']		=	$allurl[4];
			$_GET['uptime']		=	$allurl[5];
			$_GET['idcard']		=	$allurl[6];
			$_GET['work']		=	$allurl[7];
			$_GET['tag']		=	$allurl[8];
			$_GET['rtype']		=	$allurl[9];
			$_GET['type']		=	$allurl[10];
			$_GET['integrity']	=	$allurl[11];
		}
		if ($_GET['salary']){
		    $salary				=	explode('_', $_GET['salary']);
		    $_GET['minsalary']	=	$salary[0];
		    $_GET['maxsalary']	=	$salary[1];
		}
		if ($_GET['age']){
		    $age				=	explode('_', $_GET['age']);
		    $_GET['minage']		=	$age[0];
		    $_GET['maxage']		=	$age[1];
		}

        //搜索器的参数键值列表
        $FinderParams=array('hy','job1','job1_son','job_post','provinceid','cityid','three_cityid','minsalary','maxsalary','edu','exp','sex','type','report','uptime','keyword');

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
			$_GET['three_cityid'] 	= 	$this->config['three_cityid'];
		}
		$CacheM		=	$this->MODEL('cache');
        $CacheList	=	$CacheM->GetCache(array('job','city','user','hy','uptime'));
        $this->yunset($CacheList);
        include(CONFIG_PATH.'db.data.php');
        $this->yunset('integrity_name',$arr_data['integrity_name']);

		//当前的搜索条件
		foreach($_GET as $k=>$v){
			if(in_array($k,$FinderParams)&&$v!=""&&$v!="0"){
				if($v!=""){
					$finder[$k]	=	$v;
				}
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
		    if($finder&&is_array($finder)){
		        foreach($finder as $key=>$val){
		            $para[]	=	$key."=".$val;
		        }
		        $paras		=	@implode('##',$para);
		        $this->yunset("paras",$paras);
		    }
		}

		//招聘频道获取城市和职位
		if((int)$_GET['three_cityid']){
			foreach($CacheList['city_type'] as $k=>$v){
				if(in_array((int)$_GET['three_cityid'],$v)){
					$zpthreecityid	=	$k;
				}
			}
			$this->yunset("zpthreecityid",$zpthreecityid);
		}elseif((int)$_GET['cityid']){
			foreach($CacheList['city_type'] as $k=>$v){
				if(in_array((int)$_GET['cityid'],$v)){
					$zpcityid		=	$k;
				}
			}
			$this->yunset("zpcityid",$zpcityid);
		}
		if((int)$_GET['job_post']){
			foreach($CacheList['job_type'] as $k=>$v){
				if(in_array((int)$_GET['job_post'],$v)){
					$zpjobpost		=	$k;
				}
			}
			$this->yunset("zpjobpost",$zpjobpost);
		}elseif((int)$_GET['job1_son']){

			foreach($CacheList['job_type'] as $k=>$v){

				if(in_array((int)$_GET['job1_son'],$v)){

					$zpjob1son		=	$k;
				}
			}
			$this->yunset("zpjob1son",$zpjob1son);
		}
		//招聘频道获取城市和职位end

		//关键字显示
        include PLUS_PATH."keyword.cache.php";
        if(is_array($keyword)){
			foreach($keyword as $k=>$v){
				if($v['type']=='5'&&$v['tuijian']=='1'){
					$resumekeyword[]	=	$v;
				}
			}
        }
        $this->yunset("resumekeyword",$resumekeyword);
        //关键字显示end

		//设置用户类型
		if($this->uid &&  $this->usertype &&  ($this->usertype != 2 || $this->usertype != 3)){
			$typename		=	array('1' => '求职者');
			$this -> yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">'.$this->username.'</span>，账户属于'.$typename[$this->usertype].'。');
		}

		//已下载
		if($this->usertype == 2 || $this->usertype == 3){
    		$downM    =	  $this -> MODEL('downresume');
    		$down     =	  $downM -> getSimpleList(array('comid' => $this->uid,'usertype'=>$this->usertype), array('field' => '`eid`'));
    		$eid      =   array();
    		foreach ($down as $v){
    		    $eid[]	=	$v['eid'];
    		}
    		$this -> yunset('eid',$eid);

    		$lookResumeIds    =   @explode(',', $_COOKIE['lookresume']);
    		$this->yunset("lookResumeIds", $lookResumeIds);
		}

		$companyM    =	  $this -> MODEL('company');
		$comnum     =	  $companyM -> getCompanyNum(array('uid' => $this->uid));
		$this->yunset("comnum", $comnum);
		// 处理区分SEO,有搜索条件的和没有搜索条件的不同
		if (isset($finder)){
		    $this->seo("user_search");
		}else{
		    $this->seo("user");
		}
		if($this->userInfo){
			$this->yunset('r_status',$this->userInfo['r_status']);
		}
		$this->yun_tpl(array('search'));
	}
	function search_action(){
		$this->usersearch();
	}
	function index_action(){
		if($this->config['sy_default_userclass']=='1'){
	        if($this->config['sy_resumedir']!=""){
				$resumeclassurl	=	$this->config['sy_weburl']."/resume/index.php?c=search&";
			}else{
				$resumeclassurl	=	$this->config['sy_weburl']."/index.php?m=resume&c=search&";
			}
			$this->yunset("resumeclassurl",$resumeclassurl);

			$CacheM		=	$this->MODEL('cache');
            $CacheList	=	$CacheM->GetCache(array('job','city','hy'));
            $this->yunset($CacheList);

			$this->yunset(array('gettype'=>$_SERVER["QUERY_STRING"],'getinfo'=>$_GET));
			$this->seo("user");
			$this->yun_tpl(array('index'));
		}else{
			$this->usersearch();
		}
	}

	function showuid_action(){
	    if($_GET['uid']){
	        $resumeM	=	$this->MODEL('resume');
	        $def_job	=	$resumeM -> getResumeInfo(array('uid' => intval($_GET['uid']), 'r_status' => 1), array('field' => '`def_job`'));
	        $id			=	$def_job['def_job'];
	        header('Location:'.Url('resume', array('c' => 'show', 'id' => $id)));
	    }
	}

}
?>