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
class company extends common{
    
	public $comInfo = array();

    function __construct($tpl, $db, $def = '', $model = 'index', $m = '')
    {
        $this -> common($tpl, $db, $def, $model, $m);
        
        if (!empty($this->spid) && in_array($_GET['c'], array('info','uppic','right','ad','binding','pay','paylog','integral','child'))) {
            
            $this->ACT_msg('index.php','操作错误',8,3);
            
        }
        
		// 入口判断企业资料是否完善 排除部分act
		$Company    =   $this->MODEL('company');

		$uid        =   $this->uid;

		$this -> comInfo  =  $Company->getInfo($uid, array('info' => '1', 'edit' => '1', 'logo' => '1','utype'=> 'user'));

        if (!in_array($_GET['c'], array('binding', 'map', 'info', 'log', 'uppic')) && !in_array($_GET['act'],array('logout','jobCheck'))) {
            // 强制完善基本资料
            if ($this -> config['com_enforce_info']==1) {
                if(! $this->comInfo['info']['name'] || ! $this->comInfo['info']['provinceid'] || (! $this->comInfo['info']['linktel'] && ! $this->comInfo['info']['linkphone'])){
					
					$remindInfo['url']		=	'index.php?c=info';
					$remindInfo['title']	=	'企业资料尚未完善！';
					$remindInfo['msg']		=	'完善企业信息有助于帮您快速招聘人才！';
					
					$this	->	yunset('isremind', 1);
					$this	->	yunset('remindInfo',$remindInfo);

					$this	->	com_tpl('info');
				}
            }elseif(!$this->comInfo['info']['uid']){
			
				//容错机制，前期强制完善资料，后期开放，防止部分数据无uid 又可以直接操作会员中心
				$userinfoM    =   $this->MODEL("userinfo");
				$userinfoM -> activUser($uid,2);
			}
        }

		if(!$_GET['c']){

			$this -> yunset('js_def', '1');
		}else if(in_array($_GET['c'], array('job', 'jobadd','zhaopinhui','special','partadd','partok','part','partapply','tongji', 'likeresume'))){

			$this -> yunset('js_def', '2');
		}else if(in_array($_GET['c'], array('hr','down','talent_pool','look_resume','record','resume','invite', 'subscribe','finder', 'attention_me', 'look_job'))){

			$this -> yunset('js_def', '3');

		}else if(in_array($_GET['c'], array('paylogtc','pay', 'payment' ,'paylog', 'right', 'integral', 'integral_reduce', 'reward_list'))){


			$this -> yunset('js_def', '4');
		}else if(in_array($_GET['c'], array('vs','child','sysnews','msg','pl','yqmb','setname','report'))){

			$this -> yunset('js_def', '5');
		}else if(in_array($_GET['c'], array('info','uppic','map','news','show','product','comtpl','banner','binding'))){

			$this -> yunset('js_def', '6');
		}
         
    }
    
    function public_action()
    {
        
        $statis     =   $this->company_satic();
        
        $adminM     =   $this->MODEL('admin');
        $JobM       =   $this->MODEL('job');

        $now_url    =   @explode('/', $_SERVER['REQUEST_URI']);
        $now_url    =   $now_url[count($now_url) - 1];
        
        $this -> yunset('now_url', $now_url);

        $company    =   $this -> comInfo;
        $this->yunset('company', $company);

        $guweninfo  =   $adminM->getAdminUser(array('uid' => $company['crm_uid']));
        $guweninfo['photo_n']  	=   checkpic($guweninfo['photo'], $this->config['sy_guwen']);
        $guweninfo['ewm_n']  	=   checkpic($guweninfo['ewm'], $this->config['sy_wx_qcode']);
        $this->yunset('guweninfo', $guweninfo);
		if (!empty($guweninfo)) {

            $AtnM  =  $this->MODEL('atn');
            $atn   =  $AtnM->getatnInfo(array('uid' => $this->uid , 'usertype'=>$this->usertype,'conid'=>$guweninfo['uid']));
            $this->yunset('atn', $atn);
            
            $ReportM        =   $this->MODEL('report');
            $reportwhere    =   array('p_uid' => $this->uid, 'eid' => $guweninfo['uid'], 'type'=>2, 'orderby' => array('inputtime, desc'));
            $report         =   $ReportM->getReportOne($reportwhere);
            $this->yunset('report', $report);
        }  

        $qq = explode(',', $this->config['sy_qq']);
        $this->yunset('kfqq', $qq[0]);

        // 会员等级 增值包 套餐
        $ratingM    =   $this->MODEL('rating');
        $ratingList =   $ratingM->getList(array('display' => 1,'orderby' => array('type,asc', 'sort,desc')));
        $rating_1   =   $rating_2   =   $raV    =   array();
        
        if (! empty($ratingList)) {
            foreach ($ratingList as $ratingV) {
                $raV[$ratingV['id']] = $ratingV;
                if ($ratingV['category'] == 1 && $ratingV['service_price'] > 0) {
                    if ($ratingV['type'] == 1) {
                        
                        $rating_1[] = $ratingV;
                    } elseif ($ratingV['type'] == 2) {
                        
                        $rating_2[] = $ratingV;
                    }
                }
            }
        }
        $this->yunset('rating_1', $rating_1);
        $this->yunset('rating_2', $rating_2);

        if (! empty($statis)) {
            $discount = isset($raV[$statis['rating']]) ? $raV[$statis['rating']] : array();
            $this->yunset('discount', $discount);
        }
        $add	=	$ratingM->getComSerDetailList(array('orderby' => array('type,asc', 'sort,desc')), array('pack' => '1'));
        $this->yunset('add', $add);
        
        // 新投递尚未查看的简历
        $sqJobWhere     =   array('com_id' => $this->uid, 'is_browse' => 1);

        $newResumeNum   =   $JobM->getSqJobNum($sqJobWhere);
        $this->yunset('newResumeNum', $newResumeNum);
    }

