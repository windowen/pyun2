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
class job_model extends model{

    /**
     * @desc   引用log类，添加用户日志
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {

        require_once ('log.model.php');

        $LogM = new log_model($this->db, $this->def);

        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera,$type);

    }

	/**
     * @desc   引用system类，添加系统消息
     */
    private function addSystem($data) {
		include_once('sysmsg.model.php');
        $sysmsgM  =  new sysmsg_model($this->db, $this->def);
        $sysmsgM -> addInfo($data);
    }

    /**
     *  @desc   职位详情，单条查询
     *  @param  array   $where:职位查询条件
     *  @param  array   $data：自定义查询数组（参数处理条件：add处理发布时相关信息；com='yes'查询企业信息；hidecontac='yes' 处理联系方式；utype请求来源）
     */
    public function getInfo($where = array(), $data = array('add'=>'','com'=>'','hidecontac'=>'','utype'=>''))
    {
        if (! empty($where)) {

            $select =   $data['field'] ? $data['field'] : '*';

            if ($where['com_id']) {
                $where['uid']   =   $where['com_id'];
                unset($where['com_id']);
            }

            $Info   =   $this->select_once('company_job', $where, $select);

            if ($Info && is_array($Info)) {

                if (!empty($Info['welfare'])) {
                    $Info['arraywelfare']  	 =   explode(',', $Info['welfare']);
                }
                
                if($Info['sex_req']){

                    $Info['sex_req_arr'] = @explode(',',$Info['sex_req']);
                }

                $Info['job_lastupdate']  =   date('Y-m-d',$Info['lastupdate']);

                // 修改职位，语言要求判断使用
                if (!empty($data['add'])) {
                    $CacheList  		 =  $this->getClass(array('com'));
                    $Info['lang']        =   @explode(',', $Info['lang']);
                    foreach ($Info['lang'] as $k=>$v){
                        if (empty($v) || $v =='undefined'){
                            unset($Info['lang'][$k]);
                        }
						$langname[]			=	$CacheList['comclass_name'][$v];
						$Info['langname']	=	$langname;
                    }
                }
                // 查询企业信息

                /**
                 * @desc 查询企业信息，职位名称字段返回是 jobname
                 */
                if ($data['com'] == 'yes') {

                    // 查询企业申请信息
                    $userjobwhere   =   array(
                        'com_id'    =>  $Info['uid'],
                        'job_id'    =>  $Info['id'],
                        'endtime'   =>  array('<>', '')
                    );
                    $userjoblist    =   $this->getSqJobList($userjobwhere, array('field' => '`uid`,`endtime`,`datetime`'));

                    $totaltime      =   0; // 总时间
                    $surplustime    =   0; // 剩余时间
                    $i              =   0;

                    if (is_array($userjoblist) && $userjoblist) {
                        foreach ($userjoblist as $val) {
                            $surplustime    =   $val['endtime'] - $val['datetime'];
                            $totaltime      =   $totaltime + $surplustime;
                            $i              =   $i + 1;
                        }
                        $Info['totaltime']  =   $totaltime;
                        $Info['totalnum']   =   $i;
                    }
                    $ComInfo  =  $this->getComInfo($Info['uid'], array('logo' => 1));
                    
                    // 职位信息和企业信息整合
                    $Info     =  $this->getMixInfo($Info, $ComInfo);
                    
                }
                if (is_array($Info)) {
                    
                    $Info = $this->getInfoArray($Info);
                }
                $Info['comqcode'] = checkpic($Info['comqcode']);
                // 处理联系方式
                if ($data['hidecontact'] == 'yes') {

                    $linktel            =   $this->setContactHide($Info['linktel']);
                    $Info['linktel']    =   $linktel;
                }
                return $Info;
            }
        }
    }
	/**
     * @desc    获取职位联系方式
     * @param   array $data
     *          int   $id   为职位的id
     *          int   $uid  为登录的用户uid
     */
    public function getCompanyJobTel($data = array()){
        $res                        =   array(
            'errorcode'             =>  8,
            'msg'                   =>  ''
        );
        //判断参数
        $id                         =   intval($data['id']);
        $uid                        =   intval($data['uid']);
        $usertype                   =   intval($data['usertype']);
        if(empty($id)){
            $res['msg']             =   '参数错误';
            return $res;
        }
        //获取职位信息
        $Info                    =   $this -> getInfo(array('id' => $id), array('com'=>'yes'));

        if(empty($Info)){
            $res['msg']             =   '数据错误';
            return $res;
        }

        $jobInfo                    =   $this -> getContact($Info);
        
        $jobInfo['linktel_n']		=	$this->setContactHide($jobInfo['linktel']);
        $jobInfo['linkphone_n']	    =   substr_replace($Info['linkphone'],'****',4,4);
        
        $resData                    =   array(
            'linkman'  		=>  $jobInfo['linkman'],
            'linktel' 		=>  $jobInfo['linktel'],
            'linktel_n' 	=>  $jobInfo['linktel_n'],
            'address'   	=>  $jobInfo['address'],
            'linkphone'		=>  $Info['linkphone'],
            'linkphone_n'	=>  $jobInfo['linkphone_n'],
            'linkqq'   		=>  $Info['linkqq'],
            'busstops'    	=>  $Info['busstops']
        );
		
        $data	=	array(
			'id'		=>	$id,
			'uid'		=>	$uid,
			'usertype'	=>	$usertype,
			'resData'	=>	$resData,
			'com_id'	=>	$Info['uid'],
		    'rating'    =>  $Info['rating'],
			'is_link'	=>	$Info['is_link']
		);
		$res  =  $this->setCompanyLink($data);

		if (!isset($res['data']['linktel_n'])) {
		    $res['data']['linktel_n'] =   $jobInfo['linktel_n'];
		    $res['data']['address']   =   $jobInfo['address'];
		}

		return $res;
    }
	/**
     * @desc    获取企业联系方式
     * @param   array $data
     *          int   $id   为职位的id
     *          int   $uid  为登录的用户uid
     */
    public function getCompanyTel($data = array()){
        $res = array(
            'errorcode' => 8,
            'msg'       => ''
        );

        // 判断参数
        $id     =   intval($data['com_id']);
        $uid    =   intval($data['uid']);
        $usertype   =   intval($data['usertype']);

        if (empty($id)) {
            $res['msg'] = '参数错误';
            return $res;
        }

        // 获取企业信息
        $Info   =   $this->select_once('company', array('uid' => $id), '`uid`,`linkman`,`linktel`,`linkphone`,`linkqq`,`busstops`,`address`,`infostatus`,`rating`');

        if (empty($Info)) {
            $res['msg'] = '数据错误';
            return $res;
        }

        $Info['linktel_n'] = $this->setContactHide($Info['linktel']);

        $resData    =   array('linktel_n' => $Info['linktel_n']);

        // infostatus1-公开联系方式，2-不公开
        $data       =   array(
            'id'            =>  $id,
            'uid'           =>  $uid,
            'usertype'      =>  $usertype,
            'resData'       =>  $resData,
            'com_id'        =>  $Info['uid'],
            'infostatus'    =>  $Info['infostatus'],
            'rating'        =>  $Info['rating'],
            'utype'         =>  'com'
        );
        $res    =   $this->setCompanyLink($data);
        return $res;
    }

	function setCompanyLink($data){
        $uid    =   $data['uid'];
        $id     =   $data['id'];

        $res    =   array(
            'linkman'   =>  $data['resData']['linkman']
        );

        if ($uid == $data['com_id']) {

            $res['data']        =   $data['resData'];
            $res['errorcode']   =   9;

            return $res;
        }

        if (in_array($data['rating'], explode(',', $this->config['com_link_no']))) {

            if ($data['utype'] == 'com') {
                $res['msg']     =   '企业暂未显示联系方式，详情请咨询网站客服：' . $this->config['sy_freewebtel'];
            }else{
                $res['msg']     =   '企业暂未显示联系方式，请直接申请职位，详情请咨询网站客服：' . $this->config['sy_freewebtel'];
            }
            $res['errorcode']   =   2;
            return $res;

        }else if ($this->config['com_login_link'] == 1) {

            if ($data['utype'] == 'com') {

                if ($data['infostatus'] == "1") {

                    $res['data']        =   $data['resData'];
                } else {

                    $res['msg']         =   '企业暂未开启查看联系方式';
                    $res['errorcode']   =   1;
                    return $res;
                }
                $res['data']            =   $data['resData'];
            } else {

                if ($data['is_link'] == "1" || $data['is_link'] == "2") {
                    $res['data']        =   $data['resData'];
                } else {
                    $res['msg']         =   '企业暂未开启查看联系方式，请直接申请职位';
                    $res['errorcode']   =   1;
                    return $res;
                }
            }
        } elseif ($this->config['com_login_link'] == 2) {

            if ($data['utype'] == 'com') {
                $res['msg']     =   '企业暂未显示联系方式，详情请咨询网站客服：' . $this->config['sy_freewebtel'];
            }else{
                $res['msg']     =   '企业暂未显示联系方式，请直接申请职位，详情请咨询网站客服：' . $this->config['sy_freewebtel'];
            }
            $res['errorcode']   =   2;
            return $res;

        } elseif ($this->config['com_login_link'] == 3) {

            if (empty($uid)) {

                $res['msg']         =   '登录个人账号查看联系方式';
                $res['errorcode']   =   3;
                return $res;
            } else {

                if (!empty($data['usertype']) && $data['usertype'] != 1) {

                    $res['msg']         =   '只有个人用户才能查看';
                    $res['errorcode']   =   6;
                    return $res;
                } else {
                    $res['data']        =   $data['resData'];
                }
            }
        } elseif ($this->config['com_login_link'] == 4) {

            $resumenum  =   $this->select_num('resume_expect', array('uid' => $uid, 'state' => 1, 'job_classid' => array('<>', ''), 'status' => 1));

            if ($resumenum < 1 || $data['usertype']!= '1') {

                $res['msg']         =   '添加简历后查看联系方式';
                $res['errorcode']   =   4;
                return $res;
            } else {
                $tgresume          =   $this->select_num('resume_expect', array('uid'=>$uid,'state'=>1));

                if ($tgresume>0) {

                    $res['data']    =   $data['resData'];

                } else {
                    $res['msg']     =   '简历通过才能查看联系方式';
                    $res['errorcode'] = 7;
                    return $res;
                }
            }
        } elseif ($this->config['com_login_link'] == 5) {

            $uWhere['uid']      =   $uid;

            if ($data['utype'] == 'com') {
                $uWhere['com_id']   =   $data['com_id'];
            } else {
                $uWhere['job_id']   =   $id;
            }
            $msgresume          =   $this->select_num('userid_msg', array('uid'=>$uid,'jobid'=>$id));
            $sendresume         =   $this->select_num('userid_job', $uWhere);

			if (($msgresume>0||$sendresume > 0) && $data['usertype'] == 1) {

                $res['data']    =   $data['resData'];

            } else {
                $res['msg']     =   '申请职位才能查看联系方式';
                $res['errorcode'] = 5;
                return $res;
            }
        }
        if (! empty($res['data'])) {
            $res['errorcode'] = 9;
        }
        return $res;
    }
	/**
     * 获取联系方式
     */
    private function getContact($data = array())
    {
        $link   =   $this->select_once('company_job_link', array('jobid' => $data['id']), '`link_man`,`link_moblie`,`link_address`');

        $return =   array();

        if ($data['is_link'] == 1) {//默认联系方式

            $return['linkman']      =   $data['linkman'];

            if (empty($link['linktel']) && ! empty($link['linkphone'])) {

                $return['linktel']  =   $link['linkphone'];
            } else {

                $return['linktel']  =   $data['linktel'];
            }

            $return['address']      =   $data['address'];
        } elseif ($data['is_link'] == 2){

            if (! empty($link)) {//新的联系方式

                $return['linkman']  =   $link['link_man'];
                $return['linktel']  =   $link['link_moblie'];
                $return['address']  =   $link['link_address'];

            } else {//如果新的联系方式不存在，就显示默认联系方式

                $return['linkman']      =   $data['linkman'];

				if (empty($link['linktel']) && ! empty($link['linkphone'])) {

					$return['linktel']  =   $link['linkphone'];
				} else {
					$return['linktel']  =   $data['linktel'];
				}
				$return['address']      =   $data['address'];
            }
        }
        return $return;
    }

    /**
     * @desc   获取职位列表
     * @param  array   $whereData:查询条件
     * @param  array   $data:自定义处理数组  (例：后台数据：utype->admin)
     */
    public function getList($whereData,$data=array('cache'=>'','utype'=>''))
    {

        $ListJob  =  array();

        $select   =  $data['field'] ? $data['field'] : '*';

        if ($whereData['com_id']) {
            $whereData['uid']   =   $whereData['com_id'];
            unset($whereData['com_id']);
        }

        $List     =  $this -> select_all('company_job',$whereData, $select);

        if (!empty($List)) {

            $time    =  time();
            $jobids  =  array();

            foreach ($List as $v) {

                $jobids[]   =  $v['id'];
            }
            $options        =  array('job','hy','city','com');

            $cache          =  $this -> getClass($options);

            if ($data['cache']=='1') {

                $ListJob['cache']  =  $cache;

            }

            if (isset($data['sqjobnum']) && $data['sqjobnum'] == 'yes') {

                $sqList     =   $this -> getSqJobList(array('job_id'=>array('in', pylode(',', $jobids)), 'groupby'=>'job_id'), array('field'=>'`job_id`, `type`, count(`id`) as `num`'));
            }
            //职位联系信息
            if (isset($data['link']) && $data['link'] == 'yes') {

                $company    =   $this -> getComInfo($whereData['uid'], array('field'=>"`linktel`, `linkphone`, `linkman`, `address`"));

                $joblink    =   $this -> getComJobLinkList(array('jobid'=>array('in', pylode(',', $jobids))), array('field'=>'`uid`,`jobid`,`link_type`,`link_man`,`link_moblie`,`link_address`'));
            }
            /* 常用参数整理 */
            $todaystart  =  strtotime(date('Y-m-d',time()));
            $beforeYesterday    =   $todaystart - 86400 * 2;
            foreach ($List  as  $k  =>  $v) {
                if(isset($v['sdate'])){
                    if($v['sdate']>$beforeYesterday){
                        $List[$k]['newtime']  =  1;
                    }
                }
                if(isset($v['is_link']) && $v['is_link']=='1'){ // 默认联系方式

                    $List[$k]['link_man']	  =  $company['linkman'];
                    $List[$k]['link_moblie']  =  $company['linktel']?$company['linktel']:$company['linkphone'];
                    $List[$k]['address']	  =  $company['address'];

                }else if(isset($v['is_link']) && $v['is_link']=='2'){   // 新增联系方式

                    foreach($joblink as $val){
                        if($v['id']==$val['jobid']){
                            $List[$k]['link_man']	  =  $val['link_man'];
                            $List[$k]['link_moblie']  =  $val['link_moblie'];
                            $List[$k]['address']	  =  $val['link_address'];
                        }
                    }

                }else if(isset($v['is_link']) && $v['is_link']=='3'){   //  不展示联系方式

                    $List[$k]['link_man']	  =  '';
                    $List[$k]['link_moblie']  =  '';
                    $List[$k]['address']	  =  '';
                }

                /* 手机站地图，显示职位地址信息，其他联系方式清空 */
                if(isset($data['from']) && $data['from'] == 'wap_map'){
                    $List[$k]['link_man']	  =  '';
                    $List[$k]['link_moblie']  =  '';
                }

                if($v['autotime'] > $time){
                    $List[$k]['auto_n']       =  1;
                    $List[$k]['autodate']     =  date('Y-m-d',$v['autotime']);
                }
                if($v['xsdate'] > $time){
                    $List[$k]['xs']			  =  1;
                }
                if($v['rec'] == 1 && $v['rec_time'] > $time){
                    $List[$k]['rec_n']		  =  1;
                }
                if($v['urgent'] == 1 && $v['urgent_time'] > $time){
                    $List[$k]['urgent_n']	  =  1;
                }

                if(!empty($sqList)){

                    $List[$k]['jobnum']       =  0;

                    foreach($sqList as $val){

                        if($v['id'] == $val['job_id']){

                            $List[$k]['jobnum']  =  $val['num'];
                            $List[$k]['type']    =  $val['type'];

                        }
                    }
                }else{
                    $List[$k]['jobnum']          =  0;
                    $List[$k]['type']            =  1;
                }
                if(!empty($v['hy'])){
					$List[$k]['job_hy']          =	$cache['industry_name'][$v['hy']];
				}
				if(!empty($v['job1'])){
					$List[$k]['job_one_n']       =	$cache['job_name'][$v['job1']];
				}
				if(!empty($v['job1_son'])){
					$List[$k]['job_two_n']       =	$cache['job_name'][$v['job1_son']];
				}
				if(!empty($v['job_post'])){
					$List[$k]['job_three_n']     =	$cache['job_name'][$v['job_post']];
				}
				if(!empty($v['provinceid'])){
					$List[$k]['job_city_one']    =	$cache['city_name'][$v['provinceid']];
					$List[$k]['citystr']         =  $cache['city_name'][$v['provinceid']];
				}
				if(!empty($v['cityid'])){
					$List[$k]['job_city_two']    =	$cache['city_name'][$v['cityid']];

					if (isset($List[$k]['citystr'])){

					    $List[$k]['citystr']    .=  '-'.$cache['city_name'][$v['cityid']];

					}else{

					    $List[$k]['citystr']     =  $cache['city_name'][$v['cityid']];
					}
				}
				if(!empty($v['three_cityid'])){
					$List[$k]['job_city_three']  =	$cache['city_name'][$v['three_cityid']];
				}
				if(!empty($v['number'])){
					$List[$k]['job_number']      =	$cache['comclass_name'][$v['number']];
				}
				if(!empty($v['exp'])){
					$List[$k]['job_exp']         =	$cache['comclass_name'][$v['exp']];
				}else{
					$List[$k]['job_exp']         =	'不限';
				}
				if(!empty($v['report'])){
					$List[$k]['job_report']      =	$cache['comclass_name'][$v['report']];
				}
				if(!empty($v['sex'])){
					$List[$k]['job_sex']         =	$cache['com_sex'][$v['sex']];
				} 
				if(!empty($v['edu'])){
					$List[$k]['job_edu']         =	$cache['comclass_name'][$v['edu']];
				}else{
					$List[$k]['job_edu']         =	'不限';	
				}
				if(!empty($v['marriage'])){
					$List[$k]['job_marriage']    =	$cache['comclass_name'][$v['marriage']];
				}
				if(!empty($v['age'])){
					$List[$k]['job_age']         =	$cache['comclass_name'][$v['age']];
				}else{	
					$List[$k]['job_age']         =	'不限';
				}
				if(!empty($v['pr'])){
					$List[$k]['job_pr']          =	$cache['comclass_name'][$v['pr']];
				}
				if(!empty($v['mun'])){
					$List[$k]['job_mun']         =	$cache['comclass_name'][$v['mun']];
				}
				if(!empty($v['lang'])){

                    $lang                        =  @explode(',',$v['lang']);

                    foreach($lang  as $key => $value){

                        $langinfo[]              =  $cache['comclass_name'][$value];

                    }

                    $List[$k]['lang_n']          =  @implode(',', $langinfo);
                }
                if (!empty($v['minsalary']) || !empty($v['maxsalary'])) {

                    if(!empty($v['minsalary']) && !empty($v['maxsalary'])){

                        if($this ->config['resume_salarytype']==1){
                            $List[$k]['job_salary']  =  $v['minsalary'].'-'.$v['maxsalary'].'元';
                        }else{
                            if($v['maxsalary']<1000){
                                if($this->config['resume_salarytype']==2){
                                    $List[$k]['job_salary']  =  '1千以下';
                                }elseif($this->config['resume_salarytype']==3){
                                    $List[$k]['job_salary']  =  '1K以下';
                                }elseif($this->config['resume_salarytype']==4){
                                    $List[$k]['job_salary']  =  '1k以下';
                                }
                            }else if($v['minsalary']<1000){

                                $List[$k]['job_salary']  =	changeSalary($v['maxsalary']).'以下';
                            }else{

								$List[$k]['job_salary']  =  changeSalary($v['minsalary']).'-'.changeSalary($v['maxsalary']);
							}
                        }
                    }elseif (!empty($v['minsalary'])){
                        if($this ->config['resume_salarytype']==1){
                            $List[$k]['job_salary']  =  $v['minsalary'].'元以上';
                        }else{
                            $List[$k]['job_salary']  =  changeSalary($v['minsalary']).'以上';
                        }

                    }else{

                        $List[$k]['job_salary']  =  '面议';
                    }

                }else{

                    $List[$k]['job_salary']      =  '面议';
                }

                if (isset($data['isurl']) && $data['isurl']=='yes') {
                    if ($data['utype']=='admin') {
                        $List[$k]['joburl']  =	$this ->config['sy_weburl']."/job/index.php?c=comapply&id=".$v['id']."&type=admin";
                    }elseif($data['utype']=='wap'){
						$List[$k]['joburl']  =	Url('wap',array("c"=>"job",'a'=>'comapply',"id"=>$v['id']));
					}else{
                        $List[$k]['joburl']  =	Url('job', array('c' => 'comapply', 'id' => $v['id']));
                    }
                    $List[$k]['comurl']      =	Url('company', array('c' => 'show', 'id' => $v['uid']));
                }
                if(!empty($v['lastupdate'])){
                    $beginToday     =   strtotime('today');//今天开始时间戳
                    $beginYesterday =   strtotime('yesterday');//昨天开始时间戳
                    $week           =   strtotime("-1 week",time());//一周内时间戳
                    if ($v['lastupdate']>$week && $v['lastupdate']<$beginYesterday){
                        $List[$k]['lastupdate_n']   =   "一周内";
                    }elseif($v['lastupdate']>$beginYesterday && $v['lastupdate']<$beginToday){
                        $List[$k]['lastupdate_n']   =   "昨天";
                    }elseif($v['lastupdate']>$beginToday){
                        $List[$k]['lastupdate_n']   =   date("H:i",$v['lastupdate']);
                    }else{
                        $List[$k]['lastupdate_n']   =   date("Y-m-d",$v['lastupdate']);
                    }
                }
                if(!empty($v['welfare'])){
                    $List[$k]['welfare_n']  =  @explode(',', $v['welfare']);
                }
                if (isset($v['state'])){
                    if ($v['state'] == 0){
                        $List[$k]['state_n']  =  '审核中';
                    } elseif ($v['state'] == 1){
                        $List[$k]['state_n']  =  '已审核';
                    } elseif ($v['state'] == 3){
                        $List[$k]['state_n']  =  '未通过';
                    }
                }
            }

            if ($data['utype']=='admin') {

                //  后台处理企业状态、职位申请未查看简历、职位面试简历、会员等级名称等；
                $List   =   $this -> subJobList($List,$data);

            }

            $ListJob['list']    =   $List;
        }

        return $ListJob;

    }

