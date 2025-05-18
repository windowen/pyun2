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
class job_controller extends common
{

    function index_action()
    {
        $CacheM     =   $this->MODEL('cache');
        $CacheArr   =   $CacheM->GetCache(array('job', 'city', 'hy', 'com', 'uptime'));
        $this->yunset($CacheArr);
        
        if ($_GET['jobin']) {
            $job_classid    =   @explode(',', $_GET['jobin']);
            $jobname        =   $CacheArr['job_name'][$job_classid[0]];
            $this->yunset('jobname', mb_substr($jobname, 0, 6, 'utf-8'));
        }
        
        if (isset($_GET['ecity']) || isset($_GET['ejob'])){
            
            $pinyin  =  $CacheM->pinYin($_GET,array('city_index'=>$CacheArr['city_index'],'job_index'=>$CacheArr['job_index']));
            
            if (!empty($pinyin)){
                
                $_GET  =  array_merge($_GET,$pinyin);
            }
        }
        
        $searchurl  =   array();
        foreach ($_GET as $k => $v) {
            if ($k != "") {
                $searchurl[]    =   $k."=".$v;
            }
        }
        if (count($searchurl) > 1){
            $this->seo('com_search');
        }else{
            $this->seo('com');
        }
        $searchurl  =   @implode('&', $searchurl);
        $this->yunset('searchurl', $searchurl);
        
        if ($this->uid && $this->usertype == 1) {
            $lookJobIds    =   @explode(',', $_COOKIE['lookjob']);
            $this->yunset("lookJobIds", $lookJobIds);
        }
        $this->yunset('backurl', Url('wap'));
        $this->yunset('headertitle', '职位搜索');
        $this->yunset('topplaceholder', '请输入职位关键字,如：会计...');
        $this->yuntpl(array('wap/job'));
    }

    function search_action()
    {
        
        $this->index_action();
    }