    // 会员统计信息调用
    function company_satic()
    {
        // 会员套餐过期检测，并处理
        if (!empty($this->spid)) {
            
            $uid  =  $this->spid;
            $this -> yunset('spid', $this->spid);
        }else{
            
            $uid  =  $this->uid;
        }
        $statisM  =  $this->MODEL('statis');
        $statis   =  $statisM->vipOver($this->uid, 2);
        
        $this->yunset('addjobnum', $statis['addjobnum']);
        
        if (!isVip($statis['vip_etime'])) {
            
            $statis['vipIsDown']  =  1;
            $this->yunset('vipIsDown', 1); //  会员过期
        }
        
        $this->yunset('statis', $statis);
        $this->yunset('todayStart', strtotime('today'));
        
        return $statis;
    }

    function com_tpl($tpl)
    {
        $this->yuntpl(array('member/com/'.$tpl));
    }
 
    function job()
    {
        // 调用MODEL类文件
        $ReportM   =  $this->MODEL('report');
        
        $logM      =  $this->Model('log');
        
        $jobM      =  $this->Model('job');
        
        $PackM     =  $this->Model('pack');
        
        $CompanyM  =  $this->Model('company');
        // 举报
        if ($_GET['r_uid']) {
            
            if ($_GET['r_reason'] == "") {
                
                $this->ACT_layer_msg("举报内容不能为空！", 8, "index.php?c=down");
            }
            $data['p_uid']		=	(int) $_GET['r_uid'];
            
            $data['inputtime']	=	time();
            
            $data['c_uid']		=	$this->uid;
            
            $data['did']		=	$this->userid;
            
            $data['eid']		=	(int) $_GET['eid'];
            
            $data['r_name']		=	$_GET['r_name'];
            
            $data['usertype']	=	(int) $this->usertype;
            
            $data['username']	=	$this->username;
            
            $data['reason']		=	$_GET['r_reason'];
            
            $result				=	$ReportM->ReportResume($data);
            
            $this->ACT_layer_msg($result['msg'], $result['errcode'], "index.php?c=down");
            
        }
        
        // 职位发布状态修改---js
        if ($_POST['status'] && ($_POST['id'] || is_array($_POST['id']))) {
            
            $id	=	$_POST['id'];

            if ($_POST['status'] == 2) {
                
                $_POST['status']	=	0;
            }
            
            $data	=	array(
                
                'status'	=>	$_POST['status']
            );
            
            $nid	=	$jobM -> upInfo($data, array('id' => array('in',pylode(',', $id), 'uid' => $this->uid)));
            
            if ($nid) {
                
                $logM->addMemberLog($this->uid, $this->usertype, "修改职位发布状态", 1, 2); // 会员日志
				echo 1;
                die();
            } else {
                echo 2;
				die();
            }
        }

        // 职位删除
        if ($_GET['del'] || is_array($_POST['checkboxid'])) {
            
            if (is_array($_POST['checkboxid'])) {
                
                $layer_type = 1; // 提示方式
            } else if ($_GET['del']) {
                
                $layer_type = 0; // 提示方式
            }
            
            $delid = is_array($_POST['checkboxid']) ? $_POST['checkboxid'] : $_GET['del'];
       
            $numwhere['uid'] = $this->uid;
            
            $numwhere['jobid'] = array('in',pylode(',', $delid));

            $return = $jobM->delJob($delid,array('uid'=>$this->uid));
            
            if ($return['id']) {
                
                $jobWhereData = array(
                    
                    'uid' => $this->uid,
                    
                    'orderby' => 'lastupdate,desc'
                
                );
                
                $newest = $jobM->getInfo($jobWhereData, array('field' => 'lastupdate'));
                
                $uid = $this->uid;
                
                $companydata = array(
                    
                    'jobtime' => $newest['lastupdate']
                
                );
                
                $CompanyM->upInfo($uid, '', $companydata);
                
                $logM->addMemberLog($this->uid, $this->usertype, "删除职位", 1, 3);
                
                $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
            } else {
                
                $this->layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
            }
        }
    }

