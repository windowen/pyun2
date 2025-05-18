<?php
/* *
 * $Author ：PHPYUN开发团队
 *
 * 官网: http://www.phpyun.com
 *
 * 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
 *
 * 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
 */
class com_controller extends wap_controller{


    function get_user(){

        $uid	=   intval($this->uid);

        $comM	=	$this->MODEL('company');

        $rows	=	$comM->getInfo($uid, array(
            'logo'   =>  '1',
            'utype'  =>  'user'
        ));

        $this->yunset('company', $rows);

        return $rows;
    }

	function waptpl($tpname){

        $this->yuntpl(array('wap/member/com/'.$tpname));
	}

	function index_action(){

	    $jobM		=   $this -> MODEL('job');
		$userinfoM  =   $this -> MODEL('userinfo');
		$orderM  	=   $this -> MODEL('companyorder');
        


		$uid		=   intval($this->uid);

		$member		=	$userinfoM -> getInfo(array('uid'=>$uid),array('field'=>"`login_date`,`status`"));
		$this->yunset('member',$member);

		$date		=	date('Ymd');

		$reg		=	$userinfoM -> getMemberregInfo(array('uid'=>$uid,'usertype'=>$this->usertype,'date'=>$date));

		$signstate	=	$reg['id'] ? 1 : 0;

		$this->yunset('signstate',$signstate);

        $jobwhere['uid']		=   $this->uid;
		$jobwhere['state']		=	1;
		$jobwhere['r_status']	=	1;
		$jobwhere['status']		=	0;

		$jobsA		=	$jobM -> getList($jobwhere);//招聘中职位

		$jobs		=	$jobsA['list'];
		$ids		=	array();
 		if($jobs && is_array($jobs)){//获取职位ID
		    foreach($jobs as $key=>$v){

		        $ids[]=$v['id'];
			}
			
		}
 		$jobids 	=    count($ids) ? pylode(',', $ids) : '';
		$jobnums	=    count($ids);
		$this->yunset('jobids', $jobids);
		$this->yunset('jobnums', $jobnums);

		
		$statis =	$this -> company_satic();

		$this->yunset('statis',$statis);

		$this->cookie->SetCookie('updatetoast', '1', time() + 86400);

		$this->get_user();

		$this->yunset('backurl',Url('wap',array()));

		
		$UserinfoM  =   $this -> MODEL('userinfo');
        $member     =   $UserinfoM -> getInfo(array('uid'=> $uid), array('setname'=>'1'));
        $this -> yunset("member", $member);

		$nopayorder	=	$orderM	->	getCompanyOrderNum(array('uid'=>$uid,'usertype' => $this->usertype,'order_state'=>'1'));
		$this->yunset('nopayorder',$nopayorder);

		$recresume	=	$this->resumeajax();
		$this->yunset('recresume',$recresume['list']);

 		$this->waptpl('index');
	}
	function resumeajax(){
		$jobM		=	$this->MODEL('job');
		$blackM		=	$this->MODEL('black');
		$resumeM	=	$this->MODEL('resume');


        $jobwhere['com_id']		=   $this->uid;
		$jobwhere['state']		=	1;
		$jobwhere['r_status']	=	1;
		$jobwhere['status']		=	0;

	    $joblist	=	$jobM->getList($jobwhere,array('field'=>'`job1_son`,`job_post`,`cityid`'));
		$joblist    =  $joblist['list'];
	    $blacklist	=	$blackM->getBlackList(array('p_uid'=>$this->uid),array('field'=>'`c_uid`'));
	    if(is_array($joblist) && !empty($joblist)){
	        foreach($joblist as $v){
				$cityids[]						=	$v['cityid'];

				$wherea['PHPYUNBTWSTART_A']		=	'' ;
				if($v['job1_son']){
					$wherea['job_classid'][]	=	array('findin',$v['job1_son']);
				}
				if($v['job_post']){
					$wherea['job_classid'][]	=	array('findin',$v['job_post'],'OR');
				}
				$wherea['PHPYUNBTWEND_A']		=	'' ;
	        }

			$whereSql['PHPYUNBTWSTART_B']		=	'' ;
			$whereSql['job_classid']			=	$wherea['job_classid'];
			//$whereSql['cityid']					=	array('in',pylode(',',$cityids));
			$whereSql['PHPYUNBTWEND_B']			=	'' ;
	    }

	    if(is_array($blacklist) && !empty($blacklist)){
	        foreach($blacklist as $v){
	            $bids[]=$v['c_uid'];
	        }

	        $nwhereSql['uid']		=	$whereSql['uid'] 			=	array('notin',pylode(',',$bids)) ;
	    }
		if(is_array($cityids) && !empty($cityids)){
			$resumeeid=$resumeM->getResumeCityClassList(array('cityid'=>array('in',pylode(',',$cityids))),array('field'=>'`eid`'));
			foreach($resumeeid as $v){
				$eids[]=$v['eid'];
			}
			$nwhereSql['id']		=	$whereSql['id']				=	array('in',pylode(',',$eids));
		}
		$nwhereSql['uname']			=	$whereSql['uname']			=	array('<>','');
		$nwhereSql['status']		=	$whereSql['status']			=	1;
		$nwhereSql['r_status']		=	$whereSql['r_status']		=	1;
		$nwhereSql['state']			=	$whereSql['state']			=	1;
		$nwhereSql['job_classid']	=	$whereSql['job_classid']	=	array('<>','');
		$nwhereSql['defaults']		=	$whereSql['defaults']		=	1;
		$nwhereSql['orderby']		=	$whereSql['orderby']		=	'lastupdate,desc';
		$nwhereSql['limit']			=	$whereSql['limit']			=	8;

	    $resumes 		= 	$resumeM->getList($whereSql);

		$resume			=	$resumes['list'];
		if(empty($resume)){

			$resumes 	= 	$resumeM->getList($nwhereSql);
			$resume		=	$resumes['list'];
		}
	    $list			=	array();
	    if ($resume){
	        foreach ($resume as $v){
	            $uids[]	=	$v['uid'];
	        }
	        if ($uids){
	            $user 	= 	$resumeM->getResumeList(array('uid'=>array('in',pylode(',',$uids))),array('field'=>'`uid`,`name`,`nametype`,`sex`,`photo`,`phototype`,`photo_status`,`birthday`,`def_job`'));
	        }

	        foreach ($resume as $k=>$v){
	            $list[$k]['username_n']='';
	            foreach ($user as $val){
	                if ($v['uid']==$val['uid']){
	                    $list[$k]['username_n'] 	=	$val['name_n'];
						$list[$k]['photo']			=	$val['photo'];
	                }
	            }
	            $list[$k]['id']						=	$v['id'];
	            $list[$k]['edu_n']					=	$v['edu_n']?$v['edu_n'].'学历':'';
	            $list[$k]['exp_n']					=	$v['exp_n']?$v['exp_n'].'经验':'';
	            $list[$k]['sex_n']					=	$v['sex_n']?$v['sex_n']:'';
	            $list[$k]['age_n']					=	$v['age_n']?$v['age_n'].'岁':'';
	            $jobname							=	@explode(',', $v['job_classname']);
	            $list[$k]['jobname']				=	$jobname['0'];

	            $cityname							=	@explode(',', $v['city_classname']);
	            $list[$k]['cityname']				=	$cityname['0'];
	        }
	    }
	    $data['list']=$list;
	    return $data;
	}
	function com_action(){

		$statis   =	  $this -> company_satic();

		$ratingM  =	  $this -> MODEL('rating');
		$rating   =	  $ratingM -> getInfo(array('id' => $statis['rating']));
		$this -> yunset('rating', $rating);

		$backurl  =   Url('wap', array('c'=>'finance'), 'member');
		$this -> yunset('backurl',$backurl);
		$this -> yunset('header_title', '我的服务');
		$this -> get_user();
		$this -> waptpl('com');
	}

    function map_action(){

		$companyM	=	$this->MODEL('company');
		$uid		=	intval($this -> uid);
		if($_POST){

			$data	=	array(
				'xvalue'	=>	$_POST['xvalue'],
				'yvalue'	=>	$_POST['yvalue']
			);
 			$return	=	$companyM->setMap($uid,$data);
			$this->yunset('layer',$return);

		}

		$row		=	$companyM->getInfo($uid,'',array('field'=>'`x`,`y`,`address`,`provinceid`,`cityid`,`three_cityid`'));
		$backurl	=	Url('wap',array('c'=>'set'),'member');

		$this->get_user();
		$this->yunset("row",$row);

		$this->yunset('backurl',$backurl);
		$this->yunset($this->MODEL('cache')->GetCache(array('city')));
		$this->yunset('header_title',"设置地图");
        $this->waptpl('map');
    }