    /**
     *  @desc   处理列表自定义查询数据
     */
    private function subJobList($List,$data = array()) {

        foreach ($List as $v) {

            $userids[]     =   $v['uid'];
            $jobids[]      =   $v['id'];
        }

        // 查询会员套餐缓存，提取会员等级名称
        include PLUS_PATH.'comrating.cache.php';

        //  查询会员审核状态
        $comWhere['uid']            =   array('in', pylode(',', $userids));
        $comData['field']           =   '`uid`,`r_status`,`yyzz_status`,`hotstart`,`hottime`,`logo`,`logo_status`';
        $comData['logo']            =   1;
        $comListA                   =   $this -> getComList($comWhere, $comData);
        $comList                    =   $comListA['list'];

        //  查询申请列表，提取未查看简历数据
        $sqJobWhere['job_id']       =   array('in',pylode(',', $jobids));
        $sqJobWhere['is_browse']    =   '1';
        $sqJobWhere['groupby']      =   'job_id';
        $sqJobData['field']         =   'count(id) as num,`job_id`';
        $sqJobList                  =   $this   ->  getSqJobList($sqJobWhere,$sqJobData);
        
        //  查询邀请面试，提取职位面试数据数量
        $yqmsWhere['jobid']         =   array('in',pylode(',', $jobids));
        $yqmsWhere['groupby']       =   'jobid';
        $yqmsData['field']          =   'count(id) as num,`jobid`';
        $yqmsList                   =   $this   ->  getYqmsList($yqmsWhere,$yqmsData);
        
        //  查询数据进行匹配提取
        foreach ($List  as  $k  =>  $v){
        
            foreach ($comrat as $val){
        
                if ($v['rating']    ==  $val['id']) {
        
                    $List[$k]['rating_name']    =   $val['name'];
                }
            }
            foreach ($comList as $val){
        
                if ($v['uid']   ==  $val['uid']) {
        
                    $List[$k]['c_status']       =   $val['r_status'];
                }
            }
            if (!empty($sqJobList)) {
        
                foreach ($sqJobList as $val){
        
                    if ($v['id']       ==  $val['job_id']) {
        
                        $List[$k]['browseNum']  =   $val['num'];
        
                    }else {
        
                        $List[$k]['browseNum']  =   0;
                    }
                }
            }else{
        
                $List[$k]['browseNum']          =   0;
            }
            if (!empty($yqmsList)) {
        
                foreach ($yqmsList as $val){
        
                    if ($v['id']       ==  $val['job_id']) {
        
                        $List[$k]['inviteNum']  =   $val['num'];
        
                    }else {
        
                        $List[$k]['inviteNum']  =   0;
                    }
                }
            }else{
        
                $List[$k]['inviteNum']  =   0;
            }
            if($v['xsdate']     >   0){
        
                $List[$k]['xstime']     =   date('Y-m-d',$v['xsdate']);
            }
            if($v['rec_time']   >   0){
        
                $List[$k]['recdate']    =   date('Y-m-d',$v['rec_time']);
            }
            if($v['urgent_time']    >   0){
        
                $List[$k]['eurgent']    =   date('Y-m-d',$v['urgent_time']);
            }
        }

        return $List;

    }
    public function addJobInfo($data = array('utype'=>'')){
        $post   =   $data['post'];

        $id     =   $data['id'];

        $uid    =   intval($data['uid']);

        unset($post['com_id']);
        $spid   =   !empty($data['spid']) ? intval($data['spid']) : '';
		
		if($post['name']){
			$job	=	$this -> select_once('company_job',array('uid'=>$uid,'name'=>$post['name']),'`id`');
		}else{
			$oldJob	=	$this -> select_once('company_job',array('uid'=>$uid,'id'=>$id),'`name`');
			$post['name']	=	$oldJob['name'];
		}
		
        if($job['id']!=$id && $id && $job['id']){
            $return['msg']      =   '职位名称已存在！';
            $return['errcode']  =   8;
        }

        if($data['utype']=='admin'){//后台修改添加不需要审核
            if($post['r_status']==1){
              $post['state']      =	1;
            }else{
              $post['state']      =	0;
            }
        }else{
            //查询企业认证是否认证成功
            $companycert        =	$this -> select_once('company_cert',array('uid'=>$uid,'type'=>3),'`uid`,`type`,`status`');
                //在企业用户设置里企业发布职位审核未开启的情况下，未审核和未通过的企业，发布职位默认是未审核的。
                if($post['r_status']!=1){
                    $post['state']		=	0;
                }else{
                    if($this->config['com_free_status']==1 && $companycert['status']==1){
                        $post['state']			=	1;
                    }else{
                        $post['state']		=	$this->config['com_job_status'];
                    }

                }

            if((!empty($post['is_link']) && !empty($post['is_link'])==2) && ((empty($data['link_man'])||empty($data['link_moblie'])||empty($data['email'])||empty($data['link_address'])))){
                $return['msg']      =   '请填写新的联系方式！';
                $return['errcode']  =   8;
            }
        }
        $com = $this -> select_once('company',array('uid'=>$uid),'`uid`,`name`,`logo`,`provinceid`,`pr`,`mun`,`x`,`y`,`did`');
        if($com){

            $post['com_name']			=	$com['name'];
            $post['com_logo']			=	$com['logo'];
            $post['com_provinceid']	    =	$com['provinceid'];
            $post['pr']				    =	$com['pr'];
            $post['mun']				=	$com['mun'];
            $post['did']				=	$com['did'];
        }
        require_once ('statis.model.php');
        $statisM    =   new statis_model($this->db, $this->def);

        $suid       =   $spid ? $spid : $uid;
        $statis     =   $statisM -> vipOver($uid, 2);


        if($statis){
            $post['rating']			=	$statis['rating'];
        }
        if(!$id || intval($data['jobcopy'])==$id){

            $post['sdate']			=	time();
            $post['uid']			=	$uid;
            $post['did']			=	$post['did'] ? $post['did'] : $data['did'];

            $return =   $statisM -> getCom(array('uid' => $suid, 'usertype'=>$data['usertype']));

            if (!empty($return) && is_array($return)) {

                return $return;

            }else{

    			$nid	=	$this -> insert_into('company_job',$post);
                $return['id']			=   $nid;
                $msg					=   '发布职位';
                $type					=   '1';
            }
            if($nid){

                require_once ('warning.model.php');
                $warningM = new warning_model($this->db, $this->def);
                $warningM -> warning(1, $uid);//预警提醒
            }
        }else{

            $where['id']	=	$id;
            $where['uid']	=	$uid;

            $nid	=	$this -> update_once('company_job',$post,$where);

            $return['id']   =   $id;
            $msg            =   '更新职位';
            $type           =   2;
        }
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);