    function logout_action()
    {
        $this->logout();
    }

    // 兼职
    function part()
    {
        $partM = $this->MODEL("part");
        
        $logM = $this->Model('log');
        // 兼职职位延期
        if ($_POST['gotimeid']) {
            
            $_POST['day'] = intval($_POST['day']);
            
            if ($_POST['day'] < 1) {
                
                $this->ACT_layer_msg("请正确填写延期天数！", 8);
            } else {
                
                $posttime = (int) $_POST['day'] * 86400;
                
                $uid = $this->uid;
                
                $ids = @explode(",", $_POST['gotimeid']);
                
                $partjobwhere['id'] = array(
                    'in',
                    pylode(',', $ids)
                );
                
                $partjobwhere['uid'] = $uid;
                
                $row = $partM->getList($partjobwhere, array(
                    'field' => 'state,edate,id'
                ));
                
                foreach ($row as $val) {
                    
                    if ($val['edate']) {
                        
                        $edate = $val['edate'] + $posttime;
                    } else {
                        
                        $edate = '';
                    }
                    
                    $where['id'] = $val['id'];
                    
                    $where['uid'] = $uid;
                    
                    if ($row['state'] == 2) {
                        
                        $data = array(
                            
                            'state' => 1,
                            
                            'edate' => $edate
                        
                        );
                    } else {
                        
                        $data = array(
                            
                            'edate' => $edate
                        
                        );
                    }
                    $id = $partM->upInfo($data, $where);
                    
                    if ($id) {
                        
                        $logM->addMemberLog($this->uid, $this->usertype, "兼职职位延期", 9, 2);
                        
                        $this->ACT_layer_msg("兼职延期成功！", 9, $_SERVER['HTTP_REFERER']);
                    } else {
                        
                        $this->ACT_layer_msg("兼职延期失败！", 8, $_SERVER['HTTP_REFERER']);
                    }
                }
            }
        }
        
        // 兼职职位推荐
        if ($_POST['recid']) {
            
            $IntegralM = $this->MODEL('integral');
            $StatisM = $this->MODEL('statis');
            
            $id = intval($_POST['recid']);
            $uid = $this->uid;
            
            $_POST['day'] = intval($_POST['day']);
            
            if ($_POST['day'] < 1) {
                $this->ACT_layer_msg("请正确填写推荐天数！", 8, $_SERVER['HTTP_REFERER']);
            }
            
            $reow = $StatisM->getInfo($uid, array('usertype' => '2','field' => 'integral'));
			
            $partwheredata = array('uid' => $uid,'id' => $id);
            $part 	= 	$partM->geInfo('', array('where' => $partwheredata,'field' => 'name,rec_time'));
            $part	=	$part['info'];
			
            if ($part['rec_time'] < time()) {
                $time = time() + $_POST['day'] * 86400;
            } else {
                
                $time = $part['rec_time'] + $_POST['day'] * 86400;
            }
            
            $integral = $this->config['com_recjob'] * $_POST['day'];
            
            if ($reow['integral'] < $integral && $this->config['com_recjob_type'] == "2") {
                
                $this->ACT_layer_msg("您的" . $this->config['integral_pricename'] . "不足，请充值！", 8, "index.php?c=pay");
            } else {
                // 积分处理
                $IntegralM->company_invtal($this->uid,$this->usertype, $integral, false, "推荐兼职职位", true, 2, 'integral', 12);
            }
            
            $jobdata = array(
                
                'rec' => 1,
                
                'rec_time' => $time
            
            );
            
            $where['id'] = $id;
            
            $where['uid'] = $uid;
            
            $partM->upInfo($data,$where);
            
            $logM->addMemberLog($this->uid, $this->usertype, "发布推荐兼职职位《" . $part['name'] . "》", 9, 1);
            
            $this->ACT_layer_msg("推荐兼职成功！", 9, $_SERVER['HTTP_REFERER']);
        }
    }

    function yqmsInfo()
    {
        $JobM   =   $this->MODEL('job');

        $where              =   array();
        $where['uid']       =   $this->uid;
        $where['state']     =   1;
        $where['r_status']  =   1;
        $where['status']    =   0;

        $company_job        =   $JobM->getList($where, array('field' => '`name`,`id`,`is_link`','link' => 'yes'));
        $this->yunset('company_job', $company_job['list']);
        
        $yqmbM   =   $this->MODEL('yqmb');

        $ymlist        =   $yqmbM->getList(array('uid'=>$this->uid));
        $this->yunset('ymlist', $ymlist);
        $ymnum  = $yqmbM  ->getNum(array('uid'=>$this->uid));
        $ymcan  = $ymnum<$this->config['com_yqmb_num'] ? true : false; 
        $this->yunset('ymcan', $ymcan);    
        return $company_job['list'];
    }
	
	
}
?>