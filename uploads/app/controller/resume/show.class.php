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
class show_controller extends resume_controller
{

    /**
     *@desc 简历详情
     * 2019-08-14
     */
    function index_action()
    {
        $JobM       =   $this->MODEL('job');
        $statisM    =   $this->MODEL('statis');
        $getUid     =   intval($_GET['uid']);
        $getType    =   intval($_GET['type']);
        $getId      =   intval($_GET['id']);

        // 企业登录时，获取企业的职位列表
        if ($this->usertype == 2) {

            $uid            =   $this->uid;

            $jobWhere       =   array('uid' => $uid, 'state' => 1, 'r_status' => 1, 'status' => 0);

            $jobList        =   $JobM -> getList($jobWhere, array('link' => 'yes', 'field' => '`com_name`, `name`, `id`, `is_link`, `link_type`, `uid`'));

            $company_job    =   array();

            if (! empty($jobList['list'])) {
                $company_job    =   $jobList['list'];
            }

            $this->yunset('company_job', $company_job);

            //邀请模板
            $yqmbM  =   $this->MODEL('yqmb');
            $ymlist = $yqmbM  ->getList(array('uid'=>$uid));
            $ymnum  = $yqmbM  ->getNum(array('uid'=>$uid));
            $ymcan  = $ymnum<$this->config['com_yqmb_num'] ? true : false;

            $this->yunset('ymlist', $ymlist);
            $this->yunset('ymcan', $ymcan);
        }

        // 未登录情况下，记录浏览数量
        if (($this->uid == '' || $this->username == '') && $this->config['sy_resume_visitors'] > 0) {
            if ($_COOKIE['resumevisitors'] >= $this->config['sy_resume_visitors']) {

                $this->ACT_msg(Url('login'), '游客用户，每天只能访问' . $this->config['sy_resume_visitors'] . '份简历，请登录后继续访问！');
            } else {

                if ($_COOKIE['resumevisitors'] == '') {

                    $resumevisitors = 1;
                } else {

                    $resumevisitors = $_COOKIE['resumevisitors'] + 1;
                }
                $this->cookie->SetCookie('resumevisitors',$resumevisitors, strtotime(date("Y-m-d",strtotime("+1 day"))));
            }

        }

        $resumeM = $this->MODEL('resume');

        // 如果传入的是uid, 则先获取eid
        if (!empty($getUid)) {

            
				if($this->uid == $getUid){

					$rwhere	=	array('uid' => $getUid);

				}else{

					$rwhere	=	array('uid' => $getUid, 'r_status' => 1);

				}

                $def_job    =   $resumeM->getResumeInfo($rwhere, array('field' => '`def_job`'));

                if (empty($def_job)) {

                    $this->ACT_msg($this->config['sy_weburl'], '没有找到该人才！');
                } else if ($def_job['def_job'] < 1) {

                    $this->ACT_msg($this->config['sy_weburl'] . '/member', '还没有创建简历！');
                } else if ($def_job['def_job']) {

                    $id     =   $def_job['def_job'];
                }
            

        } else if (! empty($getId)) {

            $id             =   $getId;
        }

        $this->yunset("eid", $id);

        $resume_expect      =   $resumeM->getInfoByEid(array('eid' => $id, 'uid' => $this->uid, 'usertype' => $this->usertype,'spid' => $this->spid));

        if (empty($resume_expect)) {

            $this->ACT_msg($this->config['sy_weburl'], '没有找到该人才！');
        }
		if($_GET['look'] == 'admin'){
			 session_start();
		}
        if ($this->config['com_search'] == 1 && !$this->uid && !$_SESSION['auid']){
            $this->ACT_msg(Url('resume'), '请先登录');
        }
        if ($this->config['sy_user_visit_resume'] == 0 && $this->usertype == 1 && $this->uid != $resume_expect['uid']&&$_GET['look']!='admin') {

            $this->ACT_msg(Url('resume'), '个人用户无权限查看简历！');
        }
        // 查询黑名单
        $blackM             =   $this->MODEL('black');
        $blackInfo          =   $blackM -> getBlackInfo(array('p_uid' => $this->uid, 'c_uid'=> $resume_expect['uid']));

        if(!empty($blackInfo)){

            $this->ACT_msg($this->config['sy_weburl'] . '/member', '该用户已关闭简历!');

        }


        if ($resume_expect['uid'] != $this->uid && !$_SESSION['auid']) {
            if ($resume_expect['state'] == 0) {

                $this->ACT_msg($this->config['sy_weburl'] . '/member', '简历正在审核中！');
            } elseif ($resume_expect['r_status'] == 2) {

                $this->ACT_msg($this->config['sy_weburl'] . '/member', '简历暂被锁定，请稍后查看！');
            } elseif ($resume_expect['state'] == 3) {

                $this->ACT_msg($this->config['sy_weburl'] . '/member', '简历审核暂未通过！');
            }
        }
        // 查询当前企业用户，是不是采集用户
        $userinfoM  =   $this->MODEL('userinfo');
        $UserMember =   $userinfoM->getInfo(array('uid' => $resume_expect['uid']), array('field' => '`source`, `email`, `claim`'));
        $this->yunset('UserMember', $UserMember);

        $time       =   strtotime("-14 day");
        $allnum     =   $JobM->getYqmsNum(array('uid' => $resume_expect['uid'], 'datetime' => array('>', $time)));
        $replynum   =   $JobM->getYqmsNum(array('uid' => $resume_expect['uid'], 'datetime' => array('>', $time), 'is_browse' => array('>', 2)));

        $pre        =   $allnum>0 ? round(($replynum / $allnum) * 100) : 0;
        $this->yunset('pre', $pre);

        if ($this->usertype == 2) {

            $comid  =   $this->uid;
            $jobnum =   $JobM->getJobNum(array('uid' => $comid));
            $this->yunset('jobnum', $jobnum);

            // 人才收藏库
            $talent_pool    =   $resumeM->getTalentList(array('eid' => $id, 'cuid' => $this->uid));
            $this->yunset('talent_pool', $talent_pool);
        }

        $resume_expect['m_status']  =   !$_SESSION['auid'] ? $resume_expect['m_status'] : '1';

        // 查看联系方式：m_status = 1 直接查看 showcontactflag && downresumes
        if (isset($resume_expect['m_status']) && $resume_expect['m_status'] == 1) { // 直接查看

            $resume_expect['link_topmsg']   =   "<a class='yun_newedition_resume_ceil_tel_n' href='javascript:void(0)' onclick=\"getLinkStyle()\"><span>查看联系方式</span></a>";

            $resume_expect['link_msg']      =   "<a class='yun_newedition_resume_look' href='javascript:void(0)' onclick=\"getLinkStyle()\"><span>查看联系方式</span></a>";

            $resume_expect['link_msg_right']=   "<input class='yun_resume_xz' onClick=\"for_link('$id','".Url("ajax", array('c'=>'for_link'))."','".Url("ajax", array('c'=>'resume_word', 'id'=>$id))."');\" type='button' name='submit' value='下载 '>";
        } else if (isset($resume_expect['showcontactflag']) && $resume_expect['showcontactflag']) { // 可下载，提示剩余下载量

            $resume_expect['link_topmsg']   =   "<a href='javascript:void(0)' class='yun_newedition_resume_ceil_tel_n' onclick=\"isDownResume('$id',{$resume_expect['downresumes']},'".Url("ajax", array('c'=>'for_link'))."')\"><span>查看联系方式</span></a>";

            $resume_expect['link_msg']      =   "<a href='javascript:void(0)' class='yun_newedition_resume_look' onclick=\"isDownResume('$id',{$resume_expect['downresumes']},'".Url("ajax", array('c'=>'for_link'))."')\"><span>查看联系方式</span></a>";

            $resume_expect['link_res']      =   "<a href='javascript:void(0)' onclick=\"isDownResume('$id',{$resume_expect['downresumes']},'".Url("ajax", array('c'=>'for_link'))."')\">查看简历详细信息</a>";

            $resume_expect['link_msg_right']=   "<input class='yun_resume_xz' onClick=\"for_link('$id','".Url("ajax", array('c'=>'for_link'))."','".Url("ajax", array('c'=>'resume_word', 'id'=>$id))."');\" type='button' name='submit' value='下载 '>";


        } else {

            $resume_expect['link_topmsg']   =   "<a class='yun_newedition_resume_ceil_tel_n' href='javascript:void(0)' onclick=\"for_link('$id','".Url("ajax", array('c'=>'for_link'))."')\"><span>查看联系方式</span></a>";

            $resume_expect['link_msg']      =   "<a class='yun_newedition_resume_look' href='javascript:void(0)' onclick=\"for_link('$id','".Url("ajax", array('c'=>'for_link'))."')\"><span>查看联系方式</span></a>";

            $resume_expect['link_res']      =   "<a href='javascript:void(0)' onclick=\"for_link('$id','".Url("ajax", array('c' => 'for_link'))."')\">查看简历详细信息</a>";
            $resume_expect['link_msg_right']=   "<input class='yun_resume_xz' onClick=\"for_link('$id','".Url("ajax", array('c'=>'for_link'))."','".Url("ajax", array('c'=>'resume_word', 'id'=>$id))."');\" type='button' name='submit' value='下载 '>";

        }

        $data['resume_username']    =   $resume_expect['username_n'];   // 简历人姓名
        $data['resume_city']        =   $resume_expect['cityname'];     // 城市
        $data['resume_job']         =   $resume_expect['customjob'];    // 行业
        $this->data = $data;
        $this->seo('resume');


        $this->yunset('Info', $resume_expect);

        // 已邀请面试数量
        if (!empty($resume_expect) && !empty($this->uid)) {
            $usermsgnum     =   $JobM -> getYqmsNum(array('fid' => $this->uid, 'uid' => $resume_expect['uid']));
            $this->yunset('usermsgnum', $usermsgnum);
        }

        // 下载简历
        if ($_GET['type'] == 'word') {

            if ($resume_expect['uid'] == $this->uid) {

                $this->yuntpl(array('resume/wordresume'));
                die();
            } else {

                $downM  =   $this->MODEL('resumedown');
                $isDown =   $downM->getDownResumeInfo(array('eid' => $getId, 'downtime' => $_GET['downtime']));

                if (! empty($isDown)) {

                    $this->yuntpl(array('resume/wordresume'));
                }
                die();
            }
        }

        $this->yunset(array('uid' => $this->uid, 'usertype' => $this->usertype));

        $cData['uid']       =   $this->uid;
        $cData['usertype']  =   $this->usertype;
        $cData['eid']       =   $resume_expect['id'];
        $cData['ruid']      =   $resume_expect['uid'];
        $resumeCkeck        =   $resumeM->openResumeCheck($cData);
        $resumeCkeck        =   $_SESSION['auid'] ? 1 : $resumeCkeck;
        $this->yunset('resumeCkeck', $resumeCkeck);
        /* 模糊字段 */
        $this->yunset('tj', $resume_expect['tj']);

        $this->yunset(array('resumestyle' => $this->config['sy_weburl'].'/app/template/resume'));

        $tmp    =   intval($_GET['tmp']);

        $statis =   $statisM->getInfo($resume_expect['uid'], array('usertype' => 1, 'field' => '`tpl`,`paytpls`'));

        if ($statis['paytpls']) {

            $paytpls    =   @explode(',', $statis['paytpls']);
            $this->yunset('paytpls', $paytpls);
        }
        $tplM       =   $this->MODEL('tpl');

        if (!empty($tmp)) {

            $url    =   $tplM->getResumetpl(array('id' => $tmp));

            if ($this->uid != $resume_expect['uid'] && in_array($tmp, $paytpls) == false) {
                unset($tmp);
            }
        } else {

            $tmp    =   1;
            $url    =   $tplM->getResumetpl(array('id' => $statis['tpl']));
        }

        if (in_array($this->usertype, array(2, 3))) {

            $category   =   intval($this->usertype) - 1;

            // 会员等级 增值包 套餐
            $ratingM    =   $this->MODEL('rating');
            $ratingList =   $ratingM->getList(array('display' => 1, 'orderby' => array('type,asc', 'sort,desc')));

            $rating_1   =   $rating_2   =   $raV    =   array();
            if (!empty($ratingList)) {
                foreach ($ratingList as $ratingV) {

                    $raV[$ratingV['id']]    =   $ratingV;

                    if ($ratingV['category'] == $category && $ratingV['service_price'] > 0) {
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

            $statis     =   $statisM->getInfo($this->uid, array('usertype' => $this->usertype));

            if (! empty($statis)) {

                $discount = isset($raV[$statis['rating']]) ? $raV[$statis['rating']] : array();
                $this->yunset('discount', $discount);
                $this->yunset('statis', $statis);
            }

            if ($this->usertype == 2) {

                $add        =   $ratingM->getComSerDetailList(array('orderby' => array('type,asc', 'sort,desc')), array('pack' => '1'));
            } 
            $this->yunset('add', $add);

            if (!isVip($statis['vip_etime'])) {

                $this->yunset('vipIsDown', 1); //  会员过期
            }
        }

        if ($url['url'] == '') {
            unset($tmp);
        }
		
		$this->moreMenu();
		
		$cacheM     =   $this->MODEL('cache');
	    
	    $options    =   array('user');
	    
	    $cache      =   $cacheM -> GetCache($options);
	   
	    $this       ->  yunset($cache);
		
        if (!empty($tmp)) {

            $this->yunset('tplurl', $url);
            $this->yuntpl(array('resume/'.$url['url'].'/index'));
        } else {

            $this->yuntpl(array('resume/resume'));
        }
    }

    /**
     * @desc   简历详情  浏览数量
     * 2019-06-14
     */
    function GetHits_action()
    {
        $id         =   intval($_GET['id']);

        if (empty($id)) {
            echo 'document.write(0)';
        }

        $resumeM    =   $this->MODEL('resume');
        $resumeM -> addExpectHits($id);

        $hits       =   $resumeM->getExpect(array('id'=>$id), array('field' => '`hits`'));

        echo 'document.write(' . $hits['hits'] . ')';
    }

    /**
     * @desc 简历详情 - 举报简历
     * 2019-06-14
     */
    function report_action()
    {

        $_POST  =   $this->post_trim($_POST);

        if ($_POST['reason'] == '') {

            $this->ACT_layer_msg('请选择举报理由', 8);
        }
        if(empty($this->uid)){
            $this->ACT_layer_msg('请先登录！', 8);
        }

        if($_POST['reason'] == ''){
            $this->ACT_layer_msg('举报内容不能为空！', 8);
        }

        $Where = array(
            'comid' =>$this->uid,
            'usertype' =>$this->usertype,
            'eid' =>(int) $_POST['r_eid'],
            'uid' =>(int) $_POST['r_uid']
        );

        $downM		=	$this->MODEL('downresume');
        $resumeNum	=   $downM->getDownNum($Where);
        if($resumeNum==0){
            $this->ACT_layer_msg('下载之后才可以举报哦！', 8);
        }

        $data           =   array(
            'reason'    =>  $_POST['reason'],
            'c_uid'     =>  (int) $_POST['r_uid'],
            'inputtime' =>  time(),
            'p_uid'     =>  $this->uid,
            'did'       =>  $this->userid,
            'usertype'  =>  $this->usertype,
            'eid'       =>  (int) $_POST['r_eid'],
            'r_name'    =>  $_POST['r_name'],
            'username'  =>  $this->username
        );
        $reportM    =  $this->MODEL('report');
        $return     =   $reportM->ReportResume($data);
        $this->ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER']);
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