        if($nid){
            $this -> update_once('company',array('jobtime'=>time(),'uid'=>  $uid));

            if($data['utype']!='admin'){

                if($data['link_man'] || $data['link_moblie'] || $data['email'] || $data['link_address']){
                    $joblink    =   array(
                        'uid'           =>  $uid,
                        'jobid'         =>  $return['id'],
                        'link_man'      =>  $data['link_man'],
                        'link_moblie'   =>  $data['link_moblie'],
                        'link_type'     =>  $post['is_link'],
                        'email_type'    =>  $post['is_email'],
                        'email'         =>  $data['email'],
                        'link_address'  =>  $data['link_address'],
                    );
                    $linkid	=	$this -> select_once('company_job_link',array('uid'=>$uid,'jobid'=>$id),'`id`');
                    if($linkid['id']){
                        $this -> update_once('company_job_link',$joblink,array('id'=>$linkid['id']));
                    }else{
                        $linkid['id']	=	$this -> insert_into('company_job_link',$joblink);
                    }
                    if($linkid['id'] && $data['tblink']==1){//新的联系方式同步到所有职位
                        unset($joblink['jobid']);
                        $this -> update_once('company_job_link',$joblink,array('uid'=>$uid));

                        $this -> update_once('company_job',array('is_link'=>'2'),array('uid'=>$data['uid']));
                    }
                }
				if($data['x']||$data['y']){
					$xvalue	=	$data['x'];
					$yvalue	=	$data['y'];

				}else{
					$xvalue	=	$com['x'];
					$yvalue	=	$com['y'];
				}
				require_once ('company.model.php');
				$companyM = new company_model($this->db, $this->def);

				if($data['tblink']==1){

					$companyM->setMap($uid,array('xvalue'=>$xvalue,'yvalue'=>$yvalue));

				}else{

					if($return['id']){
					   $this -> update_once('company_job',array('x'=>$xvalue,'y'=>$yvalue),array('id'=>$return['id']));
					}
				}

                $LogM -> addMemberLog($data['uid'],$data['usertype'],$msg."《".$post['name']."》",1,$type);//会员日志
            }


            if($post['state']==0){

                $return['msg']      =   $msg.'成功,等待审核';

            }else{
                $return['msg']      =   $msg.'成功';
            }

            $return['errcode']  =   9;
        }else{
             
            $return['msg']      =   $msg.'失败';
            $return['errcode']  =   8;
            $return['url']      =   $_SERVER['HTTP_REFERER'];
        }
        return $return;
	}
    /**
     * @desc    添加职位数据
     * @param   array $data 表单提交数据
     * @return  array $return 返回信息
     */
    public function addInfo($Data){
        return   $this -> insert_into('company_job',$Data);
    }

    /**
     * @desc    更新数据
     * @param   array       $where ： 查询条件
     * @param   array       $data  ： 更新数据
     */
	public function upInfo($data = array(), $where = array()){

        if (!empty($where)) {
            $ListA  =   $this -> getList($where, array('field' => 'uid'));
            $list   =   $ListA['list'];

            if ($list && is_array($list)) {

                $cuids  =   array();

                foreach ($list as $v) {

                    $cuids[] = $v['uid'];
                }
            }
            $nid    =   $this -> update_once('company_job', $data, $where);

            if ($nid) {

                $this -> upComInfo($cuids, $where = array(), array('jobtime' => time()));
            }

            return $nid;
        }
    }

    /**
     * @desc    后台审核职位
     * @param   string $id (1 | 1,2,3)
     * @param   array $data
     */
    public function statusJob($id, $upData = array())
    {

        $ids    =   @explode(',', trim($id));

        $return =   array('msg' => '非法操作！', 'errcode' =>  8);

        if (!empty($id)) {

            $idstr      =   pylode(',', $ids);

            $upData     =   array(

                'state'     =>  intval($upData['state']),
                'statusbody'=>  trim($upData['statusbody']),
                'lastupdate'=>  time()
            );

            $result     =   $this -> update_once('company_job', $upData, array('id' => array('in', $idstr),'r_status'=>1));

            if ($result) {

                if($upData['state'] == 1 || $upData['state'] == 3){

                    $msg    =   array();
                    $uids   =   array();

                    $jobs   =   $this->getList(array('id' => array('in', $idstr),'r_status'=>1), array('field' => '`id`,`uid`,`name`'));

                    foreach ($jobs['list'] as $v){

                        $uids[] =   $v['uid'];
                    }

                    require_once 'notice.model.php';
                    $noticeM    =   new notice_model($this->db, $this->def);

                    
                    $member     =   $this -> getUserList(array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`email`,`moblie`'));

                    foreach ($jobs['list'] as $k => $v){

                        if ($upData['state'] == 3) {

                            $statusInfo         =   '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>审核未通过';

                            if ($upData['statusbody']) {
                                $statusInfo     .=  '，原因：'.$upData['statusbody'];
                            }

                            $msg[$v['uid']][]   =   $statusInfo;
                        }elseif ($upData['state'] == 1){

                            $msg[$v['uid']][]   =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>审核通过';
                        }


                        foreach ($member as $mv){

                            $sendData	=   array();

                            if ($v['uid'] == $mv['uid']) {

                                $sendData['type']           =   $upData['state'] == 3 ? 'zzshwtg' : 'zzshtg';

                                $sendData['uid']            =    $v['uid'];
                                $sendData['email']          =    $mv['email'];
                                $sendData['moblie']         =    $mv['moblie'];

                                $sendData['jobname']        =    $v['name'];
                                $sendData['date']           =    date('Y-m-d H:i:s');
                                $sendData['status_info']    =    $upData['statusbody'];
                                //邮箱短信通知
                                $noticeM -> sendEmailType($sendData);
								$sendData['port']			=	'5';
                                $noticeM -> sendSMSType($sendData);
                              
                            }
                        }
                    }


                    //发送系统通知
                    require_once 'sysmsg.model.php';
                    $sysmsgM    =   new sysmsg_model($this->db, $this->def);
                    $sysmsgM -> addInfo(array('uid' => $uids,'usertype'=>2,'content'=>$msg));
                }
                //查询当前信息
                //查询当前条数
                $jobwhere['id']      =     array('in',$idstr);
                $jobnum              =     $this->getJobNum($jobwhere);

                if($jobnum>1){

                    $jobtwhere['id']        =   array('in',$idstr);
                    $jobtwhere['r_status']  =   1;
                    $jobtnum                =   $this->getJobNum($jobtwhere);

                    $jobwwhere['id']        =   array('in',$idstr);
                    $jobwwhere['r_status']  =   array('<>',1);
                    $jobwnum                =   $this->getJobNum($jobwwhere);

                    if($jobwnum>0){
                        $return['msg']      =   '批量审核成功'.$jobtnum.'条，失败'.$jobwnum.'条原因:企业账户未审核';
                    }else{
                        $return['msg']      =   '批量审核成功!';
                    }

                    $return['errcode']  =  9;
                }else{

                    $jobwwhere['id']           =     array('in',$idstr);
                    $jobwwhere['r_status']     =     array('<>',1);
                    $jobtnum                   =     $this->getJobNum($jobwwhere);
                    if($jobtnum>0){
                        $return['msg']      =  '审核职位失败，原因:企业账户未审核';
                        $return['errcode']  =  8;
                    }else{
                        $return['msg']      =  '审核职位(ID:'.$idstr.')设置成功';
                        $return['errcode']  =  9;
                    }

                }

            }else{

                $return['msg']      =  '审核职位(ID:'.$idstr.')设置失败';
                $return['errcode']  =  8;
            }

        }else {

            $return['msg']          =   '请选择需要审核的职位操作！';
            $return['errcode']      =   8;
        }

        return $return;
    }
    /**
     * @desc 职位审核，企业不是已审核状态，弹出同步操作状态审核
     * @param int $id
     * @param array $data|state statusbody
     */
    public function status($id, $data = array()){

        if (!$id){

            $return     =   array(
                'errcode' => 8,
                'msg'     => '参数错误！'
            );
            return $return;
        }else{

            $job        =   $this->getInfo(array('id' => $id), array('field' => '`id`,`uid`,`name`'));

            $upData     =   array(

                'state'     =>  intval($data['state']),
                'statusbody'=>  trim($data['statusbody']),
                'lastupdate'=>  time()
            );

            $uid        =   $data['uid'];

            $result     =   $this -> update_once('company_job', $upData, array('id' => $id, 'uid' => $uid));

            if ($result) {

                if ($data['state'] == '1') {
                    require_once 'userinfo.model.php';
                    $userinfoM  =   new userinfo_model($this->db, $this->def);

                    $post   =   array(
                        'id'        =>  $id,
                        'status'    =>  1
                    );
                    $userinfoM -> status(array('uid' => $uid, 'usertype' => 2), array('post' => $post));
                }

                //发送系统通知
                require_once 'sysmsg.model.php';
                $sysmsgM    =   new sysmsg_model($this->db, $this->def);
                $msg        =   $data['state'] == 3 ? '您的职位<a href="comjobtpl,'.$id.'">《'.$job['name'].'》</a>审核未通过；原因：'.$data['statusbody'] : '您的职位<a href="comjobtpl,'.$id.'">《'.$job['name'].'》</a>审核通过';
                $sysmsgM -> addInfo(array('uid' => $uid,'usertype'=>2,'content'=>$msg));

                require_once 'notice.model.php';
                $noticeM    =   new notice_model($this->db, $this->def);

                $member     =   $this -> getUserList(array('uid' => $uid), array('field' => '`uid`,`email`,`moblie`'));
                $sendData	=   array();

                if (!empty($member)) {

                    $sendData['type']           =    $data['state'] == 3 ? 'zzshwtg' : 'zzshtg';
                    $sendData['uid']            =    $uid;
                    $sendData['email']          =    $member['email'];
                    $sendData['moblie']         =    $member['moblie'];
                    $sendData['jobname']        =    $job['name'];
                    $sendData['date']           =    date('Y-m-d H:i:s');
                    $sendData['status_info']    =    $data['statusbody'];
                    //邮箱短信通知
                    $noticeM -> sendEmailType($sendData);
					$sendData['port']			=	'5';
                    $noticeM -> sendSMSType($sendData);
                }

                $return = array(
                    'errcode' => 9,
                    'msg'     => '审核设置成功！'
                );

            }else{
                $return = array(
                    'errcode' => 8,
                    'msg'     => '审核设置失败！'
                );
            }

            return $return;
        }
    }

    /**
     * @desc    删除数据（单项、批量）
     * @param   int/array   $id
     * @param   array       $data : utype  delAccount
     */
    public function delJob($id,$data=array('utype'=>'')){

        if(!empty($id)){

            $return 		= array(
                'errcode'   => 8,
                'layertype' => 0,
                'msg'       => ''
            );

            if(is_array($id)){

                $ids    =	$id;

                $return['layertype']	=	1;

            }else{

                $ids    =   @explode(',', $id);

            }

            $id             =   pylode(',', $ids);

            $listA          =   $this -> getList(array('id'=>array('in',$id)),array('field'=>'id,uid,name'));

            $jobList        =   $listA['list'];
            if ($data['utype'] == 'admin'){

				if($data['delAccount'] == '1'){

					$jUids	=	array();

					foreach ($jobList as $jk => $jv){
						$jUids[$jv['uid']]	=	$jv['uid'];
					}

					require_once ('userinfo.model.php');
					$userinfoM	=	new	userinfo_model($this->db, $this->def);
					return  $userinfoM -> delMember($jUids);
				}else{

					$delWhere	=	array('id' => array('in',$id));
				}
			}else{
				$delWhere	=	array('id' => array('in',$id),'uid'=>$data['uid']);
			}

            $return['id']	=	$this -> delete_all('company_job', $delWhere, '');

            if($return['id']){

                $msg      =  array();
                $uids     =  array();
				$checkids =  array();

				//  提取职位 uid 和职位名称
				foreach ($jobList   as  $k => $v){

					$uids[]  =  $v['uid'];

					if ($data['utype'] == 'admin'){

						$msg[$v['uid']][]	=  '您的职位《'.$v['name'].'》已被管理员删除';
						$checkids[]			=	$v['id'];
					}elseif($data['uid'] == $v['uid']){

						$checkids[]			=	$v['id'];
					}
				}
				if(!empty($checkids)){

					$id	=	pylode(',',$checkids);
				}else{

					$id	=	0;
				}

				if(!empty($uids)&& !empty($msg)){

					$this->addSystem(array('uid'=>$uids,'usertype'=>2,'content'=>$msg));
				}

                $this -> delete_all('user_entrust_record', array('jobid' => array('in',$id)), '');
                $this -> delete_all('company_job_link', array('jobid' => array('in',$id)), '');
                $this -> delete_all('userid_msg', array('jobid' => array('in',$id)), '');
                $this -> delete_all('userid_job', array('job_id' => array('in',$id)), '');
                $this -> delete_all('fav_job', array('job_id' => array('in',$id)), '');
                $this -> delete_all('look_job', array('jobid' => array('in',$id)), '');
                $this -> delete_all('report', array('eid' => array('in',$id),'usertype'=>'1','type'=>'0'), '');
            }

            $return['msg']		=	'职位(ID:'.$id.')';
            $return['errcode']	=	$return['id'] ? '9' :'8';
            $return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
        }else{

            $return['msg']		=	'请选择您要删除的职位！';
            $return['errcode']	=	8;
        }

        return	$return;
    }

    // 查询职位数目
    function getJobNum($Where=array()){

        if ($Where['com_id']) {
            $Where['uid']   =   $Where['com_id'];
            unset($Where['com_id']);
        }
        return $this->select_num('company_job',$Where);
    }


    //获取企业信息
    private function getComInfo($uid, $data = array()){

        require_once ('company.model.php');

        $CompanyM   =   new company_model($this->db, $this->def);

        return  $CompanyM -> getInfo($uid , $data);
    }


     //获取企业信息列表
     private function getComList($whereData , $data = array()){

         require_once ('company.model.php');

         $CompanyM   =   new company_model($this->db, $this->def);

         return  $CompanyM   ->  getList($whereData , $data);
     }


    // 更新企业信息
     private function upComInfo($id = null, $where=array(), $data=array()){

        require_once ('company.model.php');

        $CompanyM   =   new company_model($this->db, $this->def);

        return  $CompanyM   ->  upInfo($id, $where, $data);
    }

    // 获取账户套餐信息
    private function getStatisInfo($uid, $data = array()){

        require_once ('statis.model.php');

        $StatisM    =   new statis_model($this->db, $this->def);

        return  $StatisM    ->  getInfo($uid , $data);
    }

    //获取账号信息列表
    private function getUserList($whereData, $data = array()){

        require_once ('userinfo.model.php');

        $UserinfoM = new userinfo_model($this->db, $this->def);

        return  $UserinfoM   ->  getList($whereData , $data);
    }

    //获取简历信息列表resume_expect
    private function getResumeExpectList($whereData, $data = array()){

        require_once ('resume.model.php');

        $resumeM    =   new resume_model($this->db, $this->def);

        return  $resumeM   ->  getList($whereData , $data);
    }
	//获取简历信息列表resume
    private function getResumeList($whereData, $data = array()){

        require_once ('resume.model.php');

        $resumeM    =   new resume_model($this->db, $this->def);

        return  $resumeM   ->  getResumeList($whereData , $data);
    }

    //职位信息和企业信息重组合并
    private function getMixInfo($JobInfo, $ComInfo){

        $JobInfo['lang']    =   @explode(',',$JobInfo['lang']);

        $JobInfo['jobname'] =   $JobInfo['name'];
        unset($JobInfo['name']);

        $JobInfo['jobrec']  =   $JobInfo['rec'];
        unset($JobInfo['rec']);
        unset($JobInfo['did']);
		unset($ComInfo['id']);
        $Info = array_merge($JobInfo, $ComInfo);
        $Info['r_status']       =   $JobInfo['r_status'];
        $Info['uid']            =   $JobInfo['uid'];
        $Info['welfare']        =   $JobInfo['welfare'];
        $Info['com_provinceid'] =   $ComInfo['provinceid'];
        $Info['provinceid']     =   $JobInfo['provinceid'];
        $Info['cityid']         =   $JobInfo['cityid'];
        $Info['three_cityid']   =   $JobInfo['three_cityid'];
        $Info['sdate']          =   $JobInfo['sdate'];
        $Info['lastupdate']     =   $JobInfo['lastupdate'];
        $Info['rating']         =   $JobInfo['rating'];
        $Info['hy']             =   $JobInfo['hy'];
        $Info['pr']             =   $ComInfo['pr'];
        $Info['mun']            =   $ComInfo['mun'];
        $Info['x']              =   !empty($JobInfo['x']) ? $JobInfo['x'] :$ComInfo['x'];
        $Info['y']              =   !empty($JobInfo['y']) ? $JobInfo['y'] :$ComInfo['y'];
        $Info['statusbody']     =   $JobInfo['statusbody'];
        $Info['totaltime']      =   $JobInfo['totaltime'];
        $Info['totalnum']       =   $JobInfo['totalnum'];

		if($ComInfo['hotstart']<=time() && $ComInfo['hottime']>=time()){

			$Info['hotlogo']	=   1;
		}
        //待处理：简历投递时间，回复率等问题
        $this   ->  getOperInfo($Info);

        $Info['cert_n'] = @explode(',', $Info['cert']);

        $Info['com_url'] = Url('company', array( 'c' => 'show', 'id' => $JobInfo['uid'] ));

        $Info['description'] = str_replace(array('ti<x>tle', '“', '”','<img'), array('title', ' ', ' ','<img style="width:100%;height:auto;"'), $Info['description']);

        return $Info;
    }

    //简历处理时间，效率等信息整理
    private function getOperInfo($Info){

       // $operatime = time() - $Info['operatime'];
         if($Info['totalnum']!=0 &&  $Info['totaltime']!=0){
          $operatime                 =   ceil($Info['totaltime']/$Info['totalnum']);
          if($operatime < 3600){
            $Info['operatime']       =   '一小时以内';
          }else if($operatime >= 3600 && $operatime < 86400){
            $Info['operatime']       =   floor($operatime/3600).'小时';
          }else if($operatime >= 86400){
            $Info['operatime']       =   floor($operatime/86400).'天';
          }
        }else{
          $Info['operatime']       =   0;
        }
        $UWhere['com_id']       =   array('=', $Info['uid']);
        $UWhere['job_id']       =   array('=', $Info['id']);
        $UWhere['is_browse']    =   array('>','1');

        $replynum               =   $this   ->  getSqJobNum($UWhere);

        if ($Info['snum']   ==  0) {

            $Info['pre']        =   '0';

        } else {

            $Info['pre']        =   round(($replynum / $Info['snum']) * 100);

        }

        return $Info;

    }


    /**
     * 信息替换缓存数组内容
     * 2019-06-12 键名改为job_开头(原来键名_n结尾)
     */
    private function getInfoArray($jobInfo){

        $options                        =   array('hy','job','com','city');
        $cache                          =   $this -> getClass($options);

        $jobInfo['job_hy']              =   $cache['industry_name'][$jobInfo['hy']];
        $jobInfo['job_one']             =   $cache['job_name'][$jobInfo['job1']];
        $jobInfo['job_two']             =   $cache['job_name'][$jobInfo['job1_son']];
        $jobInfo['job_three']           =   $cache['job_name'][$jobInfo['job_post']];
        $jobInfo['job_city_one']        =   $cache['city_name'][$jobInfo['provinceid']];
        $jobInfo['job_city_two']        =   $cache['city_name'][$jobInfo['cityid']];
        $jobInfo['job_city_three']      =   $cache['city_name'][$jobInfo['three_cityid']];
        if (!empty($jobInfo['provinceid'])){
            
            $jobInfo['city_line']       =   $cache['city_name'][$jobInfo['provinceid']];
        }
        if (!empty($jobInfo['cityid'])){
            
            $jobInfo['city_line']       =   !empty($jobInfo['city_line']) ? $jobInfo['city_line'].'-'.$cache['city_name'][$jobInfo['cityid']] : $cache['city_name'][$jobInfo['cityid']];
        }
        
        $jobInfo['com_city_one']        =   $cache['city_name'][$jobInfo['com_provinceid']];
        $jobInfo['job_number']          =   $cache['comclass_name'][$jobInfo['number']];
        $jobInfo['job_exp']             =   $cache['comclass_name'][$jobInfo['exp']];
        $jobInfo['job_report']          =   $cache['comclass_name'][$jobInfo['report']];
        if($jobInfo['sex'] == 152){
			$jobInfo['sex']			    =	2;
		}elseif ($jobInfo['sex'] == 153){
			$jobInfo['sex']			    =	1;
		}
        $jobInfo['job_sex']             =   $cache['com_sex'][$jobInfo['sex']];
        $jobInfo['job_edu']             =   $cache['comclass_name'][$jobInfo['edu']];
        $jobInfo['job_marriage']        =   $cache['comclass_name'][$jobInfo['marriage']];
        $jobInfo['job_age']             =   $cache['comclass_name'][$jobInfo['age']];
        $jobInfo['job_pr']              =   $cache['comclass_name'][$jobInfo['pr']];
        $jobInfo['job_mun']             =   $cache['comclass_name'][$jobInfo['mun']];

        if($jobInfo['minsalary'] && $jobInfo['maxsalary']){
            if($this ->config['resume_salarytype']==1){
                $jobInfo['job_salary']      =   $jobInfo[minsalary].'-'.$jobInfo[maxsalary];
            }else{
                if($jobInfo['maxsalary']<1000){
                    if($this->config['resume_salarytype']==2){
                        $jobInfo['job_salary']      =   '1千以下';
                    }elseif($this->config['resume_salarytype']==3){
                        $jobInfo['job_salary']      =   '1K以下';
                    }elseif($this->config['resume_salarytype']==4){
                        $jobInfo['job_salary']      =   '1k以下';
                    }
                }else if($jobInfo['minsalary']<1000 && $jobInfo['maxsalary'] > 1000){
                    if($this->config['resume_salarytype']==2){
                        $jobInfo['job_salary']      =   changeSalary($jobInfo[maxsalary]).'以下';
                    }elseif($this->config['resume_salarytype']==3){
                        $jobInfo['job_salary']      =   changeSalary($jobInfo[maxsalary]).'以下';
                    }elseif($this->config['resume_salarytype']==4){
                        $jobInfo['job_salary']      =   changeSalary($jobInfo[maxsalary]).'以下';
                    }
                }else{
                    $jobInfo['job_salary']      =	changeSalary($jobInfo[minsalary]).'-'.changeSalary($jobInfo[maxsalary]);
                }
            }
        }elseif($jobInfo['minsalary']){
            if($this ->config['resume_salarytype']==1){
                $jobInfo['job_salary']      =   $jobInfo[minsalary].'以上';
            }else{
                $jobInfo['job_salary']      =   changeSalary($jobInfo[minsalary]).'以上';
            }
        }else{

            $jobInfo['job_salary']      =   '面议';
        }

		/**
        if($this ->config['resume_salarytype']!=1){
            $jobInfo['minsalary'] = changeSalary($jobInfo['minsalary']);
            $jobInfo['maxsalary'] = changeSalary($jobInfo['maxsalary']);
        }
		*/

        if($jobInfo['lang']!=''){

            $lang                       =   $jobInfo['lang'];
            foreach($lang as $key => $value){
                if($value){
                    $langinfo[]             =   $cache['comclass_name'][$value];
                }

            }
            $jobInfo['job_lang']        =   $langinfo;
            $jobInfo['lang_info']       =   @implode(',', $langinfo);

        }

        $jobInfo['welfare_info']        =   $jobInfo['welfare'];
        $jobInfo['job_welfare']         =   empty($jobInfo['welfare']) ? array() : @explode(',', $jobInfo['welfare']);

        //平均回复时长
        //$operatime                      =   time() - $jobInfo['operatime'];
        if($jobInfo['totalnum']!=0 &&  $jobInfo['totaltime']!=0){
          $operatime                    =   ceil($jobInfo['totaltime']/$jobInfo['totalnum']);
          if($operatime < 3600){
            $jobInfo['operatime']       =   '1小时以内';
          }else if($operatime >= 3600 && $operatime < 86400){
            $jobInfo['operatime']       =   floor($operatime/3600).'小时';
          }else if($operatime >= 86400){
            $jobInfo['operatime']       =   floor($operatime/86400).'天';
          }
        }else{
          $jobInfo['operatime']       =   0;
        }

        return $jobInfo;
      }



    /**
     * 设置联系方式为保密格式
     */
    private function setContactHide($tel){

        $tmpTel	=   '';
        $tmpTel	=   $this -> getNumbers($tel);
        $tel	=   sub_string($tmpTel);

        return $tel;

    }

    /**
     * 获取数字
     */
    private function getNumbers($phoneStr){

        $resNum                     =   '';

        preg_match_all('/\d+/', $phoneStr, $pregArr);

        if(!empty($pregArr) && !empty($pregArr[0])){
            foreach($pregArr[0] as $pv){
                $resNum             .=  $pv.' ';
            }
        }
        return mb_substr(trim($resNum), 0, 13);
    }



    // 查询职位联系方式
    public function getComJobLinkInfo($Where = array(),$data=array()){
		$select	=   $data['field'] ? $data['field'] : '*';
		$Info	=   $this -> select_once('company_job_link', $Where, $select);
        return $Info;

    }

    /**
     * @desc    查询 company_job_link 表数据，多条查询
     * @param array $Where
     * @param array $data
     * @return boolean|void|string
     */
    public function getComJobLinkList($Where = array(), $data=array()){

        return $this->select_all('company_job_link', $Where,$data['field']);

    }

    // 添加职位联系方式
    public function addComJobLinkInfo($data=array()){

        return $this->insert_into('company_job_link',$data);

    }
    // 跟新职位联系方式
    public function upComJobLinkInfo($data=array(),$Where = array()){

        return $this->update_once('company_job_link',$data,$Where);

    }


    /**
     * @desc    职位置顶
     * @param   int     $id
     * @param   array   $data
     */
    public function addTopJob($id, $data = array()){

        if(!empty($id) && !empty($data)) {

            $ids    =   @explode(',', $id);

            $return =   array();

            if(is_array($ids)){

                // 查询职位信息，提取职位置顶时间 xsdate，uid，name
                $ListA          =   $this -> getList(array('id' => array('in', pylode(',', $ids))), array('field'=>'id,uid,name,xsdate'));

                $jobList        =   $ListA['list'];

                if (!empty($jobList)) {

                    if (intval($data['top']) == 1) {

                        $jobData['xsdate']      =   '0';

                        $return['id']           =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $ids))));

                        $return['msg']		    =	'取消职位置顶(ID:'.$ids.')';

                        $return['msg']		    =	$return['id'] ? $return['msg'].'成功！' : $return['msg'].'失败！';

                    }else if (intval($data['days']) > 0) {

                        foreach($jobList as $v){

                            if($v['xsdate']     <   time()){

                                $gid[]          =   $v['id'];   //置顶日期已过期

                            }else{

                                $mid[]          =   $v['id'];   //置顶日期尚未过期

                            }

                        }

                        $time                   =   intval($data['days']) * 86400;

                        if(!empty($gid)){

                            $jobData['xsdate']  =   time()  +   $time;

                            $return['id']       =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $gid))));
                        }

                        if(!empty($mid)){

                            $jobData['xsdate']  =   array('+', $time);

                            $return['id']       =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $mid))));

                        }

                        $return['msg']		    =	'职位置顶(ID:'.$id.')';
                        $return['msg']		    =	$return['id'] ? $return['msg'].'设置成功！' : $return['msg'].'设置失败！';

                    }else {

                        $return['msg']          =   '置顶天数不能为空，请重试！';

                    }

                    if($return['id']){

                        $msg      =  array();
                        $uids     =  array();

                        //  提取职位uid 和职位名称
                        foreach ($jobList   as  $k => $v){

                            $uids[]  =  $v['uid'];

                            if (intval($data['top']) == 0){

                                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已置顶';

                            }elseif (intval($data['top']) == 1){

                                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>被管理员取消置顶';
                            }

                        }
                        //发送系统通知
                        $this->addSystem(array('uid'=>$uids,'usertype'=>2,'content'=>$msg));

                    }

                }  else {

                    $return['msg']      =  '系统繁忙';

                }

            }

        }
        //操作状态 9：成功 8:失败 配合原有提示函数
        $return['errcode']	=	$return['id'] ? '9' :'8';

        return	$return;

    }

    //  职位推荐
    public function addRecJob($id, $data = array()){

        if(!empty($id) && !empty($data)) {

            $ids    =   @explode(',', $id);

            $return =   array();

            if(is_array($ids)){

                // 查询职位信息，提取职位推荐时间 rec_time，uid，name
                $ListA          =   $this -> getList(array('id' => array('in', pylode(',', $ids))), array('field'=>'id,uid,name,rec_time'));

                $jobList        =   $ListA['list'];

                if (!empty($jobList)) {

                    if (intval($data['rec']) == 1) {

                        $jobData['rec']         =   '0';

                        $jobData['rec_time']    =   '0';

                        $return['id']           =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $ids))));

                        $return['msg']		    =	'取消职位推荐(ID:'.$ids.')';

                        $return['msg']		    =	$return['id'] ? $return['msg'].'成功！' : $return['msg'].'失败！';

                    }else if (intval($data['days']) > 0) {

                        foreach($jobList as $v){

                            if($v['rec_time']   <   time()){

                                $gid[]          =   $v['id'];   //推荐日期已过期

                            }else{

                                $mid[]          =   $v['id'];   //推荐日期尚未过期

                            }

                        }

                        $time                   =   intval($data['days']) * 86400;

                        $jobData['rec']         =   '1';

                        if(!empty($gid)){

                            $jobData['rec_time']=   time()  +   $time;

                            $return['id']       =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $gid))));
                        }

                        if(!empty($mid)){

                            $jobData['rec_time']=   array('+', $time);

                            $return['id']       =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $mid))));

                        }

                        $return['msg']		    =	'职位推荐(ID:'.$id.')';
                        $return['msg']		    =	$return['id'] ? $return['msg'].'设置成功！' : $return['msg'].'设置失败！';

                    }else {

                        $return['msg']          =   '推荐天数不能为空，请重试！';

                    }

                    if($return['id']){

                        $msg      =  array();
                        $uids     =  array();

                        //  提取职位uid 和职位名称
                        foreach ($jobList   as  $k => $v){

                            $uids[]  =  $v['uid'];

                            if (intval($data['rec']) == 0){

                                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已推荐';

                            }elseif (intval($data['rec']) == 1){

                                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>被管理员取消推荐';
                            }

                        }
                        //发送系统通知
                       $this->addSystem(array('uid'=>$uids,'usertype'=>2,'content'=>$msg));

                    }

                }  else {

                    $return['msg']      =  '系统繁忙';

                }

            }

        }
        //操作状态 9：成功 8:失败 配合原有提示函数
        $return['errcode']	=	$return['id'] ? '9' :'8';

        return	$return;

    }

    //  职位紧急招聘
    public function addUrgentJob($id, $data = array()){

        if(!empty($id) && !empty($data)) {

            $ids    =   @explode(',', $id);

            if(is_array($ids)){

                // 查询职位信息，提取职位紧急招聘时间 urgent_time，uid，name
                $ListA          =   $this -> getList(array('id' => array('in', pylode(',', $ids))), array('field'=>'id,uid,name,urgent_time'));

                $jobList        =   $ListA['list'];

                if (!empty($jobList)) {

                    if (intval($data['urgent']) == 1) {

                        $jobData['urgent']      =   '0';

                        $jobData['urgent_time'] =   '0';

                        $return['id']           =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $ids))));

                        $return['msg']		    =	'取消职位紧急招聘(ID:'.$ids.')';

                        $return['msg']		    =	$return['id'] ? $return['msg'].'成功！' : $return['msg'].'失败！';

                    }else if (intval($data['days']) > 0) {

                        foreach($jobList as $v){

                            if($v['urgent_time']<   time()){

                                $gid[]          =   $v['id'];   //紧急招聘日期已过期

                            }else{

                                $mid[]          =   $v['id'];   //紧急招聘日期尚未过期

                            }

                        }

                        $time                   =   intval($data['days']) * 86400;

                        $jobData['urgent']      =   '1';

                        if(!empty($gid)){

                            $jobData['urgent_time']     =   time()  +   $time;

                            $return['id']       =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $gid))));
                        }

                        if(!empty($mid)){

                            $jobData['urgent_time']     =   array('+', $time);

                            $return['id']       =   $this -> upInfo($jobData, array('id' => array('in', pylode(',', $mid))));

                        }

                        $return['msg']		    =	'职位紧急招聘(ID:'.$id.')';
                        $return['msg']		    =	$return['id'] ? $return['msg'].'设置成功！' : $return['msg'].'设置失败！';

                    }else {

                        $return['msg']          =   '紧急招聘天数不能为空，请重试！';

                    }

                    if($return['id']){

                        $msg      =  array();
                        $uids     =  array();

                        //  提取职位uid 和职位名称
                        foreach ($jobList   as  $k => $v){

                            $uids[]  =  $v['uid'];

                            if (intval($data['urgent']) == 0){

                                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已设置紧急招聘';

                            }elseif (intval($data['urgent']) == 1){

                                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>被管理员取消紧急招聘';
                            }

                        }
                        //发送系统通知
                       $this->addSystem(array('uid'=>$uids,'usertype'=>2,'content'=>$msg));

                    }

                }  else {

                    $return['msg']      =  '系统繁忙';

                }

            }

        }
        //操作状态 9：成功 8:失败 配合原有提示函数
        $return['errcode']	=	$return['id'] ? '9' :'8';

        return	$return;

    }

    // 职位申请，单条查询
    function getSqJobInfo($where = array(), $data=array()) {

        if (!empty($where)) {

            $select = $data['field'] ? $data['field'] : '*';

            $info           =   $this->select_once('userid_job',$where,$select);

            if ($info && is_array($info)) {

                return $info;

            }

        };
    }

    //  申请职位列表 ，多条查询
    function getSqJobList($whereData,$data=array()) {

        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this   ->  select_all('userid_job',$whereData,$select);

        $utype  =   $data['utype'] ? $data['utype'] : '';

        if (!empty($List) && $utype != 'simple') {

            $List   =   $this -> subSqListInfo($List, $data);

        }

        return $List;

    }

    // 申请职位列表信息补充
    private function subSqListInfo($List,$data=array()) {
        $uids           = array();
        $eids           = array();
        $jobids         = array();
        $uicomuidsds    = array();
        foreach ($List as $lk => $v){
            if($v['uid']){
                $uids[]     =   $v['uid'];
            }
            if($v['eid']){
                $eids[]     =   $v['eid'];
            }
            if($v['job_id']){
                $jobids[]   =   $v['job_id'];
            }
            if($v['com_id']){
                $comuids[]  =   $v['com_id'];
            }
        }

        $cache                          =   $this -> getClass(array('job','hy','city','com'));
        //  查询个人用户名
        $userWhere['uid']           	=   array('in', pylode(',', $uids));
        $userData['field']          	=   '`uid`,`username`';

        $userList                   	=   $this -> getUserList($userWhere, $userData);

        //  查询个人简历名称
        $reWhere['id']					=   array('in', pylode(',', $eids));

        $reData['field']                =   '`id`,`name`,`job_classid`,`minsalary`,`maxsalary`,`edu`,`exp`,`hy`,`lastupdate`,`city_classid`,`sex`,`birthday`';

        $resumeexpectList               =   $this -> getResumeExpectList($reWhere, $reData);

		//  查询个人姓名
		$rWhere['uid']                  =   array('in', pylode(',', $uids));
        $rData['field']                 =   '`uid`,`name`,`nametype`,`sex`,`telphone`,`def_job`,`photo`';
        $rData['downresume_where']      =   array('comid'=>$data['uid'],'usertype'=>$data['usertype']);

        $resumeList                     =   $this -> getResumeList($rWhere, $rData);

        if($data['usertype']==2){

            $userid_msg		=	$this -> select_all('userid_msg',array('fid'=>$data['uid'],'uid'=>array('in',pylode(",",$uids))),'`uid`');

		}

		if($data['usertype']==1){

			$company_job	=	$this -> getList(array('id' => array('in',pylode(',',$jobids))),array('field'=>'id,status,minsalary,maxsalary,exp,edu'));

			$company		=	$this -> getComList(array('uid' => array('in',pylode(',',$comuids))),array('field'=>'`cityid`,`uid`,`name`,`logo`'));
			
		}

		if ($data['is_link']  ==  'yes') {

		    $downList     =   $this -> select_all('down_resume', array('comid' => array('in', pylode(',', $comuids))),'`comid`,`eid`');

		}

        foreach ($List  as  $k  =>  $v){
            if($v['is_browse']){
                $List[$k]['is_browse']     =   (int)$v['is_browse'];
            }
            if($v['datetime']){
                $List[$k]['datetime_n']     =   date('Y-m-d',$v['datetime']);
            }

            foreach ($userList as $uv){

                if ($v['uid']   ==  $uv['uid']) {

                    $List[$k]['username']   =   $uv['username'];

                }
            }

            if ($data['is_link']  ==  'yes') {

                foreach ($downList as $dv) {

                    if ($dv['comid'] == $v['com_id'] && $v['eid'] == $dv['eid']) {
                        $List[$k]['islink'] = '1';
                    }

                }

            }

            foreach ($resumeexpectList['list'] as $rv){

                if ($v['eid']   ==  $rv['id']) {

                    $List[$k]['rname']			=   $rv['name'];
					$List[$k]['jobname']		=	$rv['job_classname'];
					$List[$k]['salary']			=	$rv['salary'];
					$List[$k]['edu']			=	$rv['edu_n'];
					$List[$k]['exp']			=	$rv['exp_n'];
					$List[$k]['sex']			=	$rv['sex_n'];
					$List[$k]['age']			=	$rv['age_n'];
					$List[$k]['lastupdate_n']	=	date('Y-m-d',$rv['lastupdate']);
					$List[$k]['hyname']			=	$cache['industry_name'][$rv['hy']];

					if($rv['job_classid']!=""){

						$job  =   @explode(',' , $rv['job_classid']);

						$joblist=array();

						foreach($job as $val){

						    $joblist[]    =   $cache['job_name'][$val];

						}

						$List[$k]['jobclassname'] =   $joblist['0'];
					}

					if($rv['city_classid']!=""){

						$city =   @explode(',' , $rv['city_classid']);

						$citylist =   array();

						foreach($city as $val){
							$citylist[]=$cache['city_name'][$val];
						}

						$List[$k]['cityclassname']=$citylist['0'];
					}

                }
            }
	 
			foreach ($resumeList as $val){
				$icon  =  $resumeInfo['sex'] == 1 ? $this->config['sy_member_icon'] : $this->config['sy_member_iconv'];
                if ($v['uid']   ==  $val['uid']) {
                    $List[$k]['name']		=    $val['name_n'];
                    $List[$k]['telphone']   =    $val['telphone'];
                    $List[$k]['photo']      =    checkpic($val['photo'],$icon);
                }
            }
			foreach($userid_msg as $val){
				if($v['uid']==$val['uid']){
					$List[$k]['userid_msg']	=	1;
				}
			}
			foreach($company_job['list'] as $val){
				if($v['job_id']==$val['id']){
					$List[$k]['status']		=	$val['status'];
					if($val['minsalary'] && $val['maxsalary']){
                        if($this ->config['resume_salarytype']==1){
						  $List[$k]['job_salary'] =	$val['minsalary']."-".$val['maxsalary']."元";
                        }else{
                            if($val['maxsalary']<1000){
                                if($this->config['resume_salarytype']==2){
                                    $List[$k]['job_salary'] =   "1千以下";
                                }elseif($this->config['resume_salarytype']==3){
                                    $List[$k]['job_salary'] =   "1K以下";
                                }elseif($this->config['resume_salarytype']==4){
                                    $List[$k]['job_salary'] =   "1k以下";
                                }
                            }else{
                                $List[$k]['job_salary'] =   changeSalary($val['minsalary'])."-".changeSalary($val['maxsalary']);
                            }
                        }
					}elseif($v['minsalary']){
                        if($this ->config['resume_salarytype']==1){
                            $List[$k]['job_salary'] =	$val['minsalary']."以上元";
                        }else{
                            $List[$k]['job_salary'] = changeSalary($val['minsalary'])."以上";
                        }
					}else{
						$List[$k]['job_salary'] =	"面议";
					}
					$List[$k]['edu_n']=$cache['comclass_name'][$val['edu']];
					$List[$k]['exp_n']=$cache['comclass_name'][$val['exp']];
				}
			}
			foreach($company['list'] as $val){
				$icon  =  $this->config['sy_unit_icon'];
				if($v['com_id']==$val['uid']){
					$List[$k]['city']		=	$val['job_city_two'];
					$List[$k]['logo']      =    checkpic($val['logo'],$icon);
				}
			}
		 
        }

        return $List;



    }

    /**
     * @desc     删除申请职位记录
     * @param    $id
     * @param    array $data
     * @return   $return
     */
    function delSqJob($id = null , $data = array()) {

        $return     =   array();

        if(!empty($id) || !empty($data['where'])){

            $where      =   array();

            if (!empty($id)) {

                if(is_array($id)){

                    $ids        =	$id;

                    $return['layertype']	=	1;

                }else{

                    $ids        =   @explode(',', $id);

    				$return['layertype']	=	0;

                }

                $ids            =   pylode(',', $ids);

                $where['id']    =   array('in', $ids);

            }

            if ($data['where']) {

                $where          =   array_merge($where, $data['where']);

            }elseif($data['utype']!='admin'){

				if($data['utype'] == 'user'){
					$where['uid']		=	$data['uid'];
				}else{
					$where['com_id']	=	$data['uid'];
				}


			}
			//个人会员中心删除申请记录
            if($data['utype'] == 'user'){
				if(intval($id)){
					$userid		=   $this -> getSqJobInfo(array('id'=>intval($id),'uid'=>$data['uid']),array('field'=>'`com_id`'));
				}
			}
            //企业会员中心删除申请记录
			if($data['utype'] == 'com'){

				$sqList			=   $this -> getSqJobList(array('id'=>array('in',$ids)),array('field'=>'`uid`,`job_id`,`type`'));

				if(is_array($sqList)){

					$jobid		=	array();
					$uid		=	array();

					foreach($sqList as $v){

						if($v['type']==1){

						    $jobid[]	=	$v['job_id'];

						}

						$uid[]			=	$v['uid'];

					}

 					$this -> update_once('company_job',array('operatime' => time(),'snum' => array('-', 1)),array('id'=>array('in',pylode(",",$jobid)),'uid'=>$data['uid']));
					$this -> update_once('member_statis',array('sq_jobnum'=>array('-',1)),array('uid'=>array('in',pylode(",",$uid))));
				}

				$num=count($sqList);
				$this -> update_once('company_statis',array('sq_job'=>array('-',$num)),array('uid'=>$data['uid']));

				

			}

			 
            
			if($data['norecycle'] == '1'){	// 数据库清理操作，不插入回收表

				$return['id']	=	$this -> delete_all('userid_job', $where, '','','1');
			}else{

				$return['id']	=	$this -> delete_all('userid_job', $where, '');
			}
			

            if($return['id']){

				if($data['utype'] == 'user'){
					$this -> update_once('company_statis',array('sq_job' => array('-',1)),array('uid'=>$userid['com_id']));
					$this -> update_once('member_statis',array('sq_jobnum' => array('-',1)),array('uid'=>$data['uid']));
					$this -> addMemberLog($data['uid'],$data['usertype'],'删除申请的职位',6,3);
				}

				if($data['utype'] == 'com'){
					$this -> addMemberLog($data['uid'],$data['usertype'],'删除申请职位的人才',6,3);
				}
				 
				if($data['utype']!='lietou'){
					$return['msg']	=	'职位申请记录(ID:'.$id.')';
				}
				$return['errcode']	=	9;
				$return['msg']		=	$return['msg'].'删除成功！';

			}else{
				$return['errcode']	=	'8';
				$return['msg']		=	$return['msg'].'删除失败！';
			}
        }else{
            $return['msg']		=	'请选择您要删除的数据！';
            $return['errcode']	=	8;
        }

        return	$return;
    }
	/**
     * @desc     申请职位：批量阅读
     * @param    $id
     * @param    array $data
     * @return   $return
     */
    function ReadSqJob($id = null,$data = array()) {

        if(!empty($id)){

			$rows       =   $this -> getSqJobList(array('id'=>array('in',pylode(",",$id)),'com_id'=>$data['uid']),array('field'=>"`job_id`,`type`"));

			if($rows && is_array($rows)){

			    foreach($rows as $val){

			        if($val['type']==1){
						$jobid[]	=	$val['job_id'];
					}
				}

				$this -> update_once('company_job', array('operatime' => time()), array('id' => array('in', pylode(',', $jobid)), 'uid' => $data['uid']));


			}

			$userid       =   $this -> getSqJobList(array('com_id' => $data['uid'], 'is_browse' => array('<>' , 1)),array('field' => "`id`"));

 			if($userid && is_array($userid)){

			    foreach($userid as $v){
					$userids[]	=	$v['id'];
				}

			}


			$where['com_id']                 =	$data['uid'];

            if (!empty($userids)) {

                $where['PHPYUNBTWSTART_A']   =	'';
                $where['id'][]			     =	array('in',pylode(",",$id),'AND');
                $where['id'][]				 =	array('notin',pylode(",",$userids), 'AND');
                $where['PHPYUNBTWEND_A']	 =	'';

            }else{

                $where['id']                =	array('in', pylode(",",$id));

            }

            $return['id']	=	$this -> update_once('userid_job', array('is_browse' => 2,'endtime' => time()), $where);

			$this -> addMemberLog($data['uid'],$data['usertype'],"批量阅读申请职位的人才",6,2);

            $return['layertype']=	1;

            $return['errcode']	=	$return['id'] ? 9 : 8;
            $return['msg']		=	$return['id'] ? '操作成功！' : '操作失败！';
        }else{
            $return['msg']		=	'请选择您要操作的数据！';
            $return['errcode']	=	8;
        }

        return	$return;
    }
	/**
     * @desc     申请职位：设置简历状态
     * @param    $id
     * @param    array $data
     * @return   $return
     */
    function BrowseSqJob($id = null,$data = array()) {
        if(!empty($id)){

			$browse	=	$data['browse'];
			$port	=	$data['port'];
			$row	=	$this -> getSqJobInfo(array('id'=>$id,'com_id'=>$data['uid']),array('field'=>'`uid`,`eid`,`job_id`,`type`,`endtime`'));
			if($row['type']==1){

			    $this -> update_once('company_job',array('operatime'=>time()),array('id'=>$row['job_id'],'uid'=>$data['uid']));
			}
            //判断当前是否为标记其他状态（除了已查看  待处理）
            if($browse>2 && $row['endtime']==""){
                $userjobdata    =   array(
                    'is_browse' =>  $browse,
                    'endtime'   =>  time()
                );
            }else{
                $userjobdata    =   array(
                    'is_browse' =>  $browse
                );
            }

            $this -> update_once('userid_job',$userjobdata,array('id'=>$id,'com_id'=>$data['uid']));

			if($browse==4){

				$resume =   $this -> select_once('resume',array('uid'=>$row['uid']),array('field'=>'uid,name,telphone,email'));

				if($row['type']==1){

				    $comjob	=	$this -> select_once("company_job",array('id'=>$row['job_id'],'uid'=>$data['uid']),array('field'=>"`name`,`com_name`"));
				}

				$ndata['uid']		=	$resume['uid'];
				$ndata['cname']		=	$data['username'];
				$ndata['name']		=	$resume['name'];
				$ndata['type']		=	"sqzwhf";
				$ndata['cuid']		=	$data['uid'];
				$ndata['company']	=	$comjob['com_name'];
				$ndata['jobname']	=	$comjob['name'];

				if(checkMsgOpen($this -> config)){
					$ndata["moblie"]=	$resume["telphone"];
				}
				if($this -> config['sy_email_sqzwhf']=='1' && $resume["email"] && $this -> config['sy_email_set']=="1"){
					$ndata["email"]	=	$resume["email"];
				}
				if($ndata["email"]||$ndata['moblie']){
					include_once('notice.model.php');
    	            $noticeM		=	new notice_model($this->db, $this->def);
					$noticeM -> sendEmailType($ndata);
					$ndata['port']	=	$port;
					$noticeM -> sendSMSType($ndata);
				}
			}
			$return	=	1;
        }

        return	$return;
    }

    /**
     * @desc 申请职位数目
     */
    function getSqJobNum($Where = array()){
        return $this->select_num('userid_job', $Where);
    }
    /**
     * @desc    增加申请职位记录
     * @param   array $Where
     * @param   array $data
     * @return  $return
     */
    function addSqJob($data = array()){
        return $this->insert_into('userid_job', $data);
    }
    /**
     * @desc    修改申请职位记录
     * @param   array $Where
     * @param   array $data
     * @return  $return
     */
    function updSqJob($Where = array(), $data = array()){
        return $this->update_once('userid_job', $data, $Where);
    }

    /**
     * @desc 申请职位
     *
     * @param array $data
     *            uid usertype 申请人
     *            job_id 职位id
     *            eid 简历id
     * @return $return
     */
    function applyJob($data = array(), $sqtype = '')
    {
        $res                =   array();
        $res['errorcode']   =   8;
        $res['msg']         =   '';
        $res['url']         =   '';

        //判断是否登录
        if(empty($data['uid']) || empty($data['usertype'])){
            $res['msg']         =   '请先登录！';
            $res['url']         =   'index.php?c=login';
            $res['errorcode']   =   1;
            $res['showlogin']   =   1;
            return $res;
        }

        //判断是否登录
        if($data['usertype'] != 1){
            $res['msg']         =   '您不是个人用户！';
            $res['errorcode']   =   2;

            return $res;
        }

        //投递数量
        $row				=	$this -> getSqJobNum(array(
            'uid'			=>	$data['uid'],
            'job_id'		=>	$data['job_id']
        ));

        $uid                =   $data['uid'];
        $jobid              =   $data['job_id'];
        $port				=	$data['port'];

        if(intval($row) > 0){

            $res['errorcode']   =   3;
            $res['msg']	        =	'您已经投递过该简历，请不要重复投递！';

            return $res;
        }

        //面试数量
        $rowmsg				=	$this -> getYqmsNum(array(
            'uid'			=>	$data['uid'],
            'jobid'		    =>	$data['job_id']
        ));

        if(intval($rowmsg) > 0){
            $res['errorcode']   =   4;
            $res['msg']	        =	'您已经收到该公司的面试邀请，请不要重复投递！';

            return $res;
        }

        //简历详情
        $resume = $resumess	=	array();
        if(!empty($data['eid'])){
            $resume         =   $this -> select_once('resume_expect',array('id' => $data['eid']), '`id`,`uid`, `uname`,`status`, `integrity`, `state`,`exp`,`edu`,`sex`,`birthday`');
        }else{
            $resume         =   $this -> select_once('resume_expect',array('uid' => $data['uid'],'defaults'=>1), '`id`,`uid`,`status`, `uname`, `integrity`, `state`,`exp`,`edu`,`sex`,`birthday`');
        }

        //判断简历
        if(empty($resume['id'])){

            $res['msg']	    =	'您还没有合适的简历，请先添加简历！';
            $res['url']	    =	Url('wap',array('c'=>'addresume'), 'member');

            return $res;
        }else {
            
            if($sqtype == ''){

                if($resume['state'] == 0){
                    $res['errorcode']   =   11;
                    $res['msg']	    =	'简历正在审核中，请联系管理员';
                    $res['url']	    =	'member/index.php?c=resume';
                    return $res;
                }elseif($resume['state'] == 2){
                    $res['errorcode']   =   11;
                    $res['msg']	    =	'简历被举报，请联系管理员';
                    $res['url']	    =	'member/index.php?c=resume';
                    return $res;
                }elseif($resume['state'] == 3){
                    $res['errorcode']   =   11;
                    $res['msg']	    =	'简历未通过审核，请联系管理员';
                    $res['url']	    =	'member/index.php?c=resume';
                    return $res;
                }elseif($this->config['user_sqintegrity'] && $resume['integrity'] < $this->config['user_sqintegrity']){
                    
                    $res['msg']	    =	'该简历完整度未达到'.$this->config['user_sqintegrity'].'%,请先完善简历！';
                    $res['url']	    =	'member/index.php?c=resume';
                    $res['errorcode']=  7;
                    return $res;
                }elseif($resume['status']!='1'){
                    $res['msg']     =   '请先公开您的简历！';
                    $res['url']     =   'member/index.php?c=privacy';
                    $res['errorcode']=  10;
                    return $res;
                }
            }
        }

        $info			    =	$this -> getInfo(array('id' => $data['job_id']), array('com' => 'yes'));
        if(empty($info)){
            $res['msg']	    =	'该职位不存在';
            $res['url']	    =	'index.php?c=resume';
            $res['errorcode']=  6;
            return $res;
        }

        if ($sqtype == ''){
            //投递门槛检测
            $exp_reqs    =   !empty($info['exp_req']) ? $info['exp_req'] : '';
            
            $edu_reqs    =   !empty($info['edu_req']) ? $info['edu_req'] : '';
            
            $sex_reqs    =   !empty($info['sex_req']) ? explode(',',$info['sex_req']) : array();
            
            //是否满足工作经历需求
            if($exp_reqs){
                
                $sexp   =   $this   ->  select_once('userclass',array('id'=>$exp_reqs),'`sort`');

                $rexp   =   $this   ->  select_once('userclass',array('id'=>$resume['exp']),'`sort`');

                if(!empty($rexp)){

                    if($rexp['sort']<$sexp['sort']){

                        $return['errorcode']  = 11;
                        $return['msg']      = '您的工作经验不符合投递要求';
                        return $return;

                    }
                
                }else{

                    $return['errorcode']  = 11;
                    $return['msg']      = '您的工作经验不符合投递要求';
                    return $return;

                }
                
            }
            //是否满足教育经历需求
            if($edu_reqs){
                
                $sedu   =   $this   ->  select_once('userclass',array('id'=>$edu_reqs),'`sort`');

                $redu   =   $this   ->  select_once('userclass',array('id'=>$resume['edu']),'`sort`');

                

                if(!empty($redu)){
                    
                    if($redu['sort']<$sedu['sort']){

                        $return['errorcode']  = 11;
                        $return['msg']      = '您的教育经验不符合投递要求';
                        return $return;

                    }
                
                }else{

                    $return['errorcode']  = 11;
                    $return['msg']      = '您的教育经验不符合投递要求';
                    return $return;

                }

                
            }
            //是否满足教育经历需求
            if(!empty($sex_reqs) && !in_array($resume['sex'],$sex_reqs)){
                
                $return['errorcode']  = 11;
                $return['msg']      = '您的性别不符合投递要求';
                return $return;
            }
            
            //是否满足年龄需求
            $resume_age  =   date('Y') - date('Y',strtotime($resume['birthday']));
            
            if(($info['minage_req'] && $resume_age < $info['minage_req']) || ($info['maxage_req'] && $resume_age > $info['maxage_req'])){
                
                $return['errorcode']  = 11;
                $return['msg']      = '您的年龄不符合投递要求';
                return $return;
            }
            
            
        }
        

        $value['job_id']    =	$data['job_id'];
        $value['com_name']	=	$info['com_name'];
        $value['job_name']	=	$info['jobname'];
        $value['com_id']	=	$info['uid'];
        $value['uid']		=	$data['uid'];
		$value['did']		=	$data['did'];
        $value['eid']		=	$resume['id'];
        $value['datetime']	=	time();
        $nid				=	$this -> addSqJob($value);

        if(!empty($nid)){

            include_once ('statis.model.php');
            $statisM        =   new statis_model($this->db, $this->def);

            $statisM -> upInfo(array('sq_job' 	=>	array('+', 1)), array('uid' => $value['com_id'], 'usertype' => 2));

            $statisM -> upInfo(array('sq_jobnum' =>	array('+', 1)), array('uid' => $value['uid'], 'usertype' => 1));

            //获取联系方式
            if($info['is_link'] == 1 || $info['is_link'] == 0){
                // 默认联系方式
                $info['mobile'] =   $info['linktel'];
                // 需要将邮件发送到邮箱
                if($info['is_email'] == 1){

                    $info['email']  =   $info['linkmail'];
                }
            }elseif ($info['is_link'] == 2){
                // 新联系方式
                $job_link   =   $this -> getComJobLinkInfo(array('jobid' => $data['job_id']), array('field' => '`link_moblie`,`link_address`'));
                $info['mobile']		=   $job_link['link_moblie'];
				$info['address']	=   $job_link['link_address'];
				// 需要将邮件发送到邮箱
				if($info['is_email'] == 1){

				    $email_link     =   $this -> getComJobLinkInfo(array('jobid' => $data['job_id']), array('field' => '`email`'));
				    $info['email']  =   $email_link['email'];
				}
            }

            include_once ('notice.model.php');
            $notice        =   new notice_model($this->db, $this->def);

            //发送email
            if($this->config['sy_email_set'] == 1){
							
				
				if($this->config['sy_email_sqzw'] ==1 && !$sqtype){				

					if($info['email'] && $info['is_email']!= 3){

						include_once ('resume.model.php');
						$resumeM    =   new resume_model($this->db, $this->def);

						$user       =   $resumeM -> getInfoByEid(array('eid' => $resume['id']));

						global $phpyun;

						$phpyun -> assign('Info',$user);

						$contents	=	$phpyun -> fetch(TPL_PATH.'resume/sendresume.htm',time());

						$emailData['email']     =   $info['email'];
						$emailData['subject']   =   '您收到一份新的求职简历！——'.$this->config['sy_webname'];
						$emailData['content']   =   $contents;
						$emailData['uid']       =   $info['uid'];
						$emailData['name']      =   $info['com_name'];
						$emailData['tbContent'] =   '简历详情eid:' . $resume['id'];
						$notice -> sendEmail($emailData);
					}
				}
    

            }
            //发送短信
            if($this->config['sy_msg_isopen']=='1' ){

                if($info['mobile'] && $info['is_link']!='0'){

					if($this->config['sy_msg_sqzw']==1 && !$sqtype){
						$msgData	=   array(
							'uid'		=>  $info['uid'],
							'name'		=>  $info['com_name'],
							'cuid'		=>  '',
							'cname'		=>  '',
							'type'		=>	'sqzw',
							'jobname'	=>	$info['jobname'],
							'date'		=>	date("Y-m-d"),
							'moblie'	=>	$info['mobile'],
							'port'		=>	$port
						);
						$notice -> sendSMSType($msgData);
					}
     
                    
                }
            }

            //记录系统日志
            include_once('sysmsg.model.php');
            $sysmsgM        =  new sysmsg_model($this->db, $this->def);
            $sysmsgM -> addInfo(array('uid' => $info['uid'], 'usertype'=>2,'content' => '您收到一份新的求职简历！'));
            
            //修改投递数量
            $this -> update_once('company_job', array('snum' => array('+', 1)), array('id' => $jobid));

            //记录会员日志
            $this -> addMemberLog($uid, 1, '我申请了职位：'.$info['jobname'], 6, 1);
            $res['errorcode']   =   9;
            $res['msg']         =   '投递成功！';
            return $res;
        }else{
            $res['msg']         =   '投递失败！';
            $res['errorcode']   =   2;
            return $res;
        }
    }

    
	// 添加邀请面试数据
    public function addYqmsInfo($yqdata = array())
    {
        $arr	=	array(
            'status' => 0,
            'msg' => ''
        );

        if (empty($yqdata['fuid']) || empty($yqdata['fusername'])) {

			$arr['msg']		=	'请先登录企业账号！';
			$arr['login']	=	2;
            return $arr;
        }

		if($yqdata['fusertype'] != 2){
			$arr['login']	=	2;
			$arr['msg']		=	'很抱歉，只有企业账号才能够邀请面试！';
            return $arr;
		}

        // 判断邀请时间
		$intertime	=	strtotime($yqdata['intertime']);
		if (empty($intertime)) {
		    $arr['msg']		=	'面试时间不能为空！';
		    return $arr;
		}
		if ($intertime < time()) {
		    $arr['msg']		=	'面试时间不能小于当前时间！';
		    return $arr;
		}
		if (empty($yqdata['linktel'])) {
		    $arr['msg']		=	'联系方式不能为空！';
		    return $arr;
		}

        if (empty($yqdata['address'])) {
            $arr['msg']		=	'面试地址不能为空！';
            return $arr;
        }

        $jobtype	=	intval($yqdata['jobtype']);

        if ($jobtype == '' || $jobtype < 2) {

            $jobtype = 0;
        }

        $uid	=	$yqdata['fuid'];
        $spid	=	$yqdata['spid'];

        $data	=	array(

			'uid'		=>	$yqdata['uid'],
			'title'		=>	'面试邀请',
			'content'	=>	$yqdata['content'],
			'fid'		=>	$uid,
			'datetime'	=>	time(),
			'address'	=>	$yqdata['address'],
			'intertime'	=>	$yqdata['intertime'],
			'linkman'	=>	$yqdata['linkman'],
			'linktel'	=>	$yqdata['linktel'],
			'jobid'		=>	intval($yqdata['jobid']),
			'jobname'	=>	$yqdata['jobname']
		);

		$info	=	array(
            'linkman'   =>  $yqdata['linkman'],
		    'linktel'   =>  $yqdata['linktel'],
			'jobname'	=>	$yqdata['jobname'],
			'username'	=>	$yqdata['username'],
			'content'	=>	$yqdata['content']
		);




        $p_uid	=	$yqdata['uid'];

        $num	=	$this -> getJobNum(array('uid' => $uid, 'state' => 1, 'status' => 0, 'r_status' => 1, 'id' => $data['jobid']));

        // 判断职位数量
        if ($num < 1) {
			$arr['status']	=	4;
            $arr['msg']		=	'职位信息错误，请重新选择！';
            return $arr;
        }

        // 是否在黑名单
		$black	=	$this -> select_num('blacklist', array('c_uid' => $p_uid, 'p_uid' => $uid));

        if (!empty($black)) {
            $arr['msg']	 =	'该用户暂不接受面试邀请！';
            return $arr;
        }

        // 查看是否邀请过
        $umessage	=	$this -> getYqmsInfo(array('uid' => $p_uid, 'fid' => $uid, 'type' => $jobtype));
        if (! empty($umessage)) {
            $arr['msg']	=	'已经邀请过该人才，请不要重复邀请！';
            return $arr;
        }

        $com	=	$this->select_once('company', array('uid' => $uid), '`name`, `did`');

        $resume =	$this->select_once('resume', array('uid' => $p_uid), '`name`, `def_job`,`uid`');

		$data['did']	=	$com['did'];
        $data['fname']	=	$com['name'];

        //保存更新邀请模板
        if($yqdata['save_yqmb']=='1'){

            include_once('yqmb.model.php');

            $yqmbM  =  new yqmb_model($this->db, $this->def);

            $ymwhere = array();

            if($yqdata['ymid']){

                $ymwhere['id'] = $yqdata['ymid'];

            }

            $ydata               =   array(
                'uid'           =>  $uid,
            );
            $job    =   $this -> select_once('company_job',array('id'=>$setData['jobid']),'`name`');
            $ymdata              =   array(
                'content'       =>  $yqdata['content'],
                'address'       =>  $yqdata['address'],
                'linkman'       =>  $yqdata['linkman'],
                'linktel'       =>  $yqdata['linktel'],
                'intertime'     =>  $yqdata['intertime'],
                'did'           =>  $com['did'],
                'name'          =>  $data['jobname'].'邀请面试模板',
            );
            $yqmbM -> addInfo($ymdata,$ydata,$ymwhere);
        }
        //保存邀请模板end

		$auto	=	false;

		include_once ('integral.model.php');
        $inteM		=	new integral_model($this->db, $this->def);

        include_once ('statis.model.php');
        $statisM	=	new statis_model($this->db, $this->def);

        $statisField	=	array('field' => '`rating`,`vip_etime`,`invite_resume`,`rating_type`,`integral`', 'usertype' => 2);

        $suid	=	$spid ? $spid : $uid;

        $row	=	$statisM->getInfo($suid, $statisField);

        // 判断会员是否可用
        if (isVip($row['vip_etime'])) {

            if ($row['rating_type'] == 1) { // 套餐模式

                if ($row['invite_resume'] == 0) { // 收费会员邀请简历已用完

                    if (empty($spid)) {

                        if ($this->config['com_integral_online'] == 3) { // 积分模式

                            if ($row['integral'] >= $this->config['integral_interview']) {

                                $vid	=	$this->addYqms($data);
                               
								// 积分操作记录
                                $inteM -> company_invtal($yqdata['fuid'], 2, $this->config['integral_interview'], $auto, $this->config['integral_pricename'].'抵扣，邀请会员面试', true, 2, 'integral', 14);

                                $arr['status'] = 3;
                            }

                        } else {

							$arr['status']	=	2;
                        }

                    } else {

                        $arr['msg'] = '当前账户套餐余量不足，请联系主账户增配！';
                    }
                } else {

                    // 收费会员简历没有用完的状态,直接邀请
                    $vid	=	$this->addYqms($data);
                    
                    // 计算消费数量
                    $statisM -> upInfo(array('invite_resume' => array('-', 1)), array('uid' => $suid, 'usertype' => 2));
                    $arr['status'] = 3;
                }
            } else { // 时间模式

                $vid	=	$this->addYqms($data);
                $arr['status'] = 3;
            }
        }

        if ($arr['status'] == 3) {

            $arr['vid']	=	$vid;

            // 发送邮件 短信通知
            $this -> msgPost($yqdata['uid'], $yqdata['fuid'], $info, $yqdata['port']);

            // 记录会员日志
            $this -> addMemberLog($yqdata['fuid'], $yqdata['fusertype'], '邀请了人才：'.$resume['name'], 4, 1);

			// 查询当前信息 修改职位申请状态为“看过” userid_job.is_browse = 2
            $row	=	$this->getSqJobInfo(array('job_id' => $yqdata['jobid'], 'com_id' => $yqdata['fuid']), array('field' => '`endtime`'));

            if ($row['endtime']) {

                $jobuserdata  =  array('is_browse' => 2);

            } else {

                $jobuserdata  =  array('is_browse' => 2, 'endtime' => time());
            }

            $this -> update_once('userid_job', $jobuserdata, array('com_id' => $yqdata['fuid'], 'job_id' => $yqdata['jobid']));
        }

        return $arr;
    }

    /**
     * 发送邮件 短信
     */
    private function msgPost($uid, $comid, $row = array(), $port=null){
        $com                =   $this -> select_once('company', array('uid' => $comid), '`uid`,`name`,`linkman`,`linktel`,`linkmail`');
        $info               =   $this -> select_once('member', array('uid' => $uid), '`email`, `moblie`');
        $resume             =   $this -> select_once('resume', array('uid' => $uid), '`name`');

		$data['uid']        =   $uid;
		$data['name']       =   $resume['name'];
		$data['cuid']       =   $com['uid'];
		$data['cname']      =   $com['name'];
		$data['type']       =   "yqms";
		$data['company']    =   $com['name'];
		$data['linkman']    =   $row['linkman']?$row['linkman']:$com['linkman'];
		$data['comtel']     =   $row['linktel']?$row['linktel']:$com['linktel'];
		$data['comemail']   =   $com['linkmail'];
		$data['content']    =   @str_replace("\n","<br/>",$row['content']);
		$data['jobname']    =   $row['jobname'];
		$data['username']   =   $row['username'];
		$data['email']      =   $info['email'];
		$data['moblie']     =   $info['moblie'];

        require_once ('notice.model.php');
        $noticeM			=   new notice_model($this->db, $this->def);
        $noticeM -> sendEmailType($data);
		$data['port']	=	$port;
        $noticeM -> sendSMSType($data);
    }

    /**
     * 通用的增加邀请面试
     */
    public function addYqms($data = array()){

        $return    =   $this -> insert_into('userid_msg', $data);
        if(!empty($data['uid'])){
            include_once('history.model.php');
            $historyM   =   new history_model($this->db, $this->def);
            $historyM -> addHistory('userid_msg', $data['uid']);
        }
        return $return;
    }

    //  面试邀请，单条查询
    function getYqmsInfo($where = array() , $data=array()) {

        if (!empty($where)) {

            $select  =  $data['field'] ? $data['field'] : '*';

            $info    =  $this->select_once('userid_msg',$where,$select);

            if ($info && is_array($info)) {
                if($data['yqh']){//查看邀请函
					if($data['usertype']==1){

						$this -> update_once("userid_msg",array('is_browse'=>2),array('id'=>$where['id'],'is_browse'=>1,'uid'=>$data['uid']));
					}
					$info['comname']		=	$info['fname'];
					$info['datetime']		=	date('Y-m-d',$info['datetime']);
				}

				$job						=	$this->select_once('company_job',array('id'=>$info['jobid']) , '`status`');

				$info['jobstatus']			=	$job['status'];
            }
			return $info;
        };

    }
    function upYqms($where=array(),$updata=array()){

        if(!empty($where) && !empty($updata)){

            $this -> update_once("userid_msg",$updata,$where);

        }

    }
    //  面试邀请列表 ，多条查询
    public function getYqmsList($whereData,$data=array()) {

        $select = $data['field'] ? $data['field'] : '*';

        $List  =   $this   ->  select_all('userid_msg',$whereData,$select);
       $utype  =   $data['utype'] ? $data['utype'] : '';

        if (!empty($List) && $utype != 'simple') {

          foreach ($List as $k => $v){
            $jobids[]	=  	$v['jobid'];
          }

            $jobs	=	$this->select_all('company_job',array('id'=>array('in',pylode(',',$jobids))) , '`status`,`id`');


			foreach ($List as $lk => $v){
                if($v['datetime']){
                    $List[$lk]['datetime_n']         =   date('Y-m-d',$v['datetime']);
                }
                $List[$lk]['intertime_n']         =   strtotime($v['intertime']);

				foreach($jobs as $jk=>$jv){
					if($jv['id']==$v['jobid']){
						$List[$lk]['jobstatus']			=	$jv['status'];
					}
				}
			}
            $List   =   $this -> subYqmsListInfo($List, $data);

        }

        return $List;
    }

    // 邀请面试列表信息补充
    private function subYqmsListInfo($List, $data = array())
    {
        $uids  =  $comids  =  array();

        foreach ($List as $v){
            if($v['uid'] && !in_array($v['uid'],$uids)){
				$uids[]    =  $v['uid'];
			}

			if($v['fid'] && !in_array($v['fid'],$comids)){
				$comids[]  =  $v['fid'];
			}
        }

        //  查询个人用户名
        $userWhere['uid']           =   array('in', pylode(',', $uids));
        $userData['field']          =   '`uid`,`username`';

        $userList                   =   $this -> getUserList($userWhere, $userData);
        //  查询个人姓名
		$rWhere['uid']            	=   array('in', pylode(',', $uids));
        $rData['field']             =   '`uid`,`name`,`nametype`,`sex`,`telphone`,`def_job`';

        $resume                		=   $this -> getResumeList($rWhere, $rData);

        //  查询个人简历
        $reWhere['uid']             =   array('in', pylode(',', $uids));
        $reWhere['defaults']        =   '1';
		$reData['field']            =   '`id`,`uid`,`name`,`job_classid`,`minsalary`,`maxsalary`,`exp`,`edu`,`sex`,`birthday`';

        $expectList                 =   $this -> getResumeExpectList($reWhere, $reData);

 		$dWhere['uid']              =   array('in', pylode(',', $uids));
        $dWhere['comid']          	=   $data['uid'];

        $downList                 	=   $this -> select_all('down_resume',$dWhere, '`uid`');

		$cWhere['uid']            	=   array('in', pylode(',', $comids));
        $cData['field']             =   '`uid`,`logo`,`logo_status`';
		$cData['logo']				=   1;
		$company					=	$this -> getComList($cWhere,$cData);

        foreach ($List  as  $k  =>  $v){
            $List[$k]['datetime_n']   =   date('Y-m-d H:i',$v['datetime']);
            foreach ($userList as $uv){

                if ($v['uid']   ==  $uv['uid']) {

                    $List[$k]['username']   =   $uv['username'];

                }
            }
            foreach($resume as $val){

	            if($v['uid'] == $val['uid']){
	                $List[$k]['name']      =  $val['name_n'];
	                $List[$k]['realname']  =  $val['username_n'];
	                $List[$k]['telphone']  =  $val['telphone'];
	            }
	        }
            foreach ($expectList['list'] as $rv){

                if ($v['uid']   ==  $rv['uid']) {
	                $List[$k]['exp']       =   $rv['exp_n'];
                    $List[$k]['age']       =   $rv['age_n'];
					$List[$k]['edu']       =   $rv['edu_n'];
	                $List[$k]['sex']       =   $rv['sex_n'];
	                if ($rv['job_classid'] != "") {
	                    $List[$k]['jobclassname'] = $rv['job_classname'];
	                }
                    $List[$k]['eid']      =   $rv['id'];
                }
            }
			foreach($downList as $va){
                if ($v['uid']   ==  $va['uid']) {
				    $List[$k]['down']="1";
                }
			}
			foreach($company['list'] as $val){
				if($v['fid'] == $val['uid']){
					$List[$k]['logo']      =  $val['logo'];
				}
			}
        }

        return $List;

    }

    /**
     * @desc     删除邀请面试记录
     * @param    $id
     * @param    array $data
     * @return   $return
     */
    function delYqms($id = null , $data = array()) {

        $return         =       array();

        if(!empty($id) || !empty($data['where'])){

            $where      =       array();

            if (!empty($id)) {

                if(is_array($id)){

                    $ids    =	$id;

                    $return['layertype']	=	1;

                }else{

                    $ids        =   @explode(',', $id);
    				$return['layertype']	=	0;

                }

                $ids            =   pylode(',', $ids);

                $where['id']    =   array('in', $ids);

            }

            if (!empty($data['where'])) {

                $where          =   array_merge($where, $data['where']);

            }elseif($data['utype']!='admin'){

				if($data['usertype'] == '1'){
					$where['uid']		=	$data['uid'];
				}elseif($data['usertype'] == '2'){
					$where['fid']	=	$data['uid'];
				}
			}
            if($data['norecycle'] == '1'){		//	数据库清理，不插入回收站

				$return['id']	=	$this -> delete_all('userid_msg', $where, '','','1');
			}else{

				 

				$return['id']	=	$this -> delete_all('userid_msg', $where, '');
			}

            $this -> addMemberLog($data['uid'],$data['usertype'],"删除邀请信息",4,3);

			$return['msg']		=	'邀请面试记录(ID:'.$id.')';

            $return['errcode']	=	$return['id'] ? '9' :'8';
            $return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';

        }else{
            $return['msg']		=	'请选择您要删除的数据！';
            $return['errcode']	=	8;
        }

        return	$return;
    }
    //  面试邀请数目
    function getYqmsNum($Where = array()){
        return $this->select_num('userid_msg', $Where);
    }
    /**
     * @desc     修改邀请面试状态
     * @param    array $arr
     */
    function setYqms($arr = array()) {
		$id				=	intval($arr['id']);
		$browse			=	intval($arr['browse']);
		$uid			=	intval($arr['uid']);
        if($id){

			$nid		=	$this -> update_once("userid_msg",array('is_browse'=>$browse),array("id"=>$id,"uid"=>$uid));

			$comuid		=	$this -> getYqmsInfo(array("id"=>$id),array("field"=>'`fid`,`jobid`,`linktel`,`linkman`'));

			$company	=	$this -> getComInfo($comuid['fid'],array('field'=>'linkmail,linkman,linktel'));

			$resume		=	$this -> select_once('resume',array("uid"=>$uid),'name');

			$data['uid']		=	$comuid['fid'];
			$data['cname']		=	$arr['username'];
			$data['type']		=	"yqmshf";
			$data['cuid']		=	$uid;
			$data['cusername']	=	$resume['name'];

			if($browse==3){

				$data['typemsg']	=	'同意';
				$msg_content 		= 	'用户 <a href="usertpl,'.$uid.'">'.$arr['username'].' </a>同意了您的邀请面试！';

				include_once('sysmsg.model.php');
				$sysmsgM  			=  new sysmsg_model($this->db, $this->def);
				$sysmsgM -> addInfo(array('uid'=>$comuid['fid'],'usertype'=>2,'content'=>$msg_content));
			}elseif($browse==4){

				$data['typemsg']	=	'拒绝';
			}
			if($this->config['sy_msg_yqmshf']=='1' && $company["linktel"] && checkMsgOpen($this -> config)){

				$data["moblie"] 	=	$company["linktel"];
			}
			if($this->config['sy_email_yqmshf']=='1' && $company["linkmail"] && $this->config['sy_email_set']=="1"){

				$data["email"]		=	$company["linkmail"];
			}
			if($data["email"] || $data['moblie']){

				$data['name']		=	$comuid['linkman'];
				require_once ('notice.model.php');
				$noticeM			=   new notice_model($this->db, $this->def);
				$noticeM -> sendEmailType($data);
				$noticeM -> sendSMSType($data);
			}
			if($nid){

				return array('msg'=>'操作成功！','errcode'=>9);
			}else{

				return array('msg'=>'操作失败！','errcode'=>8);
			}
		}
    }
	/**
     * @desc    取消申请职位
     * @param   array $Where
     * @param   array $data
     * @return  $return
     */
    function qxSqJob($arr = array()){

		$id			=	intval($arr['id']);
		$uid		=	intval($arr['uid']);
		$usertype	=	intval($arr['usertype']);

		$nid=$this -> updSqJob(array('id'=>$id,'uid'=>$uid),array('body'=>$arr['body']));
		if($nid){
			$this->addMemberLog($uid,$usertype,"取消申请的职位信息",6,3);
			return array('msg'=>'取消成功！','errcode'=>9);
		}else{
			return array('msg'=>'取消失败！','errcode'=>9);
		}
    }
    //  浏览职位，单条查询
    function getLookJobInfo($id, $data=array()) {

        if (!empty($id)) {

            $where['id']    .=  (int)$id;

            $info           =   $this->select_once('look_job',$where);

            if ($info && is_array($info)) {

                return $info;

            }

            return $info;
        };

    }

    //  浏览职位列表 ，多条查询
    public function getLookJobList($whereData,$data=array()) {

        $select =	$data['field'] ? $data['field'] : '*';

        $List	=   $this   ->  select_all('look_job',$whereData,$select);

        if (!empty($List)) {

			$List   =   $this -> subLookJobListInfo($List,$data);

        }

        return $List;
    }

    // 浏览职位列表信息补充
    private function subLookJobListInfo($List,$data) {

        foreach ($List as $v){

            $jobids[]       =   $v['jobid'];
            $uids[]         =   $v['uid'];
        }

		if(!empty($data)){
			$uid			=	intval($data['uid']);
			$usertype		=	intval($data['usertype']);
		}

        /* 提取个人用户 */
        $uWhere['uid']      =   array('in', pylode(',', $uids));
        $uData['field']     =   '`uid`,`username`';
        $userList           =   $this -> getUserList($uWhere, $uData);

        /* 提前职位名称，公司名称 */
        $jWhere['id']       =   array('in', pylode(',', $jobids));
        $jData['field']     =   '`id`,`name`,`com_name`,`provinceid`,`cityid`,`status`,`minsalary`,`maxsalary`,`edu`,`exp`';
        $jobList            =   $this -> getList($jWhere, $jData);

		//  查询个人姓名
		$rWhere['uid']		=   array('in', pylode(',', $uids));
		$rData['field']		=   '`uid`,`name`,`nametype`,`sex`,`telphone`,`def_job`';
		$resumeList			=   $this -> getResumeList($rWhere, $rData);


		//  查询个人简历
		$reWhere['uid']		=   array('in', pylode(',', $uids));
		$reWhere['defaults']=   '1';
		$reData['field']	=   '`id`,`uid`,`name`,`job_classid`,`minsalary`,`maxsalary`,`height_status`,`exp`,`edu`,`sex`,`birthday`';
		$expectList			=   $this -> getResumeExpectList($reWhere, $reData);

		if(!empty($expectList['list']) && $data['utype'] != 'admin'){
			$euids			=	array();
			foreach ($expectList['list'] as $val){
				$euids[]	=	$val['uid'];
			}
		}

        $userid_msg			=	$this -> select_all('userid_msg',array('fid'=>$uid,'uid'=>array('in', pylode(',', $uids))),"uid");
		$userid_job			=   $this -> select_all('userid_job',array('com_id'=>$uid,'uid'=>array('in',pylode(',',$uids))),'`uid`,`is_browse`');
			
        foreach ($List  as  $k  =>  $v){
		
			$List[$k]['datetime_n']   =   date('Y-m-d',$v['datetime']);

            foreach ($userList as $val){

                if ($v['uid']   ==  $val['uid']) {

                    $List[$k]['username']   =   $val['username'];
                }
            }

            foreach ($jobList['list'] as $val){

                if ($v['jobid']   ==  $val['id']) {

                    $List[$k]['job_name']       =   $val['name'];
                    $List[$k]['com_name']       =   $val['com_name'];
                    $List[$k]['cityname']       =   $val['job_city_one'];
                    if($val['job_city_two']){
                        $List[$k]['cityname']   .=  '-'.$val['job_city_two'];
                    }
                    $List[$k]['salary']     =   $val['job_salary'];
                    $List[$k]['exp_n']      =   $val['job_exp'];
                    $List[$k]['edu_n']      =   $val['job_edu'];
                    if($val['status']=="1"){
                        $List[$k]['status']="已下架招聘";
                    }else{
                        $List[$k]['status']="正在招聘";
                    }
                }
            }

            foreach($resumeList as $val){

                if($v['uid'] == $val['uid']){
                    $List[$k]['name']    =  $val['name_n'];
                }
            }

			foreach ($expectList['list'] as $val){

				if ($v['uid']   ==  $val['uid']) {
					$List[$k]['eid']        =   $val['id'];
					$List[$k]['exp']        =   $val['exp_n'];
					$List[$k]['edu']        =   $val['edu_n'];
					$List[$k]['sex']        =   $val['sex_n'];
					$List[$k]['age']        =   $val['age_n'];
					if ($val['job_classid'] != "") {
						$List[$k]['jobclassidname'] = $val['job_classname'];
					}
				}
			}

            foreach($userid_msg as $val){
                if($val['uid']==$v['uid'])
                {
                    $List[$k]['userid_msg']=1;
                }
            }

			foreach($userid_job as $val){

				if($v['uid']==$val['uid']){
					$List[$k]['is_browse']		=	$val['is_browse'];
				}
			}
			
			if($data['utype']!='admin' && !in_array($v['uid'], $euids)){
				unset($List[$k]);
			}
        }

        return $List;
    }

    /**
     * @desc     删除浏览职位记录
     * @param    $id
     * @param    array $data
     * @return   $return
     */
    function delLookJob($id = null , $data = array()) {

        $return                 =   array();

        if(!empty($id) || !empty($data['where'])){

            $where              =   array();

            if (!empty($id)) {

                if(is_array($id)){

                    $ids        =	$id;

                    $return['layertype']	=	1;

                }else{

                    $ids        =   @explode(',', $id);

                    $return['layertype']	=	0;

                }

                $ids            =   pylode(',', $ids);

                $where['id']    =   array('in', $ids);

            }

            if (!empty($data['where'])) {

                $where          =   array_merge($where, $data['where']);

            }

            if($data['usertype'] == '2'){

                $where['com_id']    =  intval($data['uid']);

                $return['id']		=	$this -> update_once('look_job',array('com_status' => 1), $where);

                $this -> addMemberLog($data['uid'],$data['usertype'],"删除已浏览简历记录（ID:".$id."）",26,3);
				$return['msg']		=	'浏览的职位记录(ID:'.$id.')';
			}elseif($data['usertype'] == '1'){

				$where['uid']    =  intval($data['uid']);
				$return['id']		=	$this -> update_once('look_job',array('status' => 1), $where);
				$this -> addMemberLog($data['uid'],$data['usertype'],"删除职位浏览记录（ID:".$id."）",26,3);
				$return['msg']		=	'职位浏览记录(ID:'.$id.')';
			}else{
			    if($data['norecycle'] == '1'){	//	数据库清理，不插入回收站

					$return['id']		=	$this -> delete_all('look_job', $where, '', '', '1');
				}else{
 
					$return['id']	=	$this -> delete_all('look_job', $where, '');
				}
				$return['msg']		=	'职位浏览记录(ID:'.$id.')';
			}

            $return['errcode']		=	$return['id'] ? '9' :'8';
            $return['msg']			=	$return['id'] ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
        }else{

            $return['msg']			=	'请选择您要删除的数据！';
            $return['errcode']		=	8;
        }
        return	$return;
    }

    /**
     * @desc     新增浏览职位记录
     * @param    array $data
     * @return   $return
     */
    function addLookJob($data = array(), $utype = ''){
        
        if (!empty($data['uid']) && !empty($data['jobid'])){
            
            $num  =  $this->select_num('look_job', array('uid' => $data['uid'], 'jobid' => $data['jobid']));
            
            if ($num > 0){
                // 已浏览过的，修改浏览时间
                $this->update_once('look_job', array('datetime' => $data['datetime'], 'status' => 0),array('uid' => $data['uid'], 'jobid' => $data['jobid']));
                
            }else{
                
                $sql  =  array(
                    'uid'       =>  $data['uid'],
                    'jobid'     =>  $data['jobid'],
                    'datetime'  =>  $data['datetime'],
                    'did'       =>  !empty($data['did']) ? $data['did'] : 0
                );
                
                if (!empty($data['com_id']) && !empty($data['jobname'])){
                    
                    $job['uid']     =  $data['com_id'];
                    $job['name']    =  $data['jobname'];
                    
                    $sql['com_id']  =  $data['com_id'];
                }else{
                    
                    $job  =  $this->select_once('company_job',array('id'=>$data['jobid']),'`uid`,`name`');
                    
                    $sql['com_id']  =  $job['uid'];
                }
                
                $expect  =  $this->select_once('resume_expect', array('uid' => $data['uid'], 'defaults' => 1, 'r_status' => 1), '`id`');
                
                if(!empty($expect)){
					$result  =  $this->insert_into('look_job', $sql);
					
					if ($result){
						if ($utype != ''){
							
							require_once ('history.model.php');
							
							$historyM = new history_model($this->db, $this->def);
							$historyM   ->  addHistory('lookjob', $data['jobid']);
						}
						
						$member  =  $this->select_once('member', array('uid'=>$data['uid']), '`username`');
						
						if(!empty($expect['id']) && !empty($job['name'])){
							
							$msgS  =  '用户<a href="resumetpl,' . $expect['id'] . '">' . sub_string($member['username']) . '</a>浏览了您的职位' . $job['name'];
							
							$this->addSystem(array('uid' => $job['uid'],'usertype' => 2,'content' => $msgS));
						}
					}
					
					return $result;
				}	
            }
        }
    }

    // 浏览职位数目
    function getLookJobNum($Where = array(), $data = array()) {

		if(!empty($data) && (int)$data['usertype'] == 2){
			
			$lookJobS	=	$this -> select_all('look_job', $Where, '`uid`');
			if(!empty($lookJobS)){
				$leuids	=	array();
				foreach($lookJobS as $lv){
					$leuids[]	=	$lv['uid'];
				}
				$num	=	$this -> select_num('resume_expect', array('uid' => array('in', pylode(',', $leuids)), 'defaults' => '1'));
			}
			$num	=	$num ? $num : 0;
		}else{

			$num	=	$this->select_num('look_job', $Where);
		}
        return $num;
    }

    /**
     * 收藏职位
     * @param   array $data
     * uid usertype 申请人
     * job_id       职位id
     * @return  $return
     */
    function collectJob($data = array('jobtype'=>''))
    {
        $res                  =  array();
        $res['errorcode']     =  8;
        $res['msg']           =  '';
        $res['url']           =  '';

        //判断是否登录
        if(empty($data['uid']) || empty($data['usertype'])){
            $res['msg']        =  '请先登录！';
            $res['url']        =  'index.php?c=login';
            $res['errorcode']  =  8;
            $res['state']      =  0;
            return $res;
        }

        //判断是否登录
        if($data['usertype'] != 1){
            $res['msg']        =   '您不是个人用户！';
            $res['errorcode']  =  8;
            $res['state']      =	4;
            return $res;
        }
        if($data['jobtype']=='lt'){

            $res            =   $this   ->  collectLtJob($data);

        }else{

            $res            =   $this   ->  collectComJob($data);
        }
        return $res;
    }
    
    private function collectComJob($data=array())
    {
        $is_set  =  $this -> getFavJob(array('uid' => $data['uid'], 'job_id' => $data['job_id']));
        if(!empty($is_set)){
            $res['msg']       =  '您已经收藏过该职位，请不要重复收藏！';
            $res['errorcode'] =  8;
            $res['state']     =  3;
        }else{
            $job                =   $this -> getInfo(array('id' => $data['job_id']));
            $value['job_id']    =   $job['id'];
            $value['com_name']  =   $job['com_name'];
            $value['job_name']  =   $job['name'];
            $value['com_id']    =   $job['uid'];
            $value['uid']       =   $data['uid'];
            $value['datetime']  =   time();
            $nid                =   $this -> addFavJob($value);
            if(!empty($nid)){
                //修改统计数量
                include_once ('statis.model.php');
                $statisM  =  new statis_model($this->db, $this->def);
                $statisM->upInfo(array('fav_jobnum'=>array('+', 1)), array('uid'=>$data['uid'], 'usertype' => 1));
                $member   =  $this->select_once("member",array("uid"=>$data['uid']),"`username`");
                //记录系统日志
                $this -> addSystem(array('uid' => $job['uid'],'usertype'=>2, 'content' => '用户 '.sub_string($member['username']).' 收藏了您的职位：'.$job['name']));

                $res['msg']        =  '收藏成功！';
                $res['errorcode']  =  9;
                $res['state']	   =  1;
            }else{
                $res['msg']        =  '收藏失败！';
                $res['errorcode']  =  8;
                $res['state']	   =  2;
            }
        }
        return $res;
    }

    /**
     * @desc     新增收藏记录
     * @param    array $data
     * @return   $return
     */
    function addFavJob($data = array()){
        return $this->insert_into('fav_job', $data);
    }

    // 收藏职位数目
    function getFavJobNum($Where = array()) {
        return $this->select_num('fav_job', $Where);
    }

    // 收藏职位
    function getFavJob($Where = array()) {
        return $this->select_once('fav_job', $Where);
    }
     //  申请职位列表 ，多条查询
    function getFavJobList($whereData,$data=array()) {
        $select = $data['field'] ? $data['field'] : '*';

        $List  =   $this   ->  select_all('fav_job',$whereData,$select);
        if (!empty($List)) {
            if($data['datatype']=='moreinfo'){//更多详细信息
                foreach ($List as $v){
    				if($v['type']==1){
    					$com_jobid[]=$v['job_id'];
    				}
    			}
    		
    			//  职位company_job
    			$jobWhere['id']				=   array('in', pylode(',', $com_jobid));
    			$jobData['field']			=   '`id`,`minsalary`,`maxsalary`,`provinceid`,`cityid`,`state`,`status`,`exp`,`edu`';

    			$jobList					=   $this -> getList($jobWhere, $jobData);

    			$StateNameList=array('0'=>'等待审核','1'=>'招聘中','2'=>'已结束','3'=>'未通过');

    			foreach ($List  as  $k  =>  $v){

					$List[$k]['datetime_n']				=	date('Y-m-d',$v['datetime']);
    				$List[$k]['statename']				=	'已关闭';
    				foreach($jobList['list'] as $val){
    					if($v['job_id']==$val['id']){
    						$List[$k]['job_edu']		=	$val['job_edu'];
    						$List[$k]['job_exp']		=	$val['job_exp'];
    						$List[$k]['salary']			=	$val['job_salary'];
    						$List[$k]['cityname']		=	$val['job_city_one'];
    						if($val['job_city_two']){
    							$List[$k]['cityname']	.=	'-'.$val['job_city_two'];
    						}
    						$List[$k]['statename']		=	$StateNameList[$val['state']];
    						if($val['status'] == 1){
    							$List[$k]['statename']	= 	'已下架';
    						}
    					}
    				}

    			}
            }
        }
        return $List;
    }
	/**
     * @desc     删除个人收藏职位记录
     * @param    $id
     * @param    array $data
     * @return   $return
     */
    function delFavJob($id = null , $data = array('utype'=>null)) {

        $return     =   array();

        if(!empty($id)){

            $where      =   array();

            if (!empty($id)) {

                if(is_array($id)){

                    $ids	=	$id;
					$return['layertype']	=	1;

                }else{

					$ids	=   @explode(',', $id);
    				$return['layertype']	=	0;
				}

                $ids	=	pylode(',', $ids);

                $where['id']	=	array('in', $ids);
				$where['uid']	=	$data['uid'];
			}
  

            $return['id']	=	$this -> delete_all('fav_job', $where, '');
			if($return['id']){

				if($data['utype'] == 'user'){

					$this -> update_once('member_statis',array('fav_jobnum'=>array('-',1)),array('uid'=>intval($data['uid'])));
					$this -> addMemberLog(intval($data['uid']),intval($data['usertype']),"删除收藏的职位",5,3);
				}

				$return['errcode']	=	9;
				$return['msg']		=	'删除成功！';

			}else{

				$return['errcode']	=	'8';
				$return['msg']		=	'删除失败！';
			}
        }else{

            $return['msg']		=	'请选择您要删除的数据！';
            $return['errcode']	=	8;
        }

        return	$return;
    }
    // 添加职位，企业会员套餐判断
    public function ckStatis($uid, $data = array()){

        if(!empty($uid)){

            $err        =   array();

            require_once ('statis.model.php');

            $StatisM    =   new statis_model($this->db, $this->def);

            $statis     =   $StatisM -> getInfo(intval($uid), array('usertype'=>intval($data['usertype']),'field'=>'`vip_etime`,`rating_type`,`job_num`'));

            if (!empty($statis)) {

                if(isVip($statis['vip_etime'])){

                    if($statis['rating_type'] == 1){

                        if($statis['job_num'] < 1){

                            $err['msg'] = '该会员发布职位用完！';
                            $err['errcode'] = 8;
                        }else{
                            $StatisM -> upInfo(intval($uid), array('usertype'=>'2'), array('job_num'=>array('-',1)));
                        }
                    }
                }else{
                    $err['msg'] = '该会员套餐已到期！';
                    $err['errcode'] = 8;
                };
                return $err;
            }
        }
    }

    /**
     *  @desc   获取缓存数据
     */
    private function getClass($options){

        if (!empty($options)){

            include_once ('cache.model.php');

            $cacheM     =   new cache_model($this->db, $this->def);

            $cache      =   $cacheM -> GetCache($options);

            return $cache;
        }
    }

    //更新职位点击率
    function addJobHits($id){
        if($this -> config['sy_job_hits'] > 100 || !$this -> config['sy_job_hits']){
            $hits       =   1;
        }else{
            $hits       =   mt_rand(1, $this->config['sy_job_hits']);
        }
        $this -> update_once('company_job', array('jobhits' => array('+', $hits)), array('id' => $id));
    }

    //职位推广：紧急 推荐 置顶 兼职推荐
    function jobPromote($uid, $data = array())
    {
        require_once ('statis.model.php');

        $StatisM    =   new statis_model($this->db, $this->def);

        $time       =   time();

        $type       =   intval($data['type']);

        $suid       =   !empty($data['spid']) ? intval($data['spid']) : $uid;

        $statis     =   $StatisM -> getInfo($suid, array('usertype' => '2'));

        $online     =   (int)$this->config['com_integral_online'];   //  消费模式
        $integralPro=   (int)$this->config['integral_proportion'];   //  积分比例

        $topPirce   =   $this->config['integral_job_top'];      //  职位置顶金额
        $recPirce   =   $this->config['com_recjob'];            //  职位推荐金额
        $urgentPirce=   $this->config['com_urgent'];            //  职位紧急招聘金额

        $return     =   array();

        if (isVip($statis['vip_etime'])) {

			$comSingle	=	@explode(',', $this->config['com_single_can']);

            if ($type == 1) { // 置顶
				
				$return['single']	=	in_array('jobtop', $comSingle)? '1' : '2';

                if ($statis['top_num'] > 0) {

                    $return['status']   =   1;
                    $return['num']      =   $statis['top_num'];
                } else {

                    if(empty($data['spid'])){

                        if ($online!=4) {

                            if ($online == 3) {

                                $return['jifen']    =   $topPirce * $integralPro;
                                $return['integral'] =   intval($statis['integral']);
                                $return['propor']   =   $integralPro;
                            }else{

                                $return['price']    =   $topPirce;
                                $return['integral'] =   intval($statis['integral']);
                            }

                        }else{
                            $return['price']        =   $topPirce;
                        }

						$return['msg']          =   "您的套餐已用完，您可以<a href='".$this->config['sy_weburl']."/wap/member/index.php?c=rating' style='color:red;cursor:pointer;'>购买会员</a>！";
                        $return['online']   =   $online;
                        $return['status']   =   2;

                    }else{

                        $return['msg']      =   '当前账户套餐余量不足，请联系主账户增配！';
                    }

                }
            } else if ($type == 2) { // 推荐
				
				$return['single']	=	in_array('jobrec', $comSingle)? '1' : '2';

                if ($statis['rec_num'] > 0) {

                    $return['status']   =   1;
                    $return['num']      =   $statis['rec_num'];
                } else {

                    if(empty($data['spid'])){

                        if ($online!=4) {

                            if ($online == 3) {

                                $return['jifen']    =   $recPirce * $integralPro;
                                $return['integral'] =   intval($statis['integral']);
                                $return['propor']   =   $integralPro;
                            }else{

                                $return['price']    =   $recPirce;
                                $return['integral'] =   intval($statis['integral']);
                            }

                        }else{
                            $return['price']    =   $recPirce;
                        }

						$return['msg']		=   "您的套餐已用完，您可以<a href='".$this->config['sy_weburl']."/wap/member/index.php?c=rating' style='color:red;cursor:pointer;'>购买会员</a>！";
                        $return['online']   =   $online;
                        $return['status']   =   2;
                    }else{
                        $return['msg']      =   '当前账户套餐余量不足，请联系主账户增配！';
                    };
                }
            } else if ($type == 3) { // 紧急
				
				$return['single']	=	in_array('joburgent', $comSingle)? '1' : '2';

                if ($statis['urgent_num'] > 0) {

                    $return['status']   =   1;
                    $return['num']      =   $statis['urgent_num'];
                } else {

                    if(empty($data['spid'])){

                        if ($online!=4) {

                            if ($online == 3) {

                                $return['jifen']    =   $urgentPirce * $integralPro;
                                $return['integral'] =   intval($statis['integral']);
                                $return['propor']   =   $integralPro;
                            }else{

                                $return['price']    =   $urgentPirce;
                                $return['integral'] =   intval($statis['integral']);
                            }

                        }else{
                            $return['price']        =   $urgentPirce;
                        }

						$return['msg']		=   "您的套餐已用完，您可以<a href='".$this->config['sy_weburl']."/wap/member/index.php?c=rating' style='color:red;cursor:pointer;'>购买会员</a>！";
                        $return['online']   =   $online;
                        $return['status']   =   2;
                    }else{
                        $return['msg']      =   '当前账户套餐余量不足，请联系主账户增配！';
                    };
                }
            } else if ($type == 4) { // 兼职推荐

				$return['single']	=	in_array('jobrec', $comSingle)? '1' : '2';

                if ($statis['rec_num'] > 0) {

                    $return['status']   =   1;
                    $return['num']      =   $statis['rec_num'];
                } else {

                    if(empty($data['spid'])){

                        if ($online!=4) {

                            if ($online == 3) {

                                $return['jifen']    =   $recPirce * $integralPro;
                                $return['integral'] =   intval($statis['integral']);
                                $return['propor']   =   $integralPro;
                            }else{

                                $return['price']    =   $recPirce;
                                $return['integral'] =   intval($statis['integral']);
                            }

                        }else{
                            $return['price']        =   $recPirce;
                        }

						$return['msg']		=   "您的套餐已用完，您可以<a href='".$this->config['sy_weburl']."/wap/member/index.php?c=rating' style='color:red;cursor:pointer;'>购买会员</a>！";
                        $return['online']   =   $online;
                        $return['status']   =   2;
                    }else{
                        $return['msg']      =   '当前账户套餐余量不足，请联系主账户增配！';
                    };
                }
            }

        } else {

            $return['status']          =   3; // 会员到期
        }
        $return['pricename']=   $this -> config['integral_pricename'];
        return $return;
    }

    /**
     * @desc 职位推广设置：置顶、推荐（含兼职）、紧急招聘
     */
    function setJobPromote($id, $data = array()) {

        $return =   array();

        if (!empty($id) && !empty($data)) {

            $uid        =   intval($data['uid']);
            $spid       =   intval($data['spid']);

            $usertype   =   intval($data['usertype']);
            $type       =   trim($data['type']);
            $days       =   intval($data['days']);

            if($type != 'recpart'){

                $job    =   $this->getInfo(array('id' => intval($id)), array('field' => '`id`,`rec`,`rec_time`,`urgent`,`urgent_time`,`xsdate`'));
            }else{

                $job    =   $this->select_once('partjob', array('id' => intval($id)), '`id`,`rec_time`');
            }

            $suid   =   !empty($spid) ? $spid : $uid;

            $statis =   $this -> getStatisInfo($suid, array('usertype' => $usertype, 'field' => '`top_num`,`urgent_num`,`rec_num`'));

            $pData  =   array(

                'uid'   =>  $uid,
                'spid'  =>  $spid,
                'usertype'  =>  $usertype,
                'day'   =>  $days,
                'job'   =>  $job,
                'statis'=>  $statis
            );

            if ($type == 'top') {

                $return =   $this -> setTopPromote($pData);
            }else if($type == 'rec'){

                $return =   $this -> setRecPromote($pData);
            }else if($type == 'urgent'){

                $return =   $this -> setUrgentPromote($pData);
            }else if($type == 'recpart'){

                $return =   $this -> setRecPartPromote($pData);
            }

        } else {

            $return = array('errcode' => 8, 'msg' => '参数错误，请重试！');

        }

        return $return;
    }

    /**
     * @desc    职位置顶
     * @param   array $data
     */
    private function setTopPromote($data = array()) {

        $return     =   array('errcode' => 8, 'msg' => '参数错误，请重试！');


        if (!empty($data)) {

            $uid        =   intval($data['uid']);

            $spid       =   intval($data['spid']);

            $suid       =   !empty($spid) ? $spid : $uid;

            $usertype   =   intval($data['usertype']);

            $day        =   intval($data['day']);

            $job        =   $data['job'];

            $statis     =   $data['statis'];

            if ($statis['top_num'] >= $day) {

                $xsDate =   $job['xsdate'] > time() ? array('+', $day * 86400) : time() +  $day * 86400;

                $return['id']   =   $this -> upInfo(array('xsdate' => $xsDate), array('id' => intval($job['id'])));

                $this -> addMemberLog($uid, $usertype, '设置职位置顶'.$day.'天', 1, 4);

                $this -> update_once('company_statis', array('top_num' => array('-', $day)), array('uid' => $suid));

                $return['msg']      =   '职位置顶设置成功！';
                $return['errcode']  =   9;

            }else {

                $return['msg']      =   '您的套餐数据不足当前设置的置顶天数，请重新输入！';
                $return['errcode']  =   7;
            }
        }

        return $return;

    }

    /**
     * @desc    职位推荐
     * @param   array $data
     */
    private function setRecPromote($data = array()) {

        $return     =   array('errcode' => 8, 'msg' => '参数错误，请重试！');

        if (!empty($data)) {

            $uid        =   intval($data['uid']);

            $spid       =   intval($data['spid']);

            $suid       =   !empty($spid) ? $spid : $uid;

            $usertype   =   intval($data['usertype']);

            $day        =   intval($data['day']);

            $job        =   $data['job'];

            $statis     =   $data['statis'];

            if ($statis['rec_num'] >= $day) {

                $recDate    =   $job['rec_time'] > time() ? $job['rec_time'] + $day * 86400 : time() +  $day * 86400;

                $this -> upInfo(array('rec_time' => $recDate, 'rec' => 1), array('id' => intval($job['id'])));

                $this -> addMemberLog($uid, $usertype, '设置职位推荐'.$day.'天', 1, 4);

                $this -> update_once('company_statis', array('rec_num' => array('-', $day)), array('uid' => $suid));

                $return['msg']      =   '职位推荐设置成功！';
                $return['errcode']  =   9;

            }else {

                $return['msg']      =   '您的套餐数据不足当前设置的推荐天数，请重新输入！';
                $return['errcode']  =   7;
            }
        }

        return $return;

    }

    /**
     * @desc    职位紧急招聘
     * @param   array $data
     */
    private function setUrgentPromote($data = array()) {

        $return     =   array('errcode' => 8, 'msg' => '参数错误，请重试！');

        if (!empty($data)) {

            $uid        =   intval($data['uid']);

            $spid       =   intval($data['spid']);

            $suid       =   !empty($spid) ? $spid : $uid;

            $usertype   =   intval($data['usertype']);

            $day        =   intval($data['day']);

            $job        =   $data['job'];

            $statis     =   $data['statis'];

            if ($statis['urgent_num'] >= $day) {

                $urgentDate =   $job['urgent_time'] > time() ? $job['urgent_time'] + $day * 86400 : time() +  $day * 86400;

                $this -> upInfo(array('urgent_time' => $urgentDate, 'urgent' => 1), array('id' => intval($job['id'])));

                $this -> addMemberLog($uid, $usertype, '设置职位紧急招聘'.$day.'天', 1, 4);

                $this -> update_once('company_statis', array('urgent_num' => array('-', $day)), array('uid' => $suid));

                $return['msg']      =   '职位紧急招聘设置成功！';
                $return['errcode']  =   9;

            }else {

                $return['msg']      =   '您的套餐数据不足当前设置的紧急招聘天数，请重新输入！';
                $return['errcode']  =   7;
            }

        }

        return $return;

    }

    /**
     * @desc    兼职推荐
     * @param   array $data
     */
    private function setRecPartPromote($data = array()) {

        $return     =   array('errcode' => 8, 'msg' => '参数错误，请重试！');

        if (!empty($data)) {

            $uid        =   intval($data['uid']);

            $spid       =   intval($data['spid']);

            $suid       =   !empty($spid) ? $spid : $uid;

            $usertype   =   intval($data['usertype']);

            $day        =   intval($data['day']);

            $part       =   $data['job'];

            $statis     =   $data['statis'];

            if ($statis['rec_num'] >= $day) {

                $recDate    =   $part['rec_time'] > time() ? $part['rec_time'] + $day * 86400 : time() +  $day * 86400;

                $this -> update_once('partjob', array('rec_time' => $recDate), array('id' => intval($part['id'])));

                $this -> addMemberLog($uid, $usertype, '设置兼职推荐'.$day.'天', 9, 4);

                $this -> update_once('company_statis', array('rec_num' => array('-', $day)), array('uid' => $suid));

                $return['msg']      =   '兼职推荐设置成功！';
                $return['errcode']  =   9;

            }else {

                $return['msg']      =   '您的套餐数据不足当前设置的推荐天数，请重新输入！';
                $return['errcode']  =   7;
            }
        }

        return $return;

    }

	/**
     * @desc     屏蔽企业
     */
    function pbComs($pbData = array()) {

        $return	=	array();

		$info	=	$this->getYqmsInfo(array('id'=>$pbData['id'], 'uid'=>$pbData['uid']) );
		$data['p_uid']		=	$info['fid'];
		$data['inputtime']	=	mktime();
		$data['c_uid']		=	$pbData['uid'];
		$data['usertype']	=	1;
		$data['com_name']	=	$info['fname'];

		$haves	=	$this->select_once('blacklist',array('c_uid'=>$data['c_uid'],'p_uid'=>$data['p_uid'],'usertype'=>$data['usertype']) );

		if(is_array($haves)){

			$return['msg']		=	"该用户已在您黑名单中！";
			$return['url']		=	$_SERVER['HTTP_REFERER'];
			$return['errcode']	=	8;
		}else{

			$nid	=	$this->insert_into('blacklist',$data);

			$this->delete_all('userid_msg',array('uid'=>$data['c_uid'],'fid'=>$data['p_uid']),'');

			if($nid){

				$this -> addMemberLog($data['c_uid'], $data['usertype'], "屏蔽公司 <".$data['fname']."> ，并删除邀请信息",26,3);
				$return['msg']		=	'操作成功！';
				$return['url']		=	'index.php?c=invite';
				$return['errcode']	=	9;
			}else{

				$return['msg']		=	'操作失败！';
				$return['url']		=	'index.php?c=invite';
				$return['errcode']	=	8;
			}
		}
        return	$return;
    }


    /**
     * @desc 发布职位条件查询
     */
    public function getAddJobNeedInfo($uid, $job = null, $spid = ''){

        require_once 'company.model.php';
        $comM  =   new company_model($this->db, $this->def);

        $info  =   $comM -> getInfo($uid);

        $msgList   =   array();

        if(!$info['name'] || !$info['provinceid'] || !$info['linktel']){
            if (empty($spid)) {
                $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">基本信息未完善 <a href="'.$this->config['sy_weburl']."/member/index.php?c=info".'" class="yun_prompt_release_ws_a" target="_blank">立即完善&gt;</a></div>';

                $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">基本信息未完善 <a href="'.Url('wap', array('c'=>'info'), 'member').'" class="yun_prompt_release_ws_a">立即完善&gt;</a></div>';
            }else{
                $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">基本信息未完善 <a class="yun_prompt_release_ws_a" target="_blank">待完善&gt;</a></div>';
                $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">基本信息未完善 <a class="yun_prompt_release_ws_a">待完善&gt;</a></div>';
            }

        }

        if($this->config['com_enforce_mobilecert']==1){
            if($info['moblie_status']!="1"){
                if (empty($spid)) {
                    $msgList['pc'][]    =  '<div class="yun_prompt_release_ws">手机未认证 <a href="'.$this->config['sy_weburl']."/member/index.php?c=binding".'" class="yun_prompt_release_ws_a" target="_blank">立即认证&gt;</a></div>';

                    $msgList['wap'][]   =  '<div class="yun_prompt_release_ws">手机未认证 <a href="'.Url('wap', array('c'=>'bindingbox','type'=>'moblie'), 'member').'" class="yun_prompt_release_ws_a">立即认证&gt;</a></div>';
                }else{
                    $msgList['pc'][]    =  '<div class="yun_prompt_release_ws">手机未认证 <a class="yun_prompt_release_ws_a" target="_blank">待认证&gt;</a></div>';
                    $msgList['wap'][]   =  '<div class="yun_prompt_release_ws">手机未认证 <a class="yun_prompt_release_ws_a">待认证&gt;</a></div>';
                }
            }
        }

        if($this->config['com_enforce_emailcert']  ==1){
            if($info['email_status']!="1"){
                if (empty($spid)) {
                    $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">邮箱未认证 <a href="'.$this->config['sy_weburl']."/member/index.php?c=binding".'" class="yun_prompt_release_ws_a" target="_blank">立即认证&gt;</a></div>';

                    $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">邮箱未认证 <a href="'.Url('wap', array('c'=>'bindingbox','type'=>'email'), 'member').'" class="yun_prompt_release_ws_a">立即认证&gt;</a></div>';
                }else{
                    $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">邮箱未认证 <a  class="yun_prompt_release_ws_a" target="_blank">待认证&gt;</a></div>';
                    $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">邮箱未认证 <a  class="yun_prompt_release_ws_a">待认证&gt;</a></div>';
                }
            }
        }

        if($this->config['com_enforce_licensecert']==1){
            $cert =   $comM -> getCertInfo(array('uid'=>$uid,'type'=>3), array('field' => 'uid'));
            if($info['yyzz_status']!="1" || empty($cert)){
                if (empty($spid)) {

                    $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">企业资质未认证 <a href="'.$this->config['sy_weburl']."/member/index.php?c=binding".'" class="yun_prompt_release_ws_a" target="_blank">立即认证&gt;</a></div>';

                    $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">企业资质未认证 <a href="'.Url('wap', array('c'=>'comcert'), 'member').'" class="yun_prompt_release_ws_a">立即认证&gt;</a></div>';
                }else{
                    $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">企业资质未认证 <a class="yun_prompt_release_ws_a" target="_blank">待认证&gt;</a></div>';
                    $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">企业资质未认证 <a class="yun_prompt_release_ws_a">待认证&gt;</a></div>';
                }

            }
        }

        if($this->config['com_enforce_setposition']==1){
            if(empty($info['x']) || empty($info['y'])){
                if (empty($spid)) {
                    $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">地图未设置 <a href="'.$this->config['sy_weburl']."/member/index.php?c=map".'" class="yun_prompt_release_ws_a" target="_blank">立即设置&gt;</a></div>';

                    $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">地图未设置 <a href="'.Url('wap', array('c'=>'map'), 'member').'" class="yun_prompt_release_ws_a">立即设置&gt;</a></div>';
                }else {
                    $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">地图未设置 <a class="yun_prompt_release_ws_a" target="_blank">待设置&gt;</a></div>';
                    $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">地图未设置 <a class="yun_prompt_release_ws_a">待设置&gt;</a></div>';
                }
            }
        }
		
		if($this->config['com_gzgzh'] == '1'){
			
			$uInfo  =  $this->select_once('member', array('uid' => $uid), '`wxid`');
			
			if(empty($uInfo['wxid'])){

                $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">微信公众号未关注 <a href="javascript:;" onclick="gzhShow();" class="yun_prompt_release_ws_a">立即关注&gt;</a></div>';
                $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">微信公众号未关注 <a href="'.Url('wap', array('c'=>'wxconnect', 'login'=>'1')).'" class="yun_prompt_release_ws_a">立即关注&gt;</a></div>';
            } 
		}

        if(empty($job)){
            $msgList['pc'][]   =  '<div class="yun_prompt_release_ws">发布职位 <a href="'.$this->config['sy_weburl']."/member/index.php?c=jobadd".'" class="yun_prompt_release_ws_a" target="_blank">立即发布&gt;</a></div>';
            $msgList['wap'][]  =  '<div class="yun_prompt_release_ws">发布职位 <a href="'.Url('wap', array('c'=>'jobadd'), 'member').'" class="yun_prompt_release_ws_a">立即发布&gt;</a></div>';
        }

        return $msgList;
    }

    
    //发布工具搜索
    public function Getpubtool($where = array(),$data = array())
    {
        $select = $data['field'] ? $data['field'] : '*';
        $lists  = $this->select_all('company_job',$where,$select);

        //是否限制职位
        if(isset($data['rule'])){
            $lists  = $this->makelists($lists,$where,1,$data['rule']);
        }


        $newlist = array();
        foreach ($lists as $k => $v) {
            $list = $this->getInfoArray($v);           
            $newlist[] = $list;
        }
        return $newlist;
    }

    //发布工具限制企业职位数
    protected function makelists($lists,$where,$page = 1,$rpt)
    {
        $newlist = array();

        $limit = $where['limit'][1];
        //限制重复企业数
        $rpt = $rpt;

        //去重之后列表
        $newlist = $this->arrayuniq($lists,$rpt,$limit);

        $count = count($newlist);

        //职位条数不够继续取
        if($count < $limit){

            $pages = $page * $limit;
            $where['limit'] = array("$pages","$limit");
            $lists  = $this->select_all('company_job',$where,'*');
            if(empty($lists)){
                return $newlist;
            }else{
                $lists = array_merge($newlist,$lists);
                return $this->makelists($lists,$where,$page+1,$rpt);
            }

        }else{
            return $newlist;
        }
    }

    //除去多余企业职位
    protected function arrayuniq($lists,$rpt,$limit)
    {
        $i = 1;
        $newlist = array();
        foreach($lists as $k=>$v){

            $arr = array_column($newlist,'uid');
            $arr = array_count_values($arr);
            $uid =  $arr[$v['uid']];
            if($uid < $rpt){
                $i++;
                if($i > $limit){
                    break;
                }
                $newlist[] = $v;
            }
        }
        return $newlist;
    }




}
?>