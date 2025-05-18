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
class comapply_controller extends job_controller
{

    /**
     * 职位详情
     * 2019-06-12
     */
    function index_action()
    {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->ACT_msg($_SERVER['HTTP_REFERER'], '参数错误！');
        }

        $JobM       =   $this->MODEL('job');
        $ComM       =   $this->MODEL('company');
        $statisM    =   $this->MODEL('statis');
        $RatingM    =   $this->MODEL('rating');
        $userinfoM  =   $this->MODEL('userinfo');

        $JobInfo    =   $JobM->getInfo(array('id' => $id), array('com' => 'yes', 'hidecontact' => 'yes'));
        $recjobnum  =   $JobM->getJobNum(array('state'=>1,'r_status'=>1,'status'=>0,'job1'=>$JobInfo['job1'],'id'=>array('<>',$JobInfo['id'])));
        $this->yunset('recjobnum',$recjobnum);

	
        $viplist    =   $statisM->getInfo($JobInfo['uid'], array('usertype' => 2,'field' => '`vip_etime`,`rating`'));
        $comrat     =   $RatingM->getInfo(array('id' => $viplist['rating']), array('pic' => '1'));
        $this->yunset('comrat', $comrat);
        if (isVip($viplist['vip_etime'])) {

            $eviplist   =   1; // 表示未过期
        } else {

            $eviplist   =   2; // 表示已过期
        }
        $this->yunset('eviplist', $eviplist);

        // 联系方式
        $link       =   $JobM->getCompanyJobTel(array('id' => $id, 'uid' => $this->uid, 'usertype' => $this->usertype));
        $this->yunset('link', $link);

        $member     =   $userinfoM->getInfo(array('uid' => $JobInfo['uid']), array('field' => '`login_date`'));
        $JobInfo['login_date']  =   $member['login_date'];

        $company    =   $ComM->getInfo($JobInfo['uid'], array('field' => '`r_status`'));

        if ($_GET['type'] != 'admin') {
            // 职位状态判断
            if (empty($this->uid) || $this->uid != $JobInfo['uid']) {

                if ($company['r_status'] == 0 || $company['r_status'] == 3) {
                    $this->ACT_msg($this->config['sy_weburl'], '企业暂未通过审核！');
                } elseif ($company['r_status'] == 2 || $company['r_status'] == 4) {
                    $this->ACT_msg($this->config['sy_weburl'], '企业已被锁定！');
                }

                if ($JobInfo['r_status'] == 2) {
                    $this->ACT_msg($this->config['sy_weburl'], '企业已被锁定！');
                } elseif ($JobInfo['r_status'] != 1) {
                    $this->ACT_msg($_SERVER['HTTP_REFERER'], '职位不存在!');
                } elseif ($JobInfo['state'] == 0) {
                    $this->ACT_msg($_SERVER['HTTP_REFERER'], '职位审核中！');
                } elseif ($JobInfo['state'] == 3) {
                    $this->ACT_msg($_SERVER['HTTP_REFERER'], '该职位未通过审核！');
                }

                // if ($JobInfo['status'] == 1) {
                //     $this->yunset('stop', 1);
                // }
            }
        }

        if ($JobInfo['status'] == 1) {
            $this->yunset('stop', 1);
        }


        // 有管理员登录信息时，设置state为1
        if (!empty($_SESSION['auid'])) {
            $adminM =   $this->MODEL('admin');
            $row    =   $adminM->getAdminUser(array('uid' => array('=', $_SESSION['auid'])));
            if (!empty($row) && $_SESSION['ashell'] == md5($row['username'].$row['password'].$this->md)) {
                $JobInfo['state'] = 1;
            }
        }

        $CacheM     =   $this->MODEL('cache');
        $CacheList  =   $CacheM->GetCache(array('job', 'city', 'com', 'user', 'hy'));
        $this->yunset($CacheList);

        /* 个人会员登录状态 */
        if ($this->uid && $this->usertype == 1) {

            $UJWhere['uid']     =   $this->uid;
            $UJWhere['job_id']  =   $id;
            $userIdJobNum       =   $JobM -> getSqJobNum($UJWhere);
            if ($userIdJobNum > 0) {
                $this->yunset('userIdJobNum', $userIdJobNum);
            }

            $FJWhere['uid']     =   $this->uid;
            $FJWhere['job_id']  =   $id;
            $FJWhere['type']    =   '1';
            $favJobNum          =   $JobM -> getFavJobNum($FJWhere);
            if ($favJobNum > 0) {
                $this->yunset('favJobNum', $favJobNum);
            }

            $this->yunset('usertype', '1');

            $ResumeM    =   $this->MODEL('resume');
            $resumenum  =   $ResumeM->getExpectNum(array('uid'=>$this->uid,'status'=>1,'state'=>1,'r_status'=>1));
            $this->yunset('resumenum', $resumenum);
        }

