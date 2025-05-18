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
class company_model extends model{

    //判断是否需要限制每天操作次数
    private function _isComVipDayActionNeedCheck($uid){

		//时间会员模式要限制
        $member	=	$this -> select_once('member', array('uid' => $uid),'`usertype`');

		if($member['usertype'] == 2){

			$row = $this -> select_once('company_statis', array('rating_type'=>2,'uid'=>$uid), 'uid, `rating`');
		}

		if(isset($row['uid']) && $row['uid'] > 0){
            return $row['rating'];
        }

        return false;
    }

    //时间套餐企业会员，每天操作次数是否超量检查
    public function comVipDayActionCheck($type,$uid) {

        //时间会员等级id
        $ratingId   =   $this->_isComVipDayActionNeedCheck($uid);

        //如果不需要限制每日最大操作次数，则直接可以进行操作
        if(!$ratingId){
            return array('status' => 1);
        }

         //各操作项每日最大操作次数配置字段
        $typeMapping    =   array(
            'jobnum'      =>  'job_num',//发布职位
            'resume'      =>  'resume',//下载（查看联系方式）简历
            'interview'   =>  'interview',//邀请面试
            'refreshjob'  =>  'breakjob_num',//刷新职位
            'zph'         =>  'zph_num'
            );

        if(!isset($typeMapping[$type])){
            return array('status' => -1, 'msg' => '函数参数错误');
        }

        $field  = $typeMapping[$type];


        //如果该配置为0，则不限操作次数
        $row    = $this    ->  select_once('company_rating', array('id'=>$ratingId),'`'.$field.'`,`name`');

        if(!isset($row[$field]) || $row[$field] == 0){
            return array('status' => 1);
        }

        $dayMaxNum  = $row[$field];
        $currentNum = 0;
        $today      = strtotime('today');

        switch($type){
            case 'jobnum'://发布职位
                $currentNum1 = $this->select_num('company_job',array('uid'=>$uid,'sdate'=>array('>=',$today)));
                $currentNum2 = $this->select_num('partjob',array('uid'=>$uid,'addtime'=>array('>=',$today)));
                $currentNum  = $currentNum1+$currentNum2+$currentNum3;
               break;
            case 'resume'://查看（下载）简历
                $currentNum = $this->select_num('down_resume',array('comid'=>$uid,'downtime'=>array('>=',$today)));
                break;
            case 'interview'://邀请面试
                $currentNum = $this->select_num('userid_msg',array('fid'=>$uid,'datetime'=>array('>=',$today)));
                break;
            case 'refreshjob':
                $jobSxLog =   $this->select_all('member_log', array('uid' => $uid, 'ctime' => array('>=', $today),'opera'=>'1', 'type'=>'4'),'`jobnum`');
                if (!empty($jobSxLog)) {
                    foreach ($jobSxLog as $v){
                        if ($v['jobnum']) {
                            $currentNum1    =   $currentNum1 + $v['jobnum'];
                        }
                    }
                }
                $partSxLog =   $this->select_all('member_log', array('uid' => $uid, 'ctime' => array('>=', $today),'opera'=>'9', 'type'=>'4'),'`jobnum`');
                if (!empty($partSxLog)) {
                    foreach ($partSxLog as $v){
                        if ($v['jobnum']) {
                            $currentNum2    =   $currentNum2 + $v['jobnum'];
                        }
                    }
                }
                
                $currentNum  = $currentNum1+$currentNum2;

                break;
            case 'zph'://招聘会流程有待优化
                $currentNum = $this->select_num('zhopinhui_com',array('uid' => $uid, 'ctime' => array('>=' , $today)));
                break;
        }

        $typeMsgMapping  = array(
            'jobnum'      => '发布职位',//发布职位
            'resume'      => '下载简历（查看联系方式）',//下载（查看联系方式）简历
            'interview'   => '邀请面试',//邀请面试
            'refreshjob'  => '刷新职位',//刷新职位
            'zph'         => '报名招聘会'
        );
        $msg = $row['name'].'每天最多'.$typeMsgMapping[$type].$dayMaxNum.'  次';
        
        if($type == 'refreshjob'){
            $num    =   $dayMaxNum - $currentNum;
            return $currentNum < $dayMaxNum ? array('status' => 1, 'sxnum' => $num) : array('status' => -1, 'msg' => $msg);
        }else{

            return $currentNum < $dayMaxNum ? array('status' => 1) : array('status' => -1, 'msg' => $msg);
        }
    }