	function reportlist_action(){

        if($_POST['submit']){

            if($_POST['reason'] == ''){

                $data['msg']        =   '请选择举报原因！';
                $this->yunset('layer', $data);
            }else{

				$uid				=	intval($this -> uid);

				$rdata              =   array();
                $rdata['c_uid']     =   (int)$_GET['uid'];
                $rdata['inputtime'] =   time();
                $rdata['p_uid']     =   $uid;
                $rdata['did']       =   $this->userid;
                $rdata['usertype']  =   (int)$this->usertype;
                $rdata['eid']       =   (int)$_GET['eid'];
                $rdata['r_name']    =   $_GET['r_name'];
                $rdata['username']  =   $this->username;
                $rdata['r_reason']  =   @implode(',',$_POST['reason']);

                $logM               =   $this -> MODEL('log');
                $reportM            =   $this -> MODEL('report');
                $nid                =   $reportM -> ReportResume($rdata);
                if($nid){

                    $logM -> addMemberLog($this->uid, $this->usertype,"举报简历",23,1);
                    $this->ACT_msg_wap('index.php?c=down', '举报成功！', 1, 4);
                }else{

                    $this->ACT_msg_wap('index.php?c=down', '举报失败！', 2, 4);
                }
            }
        }

		$backurl  =   Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"举报简历");
		$this->get_user();
		$this->waptpl('reportlist');
	}

	function info_action(){



		$companyM =   $this->MODEL('company');

		if($_POST['submit']){

			$company    =   $this -> comInfo['info'];

			if($company){
				$rstaus     =   $company['r_status'];
			}else{
				$rstaus		=	$this->config['com_status'];
			}

		    $mData     =  array(
		        'moblie'        =>  $_POST['linktel'],
		        'email'         =>  $_POST['linkmail']
		    );

			$setData	=	array(
				'name' 			=> 	$_POST['name'],
				'shortname' 	=> 	$_POST['shortname'],
				'hy' 			=> 	$_POST['hy'],
				'pr' 			=> 	$_POST['pr'],
				'mun' 			=> 	$_POST['mun'],
				'provinceid' 	=>	$_POST['provinceid'],
				'cityid' 		=> 	$_POST['cityid'],
				'three_cityid' 	=> 	$_POST['three_cityid'],
			    'address' 		=>	$_POST['address'],
			    'x' 		    =>	$_POST['x'],
			    'y' 		    =>	$_POST['y'],
			    'linkman'		=> 	$_POST['linkman'],
				'linktel'		=>	$_POST['linktel'],
				'linkphone' 	=> 	$_POST['linkphone'],
				'linkmail' 		=> 	$_POST['linkmail'],
				'sdate' 		=> 	$_POST['sdate'],
				'moneytype' 	=> 	$_POST['moneytype'],
				'money' 		=>	$_POST['money'],
				'linkjob' 		=> 	$_POST['linkjob'],
				'linkqq' 		=> 	$_POST['linkqq'],
				'website' 		=> 	$_POST['website'],
				'busstops' 		=> 	$_POST['busstops'],
				'infostatus' 	=> 	$_POST['infostatus'],
				'welfare' 		=> 	$_POST['welfare'],
				'preview' 		=> 	$_POST['preview'],
				'r_status' 		=> 	$rstaus,
			    'lastupdate'	=>  time(),
				'rating'		=>	$company['rating'],
			    'content'		=> 	str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content'])
			);
			if(!$this -> comInfo['info']['uid']){
				$userinfoM    =   $this->MODEL("userinfo");
				$userinfoM -> activUser($this->uid,2);
			}
			$return =  $companyM -> setCompany(array('uid'=>$this->uid),array('mData'=>$mData,'comData'=>$setData,'utype'=>'user','wap'=>1));

			if($return['errcode'] == 9){
				$data	=	array('msg'=>$return['msg'],'url'=>'index.php');
			}else{
				$data	=	array('msg'=>$return['msg']);
			}

			echo json_encode($data);die;

		}else{

			$row		=	$companyM -> getInfo($this->uid, array('info' => '1', 'edit' => '1','logo' => '1','utype'=>'user'));
			if(!$row['uid']){

				//获取注册信息
				$userinfoM			=   $this->MODEL("userinfo");
				$row['info']		=	$userinfoM -> getInfo(array('uid'=>$this -> uid),array('field'=>'`moblie` as linktel,`email` as linkmail,`moblie_status`,`email_status`'));

			}
			$this->yunset('row', $row['info']);
		}
		$this -> yunset($this -> MODEL('cache') -> GetCache(array('city','com','hy')));
		$this -> yunset('header_title','基本信息');
		$this -> waptpl('info');
	}

	function jobCheck_action(){

	    $jobM   =   $this->MODEL('job');
	    $statisM=   $this->MODEL('statis');

	    $uid    =   $this->uid;

	    $statis =   $statisM -> getInfo($uid, array('usertype' => 2, 'field' => '`integral`'));

	    $result =   $jobM -> getAddJobNeedInfo($uid, 1, $this->spid);

	    $comSingle	=	@explode(',', $this->config['com_single_can']);

	    $single	=	in_array('issuejob', $comSingle)? '1' : '2';

	    $return =   array(

	        'msgList'   =>  $result['wap'],
	        'integral'  =>  (int)$statis['integral'],
	        'spid'      =>  $this->spid,
	        'singlecan'	=>	$single
	    );


	    echo json_encode($return);

	    die();

	}

	function jobadd_action(){

		$this -> get_user();
		$statics	=	$this->company_satic();

		$jobM		=	$this -> MODEL('job');
		$statisM	=	$this -> MODEL('statis');
		$companyM	=	$this -> MODEL('company');
		$cacheArr   =   $this->MODEL('cache')->GetCache('job');
		$uid		=   $this->uid;

		if(!$_GET['id'] && !$_POST['submit']){

			if($statics['addjobnum']==0){

			    $this->ACT_msg_wap(Url('wap', array('c' => 'rating'), 'member'), '您的会员已到期！', '2', 5);
			}

			if($statics['addjobnum']==2){

				if($this->config['integral_job']!='0'){

				    $this->ACT_msg_wap(Url('wap', array('c' => 'rating'), 'member'), '您的套餐已用完！', '2', 5);
				}else{
				    if ($this->spid) {
						$statisM -> upInfo(array('job_num'=>'1'),array('uid'=>$this->spid,'usertype'=>'2'));
					}else{
						$statisM -> upInfo(array('job_num'=>'1'),array('uid'=>$this->uid,'usertype'=>'2'));
					}

				}
			}
		}

		$row	=	$companyM -> getInfo($uid);

		if(!$row['name'] || ! $row['provinceid'] || (!$row['linktel'] && ! $row['linkphone'])){

		    $this->ACT_msg_wap(Url('wap', array('c' => 'info'), 'member'), '请先完善基本资料！', '2', 5);
		}

		$msg  =   array();

		$isallow_addjob = '1';
		
		$joblock	=	$this->config['joblock'];
		
		$this->yunset('joblock', $joblock);
		
		if($this->config['com_enforce_emailcert']=="1"){
		    if($row['email_status']!="1"){
				$isallow_addjob="0";
				$msg[]="邮箱认证";
			}
		}
		if($this->config['com_enforce_mobilecert']=="1"){
		    if($row['moblie_status']!="1"){
		    	$isallow_addjob="0";
				$msg[]="手机认证";
			}
		}
		if($this->config['com_enforce_licensecert']=="1"){
		    if($row['yyzz_status']!="1"){
		    	$isallow_addjob="0";
				$msg[]="营业执照认证";
			}
		}
		if($this->config['com_enforce_setposition']=="1"){
		    if(empty($row['x'])||empty($row['y'])){
		        $isallow_addjob="0";
				$msg[]="企业地图设置";
 			}
		}

		if($isallow_addjob=="0"){

		    if ($this->spid) {
				$this->ACT_msg_wap('index.php', "请先完成".implode(",",$msg)."！", 2, 5);
		    }else {
				$this->ACT_msg_wap('index.php?c=set', "请先完成".implode(",",$msg)."！", 2, 5);
		    }


		}else if($_GET['id']){

			$job =   $jobM->getInfo(array('id' => intval($_GET['id']),'uid'=>$this -> uid),array('add'=>'yes'));

            if ($job['id']) {

                $job['langid']  =   pylode(',', $job['lang']);
                $job['days']    =   ceil(($job['edate'] - $job['sdate']) / 86400);

                $job_link       =   $jobM->getComJobLinkInfo(array('jobid' => (int) $_GET['id'],'uid' => $uid));
                $this->yunset('job_link', $job_link);

                if ($job['description']) {
                    $job['description_t']   =   strip_tags($job['description']);
                }
                if(empty($cacheArr['job_type'])){
                	$job['jobArr'][] = $job['job1'];
                }else{
                	if($job['job_post']){
	                	$job['jobArr'][] = $job['job_post'];
	                }else{
	                	$job['jobArr'][] = $job['job1_son'];
	                }
                }

                
		        if($job['sex_req']){

		            $job['sex_req_arr'] = @explode(',',$job['sex_req']);
		        }

                $this->yunset('row', $job);
            } else {
				$this->ACT_msg_wap('index.php?c=job', "非法操作！", 2,5);
            }
		}


		if($_POST['submit']){

			if ($_POST['job_classid']) {



                if(empty($cacheArr['job_type'])){
                    $_POST['job1']      =   $_POST['job_classid'];
                    $_POST['job1_son']  =   '';
                    $_POST['job_post']  =   '';
                }else{
                	$categoryM  =   $this->MODEL('category');

	                $row1       =   $categoryM->getJobClass(array('id' => intval($_POST['job_classid'])), '`keyid`');
	                $row2       =   $categoryM->getJobClass(array('id' => $row1['keyid']), '`keyid`');

                    if ($row2['keyid'] == '0') {

                        $_POST['job1_son']  =   $_POST['job_classid'];
                        $_POST['job1']      =   $row1['keyid'];
                        $_POST['job_post']  =   '';
                    } else {

                        $_POST['job1_son']  =   $row1['keyid'];
                        $_POST['job1']      =   $row2['keyid'];
                        $_POST['job_post']  =   $_POST['job_classid'];
                    }
                }
            }

            unset($_POST['job_classid']);

			$cinfo				=	$this->comInfo;

			$rstatus			=	$cinfo['r_status'];

			$description	    =	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'background-color:','background-color:','white-space:'),$_POST['description']);

			$post  =  array(

				'job1'          =>  intval($_POST['job1']),
                'job1_son'      =>  intval($_POST['job1_son']),
                'job_post'      =>  intval($_POST['job_post']),

				'r_status'     	=> 	$rstatus,
				'provinceid'    =>  intval($_POST['provinceid']),
                'cityid'        =>  intval($_POST['cityid']),
				'three_cityid'  =>  intval($_POST['three_cityid']),

				'minsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
				'maxsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),

				'description'	=>	$description,

				'is_link'		=>	$_POST['islink'],
			    'is_email'		=>	$_POST['isemail'],
			    'welfare'		=>	$_POST['welfare'],

				'hy'            =>  intval($_POST['hy']),
				'number'        =>  intval($_POST['number']),
                'exp'           =>  intval($_POST['exp']),
                'report'        =>  intval($_POST['report']),
                'age'           =>  intval($_POST['age']),
                'sex'           =>  intval($_POST['sex']),
                'edu'           =>  intval($_POST['edu']),
                'is_graduate'   =>  intval($_POST['is_graduate']),
                'marriage'      =>  intval($_POST['marriage']),
				'lang'          =>  trim(pylode(',', $_POST['lang'])),
				'lastupdate'	=>	time(),
				'source'		=>	2,
				'zuid'    		=>  $this->spid,

				'exp_req'       =>  trim($_POST['exp_req']),
				'edu_req'       =>  trim($_POST['edu_req']),
                'sex_req'       =>  trim(pylode(',', $_POST['sex_req'])),
            );

			if($this->config['joblock']!=1 || (empty($_POST['id']) && $this->config['joblock']==1)){

				$post['name']	=	$_POST['name'];

			}

			$data=array(
				'post'			=>	$post,
				'id'			=>	intval($_POST['id']),
			    'uid'			=>	$this->uid,
			    'spid'			=>	$this->spid,
			    'usertype'		=>	$this->usertype,
				'did'			=>	$this->userdid,

				'link_man'     	=>  intval($_POST['islink']) == 2 ? $_POST['link_man'] : '',
				'link_moblie'	=>  intval($_POST['islink']) == 2 ? $_POST['link_moblie'] : '',
				'email'     	=>  intval($_POST['islink']) == 2 ? $_POST['email']: '',
				'link_address' 	=>  intval($_POST['islink']) == 2 ? $_POST['link_address'] : '',
				'x' 			=>  intval($_POST['islink']) == 2 ? $_POST['x'] : '',
				'y' 			=>  intval($_POST['islink']) == 2 ? $_POST['y'] : '',

				'tblink'		=>	$_POST['tblink']

			);

			$this->cookie->SetCookie('delay', '', time() - 60);

			$jobM	=	$this -> MODEL('job');

			$return	=	$jobM->addJobInfo($data);

			if(intval($_POST['r_status'])==1){

				if($this->config['com_free_status']==1 && $row['yyzz_status']==1){
					$return['url_tg']='index.php?c=job_tg&id='.$return['id'];
				}else{
					if($this->config['com_job_status']=="0"){

						$return['url']='index.php?c=job';
					}else if($this->config['com_job_status']=="1" ){

						$return['url_tg']='index.php?c=job_tg&id='.$return['id'];
					}

				}

			}else{
				$return['url']='index.php?c=job';
			}

			echo json_encode($return);die;
		}

		$cacheList    =   $this->MODEL('cache')->GetCache(array('city','com','hy','job','user'));
		$this -> yunset($cacheList);
		$this -> yunset('header_title',"发布职位");
		$this -> waptpl('jobadd');
	}

	function job_tg_action()
    {
        $this -> company_satic();

        if ($_GET['id']) {
            $id     =   (int) $_GET['id'];
            $job    =   $this->MODEL('job')->getInfo(array('id' => $id, 'state' => '1', 'status' => '0'));

            if ($job && is_array($job)) {

                $this->yunset('job', $job);
            } else {

                $this->ACT_msg_wap('index.php?c=job', '该职位未满足推广条件', 2, 5);
            }
        }

        $backurl = Url('wap', array('c' => 'job'), 'member');
        $this->yunset('backurl', $backurl);
        $this->yunset('header_title', "职位推广");
        $this->waptpl('job_tg');
    }

	function job_action()
    {

        $jobM   =   $this->MODEL('job');

        $jobwhere['uid']		=   $this->uid;
		$jobwhere['orderby']	=	array('lastupdate,desc','id,desc');
        $rows   =   $jobM->getList($jobwhere, array('sqjobnum' => 'yes'));

        $zp = $sh = $xj = 0;

        if (is_array($rows['list'])) {

            foreach ($rows['list'] as $value) {
                if ($value['state'] == 1 && $value['status'] != 1) {
                    $zp += 1;
                }
                if ($value['state'] != '1') {
                    $sh += 1;
                }
                if ($value['status'] == '1') {
                    $xj += 1;
                }
            }
        }

        $this -> yunset(array('zp' => $zp, 'sh' => $sh, 'xj' => $xj));
        $this -> yunset('rows', $rows['list']);
        $this -> company_satic();
        $backurl = Url('wap', array('c' => 'jobcolumn'), 'member');
        $this -> yunset('backurl', $backurl);
        $this -> yunset('header_title', '职位管理');
        $this -> waptpl('job');
    }

	function refreshjob_action(){
		if($_GET['up']){
			$jobM	=	$this -> MODEL('job');
			$logM	=	$this -> MODEL('log');
 			$nid	=	$jobM -> upInfo(array('lastupdate'=>time()), array('id' => intval($_GET['up']),'uid'=>$this->uid));

			if($nid){

				$job	=	$jobM -> getInfo(array('id' => $_GET['up']),array('field'=>"name"));
				$logM -> addMemberLog($this->uid, $this->usertype,"刷新职位《".$job['name']."》",1,4);
				$this->layer_msg('刷新职位成功！',9,0,$_SERVER['HTTP_REFERER']);
			}else{

				$this->layer_msg('刷新失败！',8,0,$_SERVER['HTTP_REFERER']);
			}
		}
	}

	function jobset_action(){
		if($_GET['status']){

			if($_GET['status']==2){
				$_GET['status']=0;
			}

			$uid	=	$this -> uid;

			$this -> MODEL('job') -> upInfo(array('status'=>intval($_GET['status'])), array('id' => intval($_GET['id'])));

			$this -> MODEL('log') -> addMemberLog($uid,$this->usertype,"修改职位招聘状态",1,2);

			$this -> get_user();

			$this -> waplayer_msg("设置成功！");
		}
	}

	function jobdel_action(){
		if($_GET['id']){

			$jobM	=	$this -> Model('job');
			$logM	=	$this -> Model('log');
			$PackM	=	$this -> Model('pack');
			$comM	=	$this -> Model('company');

			$uid	=	$this -> uid;

			$nid = $jobM -> delJob((int)$_GET['id'],array('uid'=>$this->uid));
			
			if($nid){
			
				$newest = $jobM -> getInfo(array('uid'=>$uid,'orderby'=>'lastupdate'),array('field'=>"`lastupdate`"));
				$comM -> upInfo($uid,'',array('jobtime'=>$newest['lastupdate']));
				$logM -> addMemberLog($uid,$this->usertype,"删除职位记录（ID:".(int)$_GET['id']."）",1,3);
				$this->waplayer_msg("删除成功！");
			}else{
			
				$this->waplayer_msg("删除失败！");
			}
		}
	}

	/**
	 * @desc 兼职报名
	 */
	function partapply_action(){

        $partM              =   $this->MODEL('part');
        $logM               =   $this->Model('log');

		$uid				=	$this -> uid;

        $where['comid']     =   $uid;

        if ($_GET['jobid']) {

            $jobid          =   intval($_GET['jobid']);

            $where['jobid'] =   $jobid;

            $urlarr['jobid']=   $jobid;
        }

		$urlarr['c']        =   'partapply';
        $urlarr['page']     =   '{{page}}';

		$pageurl            =   Url('wap', $urlarr, 'member');

        $pageM              =   $this -> MODEL('page');
        $pages              =   $pageM -> pageList('part_apply', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   array('ctime,desc');
            $where['limit']     =   $pages['limit'];

            $rows               =   $partM -> getPartSqList($where);

            $this -> yunset('rows', $rows);
        }
        $backurl                =   Url('wap', array('c' => 'part'), 'member');

        $this -> yunset('backurl', $backurl);

        $this -> yunset('header_title', '兼职报名');


        $this -> get_user();

        $id     =   intval($_GET['del']);

        if ($id) {

            $delRes =   $partM -> delPartApply($id, array('uid' => $uid, 'usertype' => $this->usertype));

            $this -> layer_msg($delRes['msg'], $delRes['errcode'], $delRes['layertype'], $_SERVER['HTTP_REFERER']);

            if ($delRes['errcode'] == 9) {

                $logM -> addMemberLog($uid, $this->usertype, '删除兼职报名', 6, 3);
            }

            $data['msg']    =   $delRes['msg'];

            $data['url']    =   'index.php?c=partapply';

            $this -> yunset('layer',  $data);
        }

        if (intval($_GET['id']) && intval($_GET['status'])) {

            $nid=$partM -> upPartSq(array("comid"=>$this->uid,"id"=>intval($_GET['id'])),array('status'=>intval($_GET['status'])));

			if ($nid) {

                $logM -> addMemberLog($uid, $this->usertype, "更改兼职报名状态（ID:" . (int) $_GET['id'] . "）", 6, 2);

                $this->waplayer_msg('操作成功!');
            } else {

                $this->waplayer_msg('操作失败！');
            }
        }


        $this->waptpl('partapply');
    }

	function hr_action(){

		$JobM			=   $this -> MODEL('job');

		$uid			=	$this -> uid;
		$where['com_id']=	$uid;
		$where['type']	=	array('<>',3);
		 //分页链接
		$urlarr['c']	=	$_GET['c'];
        $urlarr['page']	=	'{{page}}';

        $pageurl		=	Url('wap',$urlarr,'member');

        //提取分页
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('userid_job',$where,$pageurl,$_GET['page']);

        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){

			$where['orderby']		=	'datetime';

            $where['limit']			=	$pages['limit'];

            $rows	=	$JobM -> getSqJobList($where,array('uid'=>$uid,'usertype'=>$this->usertype,'is_link'=>'yes'));
			$this->yunset('rows',$rows);
        }

		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->yunset('header_title',"应聘简历");
		$this->get_user();
		$this->waptpl('hr');
	}

	function hrset_action(){
		$id		=	(int)$_GET['id'];
		$browse	=	(int)$_GET['is_browse'];
		$JobM   =   $this -> MODEL('job');

		$uid	=	$this -> uid;
		$data	=	array(
			'uid'		=>	$uid,
			'usertype'	=>	$this->usertype,
			'username'	=>	$this->username,
			'browse'	=>	$browse,
			'port'		=>	'2'
		);
		$nid	=	$JobM -> BrowseSqJob($id,$data);
		$nid?$this->waplayer_msg("操作成功！"):$this->waplayer_msg("操作失败！");
	}

	function delhr_action(){
		$JobM   =   $this -> MODEL('job');
		$id		=   intval($_GET['id']);
		$arr    =   $JobM -> delSqJob($id,array('utype'=>'com','uid'=>$this->uid,'usertype'=>$this->usertype));
		$this ->  waplayer_msg($arr['msg']);
	}

	function password_action(){
		$backurl=Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"修改密码");
		$this->get_user();
		$this->waptpl('password');
	}

	function time_action(){

		$StatisM  =	  $this	-> MODEL('statis');
		$statis   =	  $StatisM -> getInfo($this->uid,array('usertype'=>'2'));//查询会员信息
		$this -> yunset('statis',$statis);

		$orderM		=	$this -> MODEL('companyorder');
		$banks		=	$orderM	-> getBankList(array('id'=>array('>', 0)));
		$this	->	yunset('banks',$banks);

		$whereData	=	array(
			'category'		=>	'1',
			'service_price'	=>	array('>','0'),
			'display'		=>	'1',
			'orderby'		=>	array('type,asc','sort,desc'),
		    'type'          =>  $this->config['com_vip_type'] == 2 ? 1 : 2
		);

		$ratingM	=	$this -> MODEL('rating');
		$rows		=	$ratingM ->	getList($whereData);

		if($rows&&is_array($rows)){
			foreach ($rows as $k=>$v){
				$rname=array();
				if($v['job_num']>0){
					$rname[]	=	'发布职位：'.$v['job_num'].'份';
				}
				if($v['breakjob_num']>0){
					$rname[]	=	'刷新职位：'.$v['breakjob_num'].'份';
				}
				if($v['resume']>0){
					$rname[]	=	'下载简历：'.$v['resume'].'份';
				}
				if($v['interview']>0){
					$rname[]	=	'邀请面试：'.$v['interview'].'份';
				}
				if($v['zph_num']>0){
					$rname[]	=	'招聘会：'.$v['zph_num'].'份';
				}
				
				
				if($v['top_num']>0){
					$rname[]	=	'赠送职位置顶：'.$v['top_num'].'天';
				}
				if($v['urgent_num']>0){
					$rname[]	=	'赠送紧急招聘：'.$v['urgent_num'].'天';
				}
				if($v['rec_num']>0){
					$rname[]	=	'赠送职位推荐：'.$v['rec_num'].'天';
				}

				$rows[$k]['rname']	=	@implode('；',$rname);
			}
		}

		$this	->	yunset('rows',$rows);
		$this	->	yunset('row',$rows[0]);
		$this	->	yunset('js_def',4);
		$this	->	yunset('header_title',"购买会员");

		$this	->	get_user();

		if($this->config['com_vip_type'] == 1 || $this->config['com_vip_type'] == 0){

		    $this -> waptpl('member_time');
		}else if($this->config['com_vip_type'] == 2){

		    $this -> waptpl('member_rating');
		}
	}

	function rating_action(){

	    header('Location:'.Url('wap', array('c' => 'server'), 'member'));

	}

	function added_action(){

	    header('Location:'.Url('wap', array('c' => 'server'), 'member'));
	}

	function pay_action(){

	    $orderM		=	$this	->	MODEL('companyorder');
	    $banks		=	$orderM	->	getBankList(array('id'=>array('>', 0)));
	    $paytype	=	array(
	        'alipay'	=>	$this->config['alipay']=='1' && $this->config['alipaytype']=='1'	?	'1'	:	'',
	        'bank'		=>	$this->config['bank']=='1' && $banks	?	'1'	:	''
	    );

	    

	    if($paytype){
	        $this	->	yunset("paytype",$paytype);
	        $this	->	yunset("js_def",4);
	    }else{
	        $data['msg']	=	"暂未开通手机支付，请移步至电脑端充值！";
	        $data['url']	=	$_SERVER['HTTP_REFERER'];
	        $this	->	yunset("layer",$data);
	    }
	    $nopayorder	=	$orderM	->	getCompanyOrderNum(array('uid'=>$this->uid,'usertype' => $this->usertype,'order_state'=>'1'));
	    $this		->	yunset('nopayorder',$nopayorder);
	    $this		->	yunset("banks",$banks);
	    $this		->	yunset($this->MODEL('cache')->GetCache(array('integralclass')));
	    $this		->	yunset('header_title',"充值".$this->config['integral_pricename']);
	    $this		->	get_user();
	    $this		->	waptpl('pay');
	}

	function payment_action(){
		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']	=	'1';
		}
		$orderM		=	$this -> MODEL('companyorder');
		$banks		=	$orderM	-> getBankList(array('id'=>array('>', 0)));
		if($this->config['bank']=='1' &&  $banks){
			$paytype['bank']	=	'1';
		}

		if($paytype){
			if($_GET['id']){//订单
				$orderM	=	$this	->	MODEL('companyorder');
				$order	=	$orderM	->	getInfo(array('uid'=>$this->uid,'id'=>(int)$_GET['id']),array('bank'=>1));
				if(empty($order)){
					$this->ACT_msg_wap($_SERVER['HTTP_REFERER'],"订单不存在！",2,5);
				}elseif($order['order_state']!='1'){
					header("Location:index.php?c=paylog");
				}else{
					$this	->	yunset("order",$order);
				}
			}
 			$this	->	yunset("paytype",$paytype);
 			$this	->	yunset("js_def",4);
		}else{
			$data['msg']	=	"暂未开通手机支付，请移步至电脑端充值！";
			$data['url']	=	$_SERVER['HTTP_REFERER'];
			$this	->	yunset("layer",$data);
		}
		$this	->	yunset("banks",$banks);
		$this	->	yunset('header_title',"订单确认");
		$this	->	get_user();
		$this	->	waptpl('payment');
	}

	//会员统计信息调用
	function company_satic(){

		$statisM  =  $this->MODEL('statis');
		// 会员套餐过期检测，并处理

		$suid     =   $this->spid ? $this->spid : $this->uid;
		$statis   =  $statisM -> vipOver($suid, 2);

		$this->yunset('addjobnum', $statis['addjobnum']);

		if($statis['integral'] == ''){
		    $statis['integral']   =   0;
		}
		$this->yunset('statis',$statis);

		return $statis;
	}

	function getOrder_action(){

	    $_POST				=	$this -> post_trim($_POST);

	    if (empty($_POST)) {
	        echo json_encode(array('error' => 1, 'msg' => '参数错误，请重试！'));die();
	    }

	    $data				=	$_POST;
	    $data['uid']		=   $this -> uid;
	    $data['username']	=   $this -> username;
	    $data['usertype']	=   $this -> usertype;
	    $data['did']		=   $this -> userdid;


	    $compayM            =   $this->MODEL('compay');
	    $return				=	$compayM->orderBuy($data);

	    if($return['error'] == 0){
	        $dingdan	=	$return['orderid'];
	        $price		=	$return['order_price'];
	        $id			=	$return['id'];

	        //多种支付方式并存 进行选择
	        if($_POST['paytype']=='alipay'){

	            $url = $this->config['sy_weburl'].'/api/wapalipay/alipayto.php?dingdan='.$dingdan.'&dingdanname='.$dingdan.'&alimoney='.$price;
	        }
	        echo json_encode(array(
	            'error' => 0,
	            'url'   => $url,
	            'msg'   =>  '下单成功，请付款！'
	        ));

	    }else{
	        echo json_encode($return);
	    }
	}

	function dkzf_action(){
		$data				=	$_POST;
		$data['uid']		=	$this	->	uid;
		$data['username']	=	$this	->	username;
		$data['usertype']	=	$this	->	usertype;

		$jfdkM				=	$this	->	MODEL('jfdk');
		$return				=	$jfdkM	->	dkBuy($data);
		echo json_encode($return);
	}

	/**
	 * 充值、购买会员、购买增值包生成订单
	 */
	function dingdan_action()
	{

		$rdata['price']			=  $_POST['price'];
		$rdata['comvip']		=  $_POST['comvip'];
		$rdata['comservice']	=  $_POST['comservice'];
		$rdata['dkjf']			=  $_POST['dkjf'];
		$rdata['price_int']		=  $_POST['price_int'];
		$rdata['integralid']	=  $_POST['integralid'];
		$rdata['uid']			=  $this->uid;
		$rdata['usertype']		=  $this->usertype;
		$rdata['did']			=  $this->userdid;
		$rdata['paytype']	    =  $_POST['paytype'];
		$rdata['type']		    =  'wap';
		$rdata['port']		    =  '2';
		$orderM	 =  $this	->	MODEL('companyorder');
		$return	 =  $orderM	->	addComOrder($rdata);
		//支付宝支付，跳转到相应的链接
		if($return['errcode'] == 9 && !empty($return['url'])){

		    header('Location: '.$return['url']);exit();
		}else{
		    $this->yunset("layer",$return);
		}

		$backurl  =  Url('wap',array(),'member');
		$this -> yunset('backurl',$backurl);
		$this -> yunset('headertitle','订单');
		$this -> get_user();
		$this -> waptpl('pay');
	}
	/**
	 * 银行转账
	 */
	function paybank_action(){
		$id		=	intval($_GET['id']);
		$data	=	array(
			'wap'			=>	'1',
			'uid'			=>	$this -> uid,
		    'usertype'	    =>  $this -> usertype,
			'did'			=>	$this->userdid,
			'id'			=>	$id,
			'bank_name'		=>	$_POST['bank_name'],
			'bank_number'	=>	$_POST['bank_number'],
			'bank_price'	=>	$_POST['bank_price'],
			'bank_time'		=>	$_POST['bank_time'],
			'base'			=>	$_POST['preview'],
			'price'			=>	$_POST['price'],
		);
		//生成新订单，需要以下参数
		if(empty($id)){
			$data['price']		=	$_POST['price'];
			$data['comvip']		=	$_POST['comvip'];
			$data['comservice']	=	$_POST['comservice'];
			$data['dkjf']		=	$_POST['dkjf'];
			$data['price_int']	=	$_POST['price_int'];
			$data['integralid']	=	$_POST['integralid'];
		}
		$orderM		=	$this	->	MODEL('companyorder');
		$return		=	$orderM	->	payComOrderByBank($data);

		$this		->	yunset("layer",$return);
		$backurl	=	Url('wap',array(),'member');
		$this		->	yunset('backurl',$backurl);
		$this		->	get_user();
		$this		->	waptpl('payment');
	}
	function look_job_action(){
		$JobM					=	$this -> MODEL("job");
		$uid					=	$this -> uid;
		$where['com_id']		=	$uid;
		$where['com_status']	=	0;
	    $urlarr['c']			=	$_GET['c'];
		$urlarr['page']			=	'{{page}}';
	    $pageurl				=	Url('wap',$urlarr,'member');

	    $pageM					=	$this   ->  MODEL('page');
	    $pages					=	$pageM  ->  pageList('look_job',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){

	        $where['orderby']	=	'datetime';
	        $where['limit']		=	$pages['limit'];
	        $List				=  $JobM  ->  getLookJobList($where,array('uid'=>$uid,'usertype'=>$this->usertype));

			$this->yunset("rows",$List);
	    }

		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->yunset('header_title',"谁看过我");
		$this->get_user();
		$this->waptpl('look_job');
	}

	function lookresumedel_action(){
		if($_GET['id']){
			$uid			=	$this -> uid;
			$id   			=	intval($_GET['id']);
			$lookresumeM    =	$this->MODEL('lookresume');
			$return         =	$lookresumeM -> delInfo(array('id'=>$id,'uid'=>$uid,'usertype'=>2));
			$this -> waplayer_msg($return['msg']);
		}
	}
	function lookjobdel_action(){
		if($_GET['id']){
			$uid	=	$this -> uid;
			$id		=	intval($_GET['id']);
			$jobM	=	$this->MODEL('job');
			$return	=	$jobM -> delLookJob($id,array('uid'=>$uid,'usertype'=>$this->usertype));
			$this -> waplayer_msg($return['msg']);
		}
	}

	function look_resume_action(){

		$uid					=	$this -> uid;

		$lookresumeM  			=  $this -> MODEL('lookresume');
		$where['com_id']		=  $uid;
		$where['com_status']	=  0;
		$where['usertype']		=   2;
		$urlarr['c']			=	$_GET['c'];
		$urlarr['page']			=	'{{page}}';
	    $pageurl				=	Url('wap',$urlarr,'member');

	    $pageM					=	$this   ->  MODEL('page');
	    $pages					=	$pageM  ->  pageList('look_resume',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){
	        $where['orderby']	=	'datetime';
	        $where['limit']		=	$pages['limit'];

	        $List				=  $lookresumeM  ->  getList($where,array('uid' => $uid, 'usertype' => $this->usertype));

			$this->yunset("rows",$List['list']);
	    }

		$backurl=Url('wap',array('c' => 'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->yunset('header_title',"浏览简历");
		$this->get_user();
		$this->waptpl('look_resume');
	}

	function talentpooldel_action(){
		if($_GET['id']){
			$uid		=	$this -> uid;
			$id   		=	intval($_GET['id']);
			$ResumeM    =	$this->MODEL('resume');
			$return   	=	$ResumeM -> delTalentPool($id,array('uid'=>$uid,'usertype'=>$this->usertype));
			$this -> waplayer_msg($return['msg']);
		}
	}
	function talent_pool_action(){

		$uid			=	$this -> uid;
		$ResumeM		=	$this -> MODEL('resume');
		$where['cuid']	=	$uid;

		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('wap',$urlarr,'member');

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('talent_pool',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];
	        $List		=	$ResumeM -> getTalentList($where,array('uid'=>$uid, 'isdown' => 1));
			$this->yunset("rows",$List);
	    }

		if($_GET['type']){
			$backurl=Url('wap',array(),'member');
		}else{
			$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		}
		$this->yunset('backurl',$backurl);

		$this->yunset('header_title',"收藏人才");
		$this->get_user();
		$this->waptpl('talent_pool');
	}

	function invite_action(){
		$JobM    		=   $this -> MODEL('job');
		$uid            =   $this -> uid;
		$where['fid']	=  	$uid;

		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('wap',$urlarr,'member');

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('userid_msg',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];


	        $list    =   $JobM -> getYqmsList($where,array('uid'=>$this->uid,'usertype'=>$this->usertype));
	    }
		$this -> yunset("rows",$list);
		

	    $backurl=Url('wap',array('c'=>'resumecolumn'),'member');
	    $this->yunset('backurl',$backurl);
		

		$this->yunset('header_title',"面试邀请");
		$this->get_user();
		$this->waptpl('invite');
	}

	function invite_del_action(){
		if($_GET['id']){
			$id			=  intval($_GET['id']);
			$JobM		=  $this->MODEL('job');
			$return		=  $JobM -> delYqms($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
			$this -> waplayer_msg($return['msg']);
		}
	}

	/**
	 * @desc 兼职列表
	 */
	function part_action()
    {
        $partM  =   $this->MODEL('part');

        $uid    =   $this -> uid;

        $where['uid']   =   $uid;

        $urlarr['c']    =   'part';

        $urlarr['page'] =   '{{page}}';

        $pageurl        =   Url('wap', $urlarr, 'member');

        $pageM          =   $this->MODEL('page');



        $pages          =   $pageM -> pageList('partjob', $where, $pageurl, $_GET['page']);

        if ($pages['total'] > 0) {

            $where['orderby']   =   'lastupdate,desc';

            $where['limit'] =   $pages['limit'];

            $rows           =   $partM -> getList($where);

            $this -> yunset('rows', $rows);
        }

        $this -> company_satic();

        $backurl = Url('wap', array( 'c' => 'jobcolumn' ), 'member');

        $this -> yunset('backurl', $backurl);

        $this -> yunset('header_title', '兼职管理');

        $this -> get_user();

        $this -> waptpl('part');
    }

    // 发布兼职
	function partadd_action()
    {
        $partM      =   $this->MODEL('part');
        $statisM    =   $this->MODEL('statis');

        $statics    =   $this->company_satic();

        $cachearr   =   $this->MODEL('cache')->GetCache(array('city', 'part'));
        $this->yunset($cachearr);

        $id         =   intval($_GET['id']);

        if ($id) {

            $pwhere =   array(
                'uid'   =>  $this->uid,
                'id'    =>  $id
            );

            $row    =   $partM->getInfo($pwhere);
            $row    =   $row['info'];

            $row['content_t']   =   strip_tags($row['content']);
            $row['workcishu']   =   count($row['worktime_n']);
            $this->yunset('row', $row);
        } else {

            if ($statics['addjobnum'] == 0) {

                if ($this->spid) {

                    $this->ACT_msg_wap(Url('wap', array(), 'member'), '当前账号会员已到期，请联系主账号进行升级！', '2', 5);
                }else{

                    $this->ACT_msg_wap(Url('wap', array('c' => 'rating'), 'member'), '您的会员已到期！', '2', 5);
                }
            }

            if ($statics['addjobnum'] == 2) {

                if ($statics['job_num'] == 0) {

                    if ($this->spid) {
                        $this->ACT_msg_wap(Url('wap', array(), 'member'), '当前套餐已用完，请联系主账号进行分配！', '2', 5);
                    }else{
                        $this->ACT_msg_wap(Url('wap', array('c' =>'rating'), 'member'), '您的套餐已用完！', '2', 5);
                    }

                } else {
                    $statisM->upInfo(array('job_num' => 1), array('uid' => $this->uid, 'usertype' => $this->usertype));
                }
            }
        }
        if ($_POST['submit']) {

            $this->cookie->SetCookie("delay", "", time() - 60);

            if ($_POST['timetype'] != '1') {
                $_POST['edate'] = "";
            } else {
                $_POST['edate'] = strtotime($_POST['edate']);
            }

            $cinfo = $this->comInfo;

            $rstatus = $cinfo['r_status'];

            $_POST['r_status'] = $rstatus;

            $data = $_POST;

            $data['uid'] = $this->uid;
            $data['spid'] = $this->spid;
            $data['usertype'] = $this->usertype;
            $data['utype'] = 'user';

            $return = $partM->upPartInfo($data);

            $return['url'] = 'index.php?c=part';

            echo json_encode($return);
            die();
        }

        $this->get_user();

        $this->yunset("layer", $data);

        $this->yunset('header_title', "发布兼职");
        $this->waptpl('partadd');
    }

	// 兼职删除
	function partdel_action()
    {
        $partM  =   $this->MODEL('part');
        $id     =   (int) $_GET['id'];

        if ($id) {

            $uid    =	$this->uid;

            $delRes =   $partM -> delPart($id, array('uid' => $uid, 'usertype' => $this->usertype));

            $this->layer_msg($delRes['msg'], $delRes['errcode'], $delRes['layertype'], $_SERVER['HTTP_REFERER']);
        }
    }

	function photo_action(){
		$companyM	=	$this->MODEL('company');

		if($_POST['submit']){
 			if(!$this -> comInfo['info']['uid']){
				$userinfoM    =   $this->MODEL("userinfo");
				$userinfoM -> activUser($this->uid,2);
			}
			$return   =  $companyM -> upLogo(array('uid'=>$this->uid),array('utype'=>'user','base'=>$_POST['uimage']));

			$this->layer_msg($return['msg'],$return['errcode']);
		}else{
			$company	=	$companyM->getInfo($this->uid,array('logo'=>'1','utype'=>'user'));

			if($_GET['t']){
				$backurl	=	Url('wap',array(),'member');
			}else if($_GET['type']){
				$backurl	=	Url('wap',array('c'=>'integral'),'member');
			}else{
				$backurl	=	Url('wap',array('c'=>'info'),'member');
			}

			$this->get_user();
			$this->yunset('backurl',$backurl);
			$this->yunset('header_title',"企业LOGO");
			$this->yunset("company",$company);
			$this->waptpl('photo');
		}
	}

	function comcert_action(){

	    $uid       =   intval($this -> uid);

	    $comM      =   $this -> MODEL('company');

	    $company   =   $comM -> getInfo($uid, array('field' => 'name,r_status'));
	    $this      ->  yunset('company' , $company);

	    $cert      =   $comM -> getCertInfo(array('uid' => $uid, 'type' => '3'));
	    $this      ->  yunset('cert' , $cert);

		if($_POST['submit']){

		    $usertype =   intval($this->usertype);
		    $data     =   array(
		        'msg' =>  ''
		    ) ;
		    if($company['r_status']==0){
		        $status	=	$company['r_status'];
		    }else{
		        $status	=	$this -> config['com_cert_status'] == '1' ? 0 : 1;
		    }
			$upData   =   array(
			    'status'     => $status,
			    'check'      => $photo,
			    'base'		 =>	$_POST['preview'],
			    'ctime'      => time()
			);
			if($data['msg'] == ''){

			    if (!empty($cert) && $cert['ctime']) {

			        $err   =   $comM -> upCertInfo(array('id'=>intval($cert['id']), 'uid' => $uid), $upData, array('yyzz' => '1', 'usertype' => $usertype,  'com_name'=>trim($_POST['name']),'type'=>'3'));

			    }else{
			        $postData    =   array(
			            'uid'        =>  $uid,
			            'type'       =>  '3',
			            'step'       =>  '1',
			            'did'        =>  $this ->config['did'],
			            'base'		 =>	$_POST['preview'],
			            'usertype'   =>  $usertype,
			            'com_name'   =>  trim($_POST['name'])
			        );
			        $postData    =   array_merge($postData, $upData);
			        $err         =   $comM -> addCertInfo($postData);
			    }
				$data['msg']    =   $err['msg'];
				$data['url']    =   'index.php?c=set';
			}else{
				$data['msg']    =$data['msg'];
				$data['url']    ='index.php?c=comcert';
			}
		}

		$this->yunset("layer",$data);
		if(empty($this -> spid)){
		$backurl  =   Url('wap',array('c'=>'set'),'member');
		$this     ->  yunset('backurl',$backurl);
		}

		$this     ->  yunset('header_title', '营业执照');
		$this     ->  get_user();
		$this     ->  waptpl('comcert');
	}

	function binding_action(){

	    $comM      =   $this -> MODEL('company');


	    //手机绑定
		if($_POST['moblie']){

		    $data     =   array(

		        'uid'         =>	$this->uid,

		        'usertype'    =>	$this->usertype,

		        'moblie'      =>	$_POST['moblie']


		    );

		    $errCode  =   $comM -> upCertInfo(array('uid'=>$this->uid, 'check2'=>$_POST['code']), array('status'=>'1'), $data);

		    echo $errCode; die;
		}

		//解除绑定
		if($_GET['type']){

		    $return   =	$comM -> delBd($this->uid, array('type'=>$_GET['type'], 'usertype'=>$this->usertype));

			$this->waplayer_msg($return['msg']);
		}

		$UserinfoM    =   $this -> MODEL('userinfo');
		$member       =   $UserinfoM -> getInfo(array('uid'=> $this->uid));
		$this -> yunset('member', $member);

		$company      =   $comM -> getInfo($this -> uid);
		$this -> yunset('company',$company);

		if($company['yyzz_status']!=1){

		    $cert     =   $comM -> getCertInfo(array('uid' => $this -> uid, 'type' => '3'), array('field' => '`id` , `status` , `statusbody`'));
			$this -> yunset('cert',$cert);

		}

		$backurl  =   Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"社交账号绑定");
		$this->get_user();
		$this->waptpl('binding');
	}

	

	/**
	 * @desc 手机绑定页面
	 */
	function bindingbox_action(){

	    $UserinfoM =   $this -> MODEL('userinfo');

	    $member    =   $UserinfoM -> getInfo(array('uid' => intval($this -> uid)));

	    $this      ->  yunset('member', $member);

        $backurl = Url('wap', array(
            'c' => 'set'
        ), 'member');

        $this->yunset('backurl', $backurl);

        $this->yunset('header_title', "账户绑定");

        $this->get_user();

        $this->waptpl('bindingbox');
    }

    function setname_action()
    {
        $UserinfoM = $this->MODEL('userinfo');
        if ($_POST['username']) {
            $data = array(
                'username' => trim($_POST['username']),
                'password' => trim($_POST['password']),
                'uid' => intval($this->uid),
                'usertype' => intval($this->usertype),
                'restname' => '1'
            );
            if (! empty($data['username'])) {
                $err = $UserinfoM->saveUserName($data);
                $this->cookie->unset_cookie();
                echo 1;
                die();
            }
        }
        $backurl = Url('wap', array(
            'c' => 'set'
        ), 'member');
        $this->yunset('backurl', $backurl);
        $this->yunset('header_title', "修改用户名");
        $this->get_user();
        $this->waptpl('setname');
    }

    function reward_list_action()
    {
        $redeemM = $this->MODEL('redeem');
        $where['uid'] = $this->uid;
        $where['usertype'] = $this->usertype;

        // 分页链接
        $urlarr['page'] = '{{page}}';
		$urlarr['c']	=	"reward_list";
		$pageurl		=	Url('wap',$urlarr,'member');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('change',$where,$pageurl,$_GET['page']);

		// //分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

			$where['orderby']		=	'id,desc';
		    $where['limit']			=	$pages['limit'];

		    $List					=	$redeemM	->	getChangeList($where,array('utype'=>'wap'));

			$this	->	yunset("rows",$List['list']);
			$this	->	yunset(array('dh'=>$List['dh'],'sh'=>$List['sh'],'wtg'=>$List['wtg']));
		}
		$StatisM			=	$this		->	MODEL("statis");
		$statis				=	$StatisM	->	getInfo($this->uid,array('usertype'=>'2','field'=>'`rating_name`,`integral`'));
		$statis['integral']	=	number_format($statis['integral']);
		$this		->	yunset("statis",$statis);
		$backurl	=	Url('wap',array('c'=>'integral'),'member');
		$this		->	yunset('backurl',$backurl);
		$this		->	yunset('header_title',"兑换记录");
		$this		->	get_user();
		$this		->	waptpl('reward_list');
	}

	function delreward_action(){
		$redeemM	=	$this		->	MODEL('redeem');
		$return		=	$redeemM	->	delChange(
			array(
				'uid'		=>	$this->uid,
				'id'		=>	(int)$_GET['id']
			),
			array(
				'member'	=>	'com',
				'uid'		=>	$this->uid,
				'usertype'	=>	$this->usertype,
				'id'		=>	(int)$_GET['id']
			)
		);
		$this		->	waplayer_msg($return['msg']);

	}
	function paylog_action(){
		include(CONFIG_PATH."db.data.php");
		$this				->	yunset("arr_data",$arr_data);
		$urlarr				=	array("c"=>"paylog","page"=>"{{page}}");
		$pageurl			=	Url('wap',$urlarr,'member');
		$comorderM			=	$this	->	MODEL('companyorder');
		$pageM				=	$this  	->	MODEL('page');
		$where['uid']		=	$this	->	uid;
		$where['usertype']	=	$this	->	usertype;
		$where['orderby']	=	array('order_time,desc','order_state,asc');

		$pages				=	$pageM -> pageList('company_order',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);

		if($pages['total'] > 0){

			$where['limit']		=	$pages['limit'];

			$rows				=	$comorderM -> getList($where);

			$this				->	yunset("rows",$rows);
		}

		$this	->	yunset('header_title',"订单管理");
		$this	->	get_user();
		$this	->	waptpl('paylog');
	}

	function delpaylog_action(){

		if($this->usertype!='2' || $this->uid==''){

		    $this	->	waplayer_msg('登录超时！');
		}else{

		    $comorderM	=	$this	->	MODEL('companyorder');

		    $oid		=	$comorderM	->	getList(array('uid'=>$this->uid,'id'=>(int)$_GET['id'],'order_state'=>'1'));

			if(empty($oid[0])){

			    $this	->	waplayer_msg('订单不存在！');
			}else{

			    $comorderM	->	del($oid[0]['id'],array('uid'=>$this -> uid));
				$this -> waplayer_msg('取消成功！');
			}
		}

	}

	function consume_action(){
		include(CONFIG_PATH."db.data.php");
		$this			->	yunset("arr_data",$arr_data);
		$urlarr			=	array("c"=>"consume","page"=>"{{page}}");
		$pageurl		=	Url('wap',$urlarr,'member');
		$comorderM		=	$this	->	MODEL('companyorder');
		$pageM			=	$this 	->	MODEL('page');

		$where['com_id']	=	$this->uid;
		$where['usertype']	=	$this->usertype;
    $where['orderby']   =  array('id,desc','pay_time,desc');

		$pages				=	$pageM -> pageList('company_pay',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);

		if($pages['total'] > 0){

			$where['limit']		=	$pages['limit'];

			$rows				=	$comorderM -> getPayList($where);

			$this	->	yunset("rows",$rows);
		}

		if ($_GET['type']==1){
			$this		->	yunset('backurl',Url('wap',array('c'=>'com'),'member'));
		}else{
			$backurl	=	Url('wap',array('c'=>'finance'),'member');
		}
		$this	->	yunset('backurl',$backurl);
		$this	->	yunset("rows",$rows);
		$this	->	yunset('header_title',"消费明细");
		$this	->	get_user();
		$this	->	waptpl('consume');
	}

	function down_action(){
		$downM  			=	$this -> MODEL('downresume');
		$uid            	=   $this -> uid;
		$where['comid']		=	$uid;
		$where['usertype']	=	$this->usertype;
	    $urlarr['c']		=	$_GET['c'];
		$urlarr['page']		=	'{{page}}';
	    $pageurl			=	Url('wap',$urlarr,'member');

	    $pageM				=	$this   ->  MODEL('page');
	    $pages				=	$pageM  ->  pageList('down_resume',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];

	        $List   =  $downM  ->  getList($where,array('uid'=>$uid));

			$this -> yunset("rows",$List['list']);
	    }

		$backurl=Url('wap',array('c'=>'resumecolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->yunset('header_title',"下载简历");
		$this->get_user();
		$this->waptpl('down');
	}

	function downdel_action(){
		if($_GET['id']){
			$id   =  intval($_GET['id']);
			$downM    =  $this->MODEL('downresume');
			$return   =  $downM -> delInfo($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));
			$this -> waplayer_msg($return['msg']);
		}
	}
	

	function ajax_refresh_job_action()
	{
		if(!isset($_POST['jobid'])){
			exit;
		}

		$uid      =   $this->uid;

		$jobid    =   $_POST['jobid'];

		$statis   =   $this -> company_satic();

		//检查是否达到每日最大操作次数
		if($statis['rating_type'] == '2'){

    		$companyM =   $this->MODEL('company');

    		$result   =   $companyM->comVipDayActionCheck('refreshjob',$uid);

    		if($result['status']!=1){

    		    echo json_encode($result);
    		    exit;
    		}else if($result['sxnum'] > 0){

    		    $sxnum =   $result['sxnum'];
    		    $num   =   count(@explode(',', $jobid));

    		    if($sxnum < $num){
    		        $data['msg']  =   '今日刷新套餐剩余'.$sxnum.'份，无法全部刷新！';
    		        $data['error']=   3;
    		        echo json_encode($data);
    		        exit;
    		    }
     		}
		}

 		$comtcM	              =	  $this->MODEL('comtc');
 		$_POST['spid']		  =	  $this->spid;
		$_POST['uid']         =	  $this->uid;
		$_POST['usertype']    =	  $this->usertype;

 		$return	= $comtcM->refresh_job($_POST);

 		if($return['status']==1){//职位刷新成功

			$data['msg']     =   $return['msg'];
			$data['error']   =   1;
		}else if($return['status']==2){//套餐不足，金额消费

			$data['msg']     =   $return['msg'];
			$data['error']   =   2;
		}else{//职位刷新失败

			if($return['url']){
				$data['url'] =  $return['url'];
			}
			$data['msg']     =   $return['msg'];
			$data['error']=3;
		}
		$data['serverOpen'] = $return['serverOpen'];
		echo json_encode($data);exit;
	}
    //刷新兼职
    function ajax_refresh_part_action(){
		if(!isset($_POST['partid'])){
			exit;
		}

		$partid = $_POST['partid'];

		$statis = $this->company_satic();

		//检查是否达到每日最大操作次数
		if($statis['rating_type'] == '2'){

		    $companyM =   $this->MODEL('company');

		    $result   =   $companyM->comVipDayActionCheck('refreshjob',$uid);

		    if($result['status']!=1){

		        echo json_encode($result);
		        exit;
		    }else if($result['sxnum'] > 0){

		        $sxnum =   $result['sxnum'];
		        $num   =   count(@explode(',', $partid));

		        if($sxnum < $num){
		            $data['msg']  =   '今日刷新套餐剩余'.$sxnum.'份，无法全部刷新！';
		            $data['error']=   3;
		            echo json_encode($data);
		            exit;
		        }
		    }
		}

 		$comtcM	=	$this->MODEL('comtc');

		$_POST['spid']		=	$this->spid;
		$_POST['uid']		=	$this->uid;
		$_POST['usertype']	=	$this->usertype;

 		$return	=	$comtcM->refresh_part($_POST);

 		if($return['status']==1){
			//职位刷新成功
			$data['msg']     =   $return['msg']." !";
			$data['error']   =   1;
			echo json_encode($data);
			exit;
		}else if($return['status']==2){
			//套餐不足，金额消费
			$data['msg']     =   $return['msg']." !";
			$data['error']   =   2;
			echo json_encode($data);
			exit;
		}else{
			//职位刷新失败
			if($return['url']){
				$data['url'] =  $return['url'];
			}
			$data['msg']     =   $return['msg'];
			$data['error']   =   3;
			echo json_encode($data);
 			exit;
		}
		echo json_encode($data);
		exit;
	}

    function special_action(){
        $urlarr		=	array("c"=>"special","page"=>"{{page}}");

		$pageurl	=	Url('wap',$urlarr,'member');

		$where['uid']	=	$this->uid;

		$pageM		=	$this  -> MODEL('page');

		$pages		=	$pageM -> pageList('special_com',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);

		if($pages['total'] > 0){
			if($_GET['order'])
			{
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']		=	$_GET['order'];
				$urlarr['t']			=	$_GET['t'];
			}else{
				$where['orderby']		=	'time';
			}
			$where['limit']	=	$pages['limit'];

			$specialM	=	$this -> MODEL('special');

			$List		=	$specialM -> getSpecialComList($where);

			$this->yunset("rows" , $List['list']);
		}
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');

		$this->yunset('backurl',$backurl);

		$this->yunset("header_title","专题招聘");

        $this->waptpl('special');
    }
    function delspecial_action(){
        $logM		=	$this -> MODEL('log');

		$specialM	=	$this -> MODEL('special');

		$delid		=	$specialM -> delSpecialCom(array('id'=>(int)$_GET['id'],'uid'=>$this->uid)," ");

		if($delid){
			$logM -> addMemberLog($this->uid,$this->usertype,"删除专题报名",14,3);//会员日志

			$this->layer_msg('删除成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	function zhaopinhui_action(){
		$urlarr	=	array("c"=>"zhaopinhui","page"=>"{{page}}");

		$pageurl=	Url('wap',$urlarr,'member');

		$uid            =   $this -> uid;

		$where['uid']	=	$uid;

		$pageM	=	$this -> MODEL('page');

		$pages	=	$pageM -> pageList('zhaopinhui_com',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);

		if($pages['total'] > 0){
			if($_GET['order'])
			{
				$where['orderby']		=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']		=	$_GET['order'];
				$urlarr['t']			=	$_GET['t'];
			}else{
				$where['orderby']		=	'ctime';
			}
			$where['limit']	=	$pages['limit'];

			$zphM	=	$this -> MODEL('zph');

			$List	=	$zphM -> getZphCompanyList($where);

			$this->yunset("rows" , $List);
		}
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');

		$this->yunset('backurl',$backurl);

		$this->yunset("header_title","招聘会记录");

		$this->waptpl('zhaopinhui');
	}
	function delzph_action(){
		$zphM	=	$this -> MODEL('zph');

		$logM	=	$this -> MODEL('log');

		$row	=	$zphM -> getZphComInfo(array('id'=>(int)$_GET['id'],'uid'=>$this->uid),array('field'=>"`price`,`status`"));

		$delid	=	$zphM -> delZphCom((int)$_GET['id'],array('uid'=>$this->uid));
		if($delid){
			if($row['status']==0 && $row['price']>0){
			    $IntegralM	=	$this -> MODEL('integral');

				$IntegralM -> company_invtal($this->uid,$this -> usertype, $row['price'],true,"退出招聘会",true,2,'integral');//积分操作记录
			}
			$logM -> addMemberLog($this->uid,$this->usertype,"退出招聘会",14,3);//会员日志

			$this -> layer_msg('退出成功！',9,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this -> layer_msg('退出失败！',8,0,$_SERVER['HTTP_REFERER']);
		}
	}
	

	function set_action(){

	    $comM      =   $this -> MODEL('company');

	    $company   =   $comM -> getInfo($this -> uid,array('logo'=>'1','utype'=>'user'));



		$this      ->  yunset('company', $company);

	    $cert      =   $comM -> getCertInfo(array('uid' => $this -> uid, 'type' => '3'));

	    $this      ->  yunset('cert', $cert);

	    $UserinfoM =   $this -> MODEL('userinfo');

	    $info      =   $UserinfoM -> getInfo($this -> uid, array('setname' => '1'));

	    $this      ->  yunset('member', $info);

	    $backurl   =   Url('wap', array(), 'member');

	    $this      ->  yunset('backurl', $backurl);

	    $this      ->  yunset('header_title', '账户设置');

	    $this      ->  waptpl('set');
	}

	function sysnews_action(){
	    
	   
		//应聘职位
		$JobM			=	$this -> MODEL('job');
		$ResumeM		=	$this -> MODEL('resume');
		$userid_job		=	$JobM -> getSqJobInfo(array('com_id'=>$this->uid,'is_browse'=>'1','orderby'=>'datetime,desc'));
		$uid			=	$userid_job['uid'];
		$resume			=	$ResumeM -> getResumeInfo(array('uid'=>$uid),array('field'=>'`name`'));
		$userid_job['name']  =  $resume['name'];
		$this->yunset('userid',$userid_job);
		$userid_jobnum	=	$JobM -> getSqJobNum(array('com_id'=>$this->uid,'is_browse'=>'1','type'=>1));
		$this->yunset('userid_jobnum',$userid_jobnum);
		//私信
 		$SysmsgM		=	$this -> MODEL('sysmsg');
		$sxrows			=	$SysmsgM -> getSysmsgInfo(array('fa_uid'=>$this->uid,'usertype'=>$this->usertype,'orderby'=>'ctime,desc'));
		$this->yunset("sxrows",$sxrows);
		$sxnum			=	$SysmsgM -> getSysmsgNum(array('fa_uid'=>$this->uid,'usertype'=>$this->usertype,'remind_status'=>'0'));
		$this->yunset('sxnum',$sxnum);

 		//职位咨询
		$MsgM			=	$this -> MODEL('msg');
		$jobrows		=	$MsgM -> getInfo(array('job_uid'=>$this->uid,'del_status'=>array('<>','1'),'orderby'=>'datetime,desc'));
		$this->yunset('jobrows',$jobrows);
		
		$qzwhere['job_uid'] = $this->uid;
		$qzwhere['PHPYUNBTWSTART'] = '';
		$qzwhere['reply'][]  =  array('isnull');
		$qzwhere['reply'][]  =  array('=','','OR');
		$qzwhere['PHPYUNBTWEND'] = '';
		
		$jobnum			=	$MsgM->getMsgNum($qzwhere);
		$this->yunset('jobnum',$jobnum);
		 
        $this->yunset('header_title',"消息");
    	$backurl		=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('sysnews');
	}
	//求职咨询
	function msg_action(){
		$MsgM	=	$this -> MODEL('msg');
		$where['job_uid']		=	$this->uid;
		$where['del_status']	=	array('<>','1');
		$urlarr['c']			=	$_GET['c'];
		$urlarr['page']			=	'{{page}}';
		$pageurl				=	Url('wap',$urlarr,'member');
		$pageM					=	$this -> MODEL('page');
		$pages					=	$pageM -> pageList('msg',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){
	        $where['orderby']	=	'id';
	        $where['limit']		=	$pages['limit'];
	        $rows	=	$MsgM -> getList($where);

			$this -> yunset("rows",$rows['list']);
	    }

		if($_POST['submit']){
			$data['reply']					=	$_POST['reply'];
			$data['reply_time']				=	time();
			$data['user_remind_status']		=	'0';
			$where['id']					=	(int)$_POST['id'];
			$where['job_uid']				=	$this->uid;
			$nid	=	$MsgM -> upReplyInfo($where,$data);
			if($nid){
				$LogM			=	$this -> MODEL('log');
				$LogM->addMemberLog($this->uid,$this->usertype,"回复企业评论",18,1);
				$data['msg']	=	'回复成功';
				$data['url']	=	'index.php?c=msg';
			}else{
				$data['msg']	=	'添加失败';
			}
			$this->yunset("layer",$data);
		}
        $backurl	=	Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"求职咨询");
        $this->waptpl('msg');
	}

	function delmsg_action(){
		$MsgM	=	$this -> MODEL('msg');
	    if($_GET['id']){
			$nid	=	$MsgM -> delMsg($_GET['id'],array('job_uid'=>$this->uid));
			if($nid){
				$LogM		=	$this -> MODEL('log');
				$LogM->addMemberLog($this->uid,$this->usertype,"删除求职咨询",18,3);
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
		}
	}
    //私信
	function sxnews_action(){

		$sysmsgM	        =	$this -> MODEL('sysmsg');
		$where['fa_uid']	=	$this->uid;
		$where['usertype']	=	$this->usertype;
		$urlarr["c"] 		= 	$_GET["c"];
		$urlarr["page"] 	= 	"{{page}}";
		$pageurl			=	Url('wap',$urlarr,'member');
		$pageM				=	$this->MODEL('page');
		$pages				=	$pageM->pageList('sysmsg',$where,$pageurl,$_GET['page']);
		if($pages['total'] > 0){
	        $where['orderby']	 =	array('remind_status,asc','id,desc');
	        $where['limit']		 =	$pages['limit'];
	        $rows	=	$sysmsgM -> getList($where, array('from' => 'wap_member'));
	    }
		$this->yunset('rows',$rows);
		$backurl	=	Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"系统消息");
		$this->waptpl('sxnews');
	}

    function delsxnews_action(){
		$SysmsgM  =	 $this -> MODEL('sysmsg');
		if ($_GET['id']){
			$return  =	 $SysmsgM->delSysmsg($_GET['id'],array('fa_uid'=>$this->uid));
            $LogM	 =	 $this -> MODEL('log');
			$LogM -> addMemberLog($this->uid,$this->usertype,"删除系统消息",18,3);
			$this -> waplayer_msg($return['msg']);
		}
	}

	function attention_me_action(){
		$atnM  				=  $this -> MODEL('atn');

	    $where['sc_uid']	=  $this -> uid;

	    $urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('wap',$urlarr,'member');

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('atn',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];

	        $List   =  $atnM  ->  getatnList($where,array('utype'=>'user','uid'=>$this->uid));
			$this->yunset('rows',$List);
	    }

	    $backurl=Url('wap',array('c'=>'resumecolumn'),'member');
	    $this->yunset('backurl',$backurl);

		$this->yunset('header_title',"关注我的人才");
	    $this->waptpl('attention_me');
	}

	


	function searchcom_action(){
		$name	=	trim($_POST['name']);
		if($name){
			$companyM	=	$this	->	MODEL('company');
			$where		=	array(
				'name'		=>	array('like',$name),
				'uid'		=>	array('<>',$this->uid),
				'orderby'	=>	'lastupdate,desc',
				'limit'		=>	100
			);
			$company	=	$companyM	->	getList($where,array('field'=>'`uid`,`name`','url'=>1));
			if($company&&is_array($company)){
				echo json_encode($company['list']);die;
			}else{
				echo 1;die;
			}

		}

	}
	

	function finance_action(){
		$StatisM		=	$this		->	MODEL("statis");
		$statis			=	$StatisM	->	getInfo($this->uid,array('usertype'=>'2'));
		$backurl		=	Url('wap',array(),'member');
		$this			->	yunset('backurl',$backurl);
		$this			->	yunset("statis",$statis);
		$this			->	yunset('header_title',"财务管理");
		$this			->	waptpl('finance');
	}
	function integral_action(){
		$integralM		=	$this->MODEL('integral');
		$statusList		=	$integralM	->	integralMission(array('type'=>'com','uid'=>$this->uid,'usertype'=>$this->usertype));

		$StatisM		=	$this		->	MODEL("statis");
		$statis			=	$StatisM	->	getInfo($this->uid,array('usertype'=>'2','field'=>'`integral`'));//查询会员信息

		if($_GET['type']){
			$backurl	=	Url('wap',array('c'=>'finance'),'member');
		}else{
			$backurl	=	Url('wap',array(),'member');
		}

		$reg_url 		=	Url('wap',array('c'=>'register','uid'=>$this->uid));
		$this			->	yunset('reg_url', $reg_url);
		$this			->	yunset("statis",$statis);
		$this			->	yunset("statusList",$statusList);
		$this			->	yunset('backurl',$backurl);
		$this			->	yunset('header_title',$this->config['integral_pricename']."管理");
		$this			->	waptpl('integral');
	}

	function resumecolumn_action(){

		$jobM			=	$this -> MODEL('job');

		$atnM			=	$this -> MODEL('atn');

		$resumeM		=	$this -> MODEL('resume');

		$lookresumeM	=	$this -> MODEL('lookresume');

		$downresumeM	=	$this -> MODEL('downresume');
		//应聘简历数
		$sqnum			=	$jobM -> getSqJobNum(array('com_id'=>$this->uid,'type'=>array('<>',3)));

		$this->yunset('sqnum',$sqnum);

		$userid_jobnum	=	$jobM -> getSqJobNum(array('com_id'=>$this->uid,'is_browse'=>'1','type'=>array('<>',3)));

 		$this->yunset('userid_jobnum',$userid_jobnum);


		//面试邀请数
		$userid_msgnum	=	$jobM -> getYqmsNum(array('fid'=>$this->uid));

 		$this->yunset("invitenum",$userid_msgnum);

		//浏览简历数
	    $looknum		=	$lookresumeM -> getLookNum(array('com_id'=>$this->uid,'usertype'=>2,'com_status'=>'0'));

	    $this->yunset("looknum",$looknum);

	    //收藏简历数
	    $talentnum		=	$resumeM -> getTalentNum(array('cuid'=>$this->uid));

	    $this->yunset("talentnum",$talentnum);

	    //下载简历数
	    $downnum		=	$downresumeM -> getDownNum(array('comid'=>$this->uid,'usertype'=>$this->usertype));

	    $this->yunset("downnum",$downnum);

	    //关注我的人才数
	    $atnnum			=	$atnM -> getAtnNum(array('sc_uid'=>$this->uid));

 	    $this->yunset("atnnum",$atnnum);

	    //被浏览的职位数
	    $lookjobnum		=	$jobM -> getLookJobNum(array('com_id'=>$this->uid,'com_status'=>'0'), array('usertype' => $this->usertype));

	    $this->yunset("lookjobnum",$lookjobnum);


		$backurl=Url('wap',array(),'member');

		$this->yunset('backurl',$backurl);

		$this->yunset('header_title',"简历管理");

		$this->waptpl('resumecolumn');
	}

    function jobcolumn_action(){
		$userinfoM	=	$this -> MODEL('userinfo');

		$member		=	$userinfoM -> getInfo(array('uid'=>$this->uid));

		$this->yunset("member",$member);


		$backurl=Url('wap',array(),'member');

		$this->yunset('backurl',$backurl);

 		$this->yunset("header_title","职位管理");

		$this->get_user();

		$this->company_satic();

		$this->waptpl('jobcolumn');
	}

	function integral_reduce_action(){
		$backurl	=	Url('wap',array('c'=>'integral'),'member');
		$this		->	yunset('backurl',$backurl);
		$this		->	get_user();
		$this		->	yunset('header_title',"消费规则");
		$this		->	waptpl('integral_reduce');
	}

	
	function banner_action(){

		$companyM	=	$this -> MODEL('company');

		if($_POST['submit']){

			$data			=	array(

				'base'	=>	$_POST['preview'],

				'uid'		=>	$this->uid,

				'usertype'	=>	$this->usertype

			);

			$row			 =	$companyM-> getBannerInfo('',array('where'=>array('uid'=>$this->uid)));

			if($row['id']){

				$data['type']='update';

			}else{

				$data['type']='add';

			}

			$return			 =	$companyM	->	setBanner($data);

		}

		$banner		=	$companyM-> getBannerInfo('',array('where'=>array('uid'=>$this->uid)));

		$backurl	=	Url('wap',array('c'=>'integral'),'member');

		$this->yunset("layer",$return);
		$this->yunset("banner",$banner);
		$this->yunset("backurl",$backurl);
		$this->yunset('header_title',"企业横幅");
		$this->waptpl('banner');
	}

	function show_action(){
		$companyM		=	$this->MODEL('company');

		$where['uid']	=	$this->uid;

		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"show";
		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_show',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			$where['orderby']		=	'sort,desc';

		    $where['limit']			=	$pages['limit'];

		    $List					=	$companyM->getCompanyShowList($where,array('field'=>'`title`,`id`,`picurl`'));
			$this->yunset("rows",$List);
		}

		$company		=	$companyM->getInfo($where,array('field'=>'`name`'));

		$backurl		=	Url('wap',array('c'=>'set'),'member');
		$this->yunset("js_def",2);
		$this->yunset("company",$company);
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"企业环境");
		$this->waptpl('show');
	}
	function del_action(){
		$CompanyM		=	$this -> MODEL('company');
		if($_POST['id']==""){
			$data		=	3;
		}else{
			$oid		=	$CompanyM -> delComshowInfo(array('id'=>$_POST['id'],'uid'=>$this->uid,'usertype'=>$this->usertype));
			if($oid){
				$data	=	1;
			}else{
				$data	=	2;
			}
		}
		echo json_encode($data);die;
	}

	function addshow_action(){

		$rows=$this->get_user();

		$companyM	=	$this->MODEL('company');

		if($_POST['submit']){

			$data['base']    =  $_POST['preview'];

			$data['utype']   =  'wap';

			$data['uid']     =  $this->uid;

			$data['title']   =  $_POST['title'];

			$data['r_status']=  $rows['r_status'];

			if($_POST['id']){

				$data['id']  =	(int)$_POST['id'];

			}

			$return	=	$companyM->setShow($data);

			unset($return['errcode']);

			$this->yunset("layer",$return);

		}else{
			if($_GET['id']){
				$picurl	=	$companyM->getCompanyShowInfo((int)$_GET['id'],array('uid'=>$this->uid,'type'=>'showpic'));

				$this->yunset("row",$picurl);
			}
		}

		$company	=	$companyM->getInfo(array('uid'=>$this->uid),array('field'=>'`name`'));

		$backurl	=	Url('wap',array('c'=>'show'),'member');
		$this->yunset("company",$company);
		$this->yunset('backurl',$backurl);
		$this->yunset('header_title',"企业环境");
		$this->waptpl('addshow');
	}


    // 职位推广 ：置顶，推荐 紧急条件查询
    function promotion_action()
    {
        $jobM		=   $this->MODEL('job');

        $uid        =	$this->uid;

        $server     =   intval($_POST['server']);

        $type       =   $server - 1;

        $return		=   $jobM->jobPromote($uid, array('type' => $type, 'spid' => $this -> spid));

        echo json_encode($return);
        die();
    }

    /* 职位推广（置顶、推荐、紧急招聘） */
    function setJobPromote_action()
    {
        $_POST  =   $this->post_trim($_POST);

        if (empty($_POST)) {

            $this->ACT_msg_wap('index.php?c=job','参数错误！', 1, 5);
        }

        $jobM   =   $this->MODEL('job');

        $_POST['uid']       =   $this->uid;
        $_POST['spid']      =   $this->spid;
        $_POST['username']  =   $this->username;
        $_POST['usertype']  =   $this->usertype;

        $return = $jobM->setJobPromote(intval($_POST['jobid']), $_POST);

        echo json_encode($return);die();
    }



    /**
     * @desc 会员套餐、增值服务、单项购买页面
     */
    function server_action(){

        $paytype       =   array();

        if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
            $paytype['alipay']='1';
        }
        if($paytype){
            $this->yunset('paytype',$paytype);
        }

        $ratingM    =   $this->MODEL('rating');
        $ratingList =   $ratingM -> getList(array('display' => 1, 'orderby' => array('type,asc', 'sort,desc')));

        $rating_1   =   $rating_2   =   $raV    =   array();

        if (!empty($ratingList)) {

            foreach ($ratingList as $ratingV) {

                $raV[$ratingV['id']]    =   $ratingV;

                if ($ratingV['category'] == 1 && $ratingV['service_price'] > 0) {

                    if ($ratingV['type'] == 1) {

                        $rating_1[]     =   $ratingV;
                    } elseif ($ratingV['type'] == 2) {

                        $rating_2[]     =   $ratingV;
                    }
                }
            }
        }
        $this->yunset('rating_1', $rating_1);
        $this->yunset('rating_2', $rating_2);

        $statis     =   $this->company_satic();

        if ($this->usertype == 2) {

            $add  =  $ratingM->getComServiceList(array('display' => 1 , 'orderby' => array('sort,desc')), array('detail' => 'yes'));
        }

        $this->yunset('add', $add);

        if(!empty($statis)){

            $discount           =   isset($raV[$statis['rating']]) ? $raV[$statis['rating']] : array();
            if($discount['service_discount'] > 0){
                $statis['zk']       =   $discount['service_discount'] * 0.01 ;
                $statis['zk_n']     =   $discount['service_discount'] * 0.1 ;
            }
        }

        $this->yunset('statis', $statis);

        /* 优惠券 */
        $server =   trim($_GET['server']);
        $com_single_can = explode(',', $this->config['com_single_can']);

        $serverCheck = $server;
        if($server=='sxpart'||$server=='sxjob'){
        	$serverCheck = 'sxjob';
        }
        if($server=='jobrec'||$server=='partrec'){
            $serverCheck = 'jobrec';
        }

        if($serverCheck && $serverCheck!='autojob' && !in_array($serverCheck,$com_single_can)){
        	$this->yunset('noSingle', '1');
        }
        switch($server){
            case 'issuejob':
                $this->yunset('single_price', $this->config['integral_job']);
                $this->yunset('single_msg', '本次职位发布');
                break;
            case 'jobtop':
                $this->yunset('single_price', $this->config['integral_job_top']);
                $this->yunset('single_msg', '本次职位置顶');
                break;
            case 'jobrec':
                $this->yunset('single_price', $this->config['com_recjob']);
                $this->yunset('single_msg', '本次职位推荐');
                break;
            case 'joburgent':
                $this->yunset('single_price', $this->config['com_urgent']);
                $this->yunset('single_msg', '本次职位紧急招聘');
                break;
            case 'sxjob':
                $idStr  =   $_GET['id'];
                $ids    =   @explode(',',$idStr);
                $num    =   count($ids) - $statis['breakjob_num'];
                $this->yunset('single_price', $this->config['integral_jobefresh'] * $num);
                $this->yunset('single_msg', '本次刷新职位');
                break;
            case 'sxpart':
                $this->yunset('single_price', $this->config['integral_jobefresh']);
                $this->yunset('single_msg', '本次刷新职位');
                break;
            case 'partrec':
                $this->yunset('single_price', $this->config['com_recjob']);
                $this->yunset('single_msg', '本次推荐兼职');
                break;
            case 'downresume':
                $resumeM    =   $this->MODEL('resume');
                $id         =   intval($_GET['id']);
                $price      =   $resumeM -> setDayprice($id);
                $this->yunset('single_price', $price);
                $this->yunset('single_msg', '本次下载简历');
                break;
            case 'invite':
                $this->yunset('single_price', $this->config['integral_interview']);
                $this->yunset('single_msg', '本次邀请面试');
                break;
            case 'zph':
                $zphM       =   $this -> MODEL('zph');
                $id         =   intval($_GET['bid']);
                $space      =   $zphM -> getZphSpaceInfo(array('id' => $id));
                $this->yunset('single_price', $space['price'] / $this->config['integral_proportion']);
                $this->yunset('single_msg', '本次报名招聘会');
                break;
            case 'autojob':
                $this->yunset('single_price', $this->config['job_auto']);
                $this->yunset('single_msg', '本次设置自动刷新');
                break;
           
            default:
                $this->yunset('noSingle', '1');
                break;
        }

        $this->yunset('server_id', $_GET['id']);

        $this->yunset('server', $server);

        if (!isVip($statis['vip_etime'])) {   //  过期会员

            $this->yunset('vipIsDown', '1');
        }

        $this->yunset('header_title', '优选服务');
        $this->waptpl('server');
    }

    


	function upapply_action(){
        $applyid	=	$_POST['applyid'];
        $partM		=  	$this->MODEL('part');
		$nid		=	$partM->upPartSq(array('id'=>$applyid),array('status'=>2));
    }

	function toresume_action(){
		$JobM		=   $this -> MODEL('job');
        $id			=	$_POST['id'];
		$nid		=	$JobM->updSqJob(array('id'=>$id,'com_id'=>$this->did),array('is_browse'=>2));
	}
	

	function ajaxjobclass_action(){
		$categoryM	=	$this->MODEL('category');

		$rows		=	$categoryM->getJobClass(array('id'=>trim($_POST['id']),'content'=>array('<>','')));
		if($rows&&is_array($rows)){
			if(!empty($rows['content'])){
				echo 1;die;
			}
		}
		echo 2;die;
    }

	function setexample_action(){
		$categoryM	=	$this->MODEL('category');
		$row		=	$categoryM->getJobClass(array('id'=>intval($_POST['id'])),'`content`');
		if($row['content']){
			echo $row['content'];die;
		}
	}

	/**
	 * @desc 查询企业名称和手机号码是否使用
	 */
	function ajaxCheckInfo_action(){
	    if($_POST){

	        $comM      =   $this->MODEL('company');

	        $typeStr   =   trim($_POST['typeStr']);
	        $checkStr  =   trim($_POST['checkStr']);

	        $return    =   $comM -> getCheckUsed($this->uid, array('typeStr' => $typeStr, 'checkStr' => $checkStr));

	        echo json_encode($return);die;
	    }
	}
	/**
	 * 邀请模板列表
	 */
	function yqmb_action(){

	    $comM		=   $this -> MODEL('company');

		$yqmbM      =   $this -> MODEL('yqmb');

        $rows       =   array();

        $where['uid'] =   $this -> uid;
        $urlarr		=	array('c' => 'yqmb', 'page' => '{{page}}');
		$pageurl	=	Url('member', $urlarr);

		$pageM		=	$this  -> MODEL('page');
        $pages		=	$pageM -> pageList('yqmb', $where, $pageurl, $_GET['page']);
        //邀请模板列表
		if($pages['total'] > 0){
			$where['orderby']	=	'id,desc';
			$where['limit']		=	$pages['limit'];
            $rows	=	$yqmbM -> getList($where);
		}

		$this -> yunset('rows', $rows);
		$this -> yunset('totalNum', $pages['total']);

		
		$backurl	=   Url('wap',array('c'=>'set'), 'member');
		$this -> yunset('backurl', $backurl);
		$this -> yunset('header_title', '管理邀请模板');
		$this -> get_user();
		$this -> waptpl('yqmb');
	}

	/**
	 * 创建邀请模板
	 */
	function yqmbedit_action(){

		$yid		=	intval($_GET['yid']);
		$yqmbM      =   $this -> MODEL('yqmb');
		//检查是否可以套餐足够
		if(empty($yid)){
			$mbnum	=	$yqmbM->getNum(array('uid'=>$this->uid));
			if($mbnum>=$this->config['com_yqmb_num']){
				$this->ACT_msg_wap('index.php?c=yqmb','最多可以创建'.$this->config['com_yqmb_num'].'个模板',1 ,5);
			}

		}else{
			$info		=	$yqmbM -> getInfo(array('id' => $yid));
			if(empty($info)){
				$this->ACT_msg_wap('index.php?c=yqmb', '模板不存在',1 ,5);
			}

		}

		$backurl	=   Url('wap',array('c' => 'yqmb'), 'member');
		$this -> yunset('backurl', $backurl);
		$this -> yunset('header_title', '创建修改模板');
		$this -> yunset('info', $info);
		$this -> waptpl('yqmbedit');
	}
	
    public function delYqmb_action()
    {
        $_POST  =   $this -> post_trim($_POST);

        $yqmbM  =   $this -> MODEL('yqmb');

        $res    =   $yqmbM->delYqmb($_POST['id'],array('uid'=>$this->uid));

        echo json_encode($res);

        die();
    }
    public function yqmbeditsave_action(){
    	$_POST  =   $this -> post_trim($_POST);
        $yqmbM  =   $this->MODEL('yqmb');

        $where  =   array();

        if($_POST['yid']){

            $where['id']=   $_POST['yid'];

        }

        $data = array(
            'uid'       =>  $this->uid
        );
        $setdata = array(
            'name'      => $_POST['name'],
            'linkman'   => $_POST['linkman'],
            'linktel'   => $_POST['linktel'],
            'content'   => $_POST['content'],
            'intertime' => $_POST['intertime'],
            'address'   => $_POST['address'],
        );
        $return         =   $yqmbM->addInfo($setdata,$data,$where);

        // 返回值
        if ($return['errcode'] == 9) {
        	$this -> waplayer_msg($return['msg'], 'index.php?c=yqmb');
        } else {

            $this -> waplayer_msg($return['msg'],'');
        }
    }
}

?>