        // 查询求职咨询信息
        $msgM       =   $this->MODEL('msg');
        $msgList    =   $msgM->getList(array('jobid' => $id, 'job_uid' => $JobInfo['uid'], 'reply' => array('<>',''), 'orderby' => 'datetime,desc', 'limit' => 5));
        $this->yunset('msgList', $msgList['list']);

        // 投递数量
        $allnum     =   $JobM->getSqJobNum(array('job_id' => $id));
        $replynum   =   $JobM->getSqJobNum(array('job_id' => $id, 'is_browse' => array('>', 1)));
        if ($allnum == 0) {
            $JobInfo['pre'] = 100;
        } else {
            $JobInfo['pre'] = round(($replynum / $allnum) * 100);
        }
        $JobInfo['snum'] = $allnum;

        $this->yunset('Info', $JobInfo);

        // 设置用户类型
        if ($this->uid && $this->usertype && $this->usertype != 1) {
            $typename = array('2' => '企业身份');
            $this->yunset('usertypemsg', '您当前帐号名为：<span class="job_user_name_s">' . $this->username . '</span>，账户属于' . $typename[$this->usertype] . '。');
        }

        // 获取seo使用的数据
        $data['job_name']       =   $JobInfo['jobname']; // 职位名称
        $data['company_name']   =   $JobInfo['com_name']; // 公司名称
        $data['industry_class'] =   $JobInfo['hy_n']; // 所属行业
        $data['job_class']      =   $JobInfo['job_one'].','.$JobInfo['job_two'].','.$JobInfo['job_three']; // 职位名称
        $data['job_salary']     =   $JobInfo['job_salary']; // 职位薪资
        $data['job_desc']       =   $this->GET_content_desc($JobInfo['description']); // 描述
        $this->data =   $data;

        // 判断当天推荐职位、简历数是否已满
        if ($this->uid && isset($this->config['sy_recommend_day_num']) && $this->config['sy_recommend_day_num'] > 0) {

            $recomM =   $this->MODEL('recommend');
            $num    =   $recomM->getRecommendNum(array('uid' => $this->uid));

            if ($num >= $this->config['sy_recommend_day_num']) {
                $this->yunset('sy_recommend_day_num', $this->config['sy_recommend_day_num']);
            }
        }

        // 两次推荐职位、简历的最小时间间隔
        $recommendInterval = isset($this->config['sy_recommend_interval']) ? $this->config['sy_recommend_interval'] : 0;
        $this->yunset('recommendInterval', $recommendInterval);

        $this->seo('comapply');
        $this->yun_tpl(array('comapply'));
    }

    /**
     * 职位详情
     * 求职咨询
     * 2019-06-12
     */
    function msg_action()
    {
        if ($_POST['submit']) {

            $_POST              =   $this->post_trim($_POST);
            $_POST['uid']       =   $this->uid;
            $_POST['username']  =   $this->username;
            $_POST['usertype']  =   $this->usertype;

            $msgM   =   $this->MODEL('msg');

            $res    =   $msgM->addMsg($_POST);

            $this->ACT_layer_msg($res['msg'], $res['errorcode'], $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * 职位详情
     * 浏览数量
     * 2019-06-12
     */
    function GetHits_action()
    {
        $id = intval($_GET['id']);
        if (empty($id)) {
            echo 'document.write(0)';
            die();
        }

        $JobM   =   $this->MODEL('job');

        $JobM->addJobHits($id);

        $hits   =   $JobM->getInfo(array('id' => $id), array('field' => '`uid`, `jobhits`'));
        echo 'document.write('.$hits['jobhits'].')';
        die();
    }

    /**
     * 职位详情
     * 获取联系方式
     * 2019-06-12
     */
    function gettel_action()
    {
        $id         =   intval($_POST['id']);
        $JobM       =   $this->MODEL('job');
        $dataArr    =   array('id' => $id, 'uid' => $this->uid, 'usertype' => $this->usertype);
        $telRes     =   $JobM -> getCompanyJobTel($dataArr);

        if ($telRes['errorcode'] == 9 && ! empty($telRes['data'])) {
            echo json_encode($telRes['data']);
            die();
        } else {
            echo $telRes['errorcode'];
            die();
        }
    }

    function history_action(){

        if ($_POST['id'] && $this->usertype == 1) {
            
            $id     =   intval($_POST['id']);
            
            $time   =   time();
            
            $cookieM        =   $this->MODEL('cookie');
            $cookieJobIds   =   $_COOKIE['lookjob'];
            
            if ($cookieJobIds) {
                $jobArr = @explode(',', $cookieJobIds);
                if (!in_array($id, $jobArr)) {
                    $lookJobIds =   $cookieJobIds.",".$id;
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