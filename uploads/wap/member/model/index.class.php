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
class index_controller extends wap_controller{

	function waptpl($tpname)
	{
		$this->yuntpl(array('wap/member/user/'.$tpname));
	}

	function get_user()
	{
		$ResumeM   =  $this->MODEL('resume');
		$isresume  =  $ResumeM->getResumeInfo(array('uid'=>$this->uid));

		if (! $isresume['name']) {

		    $this->ACT_msg_wap(Url('wap', array('c' => 'info'), 'member'), '请先完善个人资料', 2, 3);
		}
	}

	function index_action()
	{
		$ResumeM    =  $this->MODEL('resume');
		$UserinfoM  =  $this->MODEL('userinfo');

		$expect    =  $ResumeM->getExpect(array('uid'=>$this->uid,'defaults'=>1),array('field'=>'integrity,id,lastupdate,state,top,topdate'));
		$expect['integrity'] = $expect['integrity'] ? $expect['integrity'] : 0;
		if($expect['topdate']>1){
			$expect['topdatetime']=$expect['topdate'] - time();
			$expect['topdate']=date("Y-m-d",$expect['topdate']);
		}else{
			$expect['topdate']='未设置';
		}
		$this->yunset("expect",$expect);

		$user      =  $ResumeM->getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));
		$this->yunset("user",$user);
		// var_dump($user);exit;
		$reg        =  $UserinfoM->getMemberregInfo(array('uid'=>$this->uid,'usertype'=>$this->usertype,'date'=>date("Ymd")));
		$signstate  =  !empty($reg) ? 1 : 0;
		$this->yunset("signstate",$signstate);

		if($this->config['resume_sx']==1){//登录自动简历刷新,在后台配置

		    if(!empty($expect['id'])){

			    $ResumeM -> upInfo(array('id'=>$expect['id'],'uid'=>$this->uid),array('eData'=>array('lastupdate'=>time())));
				$ResumeM -> upResumeInfo(array('uid'=>$this->uid),array('rData'=>array('lastupdate'=>time())));
			}
		}

		$time  =  strtotime('today');
		$this->yunset("time",$time);

		$this->cookie->SetCookie("exprefresh",'1',time() + 86400);

		
		$backurl  =  Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		//对我感兴趣 总数
		$lookResumeM	=	$this->MODEL('lookresume');
		$looktotal		=	$lookResumeM -> getLookNum(array('uid' => $this->uid, 'status' => 0));
		$this->yunset('looktotal',$looktotal);
        
		$this->waptpl('index');
	}
	function photo_action(){

	    $resumeM  =  $this -> MODEL('resume');

		if($_POST['submit']){

		    $return   =  $resumeM -> upPhoto(array('uid'=>$this->uid),array('utype'=>'user','base'=>$_POST['uimage']));

			$this->layer_msg($return['msg'],$return['errcode']);
		}else{
		    $user  =  $resumeM -> getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));
			$this->yunset("user",$user);

			$this->get_user();

			$backurl  =  Url('wap',array(),'member');
			$this->yunset('backurl',$backurl);

			$this->yunset('headertitle',"个人头像");
			$this->waptpl('photo');
		}
	}

	function sq_action(){

		$JobM			=	$this -> MODEL('job');
		$statisM		=	$this -> MODEL('statis');

		$where['uid']	=	$this -> uid;

		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this -> MODEL('page');
		$pages			=	$pageM -> pageList('userid_job',$where,$pageurl,$_GET['page']);
		if($pages['total'] > 0){

				$where['orderby']	=	'id';

				$where['limit']		=	$pages['limit'];

				$rows   			=	$JobM -> getSqJobList($where,array('uid'=>$this->uid,'usertype'=>$this->usertype));

				$this->yunset("rows",$rows);
		}

		$num			=	$JobM -> getSqJobNum(array('uid'=>$this->uid));

		$statisM -> upInfo(array('sq_jobnum'=>$num),array('uid'=>$this->uid,'usertype'=>$this->usertype));

		if($_GET['back']){
			$backurl	=	Url('wap',array(),'member');
		}else{
			$backurl	=	Url('wap',array('c'=>'jobcolumn'),'member');
		}
		$this->yunset('backurl',$backurl);

		$this->get_user();

		$this->yunset('headertitle',"申请的职位");
		$this->waptpl('sq');
	}

	function partCollect_action()
	{

		$where['uid']	=	$this -> uid;

		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';

		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('part_collect',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			if($_GET['order']){

				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];

			}else{
				$where['orderby']	=	array('id,desc');
			}
			$where['limit']			=	$pages['limit'];

			$PartM		            =	$this -> MODEL('part');

			$rows					=	$PartM -> getPartCollectList($where);
			$this -> yunset("rows",$rows);
		}
		$backurl	=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		$this->get_user();

		$this->yunset('headertitle',"兼职管理");
		$this->waptpl('partcollect');
	}
	// 删除兼职收藏
    function partCollectDel_action(){
        if($_GET['del']){

            $id		 =	intval($_GET['del']);

            $partM   =	$this -> MODEL('part');

            $return  =  $partM -> delPartCollect($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));

            $this -> layer_msg($return['msg'],$return['errcode']);
        }
    }
	function partapply_action(){

		$where['uid']	=	$this -> uid;
		//分页链接
		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';

		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('part_apply',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			if($_GET['order']){

				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];

			}else{
				$where['orderby']	=	array('id,desc');
			}
			$where['limit']			=	$pages['limit'];
			$PartM		            =	$this -> MODEL('part');
			$rows					=	$PartM -> getPartSqList($where);
			$this->yunset("rows",$rows);
		}
		$backurl	=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		$this->get_user();

		$this->yunset('headertitle',"兼职管理");
		$this->waptpl('partapply');

	}
	// 删除兼职报名
	function delPartApply_action()
	{
	    if($_GET['del']){

	        $id		 =	intval($_GET['del']);

	        $partM   =	$this -> MODEL('part');

	        $return  =	$partM -> delPartApply($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));

	        $this -> layer_msg($return['msg'],$return['errcode']);
	    }
	}
	function delsq_action(){

		if($_GET['id']){

			$JobM	=	$this -> MODEL('job');
			$id		=	intval($_GET['id']);
			$arr	=	$JobM -> delSqJob($id,array('utype'=>'user','uid'=>$this->uid,'usertype'=>$this->usertype));

			$this ->  waplayer_msg($arr['msg']);
		}
	}

	function collect_action(){
		$JobM		=	$this -> MODEL('job');
		$statisM	=	$this -> MODEL('statis');

		$where['uid']	=	$this -> uid;
		//分页链接
		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';

		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('fav_job',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			if($_GET['order']){

				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];

			}else{
				$where['orderby']	=	array('id,desc');
			}
			$where['limit']			=	$pages['limit'];

			$rows					=	$JobM -> getFavJobList($where,array('datatype'=>'moreinfo'));
			$this->yunset("rows",$rows);
		}
		$num		=	$JobM -> getFavJobNum(array('uid'=>$this->uid));
		$statisM -> upInfo(array('fav_jobnum'=>$num),array('uid'=>$this->uid,'usertype'=>$this->usertype));

		$backurl	=	Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->get_user();
		// var_dump($rows);exit;
		$this->yunset('headertitle',"收藏的职位");
		$this->waptpl('collect');
	}
	// 删除职位收藏
    function jobCollectDel_action()
    {
        if($_GET['del']){

            $id		 =	$_GET['del'];

            $jobM    =  $this->MODEL('job');

            $return	 =	$jobM -> delFavJob($id,array('utype'=>'user','uid'=>$this->uid,'usertype'=>$this->usertype));

            $this -> layer_msg($return['msg'],$return['errcode']);
        }
    }
	function password_action(){

		$this->yunset('backurl',Url('wap',array('c'=>'set'),'member'));

		$this->get_user();

		$this->yunset('headertitle',"密码设置");
		$this->waptpl('password');
	}
	function invitecont_action(){

		$JobM	=	$this -> MODEL('job');

		$info	=	$JobM -> getYqmsInfo(array('id'=>intval($_GET['id']),'uid'=>$this->uid),array('yqh'=>1,'uid'=>$this->uid,'usertype'=>$this->usertype));
		$info['intertime_n']    =   strtotime($info['intertime']);
		$info['time'] = date(time());
    $this -> yunset("info",$info);
		$this -> get_user();
    $this -> yunset('time', $time);
		$this -> yunset('headertitle',"面试详情");
		$this -> waptpl('invitecont');
	}

	function inviteset_action(){

		$id		=	(int)$_GET['id'];
		$browse	=	(int)$_GET['browse'];

		$JobM	=	$this -> MODEL('job');

		$return	=	$JobM -> setYqms(array('id'=>$id,'browse'=>$browse,'uid'=>$this->uid,'username'=>$this->username, 'port' => '2'));

		$this -> waplayer_msg($return['msg']);
	}

	function delinvite_action(){

		if($_GET['id']){

			$id		=	(int)$_GET['id'];

			$JobM	=	$this->MODEL('job');

			$return	=	$JobM -> delYqms($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));

			if($return['errcode']==9){

				$this->layer_msg('操作成功！',9,0,"index.php?c=invite");

			}else{
				$this->layer_msg('操作失败！',8,0,"index.php?c=invite");
			}
		}
	}

	function invite_action(){
		$JobM		=	$this -> MODEL('job');

		if($_GET['id']){
			$data['id']		=	(int)$_GET['id'];
			$data['uid']	=	$this->uid;
			$return 		=	$JobM->pbComs($data);
			$this->layer_msg($return['msg'],$return['errcode'],0,$return['url']);
		}

		$where['uid']	=	$this -> uid;
		$where['type']	=	array('<>',1);

		$urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';

		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this   ->  MODEL('page');
		$pages			=	$pageM  ->  pageList('userid_msg',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

			$where['orderby']	=	'id';
			$where['limit']		=	$pages['limit'];

			$list				=	$JobM -> getYqmsList($where);
		}
		$this -> yunset('rows',$list);

		if($_GET['back']){
			$backurl	=	Url('wap',array(),'member');
		}else{
			$backurl	=	Url('wap',array('c'=>'jobcolumn'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->get_user();

		$this->yunset('headertitle',"面试通知");
		$this->waptpl('invite');
	}

	function look_action(){

		$LookResumeM	=	$this->MODEL('lookresume');

		$where['uid']		=	$this->uid;
		$where['status']	=	0;
		$where['orderby']	=	array('id,desc');

		$urlarr['c']	=	'look';
		$urlarr['page']	=	'{{page}}';

		$pageurl		=	Url('wap',$urlarr,'member');
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('look_resume', $where, $pageurl, $_GET['page']);

		$where['limit']	=	$pages['limit'];

		$looknew	=	$LookResumeM->getList($where, array('uid' => $this->uid, 'usertype' => $this->usertype));
		$rows		=	$looknew['list'];

		$this->yunset("rows",$rows);

		$backurl	=	Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->get_user();

		//当前用户信息
		$ResumeM    =  $this->MODEL('resume');
		$user      =  $ResumeM->getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));
		$this->yunset("user",$user);


		$CacheM     =   $this -> MODEL('cache');
        $CacheList  =   $CacheM->GetCache(array('hy'));
        
        $this -> yunset($CacheList);


		$this->yunset('headertitle',"谁看过我的简历");
		$this->waptpl('look');
	}

	//已读查看者信息
	function isshow_action(){
		$get = $_GET;
		$id = $get['id'];

		$LookResumeM  = $this->MODEL('lookresume');
		$where['id']  = $id;
		$where['uid'] = $this->uid;
		$look = $LookResumeM->getInfo($where);

		if($look){
			//判断是否已读
			if($look['isshow'] == 0){
				$LookResumeM->upInfo($where,array('isshow' => 1));
			}

		}
	}

    // 删除企业浏览简历记录
	function delLookResume_action()
	{
	    if($_GET['del']){

	        $del	      =  (int)$_GET['del'];

	        $lookResumeM  =  $this->MODEL('lookresume');

	        $return       =  $lookResumeM -> delInfo(array('id'=>$del,'uid'=>$this->uid,'usertype'=>1));

	        $this -> layer_msg($return['msg'],$return['errcode']);
	    }
	}
	function addresume_action(){

		$resumeM	=	$this -> MODEL('resume');

		$return		=	$resumeM -> addResumePage(array('uid' => $this->uid, 'needcache' => 1), 'wap');

		if($return['error']['err']!=0){

			$this -> yunset('layer',$return['error']);
		}

		$this -> yunset($return['setarr']);

		$this -> yunset($return['cache']);

		$this -> waptpl('addresume');
	}
    /**
     * 创建简历保存
     */
	function kresume_action(){
	    $_POST    =  $this->post_trim($_POST);

	    $resumeM  =  $this -> MODEL('resume');
	    $rinfo    =  $resumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'r_status,uid'));
	    if($rinfo){
	        $rstatus  =   $rinfo['r_status'];
	    }else{
	        $rstatus  =   $this->config['user_state'];
	    }
	    $rData  =  array(
	        'name'		=>	$_POST['uname'],
	        'sex'		=>	$_POST['sex'],
	        'birthday'  =>	$_POST['birthday'],
	        'living'	=>	$_POST['living'],
	        'edu'		=>	$_POST['edu'],
	        'exp'		=>	$_POST['exp'],
	        'email'     =>  $_POST['email'],
	        'telphone'  =>  $_POST['telphone']
	    );
	    $eData  =  array(
	        'lastupdate'	 =>  time(),
	        'height_status'  =>	 0,
	        'uid'		     =>	 $this->uid,
	        'ctime'			 =>	 time(),
	        'name'			 =>	 $_POST['name'],
	        'hy'			 =>	 $_POST['hy'],
	        'r_status'		 =>	 $rinfo['r_status'],
	        'job_classid'	 =>	 $_POST['job_classid'],
	        'city_classid'   =>  $_POST['city_classid'],
	        'minsalary'		 =>	 $_POST['minsalary'],
	        'maxsalary'		 =>	 $_POST['maxsalary'],
	        'type'			 =>	 $_POST['type'],
	        'report'		 =>	 $_POST['report'],
	        'jobstatus'		 =>	 $_POST['jobstatus'],
	        'state'		 	 =>	 $rstatus==1 ? $this->config['resume_status']:0,
	        'r_status'		 =>	 $rstatus,
	        'edu'            =>  $_POST['edu'],
	        'exp'            =>  $_POST['exp'],
	        'sex'            =>  $_POST['sex'],
	        'birthday'       =>	 $_POST['birthday'],
	        'source'         =>  2
	    );
	    /**************************简历是否必填工作经历*************************************************/
	    $workData =  array();
	    if($this->config['resume_create_exp']=='1'&&$_POST['iscreateexp']!='2'){

	        for ($i=0; $i < count($_POST['workname']); $i++) {
                $workData[$i]   =   array(
                    'uid'       =>  $this->uid,
                    'name'      =>  $_POST['workname'][$i],
                    'sdate'     =>  strtotime($_POST['worksdate'][$i]),
                    'edate'     =>  $_POST['totoday'][$i] ? 0 : $_POST['workedate'][$i] ? strtotime($_POST['workedate'][$i]) : 0,
                    'title'     =>  $_POST['worktitle'][$i],
                    'content'   =>  $_POST['workcontent'][$i]
                );
            }
	    }
	    /**************************简历是否必填教育经历*************************************************/
	    $eduData  =  array();
	    if($this->config['resume_create_edu']=='1'&&$_POST['iscreateedu']!='2'){
	        for ($i=0; $i < count($_POST['eduname']); $i++) {
                $eduData[$i]    =   array(
                    'uid'       =>  $this->uid,
                    'name'      =>  $_POST['eduname'][$i],
                    'sdate'     =>  strtotime($_POST['edusdate'][$i]),
                    'edate'     =>  strtotime($_POST['eduedate'][$i]),
                    'title'     =>  $_POST['edutitle'][$i],
                    'specialty'   =>  $_POST['eduspec'][$i],
                    'education'   =>  $_POST['education'][$i]
                );
            }
	    }
	    /**************************简历是否必填项目经历*************************************************/
	    $proData  =  array();
	    if($this->config['resume_create_project']=='1'){

	        for ($i=0; $i < count($_POST['proname']); $i++) {
                $proData[$i]    =   array(
                    'uid'       =>  $this->uid,
                    'name'      =>  $_POST['proname'][$i],
                    'sdate'     =>  strtotime($_POST['prosdate'][$i]),
                    'edate'     =>  strtotime($_POST['proedate'][$i]),
                    'title'     =>  $_POST['protitle'][$i],
                    'content'   =>  $_POST['procontent'][$i]
                );
            }
	    }

	    $addArr   =  array(
	        'uid'       =>  $this->uid,
	        'rData'     =>  $rData,
	        'eData'     =>  $eData,
	        'workData'  =>  $workData,
	        'eduData'   =>  $eduData,
	        'proData'   =>  $proData,
	        'utype'     =>  'user'
	    );
	    if(!$rinfo['uid']){
				$userinfoM    =   $this->MODEL("userinfo");
				$userinfoM -> activUser($this -> uid,1);
		}
	    $return   =  $resumeM -> addInfo($addArr);

        if ($return['id']){

            $return['url']  =  'index.php?c=resume&eid='.$return['id'].'&fr=1';
        }
        echo json_encode($return);die;
	}
	function addresumeson_action(){

		switch($_GET['type']){

			case 'work':		$headertitle='工作经历';  break;
			case 'edu':			$headertitle='教育经历';  break;
			case 'project':		$headertitle='项目经历';  break;
			case 'training':	$headertitle='培训经历';  break;
			case 'skill':		$headertitle='职业技能';  break;
			case 'other':		$headertitle='其他信息';  break;
			case 'desc':		$headertitle='自我评价';  break;
			case 'show':		$headertitle='作品案例';  break;
			case 'doc':	        $headertitle='粘贴简历';  break;
		}
		$this->yunset('headertitle',$headertitle);

		$this->yunset('fr',$_GET['fr']);

		if(!in_array($_GET['type'],array('expect','desc','cert','doc','edu','other','project','show','skill','training','work'))){

			unset($_GET['type']);
		}

		$ResumeM  =  $this->MODEL('resume');

		if($_GET['type']=='desc'){

			$desc		=	$ResumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'description,tag'));
			$this->yunset('tagid',$desc['tag']);

			if($desc['tag']){
				$tag	=	@explode(',',$desc['tag']);
			}
			$this->yunset('arrayTag',$tag);

			$this->yunset('description',$desc['description']);

			$this->yunset('tag_arr',$desc['tag']);

		}else{

		    if ($_GET['type'] == 'doc'){

		        $id  =  $_GET['eid'];

		    }else {

		        $id  =  $_GET['id'];
		    }
		    $table	=	'resume_'.$_GET['type'];

		    $row	=	$ResumeM -> getFb($table, $id ,$this->uid);

		    $this->yunset('row',$row);
		}

		$cacheM  =  $this->MODEL('cache');

		$cacheList  =  $cacheM -> GetCache(array('user','introduce'));

		$this->yunset($cacheList);


		$this->get_user();

		$this->waptpl('addresumeson');
	}
	function saveresumeson_action(){

		$ResumeM	=	$this->MODEL('resume');
		if($_POST['submit']){

			$_POST	=	$this->post_trim($_POST);

			if($_POST['table']=="resume"){

				if($_POST['tag']){

					$tagStr = $_POST['tag'];
				}

				$rData	=	array(
					'tag'			=>	$tagStr,
					'description'	=>	$_POST['description'],
					'lastupdate'	=>	time()
				);
				$ResumeM->upResumeInfo(array('uid'=>$this->uid),array('rData'=>$rData));

				$data['url']	=	"index.php?c=resume&eid=".$_POST['eid'];
				$data['msg']	=	"保存成功！";
				echo json_encode($data);die;
			}
			if($_POST['table']=="doc"){
					$table	=	"resume_".$_POST['table'];
					$ResumeM->upResumeTable($table,array('uid'=>$this->uid,'eid'=>$_POST['eid']),array('doc'=>$_POST['doc']));

					$data['url']	=	"index.php?c=resume&eid=".$_POST['eid'];
					$data['msg']	=	"保存成功！";
					echo json_encode($data);die;
			}
			if($_POST['eid']>0){

				if(!in_array($_POST['table'],array('expect','cert','doc','edu','exp','other','project','show','skill','tiny','training','work'))){

					$data['url']	=	"index.php?c=resume&eid=".$_POST['eid'];
					$data['msg']	=	"参数错误";
					echo json_encode($data);die;
				}
				$table	=	"resume_".$_POST['table'];

				$id		=	(int)$_POST['id'];
				$url	=	$_POST['table'];

				unset($_POST['submit']);
				unset($_POST['table']);
				unset($_POST['id']);

				$_POST['sdate']	=	strtotime($_POST['sdate']);

				if(intval($_POST['totoday'])=='1'){

					unset($_POST['totoday']);
					$_POST['edate']	=	'';
				}else{
					$_POST['edate']	=	strtotime($_POST['edate']);
				}
				if($table=='resume_skill'){
					//查询修改
					$resume	=	$ResumeM->getResumeSkill(array('id'=>$id,'eid'=>$_POST['eid']),'pic');

					if ($_POST['preview']){

		                $upArr   =  array(
		                    'dir'      =>  'user',
		                    'type'     =>  'skill',
		                    'base'     =>  $_POST['preview'],
		                );
		                $result  =  $this -> upload($upArr);

		                if (!empty($result['msg'])){

		                    $data['msg']   =  $result['msg'];

		                }elseif (!empty($result['picurl'])){

		                    $_POST['pic']  =  $result['picurl'];

		                }
		            }else{
						$_POST['pic']=$resume['pic'];
					}
				}
				if($table=='resume_show'){

					$resume	=	$ResumeM->getResumeShowInfo(array('id'=>$id,'uid'=>$this ->uid),array('field'=>'picurl'));

 		       		if($_POST['preview']){
						$upArr   =  array(
		                    'dir'      =>  'show',
		                    'base'     =>  $_POST['preview'],
		                );

		                $result  =  $this -> upload($upArr);

		                if (!empty($result['msg'])){

		                    $data['msg']      =  $result['msg'];


		                }elseif (!empty($result['picurl'])){

		                    $_POST['picurl']  =  $result['picurl'];
		                }

					}else{
						$_POST['picurl']	  =  $resume['picurl'];
					}
				}

				if($url=='work'){
					$adata	=	array(
						'name'		=>	$_POST['name'],
						'title'		=>	$_POST['title'],
						'sdate'		=>	$_POST['sdate'],
						'edate'		=>	$_POST['edate'],
						'totoday'	=>	$_POST['totoday'],
						'content'	=>	$_POST['content']
					);
				}elseif($url=='edu'){
					$adata	=	array(
						'name'		=>	$_POST['name'],
						'title'		=>	$_POST['title'],
						'sdate'		=>	$_POST['sdate'],
						'edate'		=>	$_POST['edate'],
						'education'	=>	$_POST['education'],
						'specialty'	=>	$_POST['specialty']
					);
				}elseif($url=='project'){
					$adata	=	array(
						'name'		=>	$_POST['name'],
						'title'		=>	$_POST['title'],
						'sdate'		=>	$_POST['sdate'],
						'edate'		=>	$_POST['edate'],
						'content'	=>	$_POST['content']
					);
				}elseif($url=='training'){
					$adata	=	array(
						'name'		=>	$_POST['name'],
						'title'		=>	$_POST['title'],
						'sdate'		=>	$_POST['sdate'],
						'edate'		=>	$_POST['edate'],
						'content'	=>	$_POST['content']
					);
				}elseif($url=='skill'){
					$adata	=	array(
						'name'		=>	$_POST['name'],
						'longtime'	=>	$_POST['longtime'],
						'ing'		=>	$_POST['ing'],
						'pic'		=>	$_POST['pic']
					);
				}elseif($url=='show'){
					$rinfo	=	$ResumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'r_status'));

					$adata	=	array(
						'title'		=>	$_POST['title'],
						'sort'		=>	$_POST['sort'],
						'picurl'	=>	$_POST['picurl'],
						'status'  	=>  $rinfo['r_status']==0?1:$this->config['rshow_photo_status']
					);
				}elseif($url=='other'){
					$adata	=	array(
						'name'		=>	$_POST['name'],
						'content'	=>	$_POST['content']
					);
				}
				if($id){
					$nid			=	$ResumeM->upResumeTable($table,array('id'=>$id,'uid'=>$this->uid),$_POST);
				}else{
					$adata['uid']	=	$this->uid;
					$adata['eid']	=	$_POST['eid'];

					$nid			=	$ResumeM->upResumeTable($table,'',$adata);

					if($url=='work'){
						$udata['work']		=	array('+',1);
					}elseif($url=='skill'){
					    $udata['skill']		=	array('+',1);
					}elseif($url=='project'){
					    $udata['project']	=	array('+',1);
					}elseif($url=='edu'){
					    $udata['edu']		=	array('+',1);
					}elseif($url=='training'){
					    $udata['training']	=	array('+',1);
					}elseif($url=='cert'){
					    $udata['cert']		=	array('+',1);
					}elseif($url=='other'){
					    $udata['other']		=	array('+',1);
					}

					$ResumeM->upUserResume($udata,array('eid'=>(int)$_POST['eid'],'uid'=>$this->uid));
				}

				if($table=='resume_work'){
					//计算
					$workList	=	$ResumeM->getResumeWorks(array('eid'=>(int)$_POST['eid'],'uid'=>$this->uid));

					$whour		=	0;
					$hour		=	array();
					foreach($workList as $value){
						//计算每份工作时长(按月)
						if ($value['edate']){

							$workTime	=	ceil(($value['edate']-$value['sdate'])/(30*86400));
						}else{
							$workTime	=	ceil((time()-$value['sdate'])/(30*86400));
						}
						$hour[]			=	$workTime;
						$whour			+=	$workTime;
					}
					//更新当前简历时长字段
					$avghour = ceil($whour/count($hour));

					$ResumeM->upInfo(array('id'=>(int)$_POST['eid'],'uid'=>$this->uid),array('eData'=>array('whour'=>$whour,'avghour'=>$avghour)));


	            }
				if($nid){
					$data['msg']	=	'保存成功！';
				}else{
					$data['msg']	=	'保存失败！';
				}

				$data['url']		=	"index.php?c=rinfo&eid=".$_POST['eid']."&type=".$url;
				if($_POST['fr'] == '1'){
					$data['url']	.=	"&fr=1";
				}
				$data['msg']		=	$data['msg'];
				echo json_encode($data);die;
			}
		}
	}
	/**
   * 处理单个图片上传
   * @param file/需上传文件; dir/上传目录; type/上传图片类型; base/需上传base64; preview/pc预览即上传
   */
	private function upload($data = array('file'=>null,'dir'=>null,'type'=>null,'base'=>null,'preview'=>null)){

	    $UploadM  =  $this	  ->	MODEL('upload');
	    $upArr    =  array(
        	'file'     =>  $data['file'],
        	'dir'      =>  $data['dir'],
        	'type'     =>  $data['type'],
        	'base'     =>  $data['base'],
        	'preview'  =>  $data['preview']
     	);
	    $return  =  $UploadM -> newUpload($upArr);

	    return $return;
	}
	function info_action(){

		$resumeM	=	$this -> MODEL('resume');

		if($_POST['submit']){
			$mData	=	array(
				'moblie'		=>	$_POST['telphone'],
				'email'			=>	$_POST['email'],
			);
			$rData	=	array(
				'name'			=>	$_POST['name'],
				'nametype'		=>	$_POST['nametype'],
				'sex'			=>	$_POST['sex'],
				'birthday'		=>	$_POST['birthday'],
				'edu'			=>	$_POST['edu'],
				'exp'			=>	$_POST['exp'],
				'telphone'		=>	$_POST['telphone'],
				'living'		=>	$_POST['living'],
				'email'			=>	$_POST['email'],
				'address'		=>	$_POST['address'],
				'height'		=>	$_POST['height'],
				'weight'		=>	$_POST['weight'],
				'nationality'	=>	$_POST['nationality'],
				'marriage'		=>	$_POST['marriage'],
				'domicile'		=>	$_POST['domicile'],
				'qq'			=>	$_POST['qq'],
				'homepage'		=>	$_POST['homepage'],
				'preview' 		=> 	$_POST['preview'],
			    'phototype' 	=> 	$_POST['phototype'],
			    'lastupdate'	=>  time()
			);
			$return  =  $resumeM -> upResumeInfo(array('uid'=>$this->uid),array('mData'=>$mData,'rData'=>$rData,'utype'=>'user'));

			if($_POST['eid']){
			    $return['url']  =  "index.php?c=resume&eid=".$_POST['eid'];
			}else{
			    $return['url']  =  "index.php";
			}
			$this->layer_msg($return['msg'], $return['errcode'], 0, $return['url']);
		}

		$nametype	=	array('1'=>'完全公开','2'=>'显示编号','3'=>'性别称呼');
		$this->yunset("nametype",$nametype);

		$resume		=	$resumeM -> getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));
		$this->yunset("resume",$resume);
		$this->yunset($this->MODEL('cache')->GetCache(array('user')));
		$this->yunset('headertitle',"基本信息");
 		$this->waptpl('info');
	}
    function addexpect_action()
    {
		$this->yunset('headertitle','意向职位修改');

		if($_GET['eid']){
		    $resumeM  =  $this->MODEL('resume');
		    $return   =  $resumeM->getInfo(array('eid'=>intval($_GET['eid']),'needCache'=>1,'uid'=>$this->uid));

		    $this->yunset('row', $return['expect']);
		    $this->yunset($return['cache']);
		    if(empty($return['cache']['city_type'])){
	            $this   ->  yunset('cionly',1);
	        }
	        if(empty($return['cache']['job_type'])){
	            $this   ->  yunset('jionly',1);
	        }
		}
		$this->get_user();

		$this->waptpl('addexpect');
	}
	function expect_action()
	{
	    $resumeM  =  $this->MODEL('resume');

	    $eid      =  (int)$_POST['eid'];

	    if($_POST['submit']){

	        if($eid){

	            $expectDate  =  array(
	                'name'			 =>	 $_POST['name'],
	                'job_classid'	 =>	 $_POST['job_classid'],
	                'city_classid'	 =>	 $_POST['city_classid'],
	                'minsalary'      =>	 $_POST['minsalary'],
	                'type'			 =>	 $_POST['type'],
	                'report'		 =>	 $_POST['report'],
	                'jobstatus'	     =>	 $_POST['jobstatus'],
	                'lastupdate'	 =>	 time()
	            );

	            foreach ($expectDate as $k=>$v){
	                if (empty($v) && $k!='hy'){
	                    echo -1;die;
	                }
	            }
	            $expectDate['hy']  =  $_POST['hy'];
	            $expectDate['maxsalary']  =  $_POST['maxsalary'];

	            $return  =  $resumeM -> upInfo(array('id'=>$eid,'uid'=>$this->uid), array('eData'=>$expectDate,'utype'=>'user'));
	            if($return['id']){


	                echo 1;die;

	            }else{

	                echo 0;die;
	            }
	        }else{

	            echo 0;die;
	        }
	    }
	}
	function rcomplete_action(){
		$this->yunset('headertitle',"我的简历");

		$data		=	array(
			'id'	=>	(int)$_GET['eid'],
			'uid'	=>	$this->uid,
			'limit'	=>	2
		);
		$resumeM	=	$this->MODEL('resume');
		$list		=	$resumeM->likeJob($data);

		if(empty($list)){
			$jobM	=	$this->MODEL('job');
			$jwhere =	array(
				'state'		=>	1,
				'r_status'	=>	1,
				'status'	=>	0,
				'limit'		=>	2
			);
			$jlist	=	$jobM	->	getList($jwhere);
			$list	=	$jlist['list'];
		}
		$this->yunset('list',$list);

		$backurl	=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('rcomplete');
	}
	function resume_action(){

		$this->yunset('headertitle',"我的简历");

		$ResumeM	=	$this->MODEL('resume');

		if ($_GET['eid']){
			$expect	=	$ResumeM->getExpect(array('id'=>(int)$_GET['eid'],'uid'=>$this->uid),array('needCache'=>1));
		}
		if(!$expect['id']){
			$expect	=	$ResumeM->getExpect(array('default'=>1,'uid'=>$this->uid),array('needCache'=>1));

			if (!$expect){

				$rwhere['uid']		=	$this->uid;
				$rwhere['orderby']	=	array('lastupdate,desc');
				$expect				=	$ResumeM->getExpect($rwhere,array('needCache'=>1));
			}
		}
		$CacheArr=$this->MODEL('cache')->GetCache(array('city','user','hy','job'));
		$this->yunset($CacheArr);

		if($expect['job_classid']){

			$job_classid	=	@explode(',',$expect['job_classid']);

			$jobname		=	array();
			foreach($job_classid as $val){

				$jobname[]	=	$CacheArr['job_name'][$val];
			}
		}
		$this->yunset("jobname",@implode(' ',$jobname));

		if($expect['city_classid']){
			$city_classid	=	@explode(',',$expect['city_classid']);
			$cityname		=	array();
			foreach($city_classid as $val){
				$cityname[]	=	$CacheArr['city_name'][$val];
			}
		}
		$this->yunset("cityname",@implode(' ',$cityname));

		$this->yunset("expect",$expect);

		$resume	=	$ResumeM->getUserResumeInfo(array('uid' => $this -> uid,'eid'=>$expect['id']));
		$this->yunset("resume",$resume);

		$user	=	$ResumeM->getResumeInfo(array('uid'=>$this->uid),array('logo'=>'1'));

		if($user['birthday']){
			$a				=	date('Y',strtotime($user['birthday']));
			$user['age']	=	date("Y")-$a;
		}
		if($user['tag']){
			$tag			=	@explode(',',$user['tag']);
		}
		$this->yunset("arrayTag",$tag);
		$this->yunset("user",$user);

		$rewhere['uid']		=	$this->uid;
		$rewhere['orderby']	=	array('lastupdate,desc');
		$rows				=	$ResumeM->getSimpleList($rewhere,array('field'=>'id,name,defaults'));

		$resume				=	$ResumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'def_job'));

		foreach($rows as $key=>$val){

			if($val['id']==$resume['def_job']){

				$rows[$key]['name']	=	$val['name'].'(默认)';
			}
		}
		$this->yunset("rows",$rows);

		//判断简历最大数量
		if (!empty($this->config['user_number'])){

		    $num     =  $ResumeM->getExpectNum(array('uid'=>$this->uid));

		    $maxnum  =  $this->config['user_number'] - $num;

		    if($maxnum < 0){$maxnum='0';}
		}else{
		    $maxnum  =  '';
		}
		$this->yunset("maxnum",$maxnum);

		$doc	=	$ResumeM->getResumeDoc(array('eid'=>$expect['id'],'uid'=>$this->uid));
		$this->yunset("doc",$doc);

		$wwhere['uid']		=	$this->uid;
		$wwhere['eid']		=	$expect['id'];
		$wwhere['orderby']	=	array('sdate,desc');
		$work				=	$ResumeM->getResumeWorks($wwhere);
		$this->yunset("work",$work);

		$ewhere['uid']		=	$this->uid;
		$ewhere['eid']		=	$expect['id'];
		$ewhere['orderby']	=	array('sdate,desc');
		$edu				=	$ResumeM->getResumeEdus($ewhere);
		$this->yunset("edu",$edu);

		$pwhere['uid']		=	$this->uid;
		$pwhere['eid']		=	$expect['id'];
		$pwhere['orderby']	=	array('sdate,desc');
		$project			=	$ResumeM->getResumeProjects($pwhere);
		$this->yunset("project",$project);

		$twhere['uid']		=	$this->uid;
		$twhere['eid']		=	$expect['id'];
		$twhere['orderby']	=	array('sdate,desc');
		$training		=	$ResumeM->getResumeTrains($twhere);
		$this->yunset("training",$training);

		$swhere['uid']		=	$this->uid;
		$swhere['eid']		=	$expect['id'];
		$swhere['orderby']	=	array('id,desc');
		$skill				=	$ResumeM->getResumeSkills($swhere);
		foreach($skill as $key=>$val){

			$skill[$key]['pic']	=	checkpic($val['pic']);
		}
		$this->yunset("skill",$skill);
		$owhere['uid']		=	$this->uid;
		$owhere['eid']		=	$expect['id'];
		$owhere['orderby']	=	array('id,desc');
		$other				=	$ResumeM->getResumeOthers($owhere);
		$this->yunset("other",$other);

		$shwhere['uid']		=	$this->uid;
		$shwhere['eid']		=	$expect['id'];
		$shwhere['orderby']	=	array('id,desc');
		$show				=	$ResumeM->getResumeShowList($shwhere);
		$this->yunset("show",$show);

		
		if(is_array($work)){

			$whour	=	0;
			$hour	=	array();
			foreach($work as $value){

				if ($value['edate']){
					$workTime	=	ceil(($value['edate']-$value['sdate'])/(30*86400));
				}else{
					$workTime	=	ceil((time()-$value['sdate'])/(30*86400));
				}
				$hour[] 		= 	$workTime;
				$whour 			+= 	$workTime;
			}
			$worknum 			= 	count($hour);
		}
		if($whour>24 || $worknum>3){//工作经历二年以上或工作经历三项以上
			$this->yunset('heighttwo',2);
		}

		$backurl		=Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('resume');
	}
    function rinfo_action(){

		$ResumeM	=	$this->MODEL('resume');
		$UserinfoM	=	$this->MODEL('userinfo');

		switch($_GET['type']){
			case 'work':	$headertitle	=	"工作经历";break;
			case 'edu':		$headertitle	=	"教育经历";break;
			case 'project':	$headertitle	=	"项目经历";break;
			case 'training':$headertitle	=	"培训经历";break;
			case 'skill':	$headertitle	=	"职业技能";break;
			case 'other':	$headertitle	=	"其他信息";break;
			case 'desc':	$headertitle	=	"自我评价";break;
			case 'show':	$headertitle	=	"作品案例";break;
		}
		$this->yunset('headertitle',$headertitle);

		$type	=	$_GET['type'];

		$rows	=	$ResumeM->getFbList($type,array('eid'=>(int)$_GET['eid'],'uid'=>$this->uid));
		$this->yunset("rows",$rows);

		$this->yunset("type",$_GET['type']);
		$this->yunset("eid",$_GET['eid']);
		$this->yunset("fr",$_GET['fr']);

		$urlarr		=	array('c'=>'resume','eid'=>$_GET['eid']);
		if($_GET['fr']=='1'){
			$urlarr['fr']	=	1;
		}
		$backurl	=	Url('wap',$urlarr,'member');
		$this->yunset('backurl',$backurl);

		$this->get_user();

		$this->waptpl('rinfo');
	}
	// 删除简历附表
	function delResumeFb_action()
	{
	    $table			=	$_GET['type'];

	    $fbwhere['eid']	=	intval($_GET['eid']);
	    $fbwhere['id']	=	intval($_GET['id']);
	    $fbwhere['uid']	=	$this->uid;

	    $ResumeM  =  $this->MODEL('resume');

	    $return   =  $ResumeM->delFb($table,$fbwhere,array('utype'=>'user'));

	    $this->layer_msg($return['msg'],$return['errcode']);
	}

	function resumeset_action(){

		$ResumeM								=				$this->MODEL('resume');

		$StatisM								=				$this->MODEL('statis');

		$LookResumeM							=				$this->MODEL('lookresume');

		$LogM            						=     			$this->MODEL('log');

		$uid									=				$this->uid;

		if($_GET['del']){

			$del								=				(int)$_GET['del'];

			$return 							=				$ResumeM->delResume($del,array('uid' => $this -> uid));

			if($return['id']){


				$LogM->addMemberLog($this->uid,$this->usertype,"删除简历",2,3);

			}
			$this->waplayer_msg($return['msg']);

		}else if($_GET['update']){

					$id							=					(int)$_GET['update'];

					$upexpectData				=					array(

							'lastupdate'		=>					time()

					);

					$upexpectWhere['id']		=					$id;

					$upexpectWhere['uid']		=					$uid;

					$nid						=					$ResumeM->upInfo($upexpectWhere,array('eData'=>$upexpectData));

					$nid?$this->waplayer_msg("刷新成功！"):$this->waplayer_msg("刷新失败！");

		}else if($_GET['def']){
		    // 设置默认简历
		    $ResumeM -> defaults(array('id'=>(int)$_GET['def'],'uid'=>$this->uid));

		    $this->waplayer_msg("设置成功");

		}
	}
	function lookjobdel_action(){
		if($_GET['id']){
			$JobM   =   $this -> MODEL('job');
			$id		=   intval($_GET['id']);
			$arr    =   $JobM -> delLookJob($id,array('uid'=>$this->uid,'usertype'=>$this->usertype));

			$this ->  waplayer_msg($arr['msg']);
		}
	}
	function look_job_action(){
		$this->yunset('headertitle',"职位浏览记录");
		$JobM				=   $this -> MODEL('job');

		$where['uid']		=  $this -> uid;
		$where['status']	=  0;
		 //分页链接
		$urlarr['c']		=	$_GET['c'];
        $urlarr['page']		=	'{{page}}';

        $pageurl			=	Url('member',$urlarr);

        $pageM				=	$this  -> MODEL('page');
        $pages				=	$pageM -> pageList('look_job',$where,$pageurl,$_GET['page']);

        if($pages['total'] > 0){

            if($_GET['order']){

                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
            }else{

                $where['orderby']		=	array('id,desc');
            }
            $where['limit']				=	$pages['limit'];

            $rows    	=   $JobM -> getLookJobList($where);
			$this -> yunset("look",$rows);
        }
		$backurl=Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		// var_dump($rows);exit;
		$this->waptpl('look_job');
	}
	function getYears($startYear=0,$endYear=0){
		$list=array();
		$month_list=array();
		if($endYear>0){
			if($startYear<=0){
				$startYear=	$endYear-150;
			}
			for($i=$endYear;$i>=$startYear;$i--){
				$list[]=$i;
			}
		}
		for($i=12;$i>=1;$i--){
			$month_list[]=$i;
		}
		$this->yunset("year_list",$list);
		$this->yunset("month_list",$month_list);
		return $list;
	}

	function binding_action()
	{

		$this->yunset('headertitle',"社交账号绑定");

		$comM		=   $this -> MODEL('company');
		$UserinfoM	=	$this -> MODEL('userinfo');
		$ResumeM	=	$this -> MODEL('resume');

		if($_POST['moblie']){

			$CookieM=	$this->MODEL('cookie');
			$CookieM -> SetCookie('delay', '', time() - 60);
			$data	=   array(
				'uid'      =>  $this -> uid,
				'usertype' =>  $this -> usertype,
				'moblie'   =>  $_POST['moblie']
			);

			$return	=	$comM -> upCertInfo(array('uid'=> $this->uid, 'check2' => $_POST['code']), array('status'=>'1'), $data);

			echo $return;die;
		}
		// 解除绑定
		if($_GET['type']){
			$return=	$comM -> delBd($this->uid,array('type'=>$_GET['type'],'usertype'=>$this->usertype));
			$this->waplayer_msg($return['msg']);
		}
		$member	=	$UserinfoM -> getInfo(array('uid'=> $this->uid), array('setname'=>'1'));
		$this->yunset("member",$member);
		if($member['restname']=="0"){
			$this->yunset("setname",1);
		}
		$this->get_user();
		$this->yunset("backurl",Url('wap',array('c'=>'set'),'member'));
		$this->waptpl('binding');
	}
	function idcard_action(){
		$this->yunset('headertitle',"实名认证");
		$ResumeM	=	$this -> MODEL('resume');
		$UserinfoM	=	$this -> MODEL('userinfo');
		$resume		=	$ResumeM -> getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));

		if($_POST['submit']){
			if($_POST['name']==''){
				$data['msg']	=	'请输入姓名';
			}elseif($_POST['idcard']==''){
				$data['msg']	=	'请输入身份证号';
			}else if(!$_POST['preview'] && !$resume['idcard_pic']){
				$data['msg']	=	'请上传证件照！';
			}
			if($data['msg']==""){
				$dataarr	=	array(
				    'usertype'	=>	$this->usertype,
				    'name'		=>	$_POST['name'],
					'idcard'	=>	$_POST['idcard'],
					'preview'	=>	$_POST['preview'],
				);
				$nid			=	$UserinfoM -> upidcardInfo(array('uid'=>$this->uid),$dataarr);
				$data['msg']	=	$nid['msg'];
				$data['url']	=	"index.php?c=set";
			}else{
				$data['url']	=	"index.php?c=idcard";
 				$data['msg']	=	$data['msg'];
			}
		}
		$this->yunset("layer",$data);
		$this->yunset("resume",$resume);
		$backurl	=	Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('idcard');
	}
	function bindingbox_action(){
		switch($_GET['type']){
			case 'moblie':$headertitle="手机认证";
			break;
			case 'email':$headertitle="邮箱认证";
			break;
		}
		$this->yunset('headertitle',$headertitle);
		$UserinfoM	=	$this -> MODEL('userinfo');
		$member		=	$UserinfoM -> getInfo(array('uid'=>$this->uid));
		$this->yunset("member",$member);
		$backurl	=	Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('bindingbox');
	}
	function setname_action(){
		$UserinfoM=$this->MODEL('userinfo');
		if($_POST['username']){
			$data	=	array(
				'username'	=>  trim($_POST['username']),
				'password'	=>  trim($_POST['password']),
				'uid'		=>  intval($this->uid),
				'usertype'	=>  intval($this->usertype),
				'restname'	=>  '1'
			);
			if (!empty($data['username'])) {
				$UserinfoM->saveUserName($data);
				echo 1;die;
			}
		}
		$backurl	=	Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->yunset('headertitle',"修改用户名");
		$this->waptpl('setname');
	}
	function reward_list_action(){
		$this->yunset('headertitle',"兑换记录");

		$redeemM		=	$this->MODEL('redeem');

		$statisM		=	$this->MODEL('statis');

		$where['uid']	=	$this->uid;
		$where['usertype']    =	$this->usertype;
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"reward_list";
		$pageurl		=	Url('wap',$urlarr,'member');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('change',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

			$where['orderby']		=	'id,desc';
		    $where['limit']			=	$pages['limit'];

		    $List					=	$redeemM->getChangeList($where, array('utype'=>'wap'));
		    $this->yunset(array('sh' => $List['sh'], 'dh' => $List['dh'], 'wtg' => $List['wtg']));
			$this->yunset('rows',$List['list']);
		}

		$statis				=	$statisM->getInfo($this->uid,array('usertype'=>1));

		$statis[integral]	=	number_format($statis[integral]);

		$this->yunset('statis',$statis);

		if($_GET['back']){
			$backurl		=	Url('wap',array('c'=>'redeem'));
		}else{
			$backurl		=	Url('wap',array('c'=>'integral'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('reward_list');
	}

	function privacy_action(){
		$this->yunset('headertitle',"隐私设置");
		$ResumeM	=	$this -> MODEL('resume');
		$resume		=	$ResumeM -> getResumeInfo(array('uid'=>$this->uid));
		$this->yunset("resume",$resume);
		$backurl    =	Url('wap',array('c'=>'set'),'member');
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('privacy');
	}

	function up_action(){
		$logM		=	$this->MODEL('log');
		$ResumeM	=	$this -> MODEL('resume');
		if(intval($_GET['status'])){
		    $ResumeM -> upResumeInfo(array('uid'=>$this->uid),array('rData'=>array('status'=>intval($_GET['status']))));
			$ResumeM -> upInfo(array('uid'=>$this->uid),array('eData'=>array('status'=>intval($_GET['status']))));

			if(intval($_GET['status'])==2){
				$stext	=	'隐藏';
			}else if(intval($_GET['status'])==1){
				$stext	=	'公开';
			}
			$logM->addMemberLog($this->uid,$this->usertype,"设置简历为".$stext,2,2);

			echo 1;die;
		}
	}

	function del_action(){
		if($_GET['id']){
			$blackM	=	$this->MODEL('black');
			$del	=	(int)$_GET['id'];

			$nid	=	$blackM->delBlackList($del,array('where'=>array('c_uid'=>$this->uid)));

			if($nid){
				$this->waplayer_msg('删除成功！');
			}else{
				$this->waplayer_msg('删除失败！');
			}
 		}
	}
	function delall_action(){
		$blackM		=	$this->MODEL('black');
		$blackM->delBlackList('',array('where'=>array('c_uid'=>$this->uid)));
		$this ->MODEL('log')-> addMemberLog($this -> uid, $this->usertype,"清空公司黑名单信息",26,3);//会员日志
		$this->waplayer_msg('清空成功！');
	}
	function searchcom_action(){
		$blackM		=	$this->MODEL('black');

		$comM		=	$this->MODEL('company');

		$blacklist	=	$blackM->getBlackList(array('c_uid'=>$this->uid),array('field'=>'`p_uid`'));

		if($blacklist&&is_array($blacklist)){
			$uids	=	array();

			foreach($blacklist as $val){

				$uids[]		=	$val['p_uid'];
			}
			$where['uid']	=	array('notin',pylode(',',$uids));
		}
		$where['name']		=	array('like',$this->stringfilter(trim($_POST['name'])));

		$company			=	$comM->getChCompanyList($where,array('field'=>'`uid`,`name`'));

		if($company&&is_array($company)){
			$html="";
			foreach($company as $val){
				$html.="<li class='mui-table-view-cell mui-indexed-list-item mui-checkbox mui-left'><input type='checkbox' name='comname' value='".$val['uid']."' class='listCheckBox'  />".$val['name']."</li>";
			}
		}else{
			$html="";
		}

		echo $html;die;

	}
	function save_action(){
		if(is_array($_POST['buid'])&&$_POST['buid']){
			$comM			=	$this->MODEL('company');

			$blackM			=	$this->MODEL('black');

			$where['uid']	=	array('in',pylode(',',$_POST['buid']));

			$company		=	$comM->getChCompanyList($where,array('field'=>'`uid`,`name`'));

			foreach($company as $val){

				$blackM->addBlackone(array('p_uid'=>$val['uid'],'c_uid'=>$this->uid,"inputtime"=>time(),'usertype'=>'1','com_name'=>$val['name']));

			}
			$this->waplayer_msg('操作成功！');
		}else{
			$this->waplayer_msg('请选择屏蔽的公司！');
		}
	}
	/**
	 * 简历置顶条件检测
	 */
	function topCheck_action(){

	    $data  =  array(
	        'eid'  =>  $_POST['eid'],
	        'uid'  =>  $this->uid
	    );
	    $resumeM  =  $this -> MODEL('resume');

	    $return   =  $resumeM->topResumeCheck($data);

	    echo json_encode($return);die;
	}
    /**
    *购买简历置顶
    */
	function getserver_action()
	{
		$eid      =  $_GET['id'];
		$resumeM  =  $this -> MODEL('resume');

        if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
            $paytype['alipay']='1';
        }
        if($paytype){
            $this->yunset("paytype",$paytype);
        }
        $info=$resumeM -> getExpect(array('uid'=>$this->uid,'id'=>$eid));
        if($info['topdate']>1){
			$info['topdatetime']=$info['topdate'] - time();
			$info['topdate']=date("Y-m-d",$info['topdate']);
		}else{
			$info['topdate']='未设置';
		}
        $this->yunset("info",$info);
        $this->get_user();
		if($_GET['server']==1){
			$this->yunset('headertitle',"置顶简历");
		}

        $this->waptpl('getserver');
	}

	function getOrder_action(){

		if($_POST){

		    $M	=	$this->MODEL('userpay');

			$_POST['uid']		=	$this->uid;
			$_POST['usertype']	=	$this->usertype;
			$_POST['did']		=	$this->userdid;

			if($_POST['server']=='zdresume'){

				$return = $M->buyZdresume($_POST);
				$msg="简历置顶";
			}

			if($return['order']['order_id'] && $return['order']['id']){

				$dingdan	= $return['order']['order_id'];
				$price 		= $return['order']['order_price'];
				$id 		= $return['order']['id'];

				$this ->MODEL('log')-> addMemberLog($this -> uid, $this->usertype,$msg.",订单ID".$dingdan,88,2);//会员日志

				$_POST['dingdan']		=	$dingdan;
				$_POST['dingdanname']	=	$dingdan;
				$_POST['alimoney']		=	$price;
				$data['msg']			=	"下单成功，请付款！";
				//多种支付方式并存 进行选择
				if($_POST['paytype']=='alipay'){

					$url	=	$this->config['sy_weburl'].'/api/wapalipay/alipayto.php?dingdan='.$dingdan.'&dingdanname='.$dingdan.'&alimoney='.$price;

				}

 				echo json_encode(array(
 				    'error' => 0,
 				    'url'   => $url,
 				    'msg'   =>  '下单成功，请付款！'
 				));

			}else{
			    echo json_encode(array(
			        'error' => 1,
			        'msg' => '提交失败，请重新提交订单！'
			    ));
			}
 		}else{
 		    echo json_encode(array(
 		        'error' => 1,
 		        'msg' => '参数错误，请重试！'
 		    ));

		}
	}

	function rtop_action(){
		$id			=	$_POST['id'];
		$days		=	intval($id);
		if($days<1){echo 1;die;}
		if(intval($_POST['eid'])<1){echo 2;die;}

		$statisM  	= 	$this->MODEL('statis');

		$statis		= 	$statisM->getInfo($this->uid,array('usertype'=>1));

		$num		=	$days*$this->config['integral_resume_top'];

		if($num>$statis['pay']){
			echo 3;die;

		}else{
			//积分操作
			$resumeM		=	$this -> MODEL('resume');

			$result			=	$this -> MODEL('integral')->company_invtal($this->uid,$this -> usertype, $num,false,'简历置顶',true,1,'pay');
			if($result){
				$time		=	86400*$days;

				$topdate	=	$resumeM->getExpect(array('id'=>intval($_POST['eid']) , 'uid'=>$this->uid) , array('field'=>'`topdate`'));

				if($topdate['topdate']>=time()){
					$time	=	$topdate['topdate']+$time;
				}else{
					$time	=	time()+$time;
				}

				$resumeM->upInfo(array('id'=>intval($_POST['eid']) , 'uid'=>$this->uid) ,array('eData'=> array('top'=>1,'topdate'=>$time)));

				echo 4;die;

			}else{
				echo 5;die;

			}
		}
	}
 
	function delreward_action(){
		if($this->usertype!='1' || $this->uid==''){
			$this->waplayer_msg('登录超时！');
		}else{
			$redeemM	=	$this->MODEL('redeem');

			$return		=	$redeemM->delChange('',array('uid'=>$this->uid, 'id'=>(int)$_GET['id'], 'usertype'=>$this->usertype,'member'=>1));
			$this->waplayer_msg($return['msg']);
		}
	}
       //关注企业
    function atncom_action(){
		$this->yunset('headertitle',"关注的企业");
		$atnM		=	$this->MODEL('atn');
        if($_GET['id']){

			$return		=	$atnM->delAtnAll((int)$_GET['id'],array('cuid'=>intval($_GET['uid']),'sc_usertype'=>2,'uid'=>$this->uid,'usertype'=>$this->usertype));
			$this->waplayer_msg($return['msg']);
		}

		$where['uid']				=	$this->uid;
		$where['sc_usertype']		=	'2';
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url('wap',$urlarr,'member');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('atn',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

			$where['orderby']	=	'id,desc';

		    $where['limit']		=	$pages['limit'];

			$List				=	$atnM->getatnList($where,array('utype'=>'company','wap'=>1));

			$this->yunset("rows",$List);
		}

		$backurl	=	Url('wap',array('c'=>'jobcolumn'),'member');
		$this->yunset('backurl',$backurl);

		$this->get_user();
        $this->waptpl('atncom');
    }
    
    
	function pay_action(){
		$this->yunset('headertitle',"充值");
		$orderM		=	$this->MODEL('companyorder');

		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']	=	'1';
		}

		$banks		=	$orderM->getBankList(array('id'=>array('>', 0)));
		$this->yunset("banks",$banks);
		if($this->config['bank']=='1' &&  $banks){
			$paytype['bank']	=	'1';
		}

		if($paytype){
			if($_GET['id']){
				$order	=	$orderM->getInfo(array('id'=>(int)$_GET['id']));
				if(empty($order)){

					$this->ACT_msg_wap($_SERVER['HTTP_REFERER'],"订单不存在！",2,5);

				}elseif($order['order_state']!='1'){

					header("Location:index.php?c=paylog");

				}else{

					$this->yunset("order",$order);

				}
			}
			$remark		=	"姓名：\n联系电话：\n留言：";
			$this->yunset("paytype",$paytype);
			$this->yunset("remark",$remark);
		}else{
			$data['msg']=	"暂未开通手机支付，请移步至电脑端充值！";
			$data['url']=	$_SERVER['HTTP_REFERER'];
			$this->yunset("layer",$data);

		}

		$nopayorder		=	$orderM->getCompanyOrderNum(array('uid'=>$this->uid,'usertype' => $this->usertype, 'order_state'=>1));

		$this->yunset('nopayorder',$nopayorder);
		$this->yunset($this->MODEL('cache')->GetCache(array('integralclass')));
		$this->get_user();
		$this->waptpl('pay');
	}

	function payment_action(){
		$orderM		=	$this->MODEL('companyorder');

		if($this->config['alipay']=='1' &&  $this->config['alipaytype']=='1'){
			$paytype['alipay']	=	'1';
		}

		$banks		=	$orderM	-> getBankList(array('id'=>array('>', 0)));
		$this->yunset("banks",$banks);

		if($this->config['bank']=='1' &&  $banks){
			$paytype['bank']	=	'1';
		}

 		if($paytype){
			if($_GET['id']){
				$order	=	$orderM->getInfo(array('id'=>(int)$_GET['id']));
				if(empty($order)){
					$this->ACT_msg_wap($_SERVER['HTTP_REFERER'],"订单不存在！",2,5);
				}elseif($order['order_state']!='1'){
					header("Location:index.php?c=paylog");
				}else{
					$this->yunset("order",$order);
				}
			}

			$this->yunset("paytype",$paytype);
 			$this->yunset("js_def",4);

		}else{
			$data['msg']	=	"暂未开通手机支付，请移步至电脑端充值！";
			$data['url']	=	$_SERVER['HTTP_REFERER'];
			$this->yunset("layer",$data);
		}

		$this->get_user();
		$this->yunset('headertitle',"收银台");
		$this->yunset("banks",$banks);
		$this->waptpl('payment');
	}
	/**
	 * 生成订单
	 */
	function dingdan_action(){

		$data['price_int']	   =  intval($_POST['price_int']);
		$data['integralid']	   =  intval($_POST['integralid']);
		$data['uid']		   =  $this->uid;
		$data['did']		   =  $this->userdid;
		$data['usertype']	   =  $this->usertype;
		$data['paytype']	   =  $_POST['paytype'];
		$data['type']		   =  'wap';

		$orderM   =  $this->MODEL('companyorder');
		$return   =  $orderM->addComOrder($data);

		//支付宝支付，跳转到相应的链接
		if($return['errcode'] == 9 && !empty($return['url'])){

			header('Location: '.$return['url']);exit();
		}else{
			$this->yunset("layer",$return);
		}

		$backurl  =  Url('wap',array(),'member');
		$this->get_user();
		$this->yunset('backurl',$backurl);
		$this->yunset('headertitle',"订单");

		$this->waptpl('pay');
	}

	function paybank_action(){

	    $this->cookie->SetCookie("delay", "", time() - 60);

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
			'price'			=>	$_POST['price'],
	        'order_remark'	=>	trim($_POST['remark']),
	        'base'			=>	$_POST['preview']
	    );
	    //生成新订单，需要以下参数
	    if(empty($id)){
	        $data['price']	     =	$_POST['price'];
	        $data['price_int']	 =	$_POST['price_int'];
	        $data['integralid']	 =	$_POST['integralid'];
	    }else{

	        $data['price']	     =	$_POST['price'];
	    }
	    $orderM		=	$this	->	MODEL('companyorder');
	    $return		=	$orderM	->	payComOrderByBank($data);

	    $this		->	yunset("layer",$return);
	    $backurl	=	Url('wap',array(),'member');
	    $this		->	yunset('backurl',$backurl);
	    $this		->	get_user();
	    $this		->	waptpl('paylog');
	}

	function paylog_action(){
		$this->yunset('headertitle',"充值记录");
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);

		$orderM					=	$this->MODEL('companyorder');
		$where['uid']			=	$this->uid;
		$where['usertype']		=	$this->usertype;
		$where['order_price']	=	array('>', 0);

		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"paylog";
		$pageurl		=	Url('wap',$urlarr,'member');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_order',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

		    $where['orderby']		=	array('order_time,desc','order_state,asc');
		    $where['limit']			=	$pages['limit'];

		    $List					=	$orderM->getList($where,array('utype'=>'member','uid'=>$this->uid));
			$this->yunset("rows",$List);
		}

		$backurl	=	Url('wap',array('c'=>'finance'),'member');
		$this->getStatis("finance");
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('paylog');
	}
	function delpaylog_action(){
		$orderM		=	$this->MODEL('companyorder');

		$return		=	$orderM->cancelOrder(array('id'=>(int)$_GET['id'], 'uid'=>$this->uid));

		$this->waplayer_msg($return['msg']);
	}

	function consume_action(){
		$this->yunset('headertitle',"消费记录");
		include(CONFIG_PATH."db.data.php");
		$this->yunset("arr_data",$arr_data);

		$orderM				=	$this->MODEL('companyorder');
		$where['com_id']	=	$this->uid;
		$where['usertype']	=	$this->usertype;

		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	"consume";
		$pageurl		=	Url('wap',$urlarr,'member');

		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_pay',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){

        $where['orderby']	=	array('id,desc','pay_time,desc');
		    $where['limit']		=	$pages['limit'];

		    $List				=	$orderM->getPayList($where,array('utype'=>'paylog'));

			$this->yunset("rows",$List);
		}

		$this->getStatis('finance');

		if ($_GET['type']==1){
			$backurl	=	Url('wap',array('c'=>'user'),'member');
		}else{
			$backurl	=	Url('wap',array('c'=>'finance'),'member');
		}
		$this->yunset('backurl',$backurl);
		$this->get_user();
		$this->waptpl('consume');
	}

	function likejob_action(){
		$data		=	array(
			'id'=>(int)$_GET['id'],
			'uid'=>$this->uid,
		);
		$resumeM	=	$this->MODEL('resume');
		$list		=	$resumeM->likeJob($data);


		$this		->	yunset("job",$list);
		$this		->	yunset("js_def",2);
		$backurl	=	Url('wap',array('c'=>'resume','eid'=>$_GET['id']),'member');
		$this		->	yunset('backurl',$backurl);
		$this		->	yunset('headertitle',"匹配职位");
		$this		->	get_user();
		$this		->	waptpl('likejob');
	}

	function set_action(){
		$this->yunset('headertitle',"账户设置");
		$ResumeM	=	$this -> MODEL('resume');
		$UserinfoM	=	$this -> MODEL('userinfo');
		$resume		=	$ResumeM -> getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));


		$this->yunset("resume",$resume);
		$info	=	$UserinfoM -> getInfo(array('uid'=> $this->uid),array('setname'=>'1'));
		if($info['setname']=='1'){
			$this->yunset("setname",1);
		}
		$backurl	=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('set');
	}

	
	function sysnews_action(){
		$this->yunset('headertitle',"消息");
		
		//谁看了我
		$lookresumebM		=	$this -> MODEL('lookresume');
		$lrnum		=	$lookresumebM->getLookNum(array('uid'=>$this->uid,'isshow'=>0));
		$this->yunset('lrnum',$lrnum);
		//面试通知
		$JobM		=	$this -> MODEL('job');
		$yqrows		=	$JobM -> getYqmsInfo(array('uid'=>$this->uid,'orderby'=>'datetime,desc'));
		$this->yunset('yqrows',$yqrows);
		$wkyqnum	=	$JobM -> getYqmsNum(array('uid'=>$this->uid,'is_browse'=>'1'));
		$this->yunset("wkyqnum",$wkyqnum);
		//投递反馈
		$sqrows		=	$JobM -> getSqJobInfo(array('uid'=>$this->uid,'orderby'=>'endtime,desc'));
		if($sqrows){
			$sqrows['time_n']   =	$sqrows['endtime'] ? date('Y-m-d',$sqrows['endtime']) : date('Y-m-d',$sqrows['datetime']);
		}
		$this->yunset('sqrows',$sqrows);
		//私信
		$SysmsgM	=	$this -> MODEL('sysmsg');
		$sxrows		=	$SysmsgM -> getSysmsgInfo(array('fa_uid'=>$this->uid,'usertype'=>$this->usertype,'orderby'=>'ctime,desc'));
		$sxrows['content']  =  strip_tags($sxrows['content']);
		$this->yunset('sxrows',$sxrows);
		$sxrowsnum	=	$SysmsgM -> getSysmsgNum(array('fa_uid'=>$this->uid,'usertype'=>$this->usertype,'remind_status'=>'0'));
		$this->yunset('sxrowsnum',$sxrowsnum);
		//职位咨询回复
		$MsgM		=	$this -> MODEL('msg');
		$commsg		=	$MsgM -> getInfo(array('uid'=>$this->uid,'del_status'=>array('<>','1'),'reply'=>array('<>',''),'user_remind_status'=>'0','orderby'=>'reply_time,desc'));
		$this->yunset('commsg',$commsg);
		$commsgnum	=	$MsgM -> getMsgNum(array('uid'=>$this->uid,'reply'=>array('<>',''),'user_remind_status'=>'0'));
		$this->yunset('commsgnum',$commsgnum);
		$backurl	=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('sysnews');

	}
	function setAllRead_action(){

		$lookresumebM		=	$this -> MODEL('lookresume');
		$lookresumebM->upInfo(array('uid'=>$this->uid,'isshow'=>0),array('isshow'=>1));

		$jobM				=	$this -> MODEL('job');
		$jobM -> upYqms(array('uid'=>$this->uid,'is_browse'=>'1'),array('is_browse'=>'2'));

		$sysmsgM			=	$this -> MODEL('sysmsg');
		$sysmsgM->upInfo(array('fa_uid' => $this->uid, 'usertype' => $this->usertype, 'remind_status' => '0'), array('remind_status' => '1'));

		$msgM  				=	$this -> MODEL('msg');
		$msgM -> upInfo(array('uid'=>$this->uid,'user_remind_status'=>0),array('usertype'=>$this->usertype,'user_remind_status'=>1));

		echo 1;
	}
	//私信
	function sxnews_action(){
		$this->yunset('headertitle',"私信");
		$SysmsgM			=	$this -> MODEL('sysmsg');
		$where['fa_uid']	=	$this->uid;
		$where['usertype']	=	$this->usertype;
		$urlarr["c"] 		= 	$_GET["c"];
		$urlarr["page"] 	= 	"{{page}}";
		$pageurl			=	Url('wap',$urlarr,'member');
		$pageM				=	$this->MODEL('page');
		$pages				=	$pageM->pageList('sysmsg',$where,$pageurl,$_GET['page']);
		if($pages['total'] > 0){
	        $where['orderby']	 =	'id';
	        $where['limit']		 =	$pages['limit'];
	        $rows	=	$SysmsgM -> getList($where, array('from' => 'wap_member'));
	    }
		$this->yunset('rows',$rows);
		$backurl	=	Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('sxnews');
	}
	function sxnewsset_action(){
		$SysmsgM		=	$this -> MODEL('sysmsg');
    	$id				=	(int)$_POST['id'];
		$remind_status	=	(int)$_POST['remind_status'];
		if($id&&$remind_status){
			$nid		=	$SysmsgM -> upSysmsg(array('fa_uid'=>$this->uid,'id'=>$id,'remind_status'=>'0'),array('remind_status'=>$remind_status));
			$LogM		=	$this -> MODEL('log');
			$LogM->addMemberLog($this->uid,$this->usertype,"更改系统消息状态（ID:".$id."）",18,2);
		}
		$nid?$this->waplayer_msg("操作成功！"):$this->waplayer_msg("操作失败！");
    }
	function delsxnews_action(){
		$SysmsgM  =	 $this -> MODEL('sysmsg');
		if($_GET['id']){
			$return  =	 $SysmsgM->delSysmsg($_GET['id'],array('fa_uid'=>$this->uid));
			$LogM	 =	 $this -> MODEL('log');
			$LogM -> addMemberLog($this->uid,$this->usertype,"删除系统消息",18,3);
			$this -> waplayer_msg($return['msg']);
	    }
	}

	function commsg_action(){
		$this->yunset('headertitle',"求职咨询");
		$msgM  =  $this -> MODEL('msg');
		$where['uid']	=  $this -> uid;
	    $urlarr['c']	=	$_GET['c'];
		$urlarr['page']	=	'{{page}}';
	    $pageurl		=	Url('wap',$urlarr,'member');

	    $pageM			=	$this   ->  MODEL('page');
	    $pages			=	$pageM  ->  pageList('msg',$where,$pageurl,$_GET['page']);

	    if($pages['total'] > 0){
	        $where['orderby']		=	'id';
	        $where['limit']			=	$pages['limit'];

	        $List   =  $msgM  ->  getList($where);

			$this -> yunset("rows",$List['list']);
	    }
		//提醒处理
		$msgM -> upInfo(array('uid'=>$this->uid,'user_remind_status'=>0),array('user_remind_status'=>1));

		$backurl=Url('wap',array('c'=>'sysnews'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('commsg');
	}

	function delcommsg_action(){
		$id   		=  $_GET['id'];
	    $msgM		=  $this->MODEL('msg');
	    $return		=  $msgM -> delInfo($id);

	    $this -> waplayer_msg($return['msg']);
	}
	function finance_action(){
		$resumeM	=	$this->MODEL('resume');
		$this->yunset('headertitle',"财务管理");

		$resume		=	$resumeM->getResumeInfo(array('uid'=>$this->uid),array('logo'=>1));
		$packM		=	$this->MODEL('pack');

		$where['uid']	=	$this->uid;
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$urlarr['c']	=	$_GET['c'];
		$pageurl		=	Url('wap',$urlarr,'member');

		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('company_job_sharelog',$where,$pageurl,$_GET['page']);

		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){

			$where['orderby']		=	'time,desc';
		    $where['limit']			=	$pages['limit'];

		    $List					=	$packM->getShareLogList($where);
			$this->yunset("rows",$List);
		}

		$reg_url	=	Url('wap',array('c'=>'register','uid'=>$this->uid));
		$backurl	=	Url('wap',array(),'member');

		$this->getStatis('finance');
		$this->yunset("resume",$resume);
		$this->yunset('reg_url', $reg_url);
		$this->yunset('backurl',$backurl);
		$this->waptpl('finance');
	}
	function integral_action(){

		$integralM	=	$this->MODEL('integral');
		$resumeM	=	$this->MODEL('resume');

		$statusList	=	$integralM	->	integralMission(array('type'=>'member','uid'=>$this->uid,'usertype'=>$this->usertype));

		$expectnum	=	$resumeM->getExpectNum(array('uid'=>$this->uid));

		$reg_url 	= 	Url('wap',array('c'=>'register','uid'=>$this->uid));

		$this->getStatis();

		$this->yunset("expectnum",$expectnum);
		$this->yunset("statusList",$statusList);
		$this->yunset('reg_url', $reg_url);

		if($_GET['back']){
			$backurl=Url('wap',array('c'=>'redeem'));
		}else{
			$backurl=Url('wap',array('c'=>'finance'),'member');
		}
		$this->yunset('headertitle',$this->config['integral_pricename']."管理");
		$this->yunset('backurl',$backurl);
		$this->waptpl('integral');
	}

	function integral_reduce_action(){
		$this->yunset('headertitle',"消费规则");
		$backurl=Url('wap',array('c'=>'integral'),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('integral_reduce');
	}

	function blacklist_action(){
		$this->yunset('headertitle',"屏蔽企业");
		$urlarr=array("c"=>$_GET['c'],"page"=>"{{page}}");
		$pageurl=Url('wap',$urlarr,'member');
		$this->get_page("blacklist","`c_uid`='".$this->uid."' and `usertype`='1' order by `id` desc",$pageurl,"10");
		$backurl=Url('wap',array('c'=>'privacy'),'member');
		$this->yunset('backurl',$backurl);

		$this->waptpl('blacklist');
	}
	function blacklistadd_action(){

		$this->yunset('headertitle',"添加屏蔽");
		$backurl=Url('wap',array('c'=>'blacklist'),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('blacklistadd');
	}
	function jobcolumn_action(){
		$JobM			=	$this -> MODEL('job');
		$AtnM			=	$this -> MODEL('atn');
		$LookresumeM	=	$this -> MODEL('lookresume');
		//面试通知
		$invitenum		=	$JobM -> getYqmsNum(array('uid'=>$this->uid));
		$this->yunset('invitenum',$invitenum);
		//申请的职位
		$sqnum			=	$JobM -> getSqJobNum(array('uid'=>$this->uid));
		$this->yunset('sqnum',$sqnum);
		//收藏的职位
		$collectnum		=	$JobM -> getFavJobNum(array('uid'=>$this->uid));
		$this->yunset('collectnum',$collectnum);
		$where['uid']				=	$this->uid;
		$where['sc_usertype']		=	'2';
		$atncomnum		=	$AtnM -> getAtnNum($where);
		$atnltnum		=	$AtnM -> getAtnNum(array('uid'=>$this->uid,'sc_usertype'=>'3'));
		$atnnum			=	$atncomnum+$atnltnum;
		$this->yunset('atnnum',$atnnum);

		//职位浏览记录
		$lookjobnum		=	$JobM -> getLookJobNum(array('uid'=>$this->uid,'status'=>'0'));
		$this->yunset('lookjobnum',$lookjobnum);

		//谁看过我的简历
        $looknum		=	$LookresumeM -> getLookNum(array('uid'=>$this->uid,'status'=>0));
		$this->yunset('looknum',$looknum);
		$this->yunset('headertitle',"职位管理");
		$wkyqnum		=	$JobM -> getYqmsNum(array('uid'=>$this->uid,'is_browse'=>'1'));
		$this->yunset("wkyqnum",$wkyqnum);
		$wlooknum		=	$JobM -> getLookJobNum(array('uid'=>$this->uid,'status'=>'0','datetime'=>array('<',time())));
		$this->yunset("wlooknum",$wlooknum);
		$backurl		=	Url('wap',array(),'member');
		$this->yunset('backurl',$backurl);
		$this->waptpl('job');
	}

	function getStatis($type=''){
		$statisM  	= 	$this->MODEL('statis');

		$statis		= 	$statisM->getInfo($this->uid,array('usertype'=>1));

		if($type=='finance'){
			$orderM		=	$this->MODEL('companyorder');
			$orders		=	$orderM->getPayList(array('com_id'=>$this->uid, 'usertype' =>$this->usertype, 'type'=>'1'),array('field'=>'`order_price`'));
			foreach($orders as $key=>$val){
				$allprice	+=	$val['order_price'];
			}
			if($allprice<0){
				$statis['allprice']		=	number_format(str_replace('-','', $allprice));
			}else{
				$statis['allprice']		=	'0';
			}

			$statis['freeze'] = sprintf("%.2f", $statis['freeze']);
		}

		if($type=='loglist'){
			$statis['freeze'] = sprintf("%.2f", $statis['freeze']);
		}

		$this->yunset("statis",$statis);
	}

	function transfer_action(){


		$this->yunset('headertitle',"账户分离");
		$this->waptpl('transfer');
	}

	function transfersave_action(){

		if($_POST){

			$transferM	=	$this -> MODEL('transfer');

			$return		=	$transferM -> setTransfer($this->uid,$_POST);

			if($return['errcode'] == '1')
			{
				$this->cookie->unset_cookie();
			}
			echo json_encode($return);

		}
	}
	function getIntroduceInfo_action(){
		include PLUS_PATH."introduce.cache.php";

		if($_POST['introduceid']){

			$id			=	intval($_POST['introduceid']);

			foreach($introduce_index as $key=>$val){
				if($val==$id){
					unset($introduce_index[$key]);
				}
			}
		}

		$keyid	=	array_rand($introduce_index);

		$nid	=	$introduce_index[$keyid];
		if(!empty($nid)){
			$html	=	"<div class='yun_my_introduce_sl_list'><span class='yun_my_introduce_sl'>".$introduce_name[$nid]."</span><a href='javascript:void(0)' onclick='nextintroduce()' class='yun_my_introduce_hyh'>换一个</a><div class='yun_my_introduce_sl_show' >".$introduce_content[$nid]."</div><input type='hidden' id='introduce' value='".$nid."'/></div>";

		}else{
			$html	=	"<div class='yun_my_introduce_sl_list'><span class='yun_my_introduce_sl'></span><a href='javascript:void(0)' onclick='nextintroduce()' class='yun_my_introduce_hyh'>换一个</a><div class='yun_my_introduce_sl_show' >没有示例</div><input type='hidden' id='introduce' value=''/></div>";

		}

		echo $html;die;

	}
}
?>