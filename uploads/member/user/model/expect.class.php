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
class expect_controller extends user
{

    // 创建标准简历第一步
    function index_action()
    {
        $eid = (int) $_GET['e'];
        
        $this->public_action();
        
        $resumeM  =  $this->MODEL('resume');
        
        $return   =  $resumeM->getInfo(array(
			'uid'=>$this -> uid,
            'eid' => $eid,
            'tb' => 'all',
            'needCache' => 1
        ));

        $setarr = array(
            'uid' => $this->uid,
            'row' => $return['expect'],
            'edu' => $return['edu'],
            'other' => $return['other'],
            'project' => $return['project'],
            'skill' => $return['skill'],
            'training' => $return['training'],
            'work' => $return['work'],
        );
        $this->yunset($return['cache']);
        $this->yunset($setarr);
        if(empty($return['cache']['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($return['cache']['job_type'])){
            $this   ->  yunset('jionly',1);
        }
        $resume_row = $resumeM->getUserResumeInfo(array(
			'uid' => $this -> uid,
            'eid' => $eid
        ));
        
        $this->yunset("resume_row", $resume_row);
        
        $this->yunset("layerv", 5);
        
        $this->yunset("js_def", 2);
        
        $tplM = $this->MODEL('tpl');
        
        $rows = $tplM->getResumetplList(array(
            'status' => 1,
            'orderby' => 'id,asc'
        ));
        
        $statisM = $this->MODEL('statis');
        
        $statis = $statisM->getInfo($this->uid, array(
            'usertype' => '1',
            'field' => 'tpl,paytpls'
        ));
        
        if ($statis['paytpls']) {
            
            $paytpls = @explode(',', $statis['paytpls']);
            
            $this->yunset("paytpls", $paytpls);
        }
        
        $ResumeList = $resumeM->getSimpleList(array(
            'uid' => $this->uid
        ), array(
            'field' => 'id,doc,name'
        ));
        
        $this->yunset("ResumeList", $ResumeList);

		$show =  $resumeM->getResumeShowList(array('uid'=>$this->uid,'eid'=>$eid));
		
		$this->yunset("show",$show);
		
        $this->yunset("statis", $statis);
        
        $this->yunset("rows", $rows);
        
        $this->user_tpl('expect');
    }

    /**
     * 简历创建页面
     */
    function add_action()
    {
        
        $resumeM    =   $this       ->  MODEL('resume');
        
        $return     =   $resumeM    ->  addResumePage(array('uid'=>$this->uid), 'pc');
        
        if($return['error']['err']!=0){

            $this->ACT_msg($return['error']['url'], $return['error']['msg']);
        }

        $cacheArr   =   $this->MODEL('cache') -> GetCache(array('city','job'));
        if(empty($cacheArr['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($cacheArr['job_type'])){
            $this   ->  yunset('jionly',1);
        }

        $this   ->  yunset($return['setarr']);
        
        $this   ->  user_tpl('expect_add');
    }

    /**
     * 创建简历保存
     */
    function addsave_action()
    {
        
        $resumeM = $this->MODEL('resume');
        if ($_POST['submit']) {
            $rinfo = $resumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'r_status,uid'));
            $_POST = $this->post_trim($_POST);
            if($rinfo){
              $rstatus  =   $rinfo['r_status'];
            }else{
              $rstatus  =   $this->config['user_state'];
            }
            
            $rData = array(
                'name' => $_POST['uname'],
                'sex' => $_POST['sex'],
                'birthday' => $_POST['birthday'],
                'living' => $_POST['living'],
                'edu' => $_POST['edu'],
                'exp' => $_POST['exp'],
                'email' => $_POST['email'],
                'telphone' => $_POST['telphone']
            );
            
            $eData = array(
                'lastupdate' => time(),
                'height_status' => 0,
                'uid' => $this->uid,
                'ctime' => time(),
                'name' => $_POST['name'],
                'hy' => $_POST['hy'],
                'job_classid' => $_POST['job_class'],
                'city_classid' => $_POST['city_class'],
                'minsalary' => $_POST['minsalary'],
                'maxsalary' => $_POST['maxsalary'],
                'type' => $_POST['type'],
                'report' => $_POST['report'],
                'jobstatus' => $_POST['jobstatus'],
                'state' => $rstatus==1?$this->config['resume_status']:0,
                'edu' => $_POST['edu'],
                'exp' => $_POST['exp'],
                'sex' => $_POST['sex'],
                'birthday' => $_POST['birthday'],
                'source' => 1,
                'r_status' => $rstatus
            );
            /**
             * ************************简历是否必填工作经历************************************************
             */
            $workData = array();
            if ($this->config['resume_create_exp'] == '1'&&$_POST['iscreateexp']!='2') {
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
            /**
             * ************************简历是否必填教育经历************************************************
             */
            $eduData = array();
            if ($this->config['resume_create_edu'] == '1'&&$_POST['iscreateedu']!='2') {
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
            /**
             * ************************简历是否必填项目经历************************************************
             */
            $proData = array();
            if ($this->config['resume_create_project'] == '1') {
                for ($i=0; $i < count($_POST['proname']); $i++) { 
                    $proData[$i]    =   array(
                        'uid'       =>  $this->uid,
                        'name'      =>  $_POST['proname'][$i],
                        'sdate'     =>  strtotime($_POST['edusdate'][$i]),
                        'edate'     =>  strtotime($_POST['eduedate'][$i]),
                        'title'     =>  $_POST['protitle'][$i],
                        'content'   =>  $_POST['procontent'][$i]
                    );
                }
                
            }
            
            $addArr = array(
                'uid'		=> $this->uid,
                'rData'		=> $rData,
                'eData'		=> $eData,
                'workData'	=> $workData,
                'eduData'	=> $eduData,
                'proData'	=> $proData,
                'utype'		=> 'user'
            );
            
            
            if(!$rinfo['uid']){
				$userinfoM    =   $this->MODEL("userinfo");
				$userinfoM -> activUser($this -> uid,1);
			}
            $return = $resumeM->addInfo($addArr);
            
            if ($this->config['resume_status']) {
                
                $this->ACT_layer_msg('简历创建成功，继续完善！', 9, 'index.php?c=expect&act=success&e=' . $return['id']);
            } else {
                
                $this->ACT_layer_msg('简历创建成功，等待审核，您可以继续完善！', 9, 'index.php?c=expect&act=success&e=' . $return['id']);
            }
        }
    }

    function saveexpect_action()
    {
        $resumeM = $this->MODEL('resume');
        
        $eid = (int) $_POST['eid'];
        
        if ($_POST['submit']) {
            
            if ($eid) {
                
                $expectDate = array(
                    'name' => $_POST['name'],
                    'job_classid' => $_POST['job_classid'],
                    'city_classid' => $_POST['city_classid'],
                    'minsalary' => $_POST['minsalary'],
                    'type' => $_POST['type'],
                    'report' => $_POST['report'],
                    'jobstatus' => $_POST['jobstatus'],
                    'lastupdate' => time()
                );
                
                foreach ($expectDate as $k => $v) {
                    if (empty($v)) {
                        echo - 1;
                        die();
                    }
                }
                $expectDate['hy'] = $_POST['hy'];
                $expectDate['maxsalary'] = $_POST['maxsalary'];
                
                $return = $resumeM->upInfo(array(
					'uid' => $this ->uid,
                    'id' => $eid
                ), array(
                    'eData' => $expectDate,
                    'utype' => 'user'
                ));
                if ($return['id']) {
					
                    
                    $expect = $resumeM->getExpect(array('id' => $eid,'uid'=>$this ->uid),array('needCache'=>1));
                    
                    echo json_encode($expect);die();
                } else {
                    
                    echo 0;die();
                }
            } else {
                
                echo 0;die();
            }
        }
    }

    function success_action()
    {
        $ResumeM	=	$this -> MODEL('resume');
		
		$JobM		=	$this -> MODEL('job');
        
        $uid 		= 	$this->uid;
        
        $eid 		= 	(int) $_GET['e'];
        
        if ($eid) {
            
            $this->public_action();
            
            $this->yunset('id', $eid);
            
            $resumewhere['uid'] = $uid;
            
            $info	=	$ResumeM -> getResumeInfo($resumewhere);
            
            $expectwhere['uid']	=	$uid;
            
            $expectwhere['id']	=	$eid;
            
            $expect		=	$ResumeM -> getExpect($expectwhere, array(
                'field' => 'integrity,job_classid'
            ));
            
            $userewhere['eid']	=	$eid;
			$userewhere['uid']	=	$this->uid;
            
            $resume	=	$ResumeM -> getUserResumeInfo($userewhere);
            
            $expectnum  =  $ResumeM	->	getExpectNum(array('uid'=>$this->uid));
            
            $this->yunset("expectnum", $expectnum);
            
            $this->yunset("resume", $resume);
            
            $this->yunset("expect", $expect);
            
            $this->yunset("info", $info);
			
			$jobwhere['state']				=	1;
			
			$jobwhere['sdate']				=	array('<',time());
			
			$jobwhere['r_status']			=	array('<>',2);
			
			$jobwhere['status']				=	array('<>',1);
			
			$jobwhere['PHPYUNBTWSTART_A']	=	'';
			
			$jobwhere['job1']				=	array('in',$expect['job_classid']);
			
			$jobwhere['job1_son']			=	array('in',$expect['job_classid'],'or');
			
			$jobwhere['job_post']			=	array('in',$expect['job_classid'],'or');
			
			$jobwhere['PHPYUNBTWEND_A']		=	'';
			
			$job	=	$JobM -> getList($jobwhere);
			
			$this->yunset("job", $job);
            
			$this->user_tpl('expect_success');
       
	    } else {
            
            header("Location:index.php?c=expect&act=add");
        }
    }

    function save_resume_name_action()
    {
        $ResumeM = $this->MODEL('resume');
        
        $uid = $this->uid;
        
        $eid = $_POST['eid'];
        
        if (! is_numeric($eid)) {
            
            $this->ACT_layer_msg("简历编号格式不正确！", 8, $_SERVER['HTTP_REFERER'], 2, 0);
            die();
        }
        
        if (CheckRegUser($_POST['name']) == false) {
            
            $this->ACT_layer_msg("简历名称包含特殊字符！", 8, $_SERVER['HTTP_REFERER'], 2, 0);
            die();
        }
        
        $where['id'] = $eid;
        
        $data = array(
            
            'name' => $_POST['name']
        
        );
        
        $IsSuccess = $ResumeM->upInfo($where, array('eData'=>$data));
        
        $ResumeM->upResumeInfo(array(
            'uid' => $this->uid
        ), array(
            'rData' => array(
                'lastupdate' => time()
            )
        ));
        
        if ($IsSuccess) {
            
            $this->ACT_layer_msg("修改成功！", 9, $_SERVER['HTTP_REFERER'], 2, 0);
            die();
        } else {
            
            $this->ACT_layer_msg("修改失败！", 8, $_SERVER['HTTP_REFERER'], 2, 0);
            die();
        }
    }

    /**
     * 保存自我评价
     */
    function savedescription_action()
    {
        $resumeM = $this->MODEL('resume');
        
        $eid = $_POST['eid'];
        
        if (! is_numeric($eid)) {
            
            $this->ACT_layer_msg("简历编号格式不正确！", 8, $_SERVER['HTTP_REFERER'], 2, 0);
            die();
        }
        
        $nid = $resumeM->upResumeInfo(array(
            'uid' => $this->uid
        ), array(
            'rData' => array(
                'description' => $_POST['description']
            )
        ));
        
        if ($nid) {
            
            echo 1;
            die();
        } else {
            
            echo 0;
            die();
        }
    }

    function saveall_action()
    {
        $ResumeM    =   $this->MODEL('resume');
        $uploadM    =   $this->MODEL("upload");
        
        $_POST      =   $this->post_trim($_POST);
        
        $eid        =   intval($_POST['eid']);
        
        if ($_POST['submit']) {
            
            unset($_POST['submit']);
            
            if (! is_numeric($eid)) {
                
                $this->ACT_layer_msg("简历编号格式不正确！", 8, $_SERVER['HTTP_REFERER'], 2, 0);
                die();
            }
            if($_FILES['skillfile']){

                foreach($_FILES['skillfile'] as $fk=>$fv){

                    foreach($fv as $fkk=>$fvv){

                       $fdata['file'][$fkk][$fk] =$fvv;

                    }
                } 
            }
            $table = "resume_" . $_POST['table'];
            
            if ($_POST['table'] == 'description') {
                
                if ($_POST['description'] != '') {
                    
                    $num = 10;
                    
                    if ($_POST['tag']) {
                        $tag = array_unique(@explode(',', $_POST['tag']));
                        
                        foreach ($tag as $value) {
                            
                            $tagLen = mb_strlen($value);
                            
                            if ($tagLen >= 2 && $tagLen <= 8) {
                                
                                $tagList[] = $value;
                            }
                            if (count($tagList) >= 5) {
                                break;
                            }
                        }
                        $tagStr = implode(',', $tagList);
                    }
                    $resumedata     =   array(
                        'description'   =>  $_POST['description'],
                        'tag'           =>  $tagStr,
                        'lastupdate'    =>  time()
                    );
                    $nid = $ResumeM->upResumeInfo(array('uid'=>$this->uid), array('rData' => $resumedata));
                }
            } else {
                
                for ($i = 0; $i < count($_POST['name']); $i ++) { // 判断name是否为空
                    
                    if ($_POST['name'][$i] == '') {
                        
                        $wname[] = $_POST['nameid'][$i];
                    }
                    if ($_POST['content'][$i] == '') {
                        
                        $wcontent[] = $_POST['contentid'][$i];
                    }
                    if ($_POST['sdate'][$i]) { // 选择了时间，判断先后顺序
                        
                        if ($_POST['totoday'][$i] == 2) {
                            
                            if (strtotime($_POST['sdate'][$i]) > time()) { // 选了至今，但开始时间大于现在时间的
                                
                                $rtime[]        =   $_POST['timeid'][$i];
                            }
                        } else {
                            if ($_POST['edate'][$i] == '') {
                                
                                $wedate[]       =   $_POST['edateid'][$i];
                            } else {
                                if (strtotime($_POST['sdate'][$i]) > strtotime($_POST['edate'][$i])) { // 开始时间大于结束时间的
                                    $rtime[]    = $_POST['timeid'][$i];
                                }
                            }
                        }
                    }
					if($_POST['ing'][$i]==	''){
						
						$wing[] = $_POST['ingid'][$i];
					}
                    if($_POST['title'][$i]==  '' && $_POST['table']=='work'){
                        
                        $wtitle[] = $_POST['titleid'][$i];
                    }
                    if($_POST['education'][$i]==  '' && $_POST['table']=='edu'){
                        
                        $wedu[] = $_POST['eduid'][$i];
                    }
                    
					
                    $tables =   array('work','edu','project','training');

                    if (in_array($_POST['table'], $tables)) {
                        
                        if ($_POST['sdate'][$i] == '') { // 判断没有选择开始时间的
                            
                            $wsdate[]       =   $_POST['sdateid'][$i];
                            
                            if ($_POST['totoday'][$i] == 2) { // 判断是否至今
                                
                                $rtime[]    =   $_POST['timeid'][$i];
                            }
                        }
                    }
                }
                if (! empty($wname)) { // name为空的判断，基本是弹出框第一项
                    
                    $wrong      =   1;
                    $checkname  =   pylode('-', $wname);
                    echo '<input id="namenum" type="hidden" value="' . $checkname . '">';
                }
                if (! empty($wsdate)) { // 判断没有选择开始时间的
                    
                    $wrong      =   1;
                    $checksdate =   pylode('-', $wsdate);
                    echo '<input id="sdatenum" type="hidden" value="' . $checksdate . '">';
                }
                if (! empty($wedate)) { // 判断没有选择结束时间的
                    
                    $wrong      =   1;
                    $checkedate =   pylode('-', $wedate);
                    echo '<input id="edatenum" type="hidden" value="' . $checkedate . '">';
                }
                if (! empty($rtime)) { // 选择了时间，判断先后顺序
                    
                    $wrong      =   1;
                    $checktime  =   pylode('-', $rtime);
                    echo '<input id="timenum" type="hidden" value="' . $checktime . '">';
                }
				 if (! empty($wing) && $_POST['table'] == 'skill') { 
                    
                    $wrong      =   1;
                    $checking  =   pylode('-', $wing);
                    echo '<input id="ingnum" type="hidden" value="' . $checking . '">';
                }
                if (! empty($wcontent) && $_POST['table'] == 'other') { 
                    
                    $wrong      =   1;
                    $checkcontent  =   pylode('-', $wcontent);
                    echo '<input id="contentnum" type="hidden" value="' . $checkcontent . '">';
                }

                if (! empty($wtitle) && $_POST['table'] == 'work') { 
                    
                    $wrong      =   1;
                    $checktitle =   pylode('-', $wtitle);
                    echo '<input id="titlenum" type="hidden" value="' . $checktitle . '">';
                }
                if (! empty($wedu) && $_POST['table'] == 'edu') { 
                    
                    $wrong      =   1;
                    $checkedu   =   pylode('-', $wedu);
                    echo '<input id="edunum" type="hidden" value="' . $checkedu . '">';
                }
                
                if ($wrong == 1) { // 有输入错误的，返回表名，让tanchukuang这个iframe识别是哪个弹出框需要显示提示。
                    
                    echo '<input id="wrong" type="hidden" value="' . $_POST['table'] . '">';exit();
                }
                for ($i = 0; $i < count($_POST['name']); $i ++) {
                    // 公用
                    if ($_POST['totoday'][$i] == 2) {
                        
                        $_POST['edate'][$i] == '';
                    }
                    $value = array(
                        'eid'       => $eid,
                        'uid'       => $this->uid,
                        'name'      => $_POST['name'][$i],
                        'title'     => $_POST['title'][$i],
                        'content'   => $_POST['content'][$i],
                        'sdate'     => strtotime($_POST['sdate'][$i]),
                        'edate'     => strtotime($_POST['edate'][$i])
                    );
                    if ($_POST['name'][$i] == '' && $_POST['title'][$i] == '' && $_POST['content'][$i] == '' && $_POST['sdate'][$i] == '' && $_POST['edate'][$i] == '') {

                        $value  = 1;
                    }
                    if ($_POST['table'] == 'training') {
                        
                        $num    = 5;
                    }
                    if ($_POST['table'] == 'edu') {
                        
                        $num    = 4;
                        $value  = array(
                            'eid'       => $eid,
                            'uid'       => $this->uid,
                            'name'      => $_POST['name'][$i],
                            'title'     => $_POST['title'][$i],
                            'specialty' => $_POST['specialty'][$i],
                            'education' => $_POST['education'][$i],
                            'sdate'     => strtotime($_POST['sdate'][$i]),
                            'edate'     => strtotime($_POST['edate'][$i])
                        );
                        
                        if ($_POST['name'][$i] == '' && $_POST['title'][$i] == '' && $_POST['sdate'][$i] == '' && $_POST['edate'][$i] == ''  && $_POST['education'][$i] == '') {
                            
                            $tvalue = 1;
                        }
                    }
                    
                    if ($_POST['table'] == 'skill') {
                        
                        $num = 6;

                        if(isset($fdata['file']) && $fdata['file'][$i]['tmp_name']!=''){
                            // pc端上传
                            $upArr    =  array(
                                'file'  =>  $fdata['file'][$i],
                                'dir'   =>  'user'
                            );
                            $pic    =  $uploadM->newUpload($upArr);

                            if (!empty($pic['msg'])){
                                
                                $this->ACT_layer_msg($pic['msg'], 8);
                                
                            }elseif (!empty($pic['picurl'])){
                                
                                $picurl  =  $pic['picurl'];
                            }
                        }

                        $value = array(
                            'eid'       => $eid,
                            'uid'       => $this->uid,
                            'name'      => $_POST['name'][$i],
							'ing'      => $_POST['ing'][$i],
                            'longtime'  => $_POST['longtime'][$i]
                        
                        );
						if(isset($picurl)){
                            $value['pic']   =   $picurl;
                        }
                        unset($picurl);
                        if ($_POST['name'][$i] == '' && $fdata['file'][$i]['tmp_name'] == '' && $_POST['longtime'][$i] == '') {
                            
                            $tvalue = 1;
                        }
                    }
                    
                    if ($_POST['table'] == 'project') {
                        
                        $num = 7;
                    }
                    if ($_POST['table'] == 'other') {
                        
                        $num = 9;
                        
                        $value = array(
                            'eid'       => $eid,
                            'uid'       => $this->uid,
                            'name'      => $_POST['name'][$i],
                            'content'   => $_POST['content'][$i]
                        );
                        if ($_POST['name'][$i] == '' && $_POST['content'][$i] == '') {
                            
                            $tvalue = 1;
                        }
                    }
                    
                    if ($_POST['id'][$i]) {
                        
                        if ($tvalue == 1) {
                            
                            $ResumeM->delFb($_POST['table'], array('uid'=>$this -> uid,'id'=>$_POST['id'][$i],'eid'=>$eid), array('utype' =>'user'));
                        } else {
                            $nid = $ResumeM->upResumeTable($table, array('uid' => $this ->uid,'id'=>$_POST['id'][$i]), $value);
                            
                            $ResumeM->upResumeInfo(array('uid' => $this->uid ), array('rData' => array('lastupdate' => time())));
                        }
                    } else {
                        if ($tvalue != 1 && $_POST['usedid'][$i] == '') {
                            
                            $nid    = $ResumeM->upResumeTable($table, '', $value);
                            
                            $nids[] = $nid;
                            
                            if ($_POST['timeid'][$i] == (substr($_POST['table'], 0, 1) . 'h')) {
                                
                                $delids[] = $_POST['timeid'][$i];
                            } else {
                                
                                $delids[] = substr($_POST['timeid'][$i], 2);
                            }
                            
                            $ResumeM->upResumeInfo(array('uid' => $this->uid), array('rData' => array('lastupdate' => time())));
                            
                            if ($_POST['table'] == 'work') {
                                
                                $udata['work'] = array('+',1);
                            } elseif ($_POST['table'] == 'skill') {
                                
                                $udata['skill'] = array('+',1);
                                
                            } elseif ($_POST['table'] == 'project') {
                                
                                $udata['project'] = array('+',1);

                            } elseif ($_POST['table'] == 'edu') {
                                
                                $udata['edu'] = array('+',1);
                                
                            } elseif ($_POST['table'] == 'training') {
                                
                                $udata['training'] = array('+',1);

                            } elseif ($_POST['table'] == 'cert') {
                                
                                $udata['cert'] = array('+',1);
                                
                            } elseif ($_POST['table'] == 'other') {
                                
                                $udata['other'] = array('+',1);
                            }
                            $ResumeM->upUserResume($udata, array('eid'=>$eid,'uid'=>$this->uid));
                            
                        } elseif ($_POST['usedid'][$i] != '') {
                            
                            $nid = 1;
                        }
                    }
                    
                    // 统计工作时长
                    if ($table == 'resume_work') {
                        
                        $num        =   3;
                        $workList   =   $ResumeM->getResumeWorks(array('eid'=>$eid,'uid'=>$this->uid), 'sdate,edate');
                        
                        $whour      =   0;
                        $hour       =   array();
                        
                        foreach ($workList as $value) {
                            // 计算每份工作时长(按月)
                            if ($value['edate']) {
                                
                                $workTime = ceil(($value['edate'] - $value['sdate']) / (30 * 86400));
                            } else {
                                $workTime = ceil((time() - $value['sdate']) / (30 * 86400));
                            }
                            $hour[] = $workTime;
                            $whour += $workTime;
                        }
                        // 更新当前简历时长字段
                        $avghour = ceil($whour / count($hour));
                        
                        $texpectwhere['id'] = $eid;
                        
                        $texpectdata = array(
                            'whour'     => $whour,
                            'avghour'   => $avghour
                        );
                        $ResumeM->upInfo(array('id'=>$eid), array('eData'=>$texpectdata));
                    }
                    if ($value != 1) { // 用来判断点过保存，再次点开时把有值的留下来，而没有值的移除
                        
                        $little[] = $_POST['timeid'][$i];
                    }
                }
                if (! empty($nids)) {
                    $newids = pylode('-', $nids);
                    echo '<input id="newids" type="hidden" value="' . $newids . '">';
                }
                if (! empty($delids)) {
                    $dels = pylode('-', $delids);
                    echo '<input id="dels" type="hidden" value="' . $dels . '">';
                }
            }
            if (! empty($little)) { // 点了添加且内容为空时保存，隐藏小图标
                $littleid = pylode('-', $little);
                echo '<input id="littlenum" type="hidden" value="' . $littleid . '">';
            }
            if ($nid) {
                $resume         =  $ResumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'`integrity`'));
                $resume_row     =  $ResumeM->getUserResumeInfo(array('eid'=>$eid,'uid'=>$this->uid));
                
                echo '<input id="resumeAll" type="hidden" value="' . $num . '"><input id="integrity" type="hidden" value="' . $resume['integrity'] . '"><input id="upnum" type="hidden" value="' . $resume_row[$_POST['table']] . '">';
			} else {
                echo '<input id="resumeAll" type="hidden" value="2">';
            }
        }
    }
    // 验证手机号
    function regmoblie_action()
    {
        $userinfoM  =  $this->MODEL('userinfo');
        
        $return     =  $userinfoM -> addMemberCheck(array('moblie'=>$_POST['telphone']),$this->uid);
        
        if ($return['msg']) {
            
            echo 1;die;
        } else {
            
            echo 0;die;
        }
    }
	// 验证邮箱
    function regemail_action()
    {
        $userinfoM  =  $this->MODEL('userinfo');
        
        $return     =  $userinfoM -> addMemberCheck(array('email'=>$_POST['email']),$this->uid);

        if ($return['msg']) {
            
            echo 1;die;
        } else {
            
            echo 0;die;
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

			$html	=	"<div class='eva_ex_list_bx'> <i class='eva_ex_list_bx_img'></i><div class='eva_ex_list_ct'> <div class='ct_cs'> <div class='look_other_tit'><span class='look_other_tit_n'>".$introduce_name[$nid]."</span><a href='javascript:void(0)' onclick='nextintroduce()' class='look_other_h'>换一个</a></div><div>".$introduce_content[$nid]."</div>  </div></div><input type='hidden' id='introduce' value=''/></div>";
			
		}else{

			$html	=	"<div class='eva_ex_list_bx'> <i class='eva_ex_list_bx_img'></i><div class='eva_ex_list_ct'> <div class='ct_cs'> <div class='look_other_tit'><a href='javascript:void(0)' onclick='nextintroduce()' class='look_other_h'>换一个</a></div><div>没有示例</div>  </div></div><input type='hidden' id='introduce' value=''/></div>";
		}
		
		echo $html;die;	
		
	} 
	
}
?>