    /**
     * 职位详情
     * 2019-06-20
     */
    function comapply_action()
    {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '参数错误！', 2, 5);
        }
        
        // 收藏 申请职位
        $typeStr    =   trim($_GET['type']);

        if (!empty($typeStr)) {

            $this -> typeJob($typeStr, $id,intval($_GET['eid']));
        }

        $JobM       =   $this->MODEL('job');
        $companyM   =   $this->MODEL('company');
        $uid        =   $this->uid;

        $jobField   =   array('com' => 'yes', 'hidecontact' => 'yes');

        $job        =   $JobM -> getInfo(array('id' => $id), $jobField);
        
		
        if ($uid != $job['uid']) {
            if ($job['r_status'] != 1) {
                $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '职位不存在!', 2, 5);
            }
        }

        $userinfoM  =   $this->MODEL('userinfo');
        $member     =   $userinfoM->getInfo(array('uid' => $job['uid']), array('field' => '`login_date`'));
        $job['login_date'] = $member['login_date'];

        // 联系方式
        $link       =   $JobM->getCompanyJobTel(array('id' => $job['id'], 'uid' => $this->uid, 'usertype' => $this->usertype));
        $this->yunset('link', $link);
        
        $company    =   $companyM->getInfo($job['uid'], array('field' => '`r_status`'));
        
        if ($this->uid == $job['uid']) {
            if ($job['state'] == 2) {
                $this->yunset('entype', 1);
            }
        } else {
            
            if ($company['r_status'] == 0 || $company['r_status'] == 3) {
                $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '企业暂未通过审核！');
            } elseif ($company['r_status'] == 2 || $company['r_status'] == 4) {
                $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '企业已被锁定！');
            }
            
            if ($job['state'] == 0) {
                $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '职位审核中！', 2, 5);
            } elseif ($job['state'] == 2) {
                $this->yunset('entype', 1);
            } elseif ($job['state'] == 3) {
                $this->ACT_msg_wap($_SERVER['HTTP_REFERER'], '该职位未通过审核！', 2, 5);
            } 
        }

        $JobM->addJobHits($id);
        $hits   =   $JobM->getInfo(array('id' => $id), array('field' => '`uid`, `jobhits`'));
        $job['jobhits']     =   $hits['jobhits'];
        // 投递数量
        $UJWhere['uid']     =   $this->uid;
        $UJWhere['job_id']  =   $id;
        $userid_job         =   $JobM->getSqJobNum($UJWhere);

        // 收藏数量
        $FJWhere['uid']     =   $this->uid;
        $FJWhere['job_id']  =   $id;
        $FJWhere['type']    =   '1';
        $fav_job            =   $JobM->getFavJobNum($FJWhere);

        // 邀请面试数量
        $invite_job         =   $JobM->getYqmsNum(array('jobid' => $id, 'uid' => $this->uid));

        // 举报数量
        $reportM            =   $this->MODEL('report');
        $report_job         =   $reportM->getNum(array('eid' => $id, 'p_uid' => $this->uid, 'c_uid' => $job['uid']));

        $job['userid_job']  =   $userid_job;
        $job['invite_job']  =   $invite_job;
        $job['fav_job']     =   $fav_job;
        $job['report_job']  =   $report_job;

        // 解决通过Editor上传的图片路径问题
        $job['description'] =   str_replace(array("ti<x>tle","“","”"), array("title"," "," "), $job['description']);
        $job['description'] =   htmlspecialchars_decode($job['description']);
        
        preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/', $job['description'], $res);
        
        if (!empty($res[3])) {
            foreach ($res[3] as $v) {
                if (strpos($v, 'http:') === false && strpos($v, 'https:') === false) {
                    $ossv = checkpic($v);
                    $job['description'] = str_replace($v, $ossv, $job['description']);
                }
            }
        }

        // 回复率
        $allnum     =   $JobM->getSqJobNum(array('job_id' => $id));

        $replynum   =   $JobM->getSqJobNum(array('job_id' => $id, 'is_browse' => array('>', 1)));
        if ($allnum == 0) {
            $job['pre'] = 100;
        } else {
            $job['pre'] = round(($replynum / $allnum) * 100);
        }
        $job['snum']    =   $allnum;

        // 会员等级
        $ratingM    =   $this -> MODEL('rating');
        $comrat     =   $ratingM -> getInfo(array('id' => intval($job['rating'])), array('pic' => '1'));

        // 查询咨询记录记录
        $msgM       =   $this->MODEL('msg');
        $msgList    =   $msgM->getList(array('jobid' => $id, 'job_uid' => $job['uid'], 'reply' => array('<>', ''), 'orderby' => 'datetime,desc', 'limit' => 5));
        $this->yunset('msgList', $msgList['list']);

        if ($_GET['tolist']) {
            $backurl    = Url('wap', array('c' => 'job'));
            $this->yunset('backurl', $backurl);
        }
        // 获取seo使用的数据
        $data['job_name']       =   $job['jobname']; // 职位名称
        $data['company_name']   =   $job['com_name']; // 公司名称
        $data['industry_class'] =   $job['hy_n']; // 所属行业
        $data['job_class']      =   $job['job_one'] . ',' . $job['job_two'] . ',' . $job['job_three']; // 职位名称
        $data['job_salary']     =   $job['job_salary']; // 职位薪资
        $job_desc       =   $this->GET_content_desc($job['description']); // 描述
        $data['job_desc'] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", $job_desc);
        
        $this->data = $data;
        $this->seo('comapply');
        
        $this->yunset('job', $job);
        $this->yunset('comrat', $comrat);
        $this->yunset('headertitle', '职位详情');
        if($this->config['sy_h5_share']==1){
          $this->yunset('shareurl', Url('wap', array('c' => 'job', 'a' => 'share', 'id' => $id )));
        }else{
          $this->yunset("shareurl",Url('wap',array('c'=>'job','a'=>'comapply','id'=>$id)));
        }

        $user_agent = (! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];
        
        if ($_COOKIE['mapx'] > 0 && $_COOKIE['mapy'] > 0 && strpos($user_agent, 'Android') && is_weixin()) {
            
            $this->yunset(array('mapx' => $_COOKIE['mapx'], 'mapy' => $_COOKIE['mapy'] ));
        } else {
            
            $this->yunset(array('mapx' => 0, 'mapy' => 0));
        }

        if($this->uid && $this->usertype==1){
            $ResumeM    =   $this->MODEL('resume');
            $resumenum  =   $ResumeM->getExpectNum(array('uid'=>$this->uid,'status'=>1,'state'=>1,'r_status'=>1));
            $this->yunset('resumenum', $resumenum);
        }
            
        $this->yuntpl(array( 'wap/job_show'));
    }
	//兼容以前版本链接
	function view_action(){
		if($_GET['id']){
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '.Url('wap',array('c'=>'job','a'=>'comapply','id'=>$_GET['id'])));//
		}
	}
    // 收藏 申请职位
    private function typeJob($typeStr, $id,$eid='')
    {
        $JobM   =   $this->MODEL('job');

        $data   =   array('uid' => $this->uid, 'usertype' => $this->usertype, 'did' => $this->userdid, 'job_id' => $id);

        if ($typeStr == 'sq') {
			if($eid){
				$data['eid']=	$eid;
			}
			$data['port']	=	'2';
            $res	=	$JobM->applyJob($data);
            
            $res['state']  =  $res['errorcode'];
            
        } elseif ($typeStr == 'fav') {
            
            $res = $JobM->collectJob($data);
        }

        if ($res['errorcode'] != 9) {
            $res['url'] = empty($res['url']) ? $_SERVER['HTTP_REFERER'] : $res['url'];
        }
        echo json_encode($res);
        die();
    }

    function report_action()
    {
        session_start();

        $reportM    =   $this->MODEL('report');
        $jobM       =   $this->MODEL('job');
        
        if ($this->usertype != '1') {
            $data['url']    =   $_SERVER['HTTP_REFERER'];
            $data['msg']    =   '只有个人会员才可举报！';
            echo json_encode($data);
            die();
        }
        if (md5(strtolower($_POST['authcode'])) != $_SESSION['authcode'] || empty($_SESSION['authcode'])) {
            unset($_SESSION['authcode']);
            $data['url'] = $_SERVER['HTTP_REFERER'];
            $data['msg'] = '验证码错误！';
            echo json_encode($data);
            die();
        }
        $job    =   $jobM->getInfo(array('id' => intval($_POST['id'])), array('field' => '`uid`,`com_name`'));

        $row    =   $reportM -> getReportOne(array('p_uid' => $this->uid, 'eid' => (int) $_POST['id'], 'c_uid' => $job['uid'], 'usertype' => $this->usertype));

        if (is_array($row)) {
            $data['url']    =   $_SERVER['HTTP_REFERER'];
            $data['msg']    =   '您已举报过该用户！';
            echo json_encode($data);
            die();
        }
        
        $data   =   array(
            'c_uid'         =>  $job['uid'],
            'inputtime'     =>  time(),
            'p_uid'         =>  $this->uid,
            'usertype'      =>  (int) $this->usertype,
            'eid'           =>  (int) $_POST['id'],
            'r_name'        =>  $job['com_name'],
            'username'      =>  $this->username,
            'r_reason'      =>  $this->stringfilter($_POST['reason']),
            'did'           =>  $this->userdid
        );

        $nid    =   $reportM -> addJobReport($data);
        
        if ($nid) {
            $data['url']    =   $_SERVER['HTTP_REFERER'];
            $data['msg']    =   '举报成功！';
            echo json_encode($data);
            die();
        } else {
            $data['url']    =   $_SERVER['HTTP_REFERER'];
            $data['msg']    =   '举报失败！';
            echo json_encode($data);
            die();
        }
    }

    function applyjobuid_action()
    {
        $CacheM     =   $this->MODEL('cache');
        $CacheList  =   $CacheM->GetCache(array('job', 'city', 'com', 'user', 'hy'));
        $this->yunset($CacheList);

        if (intval($_GET['jobid']) && intval($_GET['id'])) {
            
            $resumeM    =   $this->MODEL('resume');
            $row        =   $resumeM->getTempResumeInfo(array('id' => intval($_GET['id'])));
            $this->yunset('row', $row);
        }

        $JobM   =   $this -> MODEL('job');
        $job    =   $JobM -> getInfo(array('id' => $_GET['jobid']), array('com' => 'yes'));
        $this->yunset('job', $job);

        $data['job_name']       =   $job['name']; // 职位名称
        $data['company_name']   =   $job['com_name']; // 公司名称
        $data['job_desc']       =   $this->GET_content_desc($job['description']); // 描述
        $data['industry_class'] =   $job['job_hy']; // 所属行业
        $data['job_class']      =   $job['job_one'] . "," . $job['job_two'] . "," . $job['job_three'];
        $data['job_salary']     =   $job['job_salary'];
        $this->data = $data;
        $this->seo('comapply');

        $this->yunset('job', $job);
        $this->yunset($CacheList);
        $this->yunset('headertitle', '快速申请');
        $this->yuntpl(array('wap/applyjobuid'));
    }

    function applylogin_action()
    {
        $CacheM     =   $this->MODEL('cache');
        $CacheList  =   $CacheM->GetCache(array('job', 'city', 'com', 'user', 'hy' ));
        $this->yunset($CacheList);

        $JobM       =   $this->MODEL('job');
        $job        =   $JobM->getInfo(array('id' => intval($_GET['jobid'])), array('com' => 'yes'));
        $this->yunset('job', $job);

        $data['job_name']       =   $job['name']; // 职位名称
        $data['company_name']   =   $job['com_name']; // 公司名称
        $data['job_desc']       =   $this->GET_content_desc($job['description']); // 描述
        $data['industry_class'] =   $job['job_hy']; // 所属行业
        $data['job_class']      =   $job['job_one'] . "," . $job['job_two'] . "," . $job['job_three'];
        $data['job_salary']     =   $job['job_salary'];
        $this->data = $data;
        $this->seo('comapply');

        $url    =   Url('wap', array('c' => 'job', 'a' => 'applyjobuid', 'jobid' => intval($_GET['jobid']),'id' => intval($_GET['id'])));
        $this->yunset('backurl', $url);

        $this->yunset('headertitle', '设置密码');
        $this->yuntpl(array('wap/applylogin'));
    }

    /**
     * 职位详情
     * 分享数量
     * 2019-06-21
     */
    function share_action()
    {
        $id     =   intval($_GET['id']);
        $this->get_moblie();

        $JobM   =   $this->MODEL('job');
        $CacheM =   $this->MODEL('cache');
        $CacheArr   =   $CacheM->GetCache(array('job', 'city', 'hy', 'com'));

        $jobField   =   array('com' => 'yes', 'hidecontact' => 'yes');
        $job        =   $JobM->getInfo(array('id' => $id), $jobField);
        $job['content']     =   strip_tags($job['content']);
        $job['description'] =   strip_tags($job['description'], '<br>');	
        $this->yunset('job', $job);

        $link       =   $JobM->getCompanyJobTel(array('id' => $id, 'uid' => $this->uid, 'usertype' => $this->usertype));
        $this->yunset('link', $link);

        $this->yunset($CacheArr);
        
        $data['job_name']       =   $job['jobname']; // 职位名称
        $data['company_name']   =   $job['com_name']; // 公司名称
        $data['industry_class'] =   $job['job_hy']; // 所属行业
        $data['job_class']      =   $job['job_one'] . ',' . $job['job_two'] . ',' . $job['job_three']; // 职位名称
        $data['job_desc']       =   $this->GET_content_desc($job['description']); // 描述
        $data['job_salary']     =   $job['job_salary'];
        $this->data = $data;
        $this->seo('comapply');

        $this->yunset('headertitle', $job['name'].'-'.$job['com_name'].'-'.$this->config['sy_webname']);

        $this->yunset('job_style', $this->config['sy_weburl'] . '/app/template/wap/job');
        $this->yuntpl(array('wap/job/index'));
    }

    /**
     * 职位详情
     * 浏览数量
     * 2019-06-21
     */
    function GetHits_action()
    {
        $id     =   intval($_GET['id']);
        if (empty($id)) {
            echo 'document.write(0)';
        }
        $JobM   =   $this->MODEL('job');
        $JobM->addJobHits($id);

        $hits   =   $JobM->getInfo(array('id' => $id), array('field' => '`uid`, `jobhits`'));
        echo 'document.write(' . $hits['jobhits'] . ')';
    }

    /**
     * 职位详情
     * 求职咨询
     * 2019-06-12
     */
    function msg_action()
    {
        $_POST  =   $this->post_trim($_POST);
        
        $_POST['uid']       =   $this->uid;
        $_POST['username']  =   $this->username;
        $_POST['usertype']  =   $this->usertype;
        
        $msgM   =   $this->MODEL('msg');
        $res    =   $msgM->addMsg($_POST);

        $res['url']     =   empty($res['url']) ? $_SERVER['HTTP_REFERER'] : $res['url'];
        echo json_encode($res);
        die();
    }

    function jobmap_action()
    {
        $this->get_moblie();

        $comid      =   intval($_GET['id']);
        $companyM   =   $this->MODEL('company');
        $com        =   $companyM->getInfo($comid, array('field' => '`uid`,`name`,`cityid`,`address`,`x`,`y`'));
        $this->yunset('com', $com);

        $CacheM     =   $this->MODEL('cache');
        $CacheArr   =   $CacheM->GetCache(array('city'));
        $cityname   =   $CacheArr['city_name'][$com['cityid']];
        $this->yunset('cityname', $cityname);

        $user_agent =   (! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

        if (($_COOKIE['mapx'] && $_COOKIE['mapx'] > 0) && ($_COOKIE['mapy'] && $_COOKIE['mapy'] > 0) && strpos($user_agent, 'Android') && is_weixin()) {

            $this->yunset(array('mapx' => $_COOKIE['mapx'], 'mapy' => $_COOKIE['mapy']));
            
        } else {
            
            $this->yunset(array('mapx' => 0, 'mapy' => 0));
        }
        $this->yunset('title', '企业位置');
        $this->yunset('headertitle', '企业位置');
        $this->yuntpl(array('wap/job_map'));
    }

    // 安卓微信浏览器保存定位。
    function distance_action()
    {
        $x  =   $_POST['x'];
        $y  =   $_POST['y'];
        $this->cookie->setcookie('mapx', $x, time() + 1800);
        $this->cookie->setcookie('mapy', $y, time() + 1800);
    }

    
    function history_action(){
        
        if ($_POST['id'] && $this->usertype == 1) {
            
            $id     =   intval($_POST['id']);
            
            $time   =   time();
            
            $cookieM        =   $this->MODEL('cookie');
            $cookieJobIds   =   $_COOKIE['lookjob'];
            
            if ($cookieJobIds) {
                $jobArr = @explode('-', $cookieJobIds);
                if (!in_array($id, $jobArr)) {
                    $lookJobIds =   $cookieJobIds."-".$id;
                }else{
                    $lookJobIds =   $cookieJobIds;
                }
            }else{
                $lookJobIds =   $id;
            }
            
            $cookieM -> setcookie('lookjob', $lookJobIds, time()+3600);
            // 增加职位浏览记录
            $JobM   =   $this->MODEL('job');
            $JobM -> addLookJob(array('uid' => $this->uid, 'jobid' => $id, 'datetime' => $time,'did' => $this->userdid));
        }
    }
    
}
?>