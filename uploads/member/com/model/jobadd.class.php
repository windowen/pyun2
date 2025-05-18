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
class jobadd_controller extends company
{

    function index_action()
    {
        
        $company    =   $this -> comInfo['info'];
        if(!$company['name'] || ! $company['provinceid'] || (!$company['linktel'] && ! $company['linkphone'])){
            $this->ACT_msg('index.php?c=info', '请先完善基本资料！');
        }
        $this->yunset('company', $company);
        $msg        =   array();
        
        $isallow_addjob =   '1';
        
        $url        =   $this->spid ? 'index.php' : 'index.php?c=binding';
        
        if ($this->config['com_enforce_emailcert'] == '1') {
            if ($company['email_status'] != '1') {
                
                $isallow_addjob =   '0';
                $msg[]          =   '邮箱认证';
            }
        }
        if ($this->config['com_enforce_mobilecert'] == '1') {
            if ($company['moblie_status'] != '1') {
                
                $isallow_addjob =   '0';
                $msg[]          =   '手机认证';
            }
        }

        if ($this->config['com_enforce_licensecert'] == '1') {
            $comM         =   $this->MODEL('company');
            $cert =   $comM -> getCertInfo(array('uid'=>$this->uid,'type'=>3), array('field' => 'uid'));
            
            if ($company['yyzz_status'] != '1' || empty($cert)) {
                
                $isallow_addjob =   '0';
                $msg[]          =   '营业执照认证';
            }
        }
        
        if ($isallow_addjob == '0') {
            if ($this->spid) {
                $this -> ACT_msg($url, '请联系主账号，先完成'.implode('、', $msg).'！');
            }else{
                $this -> ACT_msg($url, '请先完成'.implode('、', $msg).'！');
            }
        }
        
        if ($this->config['com_enforce_setposition'] == '1') {
            if (empty($company['x']) || empty($company['y'])) {
                
                $this->ACT_msg('index.php?c=map', '请先完成地图设置！');
            }
        }
 
        if ($this->config['com_gzgzh'] == '1') {

			$userinfoM	=	$this->MODEL('userinfo');
			$uInfo		=	$userinfoM->getInfo(array('uid' => $this->uid),array('field' => '`wxid`,`unionid`'));
			if (empty($uInfo['wxid']) && empty($uInfo['unionid'])) {
			    $this->cookie->SetCookie('gzh','',(strtotime('today') - 86400));
			    $this->ACT_msg('index.php', '请先关注公众号！');
			}
        }

        $statics    =   $this -> company_satic();
        
        if ($statics['addjobnum'] == 0) {	
            
            if($this->spid){
                
                $this->ACT_msg('index.php', '当前账号会员已到期，请联系主账号进行升级！', 8);
            }else{
                
                $this->ACT_msg('index.php?c=right', '你的会员已到期！', 8);
            }
            
        }
        
        if ($statics['addjobnum'] == 2) {	
        
            if ($this->config['integral_job'] != '0') {
                if($this->spid){
                    
                    $this->ACT_msg('index.php', '您的套餐数据已用完，请联系主账号进行分配！', 8);
                }else{
                    
                    $this->ACT_msg('index.php?c=right', '你的套餐已用完！', 8);
                }
            } else {
                if($this->spid){
					$this->MODEL('statis')->upInfo(array('job_num' => '1'), array('uid' => $this->spid, 'usertype' => '2'));
				}else{
					$this->MODEL('statis')->upInfo(array('job_num' => '1'), array('uid' => $this->uid, 'usertype' => '2'));
				}
                
            }
        }

        $CacheArr   =   $this->MODEL('cache')->GetCache(array('hy', 'job', 'city', 'com', 'circle','user'));
        $this->yunset($CacheArr);
        if(empty($CacheArr['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($CacheArr['job_type'])){
            $this   ->  yunset('jionly',1);
        }
        $row            =   array();
        $row['hy']      =   $company['hy'];
        $row['sdate']   =   time();
        $row['number']  =   $CacheArr['comdata']['job_number'][0];
        $row['type']    =   $CacheArr['comdata']['job_type'][0];
        $row['exp']     =   $CacheArr['comdata']['job_exp'][0];
        $row['report']  =   $CacheArr['comdata']['job_report'][0];
        $row['age']     =   $CacheArr['comdata']['job_age'][0];
        $row['edu']     =   $CacheArr['comdata']['job_edu'][0];
        $row['marriage']=   $CacheArr['comdata']['job_marriage'][0];
        $this->yunset('row', $row);
       
        $jobnum         =   $this->MODEL('job')->getJobNum(array('uid' => $this->uid));
        $this->yunset('jobnum', $jobnum);

        $this->public_action();
        $this->com_tpl('jobadd');
    }

    function edit_action()
    {

        $jobM       =   $this->MODEL('job');
        $statis     =   $this->company_satic();
        $this->yunset('statis', $statis);

        if ($_GET['id']) {

            $id     =   intval($_GET['id']);
        } else {
            
            if ($_GET['jobcopy']) {

                if ($statis['addjobnum'] == 2) {

                    if (intval($statis['integral']) < intval($this->config['integral_job'])) {

                        $this->ACT_msg($_SERVER['HTTP_REFERER'], '你的'. $this->config['integral_pricename'].'不够发布职位！', 8);
                    }
                }
            }
            $id     =   intval($_GET['jobcopy']);
        }
        
        $row    =   $jobM->getInfo(array('id' => $id,'uid'=>$this->uid),array('add'=>'yes'));

        if (empty($row)) {
            
            $this->ACT_msg('index.php?c=jobadd', '职位参数错误！');
        }

        
        

        $company = $this -> comInfo['info'];
        if ($company['linktel'] == '' && $company['linkphone']) {
            $company['linktel'] = $company['linkphone'];
        }
        $this->yunset('company', $company);
        $CacheArr   =   $this->MODEL('cache') -> GetCache(array('hy', 'job', 'city', 'com', 'user'));
        $this->yunset($CacheArr);
        if(empty($CacheArr['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($CacheArr['job_type'])){
            $this   ->  yunset('jionly',1);
        }
        if ($row['autotime'] > time()) {
            $row['autodate']    =   date('Y-m-d', $row['autotime']);
        }
        $row['description']     =   str_replace(array('ti<x>tle','“','”'), array('title',' ',' '), $row['description']);
        $fuli           =  explode(',', $row['welfare']);
        $row['arraywelfare'] = array_filter($fuli);
        $this->yunset('row', $row);

        $job_link = $jobM->getComJobLinkInfo(array('jobid' => $id, 'uid' => $this->uid));
        $this->yunset('job_link', $job_link);
        $this->public_action();
        $this->com_tpl('jobadd');
    }

    function save_action(){
        if ($_POST) {
            
            $company    =   $this -> comInfo['info'];
            
            $rstaus     =   $company['r_status'];
           
            $description = str_replace(array('&amp;', 'background-color:#ffffff', 'background-color:#fff', 'white-space:nowrap;'), array('&', 'background-color:', 'background-color:', 'white-space:'), $_POST['description']);

            if ($_POST['job_post']) {
                
                
                $cacheArr   =   $this->MODEL('cache')->GetCache('job');

                if(empty($cacheArr['job_type'])){
                    $_POST['job1']      =   $_POST['job_post'];
                    $_POST['job1_son']  =   '';
                    $_POST['job_post']  =   '';
                }else{
                    $categoryM  =   $this->MODEL('category');
            
                    $row1       =   $categoryM->getJobClass(array('id' => intval($_POST['job_post'])), '`keyid`');
                    $row2       =   $categoryM->getJobClass(array('id' => $row1['keyid']), '`keyid`');

                    if ($row2['keyid'] == '0') {
                        
                        $_POST['job1_son']  =   $_POST['job_post'];
                        $_POST['job1']      =   $row1['keyid'];
                        $_POST['job_post']  =   '';
                    } else {
                        
                        $_POST['job1_son']  =   $row1['keyid'];
                        $_POST['job1']      =   $row2['keyid'];
                    }
                }
            }
			
            $post   = array(
            
                'job1'          =>  intval($_POST['job1']),
                'job1_son'      =>  intval($_POST['job1_son']),
                'job_post'      =>  intval($_POST['job_post']),

                'provinceid'    =>  intval($_POST['provinceid']),
                'cityid'        =>  intval($_POST['cityid']),
                'three_cityid'  =>  intval($_POST['three_cityid']),

                'minsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
                'maxsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),

                'description'   =>  $description,

                'is_link'       =>  $_POST['islink'],
                'is_email'      =>  $_POST['isemail'] ?  $_POST['isemail'] : 1,
                'r_status'      =>  $rstaus,
                'hy'            =>  intval($_POST['hy']),
                'number'        =>  intval($_POST['number']),
                'exp'           =>  intval($_POST['exp']),
                'report'        =>  intval($_POST['report']),
                'age'           =>  intval($_POST['age']),
                'sex'           =>  intval($_POST['sex']),
                'edu'           =>  intval($_POST['edu']),
                'is_graduate'   =>  intval($_POST['is_graduate']),
                'marriage'      =>  intval($_POST['marriage']),
                'welfare'       =>  @implode(',', $_POST['welfare']),
                'lang'          =>  trim(pylode(',', $_POST['lang'])),
                'lastupdate'    =>  time(),
                'zuid'    		=>  $this->spid,

                'exp_req'       =>  trim($_POST['exp_req']),
				'edu_req'       =>  trim($_POST['edu_req']),
                'sex_req'       =>  @implode(',',$_POST['sex_req']),
                
            );
			if($this->config['joblock']!=1 || (empty($_POST['id']) && $this->config['joblock']==1)){
				$post['name']	=	$_POST['name'];
			}
			 
            $data   =   array(
                'post'      => $post,
                'id'        => intval($_POST['id']),
                'uid'       => $this->uid,
                'spid'      => $this->spid,
                'usertype'  => $this->usertype,
                'did'       => $this->userdid,

                'link_man'      => intval($_POST['islink']) == 2 ? $_POST['link_man'] : '',
                'link_moblie'   => intval($_POST['islink']) == 2 ? $_POST['link_moblie'] : '',
                'email'         => intval($_POST['islink']) == 2 ? $_POST['email'] : '',
                'link_address'  => intval($_POST['islink']) == 2 ? $_POST['link_address'] : '',
                'x'             => intval($_POST['islink']) == 2 ? $_POST['map_xval'] : '',
                'y'             => intval($_POST['islink']) == 2 ? $_POST['map_yval'] : '',
                
                'tblink'    => $_POST['tblink'],
                'jobcopy'   => $_POST['jobcopy']
            );

            $this->cookie->SetCookie('delay', '', time() - 60);

            $jobM   =   $this->MODEL('job');
            
            $return =   $jobM->addJobInfo($data);

            if ($return['errcode'] == 9) {
                
                $this->ACT_layer_msg($return['msg'], $return['errcode'], $return['id']);
            } elseif ($return['url']) {
                
                $this->ACT_layer_msg($return['msg'], $return['errcode'], $return['url']);
            } else {
                
                $this->ACT_layer_msg($return['msg'], $return['errcode']);
            }
        }
    }

    function getJobNum_action()
    {
        if ($_POST['uid']) {

            $statis = $this->company_satic();

            if ($statis) {
                echo $statis['addjobnum'];
                die();
            }
        }
    }
    
    /**
     *  @desc 发布职位条件查询 
     */
    function jobCheck_action()
    {
        
        $jobM   =   $this->MODEL('job');
        $statisM=   $this->MODEL('statis');
        
        $uid    =   $this->uid;
        $statis =   $statisM -> getInfo($uid, array('usertype' => 2, 'field' => '`integral`'));
        
        $result =   $jobM->getAddJobNeedInfo($uid, 1, $this->spid);
              
        $return =   array(
            
            'msgList'   =>  $result['pc'],
            'integral'  =>  (int)$statis['integral'],
            'spid'      =>  $this->spid
        );

        echo json_encode($return);
        
        die();
        
    }
}
?>