    /**
     * @desc 引用log类，添加用户日志
     *
     * @param   int     $uid
     * @param   int     $usertype
     * @param   string  $content
     * @param   string  $opera
     * @param   string  $type
     * @return  boolean
     */
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '')
    {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);

        return $LogM->addMemberLog($uid, $usertype, $content, $opera, $type);
    }

    /**
     * @desc 引用 integral类，获取积分
     *
     * @param int $uid
     * @param string $type  config 配置积分调用参数
     * @param string $msg   备注说明：获取积分途径
     */
    private function getIntegral($uid,$usertype, $type, $msg)
    {
        require_once 'integral.model.php';

        $IntegralM = new integral_model($this->db, $this->def);

        return $IntegralM->invtalCheck($uid,$usertype, $type, $msg);
    }

    /**
     * @desc   获取缓存数据
     *
     * @param   array $options
     * @return  array $cache
     */
    private function getClass($options)
    {

        include_once ('cache.model.php');

        $cacheM     =   new cache_model($this->db, $this->def);

        $cache      =   $cacheM -> GetCache($options);

        return $cache;

    }

    /**
     * @desc 查询企业数目 company
     */
    public function getCompanyNum($whereData = array()) {

        return  $this -> select_num('company', $whereData);

    }
    /**
     * 非uid条件查询企业基本信息
     */
    public function getCompanyInfo($whereData = array(),$data = array('field'=>''))
    {

        $field  =  !empty($data['field']) ? $data['field'] : '*';

        return  $this -> select_once('company', $whereData, $field);

    }
    /**
     *  @desc   企业基本信息
     *
     *  @param  int     $uid
     *  @param  array   $data ('edit':后台修改企业  'info':PC企业会员中心, 'logo'：图片预处理)
     */
    public function getInfo($uid, $data = array('field'=>'','utype'=>'','logo'=>'')) {

        if (!empty($uid)) {

            $select     =   !empty($data['field']) ? $data['field'] : '*';

            $uid        =   intval($uid);

            $ComInfo    =   $this -> select_once('company', array('uid'=>$uid), $select);
			
            $cache      =   $this -> getClass(array('hy','city','com', 'crm'));

            if($ComInfo && is_array($ComInfo)){

				if($ComInfo['hotstart']<=time() && $ComInfo['hottime']>=time()){
					$ComInfo['hotlogo'] = 1;
				}

				if($data['setname'] == 1){

					require_once ('userinfo.model.php');

					$UserinfoM  =  new userinfo_model($this->db, $this->def);

					$info	=	$UserinfoM -> getInfo(array('uid'=> $uid),array('setname'=>'1'));
				}

				if (!empty($info) && is_array($info)) {

				    $ComInfo['setname']         =  	$info['setname']=='1'?1:0;

				     
				}

                if($ComInfo['shortname'] && !empty($data['short'])){//企业名称显示成简称
					$ComInfo['name']           =	$ComInfo['shortname'];
				}
                if($ComInfo['provinceid']){
					$ComInfo['job_city_one']   =	$cache['city_name'][$ComInfo['provinceid']];
					$ComInfo['city_line']      =    $cache['city_name'][$ComInfo['provinceid']];
				}
				if($ComInfo['cityid']){
					$ComInfo['job_city_two']   =	$cache['city_name'][$ComInfo['cityid']];
					$ComInfo['city_line']      =    !empty($ComInfo['city_line']) ? $ComInfo['city_line'].'-'.$cache['city_name'][$ComInfo['cityid']] : $cache['city_name'][$ComInfo['cityid']];
				}
				if($ComInfo['three_cityid']){
					$ComInfo['job_city_three'] =	$cache['city_name'][$ComInfo['three_cityid']];
				}
				if($ComInfo['hy']){
					$ComInfo['hy_n']           =	$cache['industry_name'][$ComInfo['hy']];
				}
				if($ComInfo['pr']){
					$ComInfo['pr_n']           =	$cache['comclass_name'][$ComInfo['pr']];
				}
				if($ComInfo['mun']){
					$ComInfo['mun_n']          =	$cache['comclass_name'][$ComInfo['mun']];
				}
                if($ComInfo['lastupdate']){
                    $ComInfo['lastupdate_n']   =    date('Y-m-d', $ComInfo['lastupdate']);
                }
                if($ComInfo['moneytype']){
                    $ComInfo['moneytype_n']    =    $ComInfo['moneytype']==1 ? '万元':'万美元';
                }

				$ComInfo['content']     =   str_replace(array('ti<x>tle','“','”'),array('title',' ',' '),$ComInfo['content']);

                if ($data['logo']=='1') {
					//非企业用户查看||企业本身查看
                    if ( (!empty($ComInfo['logo'])  && $ComInfo['logo_status']==0) || ($data['utype'] == 'user' && !empty($ComInfo['logo'])) || ($data['utype'] == 'admin' && !empty($ComInfo['logo']))){
                        $ComInfo['logo_n']      =	$ComInfo['logo'];
                        $ComInfo['logo']    	=	checkpic($ComInfo['logo']);
                    }else{
                        $ComInfo['logo_n']      =	$ComInfo['logo'];
                        $ComInfo['logo']    	=	checkpic($this->config['sy_unit_icon']);
                    }
                }
				if($ComInfo['crm_uid']){
					$crminfo				=	$this -> select_once('admin_user', array('uid' => $ComInfo['crm_uid']));
					$ComInfo['crm_name']	=	$crminfo['name'];
					$ComInfo['aphone']	    =	$crminfo['moblie'];
				}

                /* 企业 - 修改 */
				if (!empty($data['edit'])) {

                     
                    if ($ComInfo['welfare']){
                        $ComInfo['arraywelfare']  =  explode(',', $ComInfo['welfare']);
                    }

					$ComInfo['comqcode']      	  =	 checkpic($ComInfo['comqcode'] );
                    // 企业创建时间单位是年，要对以前的数据进行处理
					if (!empty($ComInfo['sdate'])){
					    if (strlen($ComInfo['sdate']) > 4){
					        $sdate  =  explode('-',$ComInfo['sdate']);
					        $ComInfo['sdate']  =  $sdate[0];
					    }
					}
                }

                /* PC/WAP 企业会员中心 - 修改  */
                if(!empty($data['info'])) {

                    if (($ComInfo['x'] == '' || $ComInfo['y'] == '') && $ComInfo['address'] != ''){

                        $ComInfo['setmap']  =  1;

                    }elseif ($ComInfo['x'] != '' && $ComInfo['y'] != ''){

                        $ComInfo['setmap']  =  2;

                    }else{

                        $ComInfo['setmap']  =  3;
                    }

                    $tel					=  $this -> getCertInfo(array('uid'=>$uid, 'type'=>'2'));

                    $cert					=  $this -> getCertInfo(array('uid'=>$uid, 'type'=>'3'));

                    $ComInfo['info']			=  $ComInfo;

                    $ComInfo['tel']				=  $tel;

                    $ComInfo['cert']			=  $cert;

                }

            }else{
                $ComInfo['logo']    	=	checkpic($this->config['sy_unit_icon']);
            }

            return $ComInfo;

        }

    }
	public function getChCompanyList($whereData, $data = array()) {

		$select    		  =   $data['field'] ? $data['field'] : '*';

		$ChCompanyList    =   $this -> select_all('company',$whereData, $select);

		return $ChCompanyList;
	}
    /**
     * @DESC 获取企业信息，多条查询
     *
     * @param array     $whereData
     * @param array     $data[] 
     */
    public function getList( $whereData , $data=array('logo'=>null)) {

        $ListCom    =   array();

        $select     =   $data['field'] ? $data['field'] : '*';

        $List       =   $this -> select_all('company', $whereData, $select);

        if (!empty($List)) {
            $ListCom['list']	=   $List;

			$options            =   array('hy','city','com');
            $cache				=   $this -> getClass($options);

			if ($data['cache']) {

                $ListCom['cache']   =   $cache;
            }

            foreach ($List  as  $k  =>  $v){
				if($v['provinceid']){
					$List[$k]['job_city_one']          =	$cache['city_name'][$v['provinceid']];
				}
				if($v['cityid']){
					$List[$k]['job_city_two']          =	$cache['city_name'][$v['cityid']];
				}
				if($v['three_cityid']){
					$List[$k]['job_city_three']        =	$cache['city_name'][$v['three_cityid']];
				}
				$uids[]=$v['uid'];

                if($v['hy']){
                    $List[$k]['hy_n']        =    $cache['industry_name'][$v['hy']];
                }
                if($v['pr']){
                    $List[$k]['pr_n']        =    $cache['comclass_name'][$v['pr']];
                }
                if($v['mun']){
                    $List[$k]['mun_n']       =    $cache['comclass_name'][$v['mun']];
                }
				if($v['hotstart']<=time() && $v['hottime']>=time()){
						$List[$k]['hotlogo'] = 1;
				}
                if ($data['logo'] == '1') {
					if(empty($v['logo']) || $v['logo_status']==1){

						$List[$k]['logo']  =  checkpic($this->config['sy_unit_icon']);
					}else{
						 $List[$k]['logo']  =  checkpic($v['logo']);
						 $List[$k]['com_logo']  =  checkpic($v['logo']);
					}

                }else{
                    $List[$k]['logo']  =  checkpic($v['logo']);
                }
			}

			if ($data['utype']=='special') {

				$all	=	$this->select_all("special_com",array('uid'=>array('in',pylode(',',$uids)),'sid'=>$data['sid']));

				foreach($List as $k => $val){

					$List[$k]['uid_n']		=	$val['uid'];

					$List[$k]['name_n']		=	$val['name'];

					foreach($all as $v){

						if($val['uid'] == $v['uid']){

							$List[$k]['cuid']	=	$v['uid'];

						}
					}
				}
			}

			if ($data['statis']) {


                $sList  =  $this -> getStatisList(array('uid'=>$whereData['uid']),array('usertype'=>2,'field'=>'`uid`,`rating_name`,`vip_stime`'));

                foreach ($List as $k => $v){

                    foreach ($sList as $sv){

                        if ($v['uid'] == $sv['uid']) {
                            $List[$k]['rating']     =   $sv['rating_name'];
                            $List[$k]['vip_stime']  =   $sv['vip_stime'];

                        }
                    }
                }
            }

            /* 处理后台所需数据 */
            if ($data['utype']=='admin') {

                $List   =   $this -> getDataList($List);

            }


            if($data['url']){

                foreach ($List as $k => $v){

                    $List[$k]['comurl']  =  Url('company',array('c'=>'show','id'=>$v['uid'])) ;

                }

            }

            $ListCom['list']    =   $List;

        }

        return $ListCom;

    }

    /**
     * @desc    查询company表内没有的数据，引用相关类，查询关联表，提取列表数据所需信息
     *
     * @param   array $List
     */
    private function getDataList($List) {

        foreach ($List as $v){

            $uids[$v['uid']]   =   $v['uid'];

            if ($v['crm_uid']) {

                $cids[$v['uid']]   =   $v['crm_uid'];

            }

        }

        //  查询member
        $mWhere['uid']      =   array('in', pylode(',', $uids));
        $mData['field']     =   '`uid`,`username`,`status`,`login_date`,`reg_date`,`source`,`usertype`';

        $mList              =   $this -> getMemberList($mWhere, $mData);

        //  查询company_statis
        $sWhere['uid']      =   array('in', pylode(',', $uids));
        $sData['field']     =   '`uid`,`pay`,`integral`,`oldrating_name`';

        $sList              =   $this -> getStatisList($sWhere, $sData);

        //  查询company_cert
        $cWhere['uid']      =   array('in', pylode(',', $uids));
        $cWhere['type']     =   '3';
        $cData['field']     =   '`uid`,`check`,`status`';

        $cList              =   $this -> getCertList($cWhere, $cData);

        //  查询company_job
        $jWhere['uid']      =   array('in', pylode(',', $uids));
        $jData['field']     =   '`uid`';

        $jList              =   $this -> getJobList($jWhere, $jData);


        //  查询admin_user 业务员信息
        $crmList            =   $this -> select_all('admin_user', array('uid' => array('in', pylode(',', $cids))), '`uid`,`name`');

        foreach ($List  as  $k  =>  $v){

            //  分站did字段为空的数据
            if($v['did']    ==  ''){

                $List[$k]['did']  =   '0';

            }

            //  提取member查询数据
            if (!empty($mList)) {

                foreach ($mList as $mv){

                    if ($v['uid']    ==  $mv['uid']) {

                        $List[$k]['username']   =   $mv['username'];
                        $List[$k]['status']     =   $mv['status'];
                        $List[$k]['login_date'] =   $mv['login_date'];
                        $List[$k]['reg_date']   =   $mv['reg_date'];
                        $List[$k]['source']     =   $mv['source'];
                        $List[$k]['usertype']   =   $mv['usertype'];

                    }
                }
            }

            //  提取company_statis查询数据
            if (!empty($sList)) {

                foreach ($sList as $sv){

                    if ($v['uid']    ==  $sv['uid']) {

                        $List[$k]['pay']            =   $sv['pay'];
                        $List[$k]['oldrating_name'] =   $sv['oldrating_name'];
                        $List[$k]['integral']       =   $sv['integral'];
                    }
                }
            }

            //  提取company_cer查询数据，默认显示未认证，查到数据再进行审核等判断
            $List[$k]['yyzz_n']                     =   '未认证';

            if (!empty($cList)) {

                foreach ($cList as $cv){

                    if ($v['uid']    ==  $cv['uid']) {

						$List[$k]['yyzzurl']  		= 	checkpic($cv['check']);

                        if($cv['status'] ==  1){

                            $List[$k]['yyzz_n']     =   '已审核';

                        }elseif($cv['status'] == 2){

                            $List[$k]['yyzz_n']     =   '未通过';

                        }elseif($cv['status'] == 0){

                            $List[$k]['yyzz_n']     =   '未审核';

                        }
                    }
                }
            }

            //  提取job查询数据，统计职位总数
            if (!empty($jList['list'])) {

                $jobList    =   $jList['list'];

                foreach ($jobList as  $jv){

                    if($v[uid]  ==  $jv[uid]){

                        $List[$k]['jobnum']++;

                    }
                }
            }

            //  提取顾问表查询数据
            if (!empty($crmList)) {

                foreach ($crmList as  $crm){

                    if($v['crm_uid']  ==  $crm['uid']){

                        $List[$k]['crm_name']    =   $crm['name'];

                    }
                }
            }

        }

        return $List;

    }


    /**
     * @desc   引用userinfo类，查询membernum信息
     */
    private function getMemberNum($whereData = array(), $data = array()) {

        require_once ('userinfo.model.php');

        $UserinfoM = new userinfo_model($this->db, $this->def);

        return  $UserinfoM   ->  GetMemberNum($whereData , $data);

    }

    /**
     * @desc   引用userinfo类，查询member列表信息
     */
    private function getMemberList($whereData = array(), $data = array()) {

        require_once ('userinfo.model.php');

        $UserinfoM = new userinfo_model($this->db, $this->def);

        return  $UserinfoM   ->  getList($whereData , $data);

    }

    /**
     * @desc   引用statis类，查询company_statis列表信息
     *
     * @param   array   $whereData  ：   查询条件
     * @param   array   $data : 自定义查询
     */

    private function getStatisList($whereData = array(), $data = array()) {

        require_once ('statis.model.php');

        $StatisM    =   new statis_model($this->db, $this->def);

        return  $StatisM -> getList($whereData , $data);

    }

    /**
     * @desc   引用job类，查询company_job列表信息
     *
     * @param   array   $whereData  ：   查询条件
     * @param   array   $data : 自定义查询
     *
     * @return  array   array('list'=>array()):职位列表
     */

    private function getJobList($whereData = array(), $data = array()) {

        require_once ('job.model.php');

        $JobM   =   new job_model($this->db, $this->def);

        return  $JobM -> getList($whereData , $data);

    }

    /**
     * @desc 更新数据
     *
     * @param int/array     $id
     * @param array         $where
     * @param array         $data
     */
    public function upInfo( $id , $where = array() , $Data = array()) {

        if (!empty($id) || $where) {

            if(!empty($id)){

                if (is_array($id)) {

                    $whereD  =   array(
                        'uid' => array('in', pylode(',', $id))
                    );
                } else {

                    $whereD  =   array(
                        'uid' => intval($id)
                    );
                }
            }

			if(!empty($where) && !empty($whereD)){

				$where	=	array_merge($where, $whereD);
			}else if(!empty($where) && empty($whereD)){

				$where	=	$where;
			}else if(!empty($whereD) && empty($where)){

				$where	=	$whereD;
			}

            $nid    =   $this -> update_once('company', $Data, $where);

            return $nid;
        }

    }

    /**
     * @desc    查询company_cert数目
     * @param   array $whereData
     * @return  number
     */
    public function getCertNum($whereData = array()) {

        return $this -> select_num('company_cert', $whereData);

    }
     /**
     * @desc    查询company_news数目
     * @param   array $whereData
     * @return  number
     */
    public function getCompanyNewsNum($whereData = array()) {

        return $this -> select_num('company_news', $whereData);

    }
	/**
     * @desc    查询company_product数目
     * @param   array $whereData
     * @return  number
     */
    public function getCompanyProductNum($whereData = array()) {

        return $this -> select_num('company_product', $whereData);

    }
    /**
     * @desc    查询company_cert详细数据
     *
     * @param   array   $whereData  ：   查询条件
     * @param   array   $data : 自定义查询
     */
    public function getCertInfo($whereData = array(),  $data = array()) {

        $select =   $data['field'] ? $data['field'] : '*';

        $Info   =   $this -> select_once('company_cert', $whereData, $select);
        if($Info['type'] == '3' || $Info['type'] == '4' || $Info['type'] == '5' ){

            $Info['old_check']  =   checkpic($Info['check']);

        }

        return $Info;

    }

    /**
     * @desc    查询company_cert列表数据
     *
     * @param   array   $whereData  ：   查询条件
     * @param   array   $data : 自定义查询
     */
    public function getCertList($whereData = array(),  $data = array()) {

        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this -> select_all('company_cert', $whereData, $select);

        if ($List && is_array($List)) {

            $uids       =   array();

            foreach ($List as $value) {

                $uids[] =   $value['uid'];

            }

            if (isset($data['utype']) && $data['utype']='comcert') {

                $ListCA     =   $this   ->    getList(array('uid'=>array('in', pylode(',', $uids))),array('field'=>'uid,name'));

                $comList    =   $ListCA['list'];
            }

            

            foreach ($List as $k => $v) {

				$List[$k]['check']  =   checkpic($v['check']);

                if (!empty($comList) && is_array($comList)) {

                    foreach ($comList as $cv){
                        if ($v['uid'] == $cv['uid']) {
                            $List[$k]['name']   =   $cv['name'];
                        }
                    }

                }
                

            }

            return $List;
        }
    }

    /**
     * @desc    修改company_cert（手机认证、邮箱认证、营业执照修改/审核）
     *
     * @param   array   $whereData  :   修改条件
     * @param   array   $upData     :   修改的数据
     * @param   array   $data       :   自定义处理数组
     */
    public function upCertInfo($whereData = array(), $upData = array(), $data = array()){

        if (!empty($upData) && !empty($whereData)){

            /* 手机认证绑定 */
            if (!empty($data['moblie'])) {

                $return =   $this -> upCertMobile($whereData, $upData, $data);
            }

            /* 邮箱认证绑定 */
            if (isset($data['email']) && !empty($data['email'])) {

                $return =   $this -> upCertEmail($whereData, $upData, $data);
            }
            /* 更新营业 */
            if (isset($data['yyzz']) && $data['yyzz'] == '1') {

                $return =   $this -> upCertYyzz($whereData, $upData, $data);
            }
            if ($data['utype']=='admin') {

                $return         =	$this -> update_once('company_cert', $upData, $whereData);
            }
        }
        return $return;
    }

    /**
     * @desc 手机认证
     */
    private function upCertMobile($whereData = array(), $upData = array(), $data = array()){

        $return     =   0;

        $moblie     =   trim($data['moblie']);

        $uid        =   intval($whereData['uid']);

        $usertype   =   intval($data['usertype']);


        $cert       =   $this -> getCertInfo(array('uid'=>$uid, 'check'=>$moblie, 'type'=>'2'),array('field'=>'`check2`,`ctime`'));

        if ($cert && is_array($cert)) {

            $time       =   time();

            $codeTime   =   $cert['ctime'] + $this->config['moblie_codetime'] * 60 ; // moblie_codetime   后台设定验证码时效（单位分钟）

            if ($codeTime < $time) { // 验证码时效性验证

                $return =   4;

            }else if ($cert['check2'] != $whereData['check2']) {

                $return =   3;

            } else {

                /* 清除该号码以前认证  使用uid和moblie   防止更新其他用户   START  */
                $this -> update_once('member', array('moblie'=>''),array('moblie' => $moblie,'uid'=>array('<>',$uid)));
				
				//	解除个人手机绑定，简历重置未审核 start
				$resumes	=	$this -> select_all('resume', array('telphone' => $moblie, 'uid' => array('<>', $uid)), '`uid`');
				if(!empty($resumes)){		
					$ruids	=	array();
					foreach($resumes as $rk=> $rv){
						$ruids[]	=	$rv['uid'];
					}
					$this -> update_once('resume', array('moblie_status'=>'0', 'telphone'=>''), array('uid'=>array('in', pylode(',', $ruids))));
					$this -> update_once('resume_expect', array('state'=>'0'), array('uid'=>array('in', pylode(',', $ruids))));
                }
				// end
				$this -> update_once('company', array('moblie_status'=>'0', 'linktel'=>''), array('linktel'=>$moblie, 'uid'=>array('<>',$uid)));

                /* 清除该号码以前认证    END  */

                /* 更新账号表数据 */
                $this->update_once('member', array('moblie'=>$moblie,'moblie_status'=>'1'), array('uid'=>$uid));

                /* 根据会员类型 更新基本信息表数据,一个认证 全部认证 */
				$this ->  update_once('resume',array('telphone'=>$moblie,'moblie_status'=>'1'),array('uid'=>$uid));
				$this ->  update_once('company', array('linktel'=>$moblie,'moblie_status'=>'1'), array('uid'=>$uid));

                /* 添加日志 */
                $this -> addMemberLog($uid, $usertype, '手机号码认证', 6 ,3);

                /* 第一次绑定查询，获取积分奖励 */
                $pay    =   $this -> select_once('company_pay', array('pay_remark'=>'手机绑定', 'com_id'=>intval($whereData['uid'])));

                if (empty($pay)) {

                    $this -> getIntegral(intval($whereData['uid']), $usertype, 'integral_mobliecert', '手机绑定');
                }
            }
        }else{

            $return     =   2;
        }

        if ($return == 0) {

            $return         =	$this -> update_once('company_cert', $upData, $whereData);
            if($return){
                $return=1;
            }else{
                $return=5;
            }

        }

        return $return;

    }


    /**
     * @desc 发送邮箱认证
     */
    function sendCertEmail($whereData = array(), $data = array()) {

        if (empty($whereData['uid']) || empty($data['usertype'])) {

            $return =   '0';

        }else if ($this->config['sy_email_set'] != '1') {

            $return =   '3';

        }else if ($this->config['sy_email_cert'] == '2'){

            $return =   '2';

        }else{

            $cert       =   $this -> getCertInfo($whereData);

            $uid        =   intval($whereData['uid']);

            $usertype   =   intval($data['usertype']);

            $email      =   trim($data['email']);

            $randstr    =   rand(10000000,99999999);

            $value      =   array(

                'status'    =>  '0',
                'step'      =>  '1',
                'check'     =>  $email,
                'check2'    =>  $randstr,
                'ctime'     =>  time()

            );

            if ($cert && is_array($cert)) {

                $this -> update_once('company_cert', $value, $whereData);

                $this -> addMemberLog($uid, $usertype, '更新邮箱认证', 13 ,2);

            } else {

                $values =   array(

                    'uid'   =>  $uid,
                    'did'   =>  intval($data['did']),
                    'type'  =>  '1'

                );

                $value  =   array_merge($value, $values);

                $this   -> insert_into('company_cert', $value);

                $this   -> addMemberLog($uid, $usertype, '添加邮箱认证', 13 ,1);

            }
            $base                =      base64_encode($uid.'|'.$randstr.'|'.$this->config['coding'].'|'.$email);

            $url                  =      Url('qqconnect', array('c'=>'cert','id'=>$base),'1');

            include_once('userinfo.model.php');

            $UserinfoM            =      new userinfo_model($this->db, $this->def);

            $userwhere['uid']     =      $uid;

            $fdata                =      $UserinfoM->getUserInfo($userwhere,array('usertype'=>$usertype,'field'=>'`name`'));

            $emailData            =     array(

                'uid'             =>    $uid,
                'name'            =>    trim($fdata['name']),
                'type'            =>    'cert',
                'email'           =>    $email,
                'url'             =>    '<a href="'.$url.'">点击认证</a> 如果您不能在邮箱中直接打开，请复制该链接到浏览器地址栏中直接打开：'.$url,
                'date'            =>    date('Y-m-d')
            );

            require_once 'notice.model.php';

            $noticeM    =   new notice_model($this->db, $this->def);

            $noticeM    ->  sendEmailType($emailData);

            $return =   '1';

        }

        return $return;
    }

    /**
     * @desc 邮箱认证
     */
    private function upCertEmail($whereData = array(), $upData = array(), $data = array()){

        $return     =   array();

        $email      =   trim($data['email']);

        $check2      =  intval($whereData['check2']);

        $uid        =   intval($whereData['uid']);

        $usertype   =   intval($data['usertype']);

        $cert       =   $this -> getCertInfo(array('uid'=>$uid,'check'=>$email, 'check2'=>$check2, 'type'=>'1'));

        if ($cert && is_array($cert)) {

            /* 清除该邮箱以前认证  使用uid和moblie   防止更新其他用户   同uid则不清除   START  */
             $this -> update_once('member',array('email'=>''), array('email' => $email,'uid' =>array('<>',$cert['uid'])));
            $this -> update_once('resume',array('email_status'=>'0', 'email'=>''), array('email'=>$email,'uid' =>array('<>',$cert['uid'])));
            $this -> update_once('company',array('email_status'=>'0', 'linkmail'=>''), array('linkmail'=>$email,'uid' =>array('<>',$cert['uid'])));

            /* 清除该号码以前认证    END  */

            $return['id']   =	$this -> update_once('company_cert', $upData, $whereData);

            if ($return['id']) {

                /* 更新账号表数据 */
                $this->update_once('member', array('email'=>$email,'email_status'=>'1'), array('uid'=>$uid));

                /* 根据会员类型 更新基本信息表数据 */


				$this ->  update_once('resume',array('email'=>$email,'email_status'=>'1'),array('uid'=>$uid));



				$this ->  update_once('company', array('linkmail'=>$email,'email_status'=>'1'), array('uid'=>$uid));





                $pay    =   $this -> select_once('company_pay', array('pay_remark'=>'邮箱认证', 'com_id'=>$uid));

                if (empty($pay)) {

                    $this -> getIntegral($uid,$usertype, 'integral_emailcert', '邮箱认证');

                }

                $return['errcode']  =   '9';
                $return['msg']      =   '认证成功！';

            } else {

                $return['errcode']  =   8;
                $return['msg']      =   '认证失败，联系管理员认证！';

            }

        }else{

            $return['errcode']  =   8;
            $return['msg']      =   '认证失败，请检查来路！';

        }

        return $return;


    }

    /**
     * @desc 营业执照更新认证
     * @param array $whereData
     * @param array $upData
     * @param array $data
     */
    private function upCertYyzz($whereData = array(), $upData = array(), $data = array()) {

        $return     =   array();
        $uid        =   intval($whereData['uid']);
        $usertype   =   intval($data['usertype']);

        if ($data['type']=='3') {

            if ($data['com_name']) {

                $comnum     =   $this -> select_num('company', array('uid' => array('<>', $uid), 'name' => trim($data['com_name'])));

                if ($comnum > 0) {

                    $return['msg']      =   '企业名称已存在！';
                    $return['errcode']  =   8;
					return $return;
                }
            }else{
                $return['msg']      =   '企业名称不能为空！';
                $return['errcode']  =   8;
            }
            $msg     =  '更新营业执照';
        }
        if ($data['type']=='4') {

            $msg     =  '更新认证';
        }
        if ($upData['file']['tmp_name'] || $upData['base']){

            $upArr   =  array(
                'file'     =>  $upData['file'],
                'dir'      =>  'cert',
                'base'     =>  $upData['base'],
                'preview'  =>  $upData['preview']
            );

            $result  =  $this -> upload($upArr);

            if (!empty($result['msg'])){

                $return['msg']      =  $result['msg'];
                $return['errcode']  =  '8';

                return $return;

            }elseif (!empty($result['picurl'])){

                $picurl  =  $result['picurl'];
            }
            unset($upData['file']);
            unset($upData['base']);
            unset($upData['preview']);
        }

        if (isset($picurl)){
            $upData['check']        =  $picurl;
        }
        $return['id']   =   $this -> update_once('company_cert', $upData, $whereData);

        if ($return['id']) {

            //名称
            if ($data['com_name']) {
				$this   ->  update_once('company_job',array('com_name'=>trim($data['com_name'])),array('uid'=>$uid));
                $this   ->  update_once('company',array('name'=>trim($data['com_name']), 'yyzz_status'=>intval($upData['status'])),array('uid'=>$uid));
            }
            $this -> addMemberLog($uid, $usertype, $msg, 13 ,2);

           
            if($usertype==2){

                $this -> update_once('company',array('yyzz_status'=>intval($upData['status'])),array('uid'=>$uid));
                if ($this->config['com_cert_status'] == '0') {

                    $return['msg']      =   '更新成功！';
                }else{
                    $return['msg']      =   '更新成功，等待审核！';

                }
            }
            
            $return['errcode']  =   9;
        }else{

            $return['msg']      =   '更新失败！';
            $return['errcode']  =   8;
        }
        return $return;
    }

    /**
     * @desc 新增 company_cert信息
     *
     * @param   $data['type'] : 3=>企业营业执照 ;  6=>认领会员
     */
    public function addCertInfo($data = array()){

        $return     =   array();

        if (!empty($data)) {
            if ($data['file']['tmp_name'] || $data['base']){

                $upArr   =  array(
                    'file'     =>  $data['file'],
                    'dir'      =>  'cert',
                    'base'     =>  $data['base'],
                    'preview'  =>  $data['preview']
                );

                $result  =  $this -> upload($upArr);

                if (!empty($result['msg'])){

                    $return['msg']      =  $result['msg'];
                    $return['errcode']  =  '8';

                    return $return;

                }elseif (!empty($result['picurl'])){

                    $picurl  =  $result['picurl'];
                }
            }

            $value  =   array(

                'uid'       =>  intval($data['uid']),
                'usertype'  =>  $data['usertype'],
                'type'      =>  trim($data['type']),
                'status'    =>  intval($data['status']),
                'step'      =>  intval($data['step']),
                'did'       =>  intval($data['did']),
                'check2'    =>  $data['type'] == '6' ? $data['check2'] : 0,
                'ctime'     =>  time(),
				'did'		=>	$data['did']
            );

            if(isset($picurl)){

                $value['check'] =   $picurl;
            }else if($data['type'] != '6'){
                $return['msg']      =   '请上传图片！';
                $return['errcode']  =   8;
                return $return;
            }

            /* 企业营业执照企业名称检测 */
            if ($data['type'] == '3') {

                if ($data['com_name']) {
                    $comnum     =   $this -> select_num('company', array('uid' => array('<>', $data['uid']), 'name'=>trim($data['com_name'])));
                    if ($comnum > 0) {

                        $return['msg']      =   '企业名称已存在！';
                        $return['errcode']  =   8;
						return $return;
                    }
                }else{
                    $return['msg']      =   '企业名称不能为空！';
                    $return['errcode']  =   8;
                }
            }

            $return['id']   =   $this -> insert_into('company_cert', $value);

            if ($return['id']) {

                //名称
                if ($data['type']=='3') {

                    $cominfo  =  $this->select_once('company',array('uid'=>$data['uid']),'`uid`');

                    if(empty($cominfo)){

                        $this->activUser($data['uid'], $data['usertype']);
                    }

                    $this   ->  update_once('company', array('name'=>trim($data['com_name']), 'yyzz_status'=>intval($data['status'])),array('uid'=>intval($data['uid'])));

                    $msg     =  '上传营业执照';

                }
                $this -> addMemberLog(intval($data['uid']), intval($data['usertype']), $msg, 13 ,1);
                 
                if($data['usertype']==2){
                    if ($this->config['com_cert_status'] == '0') {

                        $this -> getIntegral(intval($data['uid']), intval($data['usertype']), 'integral_comcert', '认证营业执照');
                        $return['msg']      =   '上传成功！';
                    }else{
                        $return['msg']      =   '上传成功，等待审核！';

                    }
                }
                
                $return['errcode']  =   9;
            }else{
                $return['msg']      =   '上传失败！';
                $return['errcode']  =   8;

            }
        }

        return $return;
    }
    /**
     * 添加相应用户
     * @param  $uid
     * @param  $usertype
     */
    private function activUser($uid,$usertype)
    {
        include_once('userinfo.model.php');

        $userinfoM  =  new userinfo_model($this->db, $this->def);
        $userinfoM -> activUser($uid, $usertype);
    }

    /**
     * @desc 删除company_cert
     *
     * @param   array   $whereData  修改条件数据
     */
    public function delCert($uid = null, $data = array()){

        $return     =   array();

        if(!empty($uid)){

            if(is_array($uid)){

                $uids   =	$uid;

                $return['layertype']	=	1;

            }else{

                $uids   =   @explode(',', $uid);

                $return['layertype']	=	0;

            }

            /* 查询证书图片 */
            $certs      =   $this->getCertList(array('uid'=>array('in',pylode(',', $uids))), array('field'=>'`uid`,`check`'));

            if (empty($certs)) {

                $return['msg']      =   '数据错误，请重试！';

                $return['errcode']  =   '8';

            }else {

                $return['id']   =   $this -> delete_all('company_cert',array('uid'=>array('in',pylode(',', $uids)), 'type'=>$data['type']), '');

                if($return['id']){

                    if($data['type']=='3'){

                        $return['msg']  =  '企业认证(UID:'.$uid;

                    }

                    $return['msg'] .=   ')删除成功';

                    $return['errcode']  =  '9';

                }
				else{

                    $return['msg']      .= ')删除失败';

                    $return['errcode']  =  '8';
                }
            }

        }else{

            $return['msg']      =  '请选择您要删除的信息！';

            $return['errcode']  =  '8';

        }

        return $return;

    }


    /**
     * @desc    查询company_order详细数据
     */
    public function getOrderInfo($whereData = array(),  $data = array()) {

        $select =   $data['field'] ? $data['field'] : '*';

        $Info   =   $this -> select_once('company_order', $whereData, $select);

        return $Info;

    }

    /**
     * @desc    查询company_consultant 顾问列表数据
     */
    public function getGwList($whereData = array(),  $data = array()) {

        require_once ('admin.model.php');

        $adminM         =   new admin_model($this->db, $this->def);

        $select =   $data['field'] ? $data['field'] : '*';

        $consultantList   =   $this -> select_all('company_consultant', $whereData, $select);

        if($data['utype']=='admin'){

            foreach($consultantList as $val){

                $consultantId[] = $val['id'];

                if($val['crm_uid']!=0){

                    $uids[] = $val['crm_uid'];

                }

            }
            if(!empty($consultantList) && $consultantList){

                $companywhere['conid']        =    array('in', pylode(',', $consultantId));

                $companywhere['groupby']      =    'conid';

                $comList                      =    $this   ->    getList($companywhere,array('field'=>'count(*) as num,conid'));

                $companyListNum               =   $comList['list'];

                $adminwhere['uid']          =    array('in', pylode(',', $uids));

                $adminList                    =   $adminM   ->    getList($adminwhere,array('field'=>'username,name,uid'));

                foreach($consultantList  as $key=>$val){

                    $week=array('1'=>'周一','2'=>'周二','3'=>'周三','4'=>'周四','5'=>'周五','6'=>'周六','7'=>'周日');

                    $consultantList[$key]['logo']     =		checkpic($val['logo']);

                    $zbtime=@explode(',',$val['status']);

                    $weeks=array();

                    foreach($zbtime as $v){

                        $weeks[]=$week[$v];
                    }

                    $consultantList[$key]['zbstatus']   =      @implode(',',$weeks);

                    foreach($companyListNum  as  $v){

                        if($val['id']==$v['conid']){

                            $consultantList[$key]['num']      =     $v['num'];
                        }
                    }

                    foreach($adminList  as  $v){

                        if($val['crm_uid']==$v['uid']){

                            $consultantList[$key]['crmusername']      =     $v['username'];
                            $consultantList[$key]['crmname']          =     $v['name'];
                        }
                    }

                }
            }

        }
        return $consultantList;

    }

    /**
     * @desc    查询company_consultant 顾问列表数据
     */
    public function getGwInfo($id = null,  $data = array('field'=>null, 'logo'=>'null')) {

        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this -> select_once('company_consultant', array('id'=>intval($id)), $select);

        if ($data['logo']){

			 $List['logo']	= 	checkpic($List['logo'] , $this->config['sy_guwen']);

        }

        return $List;

    }

    /**
     * @desc    添加company_consultant 顾问列表数据
     */
    public function addCompanyConsultant($data = array()) {

        $nid	=	$this->insert_into('company_consultant',$data);

        return $nid;

    }
    /**
     * @desc   更新company_consultant 顾问列表数据
     */
    public function upCompanyConsultant($id,$data = array()) {

        $where  =   array();

        if(!empty($id)){

            $where['id']   =   $id;

            $nid    =   $this ->  update_once('company_consultant',$data,$where);

            return $nid;

        }
    }
    /**
     * @desc   删除ompany_consultant 顾问列表数据
     */
    public function delCompanyConsultant($id,$data = array()) {

        $id           =   pylode(',', $id);
         
        $nid    =   $this ->  delete_all('company_consultant',array('id' => array('in',$id)),'');

        return $nid;

    }

    /**
     * @desc company  更新顾问数据
     * @param int/array     $conid
     * @param array         $where
     * @param array         $data
     */
    public  function upCompanyInfo($conid , $where = array() , $Data = array()) {

        if (!empty($conid)) {

            if (is_array($conid)) {

                $where['conid']   =   array('in' , pylode(',' ,  $conid));

            } else {

                $where['conid']   =   intval($conid);

            }

            $nid    =   $this -> update_once('company', $Data, $where);

            return $nid;
        }

    }

    /**
     * @desc 修改企业名称，同步修改相关表数据
     */

    function upComName($uid = null, $comname = null) {

        if (!empty($uid) && !empty($comname)) {

            $uid        =   intval($uid);
            $comname    =   trim($comname);

            $this   ->  update_once('userid_job',array('com_name'=>$comname),array('com_id'=>$uid));
            $this   ->  update_once('fav_job',array('com_name'=>$comname),array('com_id'=>$uid));
            $this   ->  update_once('report',array('r_name'=>$comname),array('c_uid'=>$uid));
            $this   ->  update_once('blacklist',array('com_name'=>$comname),array('c_uid'=>$uid));
            $this   ->  update_once('msg',array('com_name'=>$comname),array('job_uid'=>$uid));
            $this   ->  update_once('hotjob',array('username'=>$comname),array('uid'=>$uid));
        }

    }

    /**
     * @desc 分配顾问
     */

    function setComGw($data = array(), $whereData = array()) {

        if(!empty($data) && !empty($whereData)){

            $uids           =   $whereData['uid'];

            $crm_uid        =   $whereData['crm_uid'];

            $return         =   array();



            $ginfo          =   $this -> select_once('admin_user', array('uid' => $crm_uid),'`name`');
            $return['id']   =   $this -> update_once('company', $data, array('uid' => array('in', $uids)));

            if ($return['id']) {

                $msg        =  '管理员为您分配招聘顾问：'.$ginfo['name'];

                //发送系统通知
                include_once('sysmsg.model.php');

                $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                $sysmsgM    ->  addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));
            }

            $return['msg']		=	'顾问(ID:'.$crm_uid.')';

            $return['errcode']	=	$return['id'] ? '9' :'8';
            $return['msg']		=	$return['id'] ? $return['msg'].'分配成功！' : $return['msg'].'分配失败！';

        }

        return $return;


    }

    /**
     * @desc 新增名企
     */
    function addHotJob($data = array()) {
        if (!empty($data)) {

            $uid    =   intval($data['uid']);

            if ($data['time_end'] > $data['time_start']) {

                $value = array(
                    'uid'               => intval($data['uid']),
                    'username'          => trim($data['username']),
                    'rating'            => trim($data['rating']),
                    'hot_pic'           => trim($data['hot_pic']),
                    'service_price'     => intval($data['service_price']),
                    'time_start'        => $data['time_start'],
                    'time_end'          => $data['time_end'],
                    'sort'              => intval($data['sort']),
                    'beizhu'            => trim($data['beizhu']),
                    'rating_id'         => intval($data['rating_id']),
                    'did'               => intval($data['did'])
                );

                $return['id']   =   $this -> insert_into('hotjob', $value);

                if ($return['id']) {

                    $this -> update_once('company',array('hottime' => $data['time_end'], 'rec' => '1','hotstart' => $data['time_start']),array('uid'=>$uid));

                    $msg    =  '您的企业已被设置为名企招聘，时间：'.date('Y-m-d',$data['time_start']).'-'.date('Y-m-d',$data['time_end']);

                    //发送系统通知
                    include_once('sysmsg.model.php');

                    $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                    $sysmsgM    ->  addInfo(array('uid'=>$uid,'usertype'=>2, 'content'=>$msg));

                }

                $return['msg']		=	'名企招聘(ID:'.$return['id'].')';

                $return['errcode']	=	$return['id'] ? '9' :'8';
                $return['msg']		=	$return['id'] ? $return['msg'].'设置成功！' : $return['msg'].'设置失败！';

            }else{

                $return['msg']		=	'名企结束时间必须大于开始时间！';
                $return['errcode']	=	8;
            }

            return $return;
        }
    }


    /**
     * @desc 修改名企招聘
     */
    function upHotJob($id, $data = array()) {

        if (!empty($id)){

            $id     =   intval($id);
            $uid    =   intval($data['uid']);

            $hot    =   $this -> getHotJob(array('id'=>$id),array('field'=>'`hot_pic`'));
            if ($data['time_end'] > $data['time_start']) {

                $value = array(
                    'uid'               => intval($data['uid']),
                    'username'          => trim($data['username']),
                    'rating'            => trim($data['rating']),
                    'service_price'     => intval($data['service_price']),
                    'time_start'        => $data['time_start'],
                    'time_end'          => $data['time_end'],
                    'sort'              => intval($data['sort']),
                    'beizhu'            => trim($data['beizhu']),
                    'rating_id'         => intval($data['rating_id']),
                    'did'               => intval($data['did'])
                );

                if (!empty($data['hot_pic'])) {

                    $value['hot_pic']   .=   $data['hot_pic'];

                }

                $return['id']   =   $this -> update_once('hotjob',$value, array('uid'=>$id));


                if ($return['id']) {
					$this -> update_once('company',array('hottime' => $data['time_end'], 'rec' => '1','hotstart' =>$data['time_start']),array('uid'=>$uid));

					$msg    =  '您的企业已被设置为名企招聘，时间：'.date('Y-m-d',$data['time_start']).'-'.date('Y-m-d',$data['time_end']);

                    //发送系统通知
                    include_once('sysmsg.model.php');

                    $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                    $sysmsgM    ->  addInfo(array('uid'=>$uid,'usertype'=>2, 'content'=>$msg));

                }

                $return['msg']		=	'名企招聘(ID:'.$return['id'].')';

                $return['errcode']	=	$return['id'] ? '9' :'8';
                $return['msg']		=	$return['id'] ? $return['msg'].'设置成功！' : $return['msg'].'设置失败！';

            }else {

                $return['msg']		=	'名企结束时间必须大于开始时间！';
                $return['errcode']	=	8;
            }

        }else{

            $return['msg']		=	'系统繁忙！';
            $return['errcode']	=	8;

        }

        return $return;

    }

    /**
     * @desc 查询hotjob表，单条
     */
    function getHotJob($whereData = array(), $data = array()) {

        $select =   $data['field'] ? $data['field'] : '*';

        $info   =   $this -> select_once('hotjob', $whereData, $select);

        if (!empty($info)) {
            if($info['hot_pic']){

                $info['hot_pic']    =   checkpic($info['hot_pic']);

            }else{

                $company            =   $this->getInfo($whereData['uid'],array('logo'=>1,'utype'=>'admin','field'=>'`uid`,`logo`'));
                $info['hot_pic']    =   $company['logo'];

            }


        }

        return $info;

    }

    /**
     * @desc 查询hotjob表，多条
     */
    function getHotJobList($whereData = array(), $data = array('utype'=>'')) {

        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this -> select_all('hotjob', $whereData, $select);

        if (!empty($List)) {

            if ($data['utype'] == 'admin') {

                $List   =   $this -> getHotAList($List);

            }
        }

        return $List;
    }

    /**
     * @desc  名企信息完善
     */
    private function getHotAList($list) {

        foreach ($list as $k => $v){

            if(mb_strlen($v['username']) > 12){

                $list[$k]['username']   =   mb_substr($v['username'],'0','12','utf-8').'...';

            }

            if(mb_strlen($v['beizhu']) > 12){

                $list[$k]['beizhu']     =   mb_substr($v['beizhu'],'0','12','utf-8').'...';

            }

            if($v['did']<1){

                $list[$k]['did']        =   0;
            }


            $list[$k]['hot_pic']		=	checkpic($v['hot_pic']);

        }

        return $list;

    }
    /**
     * @desc  小程序名企信息完善
     */
    private function getHotWList($list)
    {
        foreach ($list as $v){

            $uid[]=$v['uid'];
        }
        $com  =  $this->select_all('company',array('uid'=>array('in',pylode(',', $uid))),'`uid`,`name`,`shortname`');

        $hjwhere['uid']	     =	array('in',pylode(',', $uid));
        $hjwhere['state']	 =	1;
        $hjwhere['status']	 =	0;
        $hjwhere['r_status'] =	1;
        $hjwhere['groupby']  =  'uid';

        $hjrows  =  $this->select_all('company_job',$hjwhere,'count(*) as num,uid');

        foreach ($list as $k => $v){

            $list[$k]['hot_pic']  =  checkpic($v['hot_pic'],$this->config['sy_unit_icon']);
            foreach ($com as $val){
                if ($v['uid'] == $val['uid']){
                    if($val['shortname']){
                        $list[$k]['name']  =  $val['shortname'];
                    }else{
                        $list[$k]['name']  =  $val['name'];
                    }
                }
            }
            $list[$k]['jobnum'] = 0;
            foreach ($hjrows as $val){
                if ($v['uid'] == $val['uid']){
                    $list[$k]['jobnum'] = $val['num'];
                }
            }
        }

        return $list;
    }
    /**
     * @desc 删除名企操作
     * @param array $whereData
     */
    public function delHotJob($uid = null) {

        if(!empty($uid)){

            $where = array('uid'=>array('in', pylode(',', $uid)));
 
            $result  =   $this  -> delete_all('hotjob', $where, '');

            if ($result) {
                $this -> update_once('company',array('hottime'=>' ','rec'=>'0'),$where);

                $msg    =  '管理员设置：名企招聘已取消';

                //发送系统通知
                include_once('sysmsg.model.php');

                $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                $sysmsgM    ->  addInfo(array('uid'=>$uid,'usertype'=>2, 'content'=>$msg));
            }



        }

        return $result;
    }



    /**
     * 查询企业产品信息
     * @param 表：company_product，company
     * @param 功能说明：获取company_product表里面所有店铺信息
     * @param 引用字段：$whereData：条件  2:$data:查询字段
     *
     */
    public function  getCompanyProductList($whereData,$data=array()){
        $CpNew      =   array();

        $field      =   $data['field'] ? $data['field'] : '*';

        $CpList     =   $this   ->  select_all('company_product',$whereData,$field);

        foreach($CpList as $val){

            $uids[]     =     $val['uid'];

        }

        if(!empty($CpList) && $CpList){

            $companywhere['uid']      =    array('in', pylode(',', $uids));

            $ListCA                  =   $this   ->    getList($companywhere);

            $companyList             =   $ListCA['list'];


            foreach($CpList  as $key=>$val){

				$CpList[$key]['pic']	=	checkpic($val['pic']);

                foreach($companyList  as  $v){

                    if($val['uid']==$v['uid']){

                        $CpList[$key]['name']    =   $v['name'];

                    }
                }

            }
        }

        return $CpList;
    }


    /**
     * 查询单条信息
     * @param 查询表：company_product
     * @param 功能说明：根据条$where条件 获取company_product表里面 单条信息
     * @param 引用字段：$where :条件  2:$data:查询字段
     */
    public function getComProductInfo($where = array(),$data=array()){

        if(!empty($where)){

            $select		=   $data['field'] ? $data['field'] : '*';

            $info		=   $this -> select_once('company_product', $where, $select);

            if(!empty($info)){

                $info['pic']    =   checkpic($info['pic']);

            }

        }

        return  $info;

    }
    /**
     * 更新审核功能
     * @param 查询表：company_product
     * @param 功能说明：根据条$id 获取company_product表里面 单条信息
     * @param 引用字段：$id :条件 2:$data:查询字段
     * @param lockInfo:审核说明
     *
     */
    public function upCompanyProductStatus($id,$data = array()){

        $where   = array();

        if(!empty($id)){

            $where['id']   =   array('in',pylode(',',$id));

            $updata  = array(

                'status'     =>  $data['status'],

                'statusbody' =>  $data['statusbody'],

            );

        }

        $nid                =   $this -> update_once('company_product', $updata, $where);

        return $nid;

    }

    /**
     * 删除信息
     * @param  表：company_product
     * @param 功能说明：根据条件$id 删除company_product表里面信息
     * @param 引用字段：$id :条件 2:$data:字段
     *
     */

    public function delCompanyProduct($id,$data=array()){

        if(!empty($id)){

            if(is_array($id)){

                $ids    =	$id;

                $return['layertype']	=	1;

            }else{

                $ids    =   @explode(',', $id);

                $return['layertype']	=	0;

            }

            $id           =   pylode(',', $ids);
            if($data['utype'] == 'user'){
				$delWhere	=	array('id' => array('in',$id),'uid'=>$data['uid']);
			}else{
				$delWhere	=	array('id' => array('in',$id));
			}
			 
            $return['id']	=	$this -> delete_all('company_product',$delWhere,'');

            if($return['id']){


                $return['msg']      =  $data['utype'] == 'user' ? '删除成功' : '企业产品(ID:'.$id.')删除成功';

                $return['errcode']  =  '9';

            } else{
                $return['msg']      =  $data['utype'] == 'user' ? '删除失败' : '企业产品(ID:'.$id.')删除失败';

                $return['errcode']  =  '8';
            }
        }else{

            $return['msg']      =  '系统繁忙';

            $return['errcode']  =  '8';

            $return['layertype']	=	'1';

        }

        return $return;

    }

    /**
     * 更新company_product功能
     * @param 查询表：company_product
     * @param 功能说明：根据条$whereData 获取company_product表里面 单条信息
     * @param 引用字段：$whereData :条件 2:$data:查询字段
     *
     */
    public function upCompanyProduct($whereData,$data = array()){
        if(!empty($whereData)){

            $updata             = array(

                'title'     =>  $data['title'],

                'uid' 		=>  $data['uid'],

                'did' 		=>  $data['did'],

                'body' 		=>  str_replace('&amp;','&',$data['body']),

                'ctime' 	=>  $data['ctime'],

                'statusbody'=>  $data['statusbody'],

                'status' 	=>  $data['status']

            );
            if($data['pic']){
                $updata['pic']  =   $data['pic'];
            }

        }

        $nid                    =   $this -> update_once('company_product', $updata, $whereData);

        return $nid;

    }

    /**
     * 添加company_product功能
     * @param 查询表：company_product
     * @param 引用字段：$data:操作字段
     *
     */
    public function addCompanyProduct($data = array()){
        if(!empty($data)){

            $addata  = array(

                'title'     =>  $data['title'],

                'uid' 		=>  $data['uid'],

                'did' 		=>  $data['did'],

                'body' 		=>  str_replace('&amp;','&',$data['body']),

                'ctime' 	=>  $data['ctime'],

                'pic' 		=>  $data['pic'],

                'statusbody'=>  $data['statusbody'],

                'status' 	=>  $data['status']

            );

        }

        $nid                =   $this -> insert_into('company_product', $addata);

        return $nid;

    }

    /**
     * 保存company_product
     * @param 表：company_product
     *
     */
    public function  setCompanyProduct($whereData,$data=array(),$usertype){

        if ($data['file']['tmp_name'] || $data['base']){

            $upArr   =  array(
                'file'     =>  $data['file'],
                'dir'      =>  'product',
                'base'     =>  $data['base'],
                'preview'  =>  $data['preview']
            );

            $result  =  $this -> upload($upArr);

            if (!empty($result['msg'])){

                $return['msg']      =  $result['msg'];
                $return['errcode']  =  '8';

                return $return;

            }elseif (!empty($result['picurl'])){

                $picurl  =  $result['picurl'];
            }

        }
        unset($data['file']);
        unset($data['base']);
        unset($data['preview']);

        if (isset($picurl)){
            $data['pic'] =   $picurl;
        }

        if(!empty($whereData)){

            $nid  	=	$this->upCompanyProduct($whereData,$data);
            $msg	=	'修改';
            $type	=	'2';
        }else{
            if(!isset($picurl)){

                $return['msg']      =   '请上传图片！';

                $return['errcode']  =   8;

                return $return;
            }
            $nid  	=	$this->addCompanyProduct($data);
            $msg	=	'添加';
            $type	=	'1';
        }
        if($nid){
			$this -> addMemberLog($data['uid'],$usertype,$msg.'企业产品',16,$type);
            $return['msg']		=	'操作成功！';
            $return['errcode']	=	9;
            $return['url']      =   'index.php?c=product';
        }else{
            $return['msg']		=	'操作失败，请稍后再试！';
            $return['url']      =   'index.php?c=product';
        }
        return $return;

    }

    //企业新闻
    /**
     * 查询企业新闻信息
     * @param 表：company_news，company
     * @param 功能说明：获取ccompany_news表里面所有店铺信息
     * @param 引用字段：$whereData：条件  2:$data:查询字段
     *
     */
    public function  getCompanyNewsList($whereData,$data=array()){

        $field         =   $data['field'] ? $data['field'] : '*';

        $CnewsList     =   $this   ->  select_all('company_news',$whereData,$field);

        foreach($CnewsList as $val){

            $uids[]     =     $val['uid'];

        }

        if(!empty($CnewsList) && $CnewsList){

            $companywhere['uid']      =    array('in', pylode(',', $uids));

            $ListCA                  =   $this   ->    getList($companywhere);

            $companyList             =   $ListCA['list'];


            foreach($CnewsList  as $key=>$val){

                foreach($companyList  as  $v){

                    if($val['uid']==$v['uid']){

                        $CnewsList[$key]['name']    =   $v['name'];

                    }

                }

            }
        }

        return $CnewsList;
    }

    /**
     * 查询单条信息(审核说明)
     * @param 查询表：company_news
     * @param 功能说明：根据条$id 获取company_news表里面 单条信息
     * @param 引用字段：$id :条件 2:$data:查询字段
     * @param lockInfo:审核说明
     *
     */
    public function getCompanyNewsLockInfo($id,$data=array()){

        if(!empty($id)){

            $select     =   $data['field'] ? $data['field'] : '*';

            $CnewsLock    =   $this -> select_once('company_news', array('id'=>intval($id)), $select);

        }

        return  $CnewsLock;

    }

	/**
      * 查询单条信息(审核说明)
      * @param 查询表：company_news
      * @param 功能说明：根据条$id 获取company_news表里面 单条信息
      * @param 引用字段：$id :条件 2:$data:查询字段
      * @param lockInfo:审核说明
      *
     */
    public function getCompanyNewsInfo($whereData,$data=array()){

        if(!empty($whereData)){

          $select   =   $data['field'] ? $data['field'] : '*';

          $info    	=   $this -> select_once('company_news', $whereData, $select);

        }

      return  $info;

    }


    /**
     * 删除信息
     * @param  表：company_product
     * @param 功能说明：根据条件$id 删除company_product表里面信息
     * @param 引用字段：$id :条件 2:$data:字段
     *
     */

    public function delCompanyNews($id,$data=array()){

        if(!empty($id)){

            if(is_array($id)){

                $ids    =	$id;

                $return['layertype']	=	1;

            }else{

                $ids    =   @explode(',', $id);

                $return['layertype']	=	0;

            }

            $id           =   pylode(',', $ids);

            if($data['utype'] == 'user'){
				$delWhere	=	array('id' => array('in',$id),'uid'=>$data['uid']);
			}else{
				$delWhere	=	array('id' => array('in',$id));
			}
			 
            $return['id']	=	$this -> delete_all('company_news',$delWhere,'');

            if($return['id']){
                $this	->	addMemberLog($data['uid'],$data['usertype'],'删除企业新闻',16,3);

                $return['msg']      =  $data['utype'] == 'user' ? '删除成功' : '企业新闻(ID:'.$id.')删除成功';

                $return['errcode']  =  '9';

            } else{
                $return['msg']      =  $data['utype'] == 'user' ? '删除失败' : '企业新闻(ID:'.$id.')删除失败';

                $return['errcode']  =  '8';
            }
        }else{

            $return['msg']      		=  '系统繁忙';

            $return['errcode']  		=  '8';

            $return['layertype']		=	'0';

        }

        return $return;

    }

    /**
     * 更新审核功能
     * @param 查询表：company_news
     * @param 功能说明：根据条$id 获取company_news表里面 单条信息
     * @param 引用字段：$id :条件 2:$data:查询字段
     * @param lockInfo:审核说明
     *
     */
    public function upCompanyNewsStatus($id,$data = array()){

        $where   = array();

        if(!empty($id)){

            $where['id']   =   array('in',pylode(',',$id));

            $updata  = array(

                'status'     =>  $data['status'],

                'statusbody' =>  $data['statusbody'],

            );

        }

        $nid                =   $this -> update_once('company_news', $updata, $where);

        return $nid;

    }

    /**
     * 更新company_news功能
     * @param 查询表：company_news
     * @param 功能说明：根据条$whereData 获取company_news表里面 单条信息
     * @param 引用字段：$whereData :条件 2:$data:查询字段
     *
     */
    public function upCompanyNews($whereData,$data = array()){
        if(!empty($whereData)){

            $updata  = array(

                'title'     =>  $data['title'],

                'body' 		=>  str_replace('&amp;','&',$data['body']),

                'ctime' 	=>  $data['ctime'],

                'status' 	=>  $data['status']

            );

        }

        $nid                =   $this -> update_once('company_news', $updata, $whereData);

        return $nid;

    }

    /**
     * 添加company_news功能
     * @param 查询表：company_news
     * @param 功能说明：根据条$whereData 获取company_news表里面 单条信息
     * @param 引用字段：$data:操作字段
     *
     */
    public function addCompanyNews($data = array()){
        if(!empty($data)){

            $addata  = array(

                'title'     =>  $data['title'],

                'uid' 		=>  $data['uid'],

                'did' 		=>  $data['did'],

                'body' 		=>  str_replace('&amp;','&',$data['body']),

                'ctime' 	=>  $data['ctime'],

                'status' 	=>  $data['status']

            );

        }

        $nid                =   $this -> insert_into('company_news', $addata);

        return $nid;

    }

    /**
     * 保存company_news
     * @param 表：company_news
     *
     */
    public function  setCompanyNews($whereData,$data=array()){
		if(!empty($data['body'])){
			if($this->config['sy_outlinks']!=1){
				$patten		=	array("\r\n", "\n", "\r");
				$patea      =   "/<a[^>]*>(.*?)<\/a>/is";//匹配a标签
				$patet      =   "/href=\"(.*)\"/";//取a标签的值
				$pateh      =   '/<a .*?href="(.*?)".*?>/is';
				$content	=	str_replace($patten, "<br/>", $data['body']);
				$str		=	htmlspecialchars_decode($content);
				$data['body']	=	preg_replace($patea,"$1", $str);
			}
		}

        if(!empty($whereData)){
            $nid  	=	$this->upCompanyNews($whereData,$data);
            $msg	=	'修改';
            $type	=	'2';

        }else{
            $nid  	=	$this->addCompanyNews($data);
            $msg	=	'添加';
            $type	=	'1';
        }
        if($nid){
			if(!$data['uid']){
				$data['uid'] = $whereData['uid'];
			}
			$this	->	addMemberLog($data['uid'],$data['usertype'],$msg.'企业新闻',16,$type);
            $return['msg']		=	'操作成功！';
            $return['errcode']	=	9;
        }else{
            $return['msg']		=	'操作失败，请稍后再试！';
            $return['errcode']	=	8;
        }
        return $return;

    } 
     
    /**
     * 添加company_show功能
     * @param 查询表：company_show
     * @param 功能说明：根据条$whereData 获取company_news表里面 单条信息
     * @param 引用字段：$data:操作字段
     *
     */
    public function addCompanyshow($data = array()){
        if(!empty($data)){

            $addata  = array(

                'title'     =>  $data['title'],

                'uid' 		=>  $data['uid'],

                'sort' 		=>  $data['sort'],

                'body' 		=>  $data['body'],

                'ctime' 	=>  $data['ctime'],

                'status' 	=>  $data['status'],

                'picurl' 	=>  $data['picurl']

            );

        }

        $nid                =   $this -> insert_into('company_show', $addata);

        return $nid;

    }

    /**
     * @desc    查询/企业环境图片
     * @param   表：company_show，company
     * @param   功能说明：获取company_show表里面所有企业环境信息
     * @param   引用字段：$whereData：条件  2:$data:查询字段
     */

    public function getCompanyShowList($whereData,$data=array()){

        $field              =   $data['field'] ? $data['field'] : '*';

        $CompanyShowList    =   $this -> select_all('company_show',$whereData,$field);

        foreach($CompanyShowList as $val){

            $uids[]         =   $val['uid'];

        }

        if(!empty($CompanyShowList) && $CompanyShowList){

            $companyList        =   array();
            if(!empty($uids)){
                $ListCA         =   $this -> getList(array('uid'=>array('in', pylode(',', $uids))));
                $companyList    =   $ListCA['list'];
            }
            foreach($CompanyShowList  as $key => $val){

				$CompanyShowList[$key]['picurl']  = checkpic($val['picurl']);

                foreach($companyList  as  $v){

                    if($val['uid']==$v['uid']){

                        $CompanyShowList[$key]['name']     =      $v['name'];

                    }
                }

            }
        }

        return $CompanyShowList;

    }

    /**
     * @desc    查询/企业环境图片(单张图片)
     * @param   表：company_show，company
     * @param   功能说明：获取company_show表里面根据id获取企业环境信息
     * @param   引用字段：$id：条件  2:$data:查询字段
     */

    public function getCompanyShowInfo($id,$data=array()){

        if(!empty($id)){

            $where['id']        =   $id;
            if($data['uid']){
				$where['uid']        =   $data['uid'];
			}
            $select             =   $data['field'] ? $data['field'] : '*';

            $companyShowInfo    =   $this -> select_once('company_show', $where, $select);


        }elseif($data['where']){

			$select             =   $data['field'] ? $data['field'] : '*';

            $companyShowInfo    =   $this -> select_once('company_show', $data['where'], $select);

		}

		if(!empty($companyShowInfo)){

			if($data['type']=='showpic'){

				$companyShowInfo['picurl']		= 	checkpic($companyShowInfo['picurl']);

            }

		}

        return $companyShowInfo;

    }

    /**
     * @desc 删除企业环境图片操作
     * @param array $whereData
     */
    public function delCompanyShow($id,$data = array()) {

        if(!empty($id)){
            if($data['utype'] == 'admin'){
				$where              =   array();
			}else{
				$where              =   array('uid' => $data['uid']);
			}


            if (is_array($id)) {

                $where['id']    =   array('in' , pylode(',' ,  $id));

            } else {

                $where['id']    =   intval($id);

            }

            $result	            =   $this -> delete_all('company_show', $where, '');
        }
        return $result;
    }

	/**
     * @desc 删除企业环境图片操作
     * @param array $whereData
     */
	public function delComshowInfo($whereData=array(),$data = array()){

		$row	=	$this -> getCompanyShowInfo($whereData['id']);

		if(is_array($row)){
			
			$oid	=   $this -> delete_all('company_show', array('id'=>$whereData['id'],'uid'=>$whereData['uid']),'');

			if($oid){

				$this -> addMemberLog($whereData['uid'],$whereData['usertype'],'删除企业环境展示',16,3);
			}
		}
		return $oid;
   }
    /**
     * @desc 更新企业环境图片操作
     * @param array $whereData
     */
    public function upCompanyShow($id,$data  = array()){

        if (!empty($id)) {
            if (is_array($id)) {

                $where['id']    =   array('in' , pylode(',' ,  $id));

            } else {

                $where['id']    =   intval($id);

            }
			if($data['uid']){
				$where['uid']    =   $data['uid'];
			}
            $nid    =   $this -> update_once('company_show', $data, $where);
        }
        return $nid;
    }

    /**
     * @desc 查询company_show  数量
     */
    public function getComShowNum($whereData = array()){

        return  $this -> select_num('company_show', $whereData);

    }

    /**
     * @desc 查询//企业横幅
     * @param 表：banner，company
     * @param 功能说明：获取company_show表里面所有/企业横幅信息
     * @param 引用字段：$whereData：条件  2:$data:查询字段
     */

    public function getBannerList($whereData,$data=array()){

        $field          =   $data['field'] ? $data['field'] : '*';

        $BannerList     =   $this -> select_all('banner',$whereData,$field);

        foreach($BannerList as $val){

            $uids[]     =   $val['uid'];

        }

        if(!empty($BannerList) && $BannerList){

            $ListCA         =   $this -> getList(array('uid'=>array('in', pylode(',', $uids))));

            $companyList    =   $ListCA['list'];

            foreach($BannerList  as $key=>$val){

				$BannerList[$key]['pic']	=	checkpic($val['pic']);

                foreach($companyList  as  $v){

                    if($val['uid']==$v['uid']){

                        $BannerList[$key]['name']     =      $v['name'];
                    }
                }
            }
        }
        return $BannerList;
    }
    /**
     * 查询/企业横幅图片(单张图片)
     * @param 表：banner，company
     * @param 功能说明：获取banner表里面根据id获取企业环境信息
     * @param 引用字段：$id：条件  2:$data:查询字段
     *
     */

    public function getBannerInfo($id,$data=array()){

        if(!empty($id)){

            $where['id']    =   $id;

        }elseif($data['where']){

			$where          =	$data['where'];

		}

        $select             =   $data['field'] ? $data['field'] : '*';

        $banner             =   $this -> select_once('banner', $where, $select);

        if (!empty($banner) && is_array($banner)) {

			$banner['pic']  =   checkpic($banner['pic'],$this->config['sy_banner']);

		}


        return $banner;

    }
    /**
    * $data 自定义数组,uid，usertype,pc：file必要参数
    */
    public function setBanner($data  =   array()){
        if(!empty($data)){

            if ($data['file']['tmp_name'] || $data['base']){

                $upArr   =  array(
                    'file'     =>  $data['file'],
                    'dir'      =>  'company',
                    'base'     =>  $data['base'],
                    'preview'  =>  $data['preview']
                );

                $result  =  $this -> upload($upArr);

                if (!empty($result['msg'])){

                    $return['msg']      =  $result['msg'];
                    $return['errcode']  =  '8';
                    return $return;

                }elseif (!empty($result['picurl'])){

                    $picurl  =  $result['picurl'];
                }
            }

            if(isset($picurl)){
                $cominfo	=	$this -> select_once('company',array('uid'=>$data['uid']),'`r_status`');
                $valueData  =   array(
                    'pic'       =>  $picurl,
                    'status'    =>  $cominfo['r_status']==0?1:$this->config['com_banner_status']
                );
            }

            if($data['type']=='add'){
				$member				=	$this->select_once('member',array('uid'=>$data['uid']),'`did`');
				$valueData['did']	=	$member['did'];
                $valueData['uid']	=   $data['uid'];

                $row	=   $this->getBannerNum(array('uid'=>$data['uid']));

                if($row>0){
                    $return['msg']      =  '已有横幅，不能再添加横幅！';
                    $return['errcode']  =  8;
                    $return['url']      =  $_SERVER['HTTP_REFERER'];

                }else{

                    $this -> addBanner($valueData);

                    $this -> addMemberLog($data['uid'],$data['usertype'],"上传企业横幅",16,1);//会员日志

                    require_once ('integral.model.php');

                    $IntegralM          =  new integral_model($this->db, $this->def);

                    $IntegralM -> invtalCheck($data['uid'],$data['usertype'],"integral_banner","上传企业横幅");

                    $return['msg']      =  '设置成功！';

                    $return['errcode']  =  9;

                    $return['url']      =  $_SERVER['HTTP_REFERER'];
                }
            }else if ($data['type']=='update'){

                $whereData['uid']       =   $data['uid'];

                $this -> upBanner("",$valueData,$whereData);

                $this -> addMemberLog($data['uid'],$data['usertype'],"修改企业横幅",16,2);//会员日志

                $return['msg']      =  '修改成功！';

                $return['errcode']  =  9;

                $return['url']      =  $_SERVER['HTTP_REFERER'];

            }
            return $return;
        }

    }

    public function upBanner($id,$data  =   array(),$where){
        if(!empty($id)){
            if (is_array($id)) {

                $where['id']    =   array('in' , pylode(',' ,  $id));

            } else {

                $where['id']    =   intval($id);

            }
        }
        $nid           =   $this -> update_once('banner', $data, $where);

        return $nid;

    }

    /**
     * @desc    添加企业横幅
     */
    public function addBanner($data  = array()){

        $nid           =   $this -> insert_into('banner', $data);

        return $nid;

    }

    /**
     * @desc 查询企业横幅数目
     * @param   array   $Where
     * @return  number
     */
    public function getBannerNum($whereData = array()){

        return $this->select_num('banner', $whereData);

    }
    /**
     * @desc 删除企业横幅操作
     * @param array $whereData
     */
    public function delBanner($id) {

        if(!empty($id)){

            $where              =   array();

            if (is_array($id)) {

                $where['id']    =   array('in' , pylode(',' ,  $id));

            } else {

                $where['id']    =   intval($id);

            }
             
            $result	            =   $this -> delete_all('banner', $where, '', '', '', $rcyDat);

        }

        return $result;
    }

    /**
     * @desc 更新企业信息操作
     * @param array $whereData
     * mData member表要修改数据;  comData company表要修改数据;  sData 后台修改操作数量数据;  utype 修改操作类型：admin-后台，user-会员中心; wap 手机站来的(此来源固话有不同)
     */
    public function setCompany($whereData , $data = array('mData' => null, 'comData' => null, 'sData' => null,'utype' => null,'wap'=>null))
    {
        $return  =  array();

        if(!empty($whereData) && is_array($whereData)){

            $mData     =  $data['mData'];
            $comData   =  $data['comData'];

            //修改相应字段检查
            $setCheck  =  $this -> setFieldCheck($whereData['uid'], $comData, $data['wap']);

            if(!empty($setCheck['msg'])){

                $setCheck['errcode']  =  8;
                return $setCheck;

            }
			if (!empty($setCheck['linkphone'])){

                $comData['linkphone']  =  $setCheck['linkphone'];
            }else{
				$comData['linkphone']  =  '';
			}


            //会员操作的修改，需要判断手机号、邮件是否已绑定,营业执照是否已验证，绑定的不能修改
            if ($data['utype'] == 'user'){

                $comField  =  '`uid`,`lastupdate`,`moblie_status`,`email_status`,`yyzz_status`,`x`,`y`,`linktel`,`moblie_status`,`crm_uid`,`crm_time`,`provinceid`,`cityid`,`three_cityid`';
                $com       =  $this -> select_once('company',array('uid' => $whereData['uid']), $comField);
                if($com['linktel'] && $com['moblie_status']=='1'){
                    $comData['linktel'] = $com['linktel'];
                }

                if (!empty($com)){

                    if ($com['moblie_status'] == '1'){

                        if (!empty($mData) && $mData['moblie']){

                            unset($mData['moblie']);
                        }
                        if (!empty($comData) && $comData['moblie']){

                            unset($comData['moblie']);
                        }
                    }
                    if ($com['email_status'] == '1'){

                        if (!empty($mData) && $mData['email']){

                            unset($mData['email']);
                        }
                        if (!empty($comData) && $comData['email']){

                            unset($comData['email']);
                        }
                    }
                    if ($com['yyzz_status'] == '1'){
                        if (!empty($comData) && $comData['name']){

                            unset($comData['name']);
                        }
                    }
                }
				if($com['x'] == '' && $com['y'] == ''){
					$is_map	=	1;
				}
            }

            // 处理会员基本信息
            if (!empty($mData)){

                require_once ('userinfo.model.php');

                $UserinfoM  =  new userinfo_model($this->db, $this->def);

                $ckresult   =  $UserinfoM -> addMemberCheck($mData, $whereData['uid'], $data['utype']);

                if (isset($ckresult) && $ckresult['msg']){

                    $ckresult['errcode']	=  8;

                    return $ckresult;
                }
            }

            // 管理员操作的，有操作数量或会员等级，对其进行处理
			/**if ($data['utype'] == 'admin' && !empty($data['sData'])){
                $this -> setStatisInfo($whereData['uid'], $data['sData']);
            }*/

            // 处理企业基本信息
            if (!empty($comData)){

                if(!empty($comData['welfare']) && is_array($comData['welfare'])){

                    $comData['welfare']	=	@implode(',',$comData['welfare']);
                }

                if(!empty($comData['preview'])){

                    $upArr    =  array(
                        'base'  =>  $comData['preview'],
                        'dir'   =>  'company'
                    );
                    $result  =  $this -> upload($upArr);

                    if(!empty($result['msg'])){

                        $return['msg']      =  $result['msg'];
                        $return['errcode']  =  8;
                        return $return;

                    }else{

                        $comData['comqcode']  =  $result['picurl'];
                    }
                    unset($comData['preview']);
                }
                
                if(!empty($comData['photos'])){

                    $upArr    =  array(
                        'file'  =>  $comData['photos'],
                        'dir'   =>  'company'
                    );
                    $result  =  $this -> upload($upArr);

                    if(!empty($result['msg'])){

                        $return['msg']      =  $result['msg'];
                        $return['errcode']  =  8;
                        return $return;

                    }else{

                        $comData['comqcode']  =  $result['picurl'];
                    }
                    unset($comData['preview']);
                }

                $return['id']  =  $this -> update_once('company', $comData, $whereData);

                if ($return['id']){

                    //更新company_job
					
                    $this -> setJobInfo($whereData['uid'], $comData);

                    // 更新之后的企业信息
                    $comField  =  '`uid`,`lastupdate`,`moblie_status`,`email_status`,`yyzz_status`,`x`,`y`,`linktel`,`moblie_status`,`crm_uid`,`crm_time`,`provinceid`,`cityid`,`three_cityid`';

					$com    =  $this -> select_once('company',array('uid' => $whereData['uid']), $comField);

					if ($data['utype'] == 'user'){

                        require_once ('cookie.model.php');
                        $cookieM  =  new cookie_model($this->db, $this->def);
                        $cookieM -> SetCookie('delay', '', time() - 60);

                        //会员日志
                        $this -> addMemberLog($whereData['uid'], 2, '修改企业信息',7);

						require_once ('integral.model.php');
						$IntegralM 	=  new integral_model($this->db, $this->def);
						
						if($is_map == 1 && $com['x']!='' && $com['y']!=''){
							$IntegralM	->	invtalCheck($whereData['uid'],'2','integral_map','设置企业地图');
						}

                        //首次完善基本资料获得积分
                        if(!empty($com)  && $com['lastupdate'] == ''){
 
                            $IntegralM -> invtalCheck($whereData['uid'],'2', 'integral_userinfo','首次填写基本资料', 25);

                            $return['integralnum']      =     1;

							require_once ('statis.model.php');
							$statisM	=	new statis_model($this->db, $this->def);
							$statis		=	$statisM -> vipOver($whereData['uid'], 2);
							$return['addjobnum']	=	$statis['addjobnum'];
                        }

                        /* 资质（营业执照、手机、邮箱、地图设置）认证查询 */
                        $cert               =   $this -> getCertInfo(array('uid' => $com['uid'], 'type' => 3), array('field' => 'status'));

                        if (!empty($cert) && is_array($cert)) {
                            $return['yyzz_i']   =   $cert['status'];
                        }

                        $return['moblie_i'] =   $com['moblie_status'];
                        $return['email_i']  =   $com['email_status'];
                        $return['map_i']    =   ($com['x'] != '' && $com['y']!='') ? 1 : 0;
                        $return['msg']      =   '基本资料修改成功';
                    }else{
                        $return['msg']      =   '企业会员(ID:'.$whereData['uid'].')基本资料修改成功';
                    }

                    $return['errcode']      =    9;
                }else{

                    if ($data['utype'] == 'user'){
                        $return['msg']		=  '基本资料修改失败';
                    }else{
                        $return['msg']		=  '企业会员(ID:'.$whereData['uid'].')基本资料修改失败';
                    }
                    $return['errcode']	=  8;
                }
            }else{

                $return['msg']		=   '没有要修改的企业信息';
                $return['errcode']	=    8;
            }
        }else{

            $return['msg']		    =   '请选择要修改的企业';
            $return['errcode']      =   8;
        }
        return $return;
    }

    /**
     * @desc 后台修改企业基本信息时，对企业会员等级和操作数量处理
     */
    public function setStatisInfo($uid, $sData){

        if (!empty($sData['rating'])) {
 
            require_once 'statis.model.php';

            $statisM    =	new statis_model($this->db, $this->def);

            $comRating  =	$this -> select_once('company_statis', array('uid' => $uid),'`rating`, `job_num`, `breakjob_num`, `down_resume`, `invite_resume`, `zph_num`, `top_num`, `rec_num`, `urgent_num`, `sons_num`, `vip_etime`');


			
			$msg		=	array();
			
			foreach($sData as $key => $value){
				
				if($key == 'job_num' && $value != $comRating[$key]){

					$msg[]	=	" 发布职位数：".$comRating[$key]." -> ".$value;
				}else if($key == 'breakjob_num' && $value != $comRating[$key]){

					$msg[]	=	" 刷新职位数：".$comRating[$key]." -> ".$value;
				}else if($key == 'down_resume' && $value != $comRating[$key]){

					$msg[]	=	" 下载简历数：".$comRating[$key]." -> ".$value;
				}else if($key == 'invite_resume' && $value != $comRating[$key]){

					$msg[]	=	" 邀请面试数：".$comRating[$key]." -> ".$value;
				}else if($key == 'zph_num' && $value != $comRating[$key]){

					$msg[]	=	" 招聘会报名：".$comRating[$key]." -> ".$value;
				}else if($key == 'top_num' && $value != $comRating[$key]){

					$msg[]	=	" 职位置顶数：".$comRating[$key]." -> ".$value;
				}else if($key == 'urgent_num' && $value != $comRating[$key]){

					$msg[]	=	" 紧急招聘数：".$comRating[$key]." -> ".$value;
				}else if($key == 'rec_num' && $value != $comRating[$key]){

					$msg[]	=	" 职位推荐数：".$comRating[$key]." -> ".$value;
				}else if($key == 'vip_etime' && $value != $comRating[$key]){

					$vEtime	=	$value ? $value : '不限';

					$vRtime	=	$comRating[$key] ? date('Y-m-d', $comRating[$key]) : '不限';

					$msg[]	=	"会员到期时间：".$vRtime." -> ".$vEtime;
				}
			}
			
			if(!empty($msg)){
				$msgContent	=	@implode('，', $msg);	
			}

            //企业会员等级做出了修改
            if ($sData['rating'] != $comRating['rating']){

                require_once 'rating.model.php';
                $ratingM	=	new rating_model($this->db, $this->def);
                $rvalue		=   $ratingM -> ratingInfo($sData['rating'], $uid);

				$rvalue['job_num']			=	$rvalue['job_num'] == $sData['job_num'] ? $rvalue['job_num'] : $sData['job_num'];
				$rvalue['breakjob_num']		=	$rvalue['breakjob_num'] == $sData['breakjob_num'] ? $rvalue['breakjob_num'] : $sData['breakjob_num'];
				$rvalue['down_resume']		=	$rvalue['down_resume'] == $sData['down_resume'] ? $rvalue['down_resume'] : $sData['down_resume'];
				$rvalue['invite_resume']	=	$rvalue['invite_resume'] == $sData['invite_resume'] ? $rvalue['invite_resume'] : $sData['invite_resume'];
				$rvalue['zph_num']			=	$rvalue['zph_num'] == $sData['zph_num'] ? $rvalue['zph_num'] : $sData['zph_num'];
				$rvalue['rec_num']			=	$rvalue['rec_num'] == $sData['rec_num'] ? $rvalue['rec_num'] : $sData['rec_num'];
				$rvalue['top_num']			=	$rvalue['top_num'] == $sData['top_num'] ? $rvalue['top_num'] : $sData['top_num'];
				$rvalue['urgent_num']		=	$rvalue['urgent_num'] == $sData['urgent_num'] ? $rvalue['urgent_num'] : $sData['urgent_num'];
				$rvalue['sons_num']			=	$rvalue['sons_num'] == $sData['sons_num'] ? $rvalue['sons_num'] : $sData['sons_num'];
				$rvalue['vip_etime']		=	$rvalue['vip_etime'] == $sData['vip_etime'] ? $rvalue['vip_etime'] : $sData['vip_etime'];

                $rinfo      =   $ratingM -> getInfo(array('id'=>$sData['rating']),array('field'=>'`name`,`time_start`,`time_end`,`yh_price`,`service_price`'));
                $resulut	=	$statisM -> upInfo($rvalue, array('uid' => $uid, 'usertype' => 2, 'adminedit' => '1', 'info' => $rinfo));


				$msg		=	"会员等级：".$comRating['rating_name']." -> ".$rValue['rating_name'];
			
				$msgContent	=	$msgContent ? $msg.'，'.$msgContent : $msg;

            } else {

                $result =	$this -> update_once('company_statis', $sData, array('uid' => $uid));
				$this -> update_once('company', array('vipetime' => $sData['vip_etime']), array('uid' => $uid));
            }

			if($result){

				if(!empty($msgContent)){
				
					require_once ('log.model.php');
					$logM	=	new log_model($this->db, $this->def);

					$msgContent	=	'企业（UID：'.$uid.'）修改套餐信息；'.$msgContent;

					$logM -> addAdminLog($msgContent); 
					$return	=	array(
						'errcode' =>	'9',
						'msg'     =>	'套餐信息更新成功！'
					);
				}else{
					$return	=	array(
						'errcode' =>	'9',
						'msg'     =>	'套餐信息未做修改！'
					);
				}
				
			}else{

				$return	=	array(
					'errcode' =>	'8',
					'msg'     =>	'套餐信息更新失败！'
				);
			}
			

			

        }else{

			$return	=	array(

				'errcode'	=>	'8',
				'msg'		=>	'参数错误，请重试！'
			);
		}

		return $return;
    }

    /**
     * @desc 修改企业基本信息同步修改职位相关信息
     */
    private function setJobInfo($uid, $comData)
    {
        $jobField = '`pr`,`mun`,`welfare`,`com_provinceid`,`rating`,`com_name`,`r_status`,`x`,`y`';
        $job = $this->select_once('company_job', array('uid' => $uid), $jobField);
		
        // 有职位的比较各修改项，有改动的才修改
       

		if (isset($comData['pr'])) {

			$cjdata['pr'] = $comData['pr'];

		}
		if (isset($comData['mun'])){

			$cjdata['mun']             =  $comData['mun'];

		}
		if (isset($comData['rating'])){

			$cjdata['rating']             =  $comData['rating'];

		}
		if (isset($comData['provinceid'])){

			$cjdata['com_provinceid']  =  $comData['com_provinceid'];

		}
		
		if (isset($comData['r_status'])){

			$cjdata['r_status']        =  $comData['r_status'];

		}
		if(isset($comData['x'])){

			$cjdata['x']        =  $comData['x'];

		}
		if(isset($comData['y'])){

			$cjdata['y']        =  $comData['y'];
		}
           
        
		
        // 如企业名称改变
        if (!empty($comData['name'])){

            $cjdata['com_name']  =  $comData['name'];
            //修改其他表中的企业名称
            $this -> update_once('blacklist',array('com_name'=>$comData['name']),array('c_uid'=>$uid));
            $this -> update_once('fav_job',array('com_name'=>$comData['name']),array('com_id'=>$uid));
            $this -> update_once('hotjob',array('username'=>$comData['name']),array('uid'=>$uid));
            $this -> update_once('msg',array('com_name'=>$comData['name']),array('job_uid'=>$uid));
            $this -> update_once('partjob',array('com_name'=>$comData['name']),array('uid'=>$uid));
            $this -> update_once('report',array('r_name'=>$comData['name']),array('c_uid'=>$uid));
            $this -> update_once('userid_job',array('com_name'=>$comData['name']),array('com_id'=>$uid));
        }
        if (!empty($cjdata)){

            $this -> update_once('company_job', $cjdata, array('uid'=>$uid));
        }
    }

    /**
     * @desc    保存企业基本信息，检查相应字段
     */
    private function setFieldCheck($uid, $comData, $data = array('wap' => null)){

        $return   =  array();
        $comname  =  $this -> select_once('company', array('name' => $comData['name']), '`uid`');

        if($comData['name']==''){

            $return['msg']  =  '企业全称不能为空！';
        }elseif(!empty($comname) && $comname['uid'] != $uid){

            $return['msg']	= '企业全称已存在！';
        }elseif($comData['hy']==''){

            $return['msg']	=  '从事行业不能为空！';
        }elseif($comData['pr']==''){

            $return['msg']	=  '企业性质不能为空！';

        }elseif($comData['mun']==''){
            $return['msg'] = '企业规模不能为空！';
        } elseif ($comData['provinceid'] == '') {

            $return['msg'] = '所在地不能为空！';
        } elseif ($comData['address'] == '') {

            $return['msg'] = '公司地址不能为空！';
        } elseif ($comData['linkman'] == '') {

            $return['msg'] = '联系人不能为空！';
        } elseif ($comData['content'] == '') {

            $return['msg'] = '企业简介不能为空！';
        }
		$mstatus	=	 $this -> select_once('company', array('uid' => $uid), '`moblie_status`');
		if($mstatus['moblie_status']!=1){
			if($comData['linktel']==''){

				if($comData['linkphone']==''){

					$return['msg']	=  '联系手机和固定电话任填一项！';
				}
			}
		}
        if (!empty($return['msg'])){

            return $return;

        } elseif (!empty($comData['linkphone'])){

            return array('linkphone'=> $comData['linkphone']);
        }
    }

    /**
     * 修改企业LOGO
     * @param array $whereData
     * @param array $data   photo/需上传的图片文件;   thumb/已处理好的缩略图;  utype/操作的用户类型;  base/需上传的base4图片;  preview/pc预览即上传
     */
    public function upLogo($whereData, $data=array('photo'=>null,'thumb'=>null,'utype'=>null,'base'=>null,'preview'=>null))
    {
        if (!empty($whereData['uid'])){

            $uid  =  $whereData['uid'];
            // logo还需上传的
            if ($data['photo'] || $data['base']){

                $upArr   =  array(
                    'file'     =>  $data['photo'],
                    'dir'      =>  'company',
                    'type'     =>  'logo'
                );
                if (!empty($data['base'])){
                    $upArr['base'] = $data['base'];
                }
                if (!empty($data['preview'])){
                    $upArr['preview'] = $data['preview'];
                }
                $result  =  $this -> upload($upArr);

                if (!empty($result['msg'])){

                    $return['msg']      =  $result['msg'];
                    $return['errcode']  =  '8';

                    return $return;

                }elseif (!empty($result['picurl'])){

                    $logo  =  $result['picurl'];
                }
            }
            // 已处理好的logo缩略图
            if (!empty($data['thumb'])){

                $logo  =  str_replace('../data','./data',$data['thumb'][1]);
            }
            if (!empty($logo)){
                // 用户操作，且后台设置用户头像需要审核的
                $cominfo	=	$this -> getInfo($uid,array('field'=>'r_status'));
				if(empty($cominfo)){

				    $this->activUser($uid, 2);
				}
				if ($data['utype'] == 'user' && $this -> config['com_logo_status'] == 1){
                    $logo_status  =  1;
                }else{
                    $logo_status  =  $cominfo['r_status']==0?1:0;
                }
                $return['id']  =  $this->update_once('company',array('logo'=>$logo,'logo_status'=>$logo_status),array('uid'=>$uid));
            }

            if (isset($return['id'])) {
                // 用户操作的，判断处理logo上传积分
                if ($data['utype'] == 'user'){

                    require_once ('integral.model.php');

                    $IntegralM 	= 	new integral_model($this -> db, $this -> def);
                    $IntegralM	->	invtalCheck($uid,'2','integral_avatar','上传LOGO');

                    $this -> addMemberLog($uid, 2, '上传LOGO', 16, 1);

                    if ($this -> config['com_logo_status'] == 1){

                        $return['errcode']  =  '9';
                        $return['msg']      =  '上传成功，管理员审核后对其他用户开放显示';

                    }else{
                        $return['errcode']  =  '9';
                        $return['msg']      =  '上传成功';
                    }
                    // pc会员中心预览即上传，处理预览图
                    if ($data['preview']){

                        $return['picurl']  =  checkpic($logo);
                    }
                }else{
                    $return['msg']      =  '企业LOGO(ID:'.$uid.')修改成功';
                    $return['errcode']  =  '9';
                }
            }else{

                $return['msg']      =  '企业LOGO(ID:'.$uid.')修改失败';
                $return['errcode']  =  '8';
            }
        }else{

            $return['msg']      =  '请选择需要修改的用户';
            $return['errcode']  =  '8';
        }
        return $return;
    }

     /**
     * 修改企业二维码
     * @param array $whereData
     * @param array $data   photo/需上传的图片文件;   thumb/已处理好的缩略图;  utype/操作的用户类型;  base/需上传的base4图片;  preview/pc预览即上传
     */
    public function upEwm($whereData,$data=array('photo'=>null,'base'=>null,'preview'=>null))
    {
        if (!empty($whereData['uid'])){

            $uid  =  $whereData['uid'];

            if ($data['photo'] || $data['base']){

                $upArr   =  array(
                    'file'     =>  $data['photo'],
                    'dir'      =>  'company',
                    'type'     =>  'ewm',
                    'base'     =>  $data['base'],
                    'preview'  =>  $data['preview']
                );
                $result  =  $this -> upload($upArr);

                if (!empty($result['msg'])){

                    $return['msg']      =  $result['msg'];
                    $return['errcode']  =  '8';

                    return $return;

                }elseif (!empty($result['picurl'])){


                        $logo  =  $result['picurl'];


                }
            }

            if (!empty($logo)){

				$cominfo	=	$this -> getInfo($uid,array('field'=>'uid'));

                if(empty($cominfo)){

                    $this->activUser($uid, 2);
				}
                $return['id']  =  $this->update_once('company',array('comqcode'=>$logo),array('uid'=>$uid));

            }

            if (isset($return['id'])) {


                $this -> addMemberLog($uid, 2, '上传二维码', 16, 1);



                if ($data['preview']){

                    $return['picurl']  =  checkpic($logo);
                }

                $return['msg']      =  $data['utype'] == 'user' ? '修改成功' : '企业二维码(ID:'.$uid.')修改成功';
                $return['errcode']  =  '9';

            }else{

                $return['msg']      =  $data['utype'] == 'user' ? '修改失败' : '企业二维码(ID:'.$uid.')修改失败';
                $return['errcode']  =  '8';
            }
        }else{

            $return['msg']      =  '请选择需要修改的用户';
            $return['errcode']  =  '8';
        }
        return $return;
    }

     /**
     * 修改企业相关图片
     * @param array $whereData
     * @param array $data   photo/需上传的图片文件; base/需上传的base4图片;  preview/pc预览即上传 type/图片类别; dir/上传路径
     */
    public function upPic($whereData, $data=array('photo'=>null,'base'=>null,'preview'=>null,'type'=>null,'dir'=>null))
    {

        if ($data['photo'] || $data['base']){

            $upArr   =  array(
                'file'     =>  $data['photo'],
                'dir'      =>  $data['dir'],
                'type'     =>  $data['type'],
                'base'     =>  $data['base'],
                'preview'  =>  $data['preview']
            );
            $result  =  $this -> upload($upArr);

            if (!empty($result['msg'])){

                $return['msg']      =  $result['msg'];
                $return['errcode']  =  '8';

                return $return;

            }elseif (!empty($result['picurl'])){

                $logo  =  $result['picurl'];
            }
        }

        if ($data['preview']){

            $return['picurl']  =  checkpic($logo);
        }


        return $return;
    }

    //设置地图
    public function setMap($id,$data=array()){

        $return		=	array('cod'=>8,'url'=>$_SERVER['HTTP_REFERER']);

        if($id){
            if($data){
                $row	= 	$this->getInfo($id,array('field'=>'`x`,`y`'));
                if($row['x'] == '' && $row['y'] == ''){
                    require_once ('integral.model.php');
                    $IntegralM 	= 	new integral_model($this->db, $this->def);
                    $IntegralM	->	invtalCheck($id,'2','integral_map','设置企业地图');
                }

                $updata['x']	=	(float)$data['xvalue'];
                $updata['y']	=	(float)$data['yvalue'];

                $return['id']	=	$this -> update_once('company', $updata, array('uid'=>$id));
                if($return['id']){
					require_once ('log.model.php');
                    $logM 		= 	new log_model($this->db, $this->def);

					$logM		->	member_log('设置企业地图',15);//会员日志

                    $this		->	update_once('company_job',array('x'=>$updata['x'],'y'=>$updata['y']),array('uid'=>$id));

					$return['msg']		=	'地图设置成功！';

					if($data['type']=='wap'){
						$return['url']	=	'index.php?c=set';
					}else{
						$return['url']	=	'index.php?c=map';
					}

                    $return['cod']		=	9;
                }else{

					$return['msg']		=	'地图设置失败！';

				}
            }else{

				$return['msg']		=	'请设置企业地图！';

			}
        }else{

			$return['msg']		=	'系统繁忙！';
		}

        return $return;

    }

	/**
      * 解除绑定 moblie、email、sina、QQ、wx
      * @param 表：member、company、resume、lt_info
      * @param 功能说明：更新company、resume、lt_info，更新member绑定状态
      * @param 参数：data:解除参数、id：uid
      *
     */
	public	function delBd($id, $data = array()){

		$return         =       array();

		if(!empty($id)) {

			if($data['type']=='moblie'){

			    $nid    =   $this -> update_once('resume',array('moblie_status'=>'0'),array('uid'=>$id));
				//	解除手机绑定，简历重置未审核
			    $nid    =   $this -> update_once('resume_expect',array('state'=>'0'),array('uid'=>$id));	

			    $nid    =   $this -> update_once('company', array('moblie_status'=>'0'), array('uid'=>$id));
				$nid    =   $this -> update_once('member',array('moblie_status'=>'0'),array('uid'=>$id));

			    if ($nid) {
			        $this -> addMemberLog($id, $data['usertype'], "解除手机绑定", 12 );
			    }
			}

			if($data['type']=='email'){

		        $nid    =   $this -> update_once('resume',array('email_status'=>'0'),array('uid'=>$id));
		        $nid    =   $this -> update_once('company', array('email_status'=>'0'), array('uid'=>$id));
				$nid    =   $this -> update_once('member',array('email_status'=>'0'),array('uid'=>$id));

		        if ($nid) {
		            $this -> addMemberLog($id, $data['usertype'], "解除邮箱绑定", 12 );
		        }
			}

			if($data['type']=='qqid'){

			    $nid	=	$this -> update_once('member',array('qqid'=>'','qqunionid'=>''),array('uid'=>$id));

			    if ($nid) {
			        $this -> addMemberLog($id, $data['usertype'], "解除QQ绑定", 12 );
			    }
 			}

			if($data['type']=='sinaid'){

				$nid	=	$this -> update_once('member',array('sinaid'=>''),array('uid'=>$id));

				if($nid){
				    $this -> addMemberLog($id, $data['usertype'], "解除新浪微博绑定", 12 );
				}

			}

			if($data['type']=='wxid'){

				$nid	=	$this -> update_once('member', array('wxid'=>'', 'wxopenid'=>'', 'unionid'=>''), array('uid'=>$id));

				if($nid){
				    $this -> addMemberLog($id, $data['usertype'], "解除微信绑定", 12 );
				}
			}

			if($nid){

				$return['msg']		=	'解除绑定成功';

				$return['errcode']	=	'9' ;

			}else{

				$return['msg']		=	'解除绑定失败';

				$return['errcode']	=	'8' ;

			}

		}else{

			$return['msg']		=	'系统繁忙';

			$return['errcode']	=	'8' ;

		}

		return $return;
	}


    /**
      * @desc 添加、更新企业环境
      * @param 表：company_show
      * @param 参数：data:参数、data['utype']上传类型wap\pc
      *
      */
	function setShow($data=array()){

		$return		=	array('errcode'=>8,'url'=>'index.php?c=show');

		if(!empty($data)){

			if ($data['file']['tmp_name'] || $data['base']){

                $upArr   =  array(
                    'file'     =>  $data['file'],
                    'dir'      =>  'show',
                    'base'     =>  $data['base'],
                    'preview'  =>  $data['preview']
                );

                $result  =  $this -> upload($upArr);

                if (!empty($result['msg'])){

                    $return['msg']      =  $result['msg'];
                    $return['errcode']  =  '8';

                    return $return;

                }elseif (!empty($result['picurl'])){

                    $picurl  =  $result['picurl'];
                }
            }

			$datashow=array(
			    'uid'	=>	$data['uid'],
			    'ctime'	=>	time()
			);
			if($data['sort']){
			    $datashow['sort']	=	$data['sort'];
			}
			if($data['id']){
			    $datashow['title']	=	$data['title'];

			    if (isset($picurl)){
			        $datashow['picurl']	=	$picurl;
			    }
			    $datashow['status'] =   $data['r_status']==0?1:$this->config['com_show_status'];
			    $nid = $this->upCompanyShow(intval($data['id']),$datashow);

			    if($nid){
			        $return['msg']	=	'更新成功！';
			        $return['errcode']	=	9;
			    }else{
			        $return['msg']	=	'更新失败！';
			    }

			}else{
			    if(!$picurl){
			        $return['msg']		=	'请上传企业环境！';
			    }else{
			        if($data['title']){

			            $datashow['title']	=	$data['title'];

			        }else{

			            $datashow['title']	=	$data['file']['name'];
			        }

			        $datashow['picurl']	=	$picurl;
			        $datashow['status'] =   $data['r_status']==0?1:$this->config['com_show_status'];

					$member				=	$this->select_once('member',array('uid'=>$data['uid']),'`did`');
					$datashow['did']	=	$member['did'];

			        $id 				= 	$this->addCompanyShow($datashow);
			        if($id){

			            $this->addMemberLog($data['uid'], 2, '添加环境展示', 16,1);

			            $return['msg']	=	'上传成功！';

			            $return['id']	=	$id ;

			            $return['errcode']	=	9;
			        }else{
			            $return['msg']	=	'上传失败！';
			        }
			    }
			}
		}else{

			$return['msg']		=	'系统繁忙';
		}
		return $return;
	}

    /**
      * @desc 处理单个图片上传
      * @param file/需上传文件; dir/上传目录; type/上传图片类型; base/需上传base64; preview/pc预览即上传
     */
    private function upload($data = array('file'=>null,'dir'=>null,'type'=>null,'base'=>null,'preview'=>null)){

        include_once('upload.model.php');

        $UploadM  =  new upload_model($this->db, $this->def);

        $upArr  =  array(
            'file'     =>  $data['file'],
            'dir'      =>  $data['dir'],
            'type'     =>  $data['type']
        );
        if (!empty($data['base'])){
            $upArr['base'] = $data['base'];
        }
        if (!empty($data['preview'])){
            $upArr['preview'] = $data['preview'];
        }
        $return  =  $UploadM -> newUpload($upArr);
        return $return;
    }
    /**
     * 后台企业logo审核
     * @param string $id    格式：单个，如1 ; 批量，如1,2,3
     * @param array $data
     */
    public function statusLogo($uid,$data = array()){

        $uid  =  @explode(',',$uid);

        foreach($uid as $v){

            if($v){

                $uids[]  =  $v;
            }
        }
        if (!empty($uids)){

            $uidstr  =  pylode(',', $uids);

            $post    =  $data['post'];

            if ($post['logo_status'] == 2){
                //审核不通过删除图片
                $post['logo']	='';
            }

            $result  =  $this -> update_once('company', $post, array('uid'=>array('in',$uidstr)));
            if ($result){

                if ($post['logo_status'] == 2){

                    // 审核不通过，相关表头像删除
                    $this -> update_once('company_job',array('com_logo'=>''),array('uid'=>array('in',$uidstr)));
                    $this -> update_once('answer',array('pic'=>''),array('uid'=>array('in',$uidstr)));
                    $this -> update_once('question',array('pic'=>''),array('uid'=>array('in',$uidstr)));

                    $statusInfo  =  '您的LOGO';

                    foreach ($uids as $k=>$v){

                        /* 处理审核信息 */
                        if($post['logo_statusbody']){

                            $statusInfo  .=  ' , 因为'.$post['logo_statusbody'].' , ';
                        }

                        $statusInfo  .=  '已被管理员删除';

                        $msg[$v]  =  $statusInfo;
                    }

                    //发送系统通知
                    include_once('sysmsg.model.php');

                    $sysmsgM  =  new sysmsg_model($this->db, $this->def);

                    $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2,'content'=>$msg));

                }else{
                    // 审核通过，修改相关表logo
                    $company  =  $this->select_all('company',array('uid'=>array('in',$uidstr)),'`uid`,`logo`');
                    foreach ($company as $k=>$v){

                        $newlogo[$v['uid']]   =  $v['logo'];
                    }

                    $this -> update_once('company_job',array('com_logo'=>array('CASE','uid',$newlogo)),array('uid'=>array('in',$uidstr)));
                    $this -> update_once('answer',array('pic'=>array('CASE','uid',$newlogo)),array('uid'=>array('in',$uidstr)));
                    $this -> update_once('question',array('pic'=>array('CASE','uid',$newlogo)),array('uid'=>array('in',$uidstr)));
                }

                $return['msg']      =  'LOGO审核(ID:'.$uidstr.')设置成功';
                $return['errcode']  =  '9';
            }else{
                $return['msg']      =  'LOGO审核(ID:'.$uidstr.')设置失败';
                $return['errcode']  =  '8';
            }
        }else{
            $return['msg']      =  '请选择要审核的LOGO';
            $return['errcode']  =  '8';
        }
        return $return;
    }
    public function statusShow($id,$data=array()){

        $id  =  @explode(',',$id);

	    foreach($id as $v){

	        if($v){

	            $ids[]  =  $v;
	        }
	    }
	    if (!empty($ids)){

	        $idstr  =  pylode(',', $ids);

	        $shows	=	$this	->	getCompanyShowList(array('id'=>array('in',$idstr)),array('field'=>'`uid`,`title`'));

	        $post    =  $data['post'];

	        if ($post['status'] == 2){
                //审核不通过删除

                $result	=	$this	->	delete_all('company_show', array('id'=>array('in',$idstr)),'');

		    }elseif($post['status'] == 0){

                $result	=	$this	->	update_once('company_show',$post, array('id'=>array('in',$idstr)),'');

		    }

	        if ($result){

	            if ($post['status'] == 0 || $post['status'] == 2){

	                foreach ($shows as $k=>$v){
						$uids[]				=	$v['uid'];
						/* 处理审核信息 */
						if ($post['status'] == 2){

							$statusInfo		=	'您的企业环境('.$v['title'].')审核未通过';

							if($post['statusbody']){

								$statusInfo  .=  ', 原因：'.$post['statusbody'];

							}

							$msg[$v['uid']][]  =  $statusInfo;

						}elseif($post['status'] == 0){

							$msg[$v['uid']][]  =  '您的企业环境('.$v['title'].')已审核通过';

						}
	                }
					//发送系统通知
	                include_once('sysmsg.model.php');

	                $sysmsgM  =  new sysmsg_model($this->db, $this->def);

	                $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));

	            }

	            $return['msg']      =  '企业环境审核(ID:'.$idstr.')设置成功';
	            $return['errcode']  =  '9';
	        }else{
	            $return['msg']      =  '企业环境审核(ID:'.$idstr.')设置失败';
	            $return['errcode']  =  '8';
	        }
	    }else{
	        $return['msg']      =  '请选择要审核的企业环境';
            $return['errcode']  =  '8';
        }

        return $return;
    }

    public function statusBanner($id,$data=array()){

        $id  =  @explode(',',$id);

	    foreach($id as $v){

	        if($v){

	            $ids[]  =  $v;
	        }
	    }
	    if (!empty($ids)){

	        $idstr  =  pylode(',', $ids);

	        $shows	=	$this	->	getBannerList(array('id'=>array('in',$idstr)),array('field'=>'`uid`'));

	        $post    =  $data['post'];

	        if ($post['status'] == 2){
                //审核不通过删除

                $result	=	$this	->	delete_all('banner', array('id'=>array('in',$idstr)),'');

		    }elseif($post['status'] == 0){

                $result	=	$this	->	update_once('banner',$post, array('id'=>array('in',$idstr)),'');

		    }

	        if ($result){

	            if ($post['status'] == 0 || $post['status'] == 2){

	                foreach ($shows as $k=>$v){
						$uids[]				=	$v['uid'];
						/* 处理审核信息 */
						if ($post['status'] == 2){

							$statusInfo		=	'您的企业横幅审核未通过';

							if($post['statusbody']){

								$statusInfo  .=  ', 原因：'.$post['statusbody'];

							}

							$msg[$v['uid']][]  =  $statusInfo;

						}elseif($post['status'] == 0){

							$msg[$v['uid']][]  =  '您的企业横幅已审核通过';

						}
	                }
					//发送系统通知
	                include_once('sysmsg.model.php');

	                $sysmsgM  =  new sysmsg_model($this->db, $this->def);

	                $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));

	            }

	            $return['msg']      =  '企业横幅审核(ID:'.$idstr.')设置成功';
	            $return['errcode']  =  '9';
	        }else{
	            $return['msg']      =  '企业横幅审核(ID:'.$idstr.')设置失败';
	            $return['errcode']  =  '8';
	        }
	    }else{
	        $return['msg']      =  '请选择要审核的企业横幅';
            $return['errcode']  =  '8';
        }

        return $return;

    }

	function status($data=array()){
		$uid 		=	$data['uid'];
		$type 		=	$data['type'];
	    $company 	= 	$this->getInfo($uid);
	    if($this->config['com_enforce_mobilecert']==1 && $company['moblie_status']!=1){
			$return['msg']		=	'请先认证手机号';
			$return['errcode']	=	10;
			return $return;
	    }
	    if($this->config['com_enforce_licensecert']==1 && $company['yyzz_status']!=1){
			$return['msg']		=	'请先认证营业执照';
			$return['errcode']	=	11;
			return $return;
	    }
	    if($this->config['com_enforce_emailcert']==1 && $company['email_status']!=1){
			$return['msg']		=	'请先认证邮箱';
			$return['errcode']	=	12;
			return $return;
	    }

	    $result 	= 	$this->comVipDayActionCheck($type,$uid);

		$return['result']	=	$result;

		return $return;
	}

	/**
	 * @desc 企业名称、手机号码是否使用排查
	 * @param int $uid
	 * @param array ($typeStr|$checkStr)
	 */
	function getCheckUsed($uid, $data = array()){

	    if (!empty($uid) && !empty($data)) {

	        $typeStr   =   $data['typeStr'];
	        $checkStr  =   $data['checkStr'];

	        if ($typeStr == 'name') {

	            $com   =   $this->select_once('company', array('name' => $checkStr, 'uid' => array('<>', $uid)));
	            $return['errcode'] =   $com ? 8 : 9;
	        }else if ($typeStr == 'linktel') {

	            $member    =   $this->select_once('member', array('moblie' => $checkStr, 'uid' => array('<>', $uid)));
	            $return['errcode'] =   $member ? 8 : 9;
	        }

	        $return['type']    =   $typeStr;

	        return $return;
	    }
	}

	/**
	 * @desc 都需平台同步回源站数据，源站只有账号，没有身份信息表数据情况
	 */
	public function addComData($uid, $data =	array('comData' => array(), 'sdata' => array())){

		$comData	=	$data['comData'];
		$sData		=	$data['sdata'];

		$comNum		=	$this->select_num('company', array('uid' => $uid));
		$comSNum	=	$this->select_num('company_statis', array('uid' => $uid));

		$comData['uid']			=		$uid;
		$comData['rating']		=		$sData['rating'];
		$comData['r_status']	=		$this->config['com_status'];

		if($comNum == 0){
			$this->insert_into('company', $comData);
		}
		if($comSNum == 0){
			require_once ('rating.model.php');
			$ratingM		=   new rating_model($this->db, $this->def);
			$vData			=	$ratingM->fetchRatingInfo(array('uid'=>$uid));
			$vData['uid']	=	$uid;
			$vData['did']	=	$this->config['did'];
			$this->insert_into('company_statis', $vData);
		}
	}

    //微信发布工具
    public function Getcompany($where=array(),$data=array())
    {
        $select        = $data['field'] ? $data['field'] : '*';
        $jWhere = array();
        if(isset($data['jWhere'])){
            $jWhere = $data['jWhere'];
        }

        $lists = $this->select_all('company',$where, $select);
        foreach($lists as $k=>$v){
            $com = $this->getInfo($v['uid']);
            $jWhere['uid']      =   $v['uid'];
            $jWhere['r_status'] =   1;
            $jWhere['status']   =   0;
            $jWhere['state']    =   1;
            // var_dump($jWhere);exit;
            $row                =   $this->getJobList($jWhere);
            $lists[$k]['row'] = $row['list'];
            $lists[$k]['com'] = $com;
        }
        return $lists;
    }

}
?>