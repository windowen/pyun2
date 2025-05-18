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
class part_model extends model
{

    /**
     * 调用缓存类，提取缓存数据
     */
    private function getClass($options)
    {
        if (! empty($options)) {

            include_once ('cache.model.php');

            $cacheM     =   new cache_model($this->def, $this->db);

            $cache      =   $cacheM->GetCache($options);

            return $cache;
        }
    }

    private function addAdminLog($content, $opera = '', $type = '', $opera_id = '')
    {
        require_once ('log.model.php');

        $LogM   =   new log_model($this->db, $this->def);

        return $LogM -> addAdminLog($content, $opera = '', $type = '', $opera_id = '');
    }

    /**
     * 引用log类，添加用户日志
     */
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '')
    {
        require_once ('log.model.php');

        $LogM   = new log_model($this->db, $this->def);

        return $LogM -> addMemberLog($uid, $usertype, $content, $opera, $type);
    }

    // 获取账号信息
    private function getUserList($whereData, $data = array())
    {
        require_once ('userinfo.model.php');

        $UserinfoM  =   new userinfo_model($this->db, $this->def);

        return $UserinfoM -> getList($whereData, $data);
    }

    // 获取个人基本信息
    private function getResumeList($whereData, $data = array())
    {
        require_once ('resume.model.php');

        $resumeM    =   new resume_model($this->db, $this->def);

        return $resumeM -> getResumeList($whereData, $data);
    }

    /**
     * @desc    兼职详情，单条查询
     *
     * @param   int $id:职位id
     * @param   array $data：自定义查询数组（eg:查询条件数据：where=>array，参数处理条件：joblen=>'10'）
     */
    public function getInfo($where = array(), $data = array('utype'=>''))
    {


        if (!empty($where)) {

            $select     =   $data['field'] ? $data['field'] : '*';
 
            $List       =   array();
             
            $Info       =   $this -> select_once('partjob', $where, $select);
             
            $cache      =   $this -> getClass(array('part', 'city'));

            if ($data['cache']) {
                $List['cache']  =   $cache;
            }
            if (is_array($Info)) {
                
                if ($Info['sex']) {
                    $Info['sex_n']          =   $cache['part_sex'][$Info['sex']];
                }
                if ($Info['worktime']) {
                    $Info['worktime_n']     =   @explode(',', $Info['worktime']);
                }
                if ($Info['provinceid']) {
                    $Info['cityid_one']     =   $cache['city_name'][$Info['provinceid']];
                }
                if ($Info['cityid']) {
                    $Info['cityid_two']     =   $cache['city_name'][$Info['cityid']];
                }
                if ($Info['three_cityid']) {
                    $Info['cityid_three']   =   $cache['city_name'][$Info['three_cityid']];
                }
                if ($Info['cityid_one'] || $Info['cityid_two'] || $Info['cityid_three']) {

                    $Info['cityname']       =   $Info['cityid_one'];
                    if ($Info['cityid_two']) {
                        $Info['cityname']   .=  '-'.$Info['cityid_two'];
                    }
                    if ($Info['cityid_three']) {
                        $Info['cityname']   .=  '-'.$Info['cityid_three'];
                    }
                }
                if ($Info['salary_type']) {
                    $Info['salary_type_n']  =   $cache['partclass_name'][$Info['salary_type']];
                }
                if ($Info['type']) {
                    $Info['type_n']         =   $cache['partclass_name'][$Info['type']];
                }
                if ($Info['billing_cycle']) {
                    $Info['billing_cycle_n']=   $cache['partclass_name'][$Info['billing_cycle']];
                }
                if ($Info['linktel']) {
                    $Info['linktel_n']      =   substr_replace($Info['linktel'], '*****', 3, 5);
                }
                if ($Info['lastupdate']) {
                    $Info['lastupdate_n']   =   date('Y-m-d',$Info['lastupdate']);
                }

                if($Info['sdate']){
                    $Info['sdate_n']        =   date('Y-m-d',$Info['sdate']);
                }
                if(!empty($Info['edate'])){
                    $Info['edate_n']        =   date('Y-m-d',$Info['edate']);
                }else{
                    $Info['edate_n']        = '长期招聘';
                }
                
                if (!empty($data['com'])) { // 相关企业信息
                    
                    require_once ('company.model.php');
                    $companyM       =   new company_model($this->db, $this->def);
                    $comInfo        =   $companyM->getInfo($Info['uid'], array('logo' => 1,'field' => '`uid`,`logo`,`name`,`shortname`,`yyzz_status`,`hy`,`pr`,`provinceid`','short' => 1));
                    $List['com']    =   $comInfo;
                }
            }
            
            $List['info']           =   $Info;

            return $List;
        }
    }

    /**
     * @desc    添加、修改职位数据，表单提交
     * @param   array $postData
     * @return  array $return
     */
    public function upPartInfo($postData = array('utype'=>'')){
        if (!empty($postData)) {

            $id         =   intval($postData['id']);

            $uid    =   intval($postData['uid']);
			$spid	=   intval($postData['spid']);
			$usertype    =   intval($postData['usertype']);
			if($uid){
				$getWhere = array('uid'=>$uid,'id' => $id);
			}else{
				$getWhere = array('id' => $id);
			}
            if ($uid) {
                
                $company        =   $this -> select_once('company', array('uid' => $uid), '`name`,`did`,`uid`');
            } elseif ($postData['id']) {
                
                $partjob        =   $this -> getInfo($getWhere, array('field' => '`uid`'));
                
                $company        =   $this -> select_once('company', array('uid' => $partjob['info']['uid']), '`name`,`did`,`uid`');
            }
            if ($postData['utype'] == 'user') {
                
                if($postData['state'] != 0 && $usertype == 2){
 
                    if($postData['r_status'] != 1){
                        
                        $postData['state']  =   0;
                    }else{
                        $postData['state']  =   $this->config['com_partjob_status'];
                    }
                }else{
                    if($postData['r_status']==1){
                        $postData['state']  =	$this->config['com_partjob_status'];
                    }else{
                        $postData['state']  =   0;
                    }
                   
                }
            }
            
            $data               = array(
                'name'          =>  trim($postData['name']),
                'type'          =>  intval($postData['type']),
                'sdate'         =>  strtotime($postData['sdate']),
                'edate'         =>  $postData['edate'],
                'worktime'      =>  pylode(',', $postData['worktime']),
                'number'        =>  intval($postData['number']),
                'sex'           =>  intval($postData['sex']),
                'salary'        =>  intval($postData['salary']),
                'salary_type'   =>  intval($postData['salary_type']),
                'billing_cycle' =>  intval($postData['billing_cycle']),
                'provinceid'    =>  intval($postData['provinceid']),
                'cityid'        =>  intval($postData['cityid']),
                'three_cityid'  =>  intval($postData['three_cityid']),
                'address'       =>  trim($postData['address']),
                'r_status'       =>  trim($postData['r_status']),
                'x'             =>  trim($postData['x']),
                'y'             =>  trim($postData['y']),
                'content'       =>  str_replace(array('&amp;', 'background-color:#ffffff', 'background-color:#fff', 'white-space:nowrap;'), array('&', 'background-color:', 'background-color:', 'white-space:'), $postData['content']),
                'linkman'       =>  trim($postData['linkman']),
                'linktel'       =>  $postData['linktel'],
                'state'         =>  intval($postData['state']),
                'lastupdate'    =>  time(),
                'uid'           =>  $company['uid'],
                'com_name'      =>  $company['name'],
                'did'           =>  $company['did']
            );

            if (!empty($id)) {

                $nid            =   $this -> update_once('partjob', $data, $getWhere);
                $return['msg']  =   '兼职职位修改';
                $type           =   '2';
                
            } else {
                
                if ($postData['utype'] == 'user') {  // 企业会员中心
                    
                    require_once ('statis.model.php');
                    $statisM        =   new statis_model($this->db, $this->def);

                    $suid           =   !empty($spid) ? $spid : $uid;
                    $return         =   $statisM -> getCom(array('uid' => $suid, 'usertype' => $usertype));
                }
                
                $data['addtime']    =   time();

                $nid                =   $this -> insert_into('partjob', $data);
                
                if ($nid) {
                    
                    require_once ('warning.model.php');
                    $warningM   =   new warning_model($this->db, $this->def);
                    $warningM -> warning(1, $uid);//预警提醒
                }
                $return['msg']      =   '兼职职位添加';
                $type               =   '1';
            }
            
            require_once ('log.model.php');
            $LogM   =   new log_model($this->db, $this->def);
            
            if ($nid) {
                
                if ($postData['utype']  ==  'com') {
                    
                    $LogM -> addMemberLog($uid, $usertype, $return['msg']."《".$postData['name']."》", 9, $type);
                }
                
                $return['errcode'] = 9;
                if (intval($postData['state']) == 0) {

                    
                    $return['msg']  =    $return['msg'].'成功，等待审核！';
                } else {
                    
                    $return['msg']  =    $return['msg'].'成功！';
                }
            } else {
                
                
                $return['errcode']  =   8;
                $return['msg']      =   $return['msg'] . '失败！';
            }
            return $return;
        }
    }

    /**
     * 更新数据
     *
     * @param array $data
     *            ： 更新数据
     * @param array $where
     *            ： 自定义查询
     */
    public function upInfo($data = array(), $where = array())
    {
        if (! empty($data) && ! empty($where)) {

            $nid    =   $this -> update_once('partjob', $data, $where);

            return $nid;
        }
    }

    /**
     * 获取兼职列表
     *
     * @param array $whereData:查询条件
     * @param array $data:自定义处理数组
     */
    public function getList($whereData = array(), $data = array()){
        
        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this -> select_all('partjob', $whereData, $select);

        $cache  =   $this -> getClass(array('part', 'city'));

        $List   =   $this -> getDataList($List, $cache);

        return $List;
    }

    /* 兼职列表查询数据完善 */
    private function getDataList($List, $cache){

        $beginToday     =   strtotime('today');//今天开始时间戳
			
		$beginYesterday =   strtotime('yesterday');//昨天开始时间戳
		
		$week           =   strtotime("-1 week",time());//一周内时间戳
        foreach ($List as $key => $value) {
            $comuids[]      =       $value['uid'];
        }
        require_once ('company.model.php');
        $companyM           =       new company_model($this->db, $this->def);
        $coms               =       $companyM->getChCompanyList(array('uid'=>array('in',pylode(',',$comuids))),array('field'=>'`uid`,`shortname`'));

        $shortname          =       array();
        foreach($coms as $ck=>$cv){

            $shortname[$cv['uid']]  =   $cv['shortname'];

        }

        foreach ($List as $k => $v) {

            $List[$k]['com_name']                =   $shortname[$v['uid']] ? $shortname[$v['uid']] : $v['com_name'];
			$List[$k]['com_name_n']				=   mb_substr($shortname[$v['uid']] ? $shortname[$v['uid']] : $v['com_name'],0,15,"utf-8");

            if ($v['salary_type']) {
                $List[$k]['salary_type_n']      =   $cache['partclass_name'][$v['salary_type']];
            }
            if ($v['billing_cycle']) {
                $List[$k]['billing_cycle_n']    =   $cache['partclass_name'][$v['billing_cycle']];
            }
            if ($v['type']) {
                $List[$k]['type_n']             =   $cache['partclass_name'][$v['type']];
            }
            if ($v['provinceid']) {
                $List[$k]['city_one_n']         =   $cache['city_name'][$v['provinceid']];
            }
            if ($v['cityid']) {
                $List[$k]['city_two_n']         =   $cache['city_name'][$v['cityid']];
            }
            if ($v['three_cityid']) {
                $List[$k]['city_three_n']       =   $cache['city_name'][$v['three_cityid']];
            }
            if ($v['edate'] == 0) {

                $List[$k]['edatetxt']           =   '长期招聘';
            } elseif ($v['edate'] < time()) {

                $List[$k]['edatetxt']           =   '<font color="red">已到期</font>';
            } elseif ($v['edate'] < (time() + 3 * 86400)) {

                $List[$k]['edatetxt']           =   '<font color="blue">3天内到期</font>';
            } elseif ($v['edate'] < (time() + 7 * 86400)) {

                $List[$k]['edatetxt']           =   '<font color="blue">7天内到期</font>';
            } else {

                $List[$k]['edatetxt']           =   date('Y-m-d', $v['edate']);
            }

            if ($v['rec_time'] > time()) {

                $List[$k]['rec_day']            =   ceil(($v['rec_time'] - time()) / 86400);
            } else {

                $List[$k]['rec_day']            =   '0';
            }

            if ($v['rec_time'] > 1000) {

                $List[$k]['recdate']            =   date('Y-m-d', $v['rec_time']);
            }

            if ($v['lastupdate']>$week && $v['lastupdate']<$beginYesterday){
                
                $List[$k]['lastupdate_n']='一周内';
            }elseif($v['lastupdate']>$beginYesterday && $v['lastupdate']<$beginToday){
                
                $List[$k]['lastupdate_n']='昨天';
            }elseif($v['lastupdate']>$beginToday){
                
                $List[$k]['lastupdate_n']='今天';
            }else{
                
                $List[$k]['lastupdate_n']=date("Y-m-d",$v['lastupdate']);
            }

            $sqNum  =   $this -> getPartSqNum(array('jobid' => $v['id']));

            $List[$k]['applynum']               =   $sqNum ? $sqNum : '0';
        }

        return $List;
    }

    /**
     * @desc    删除兼职操作
     * @param   $id
     * @param   array $data
     * @return  array $return
     */
    public function delPart($id, $data = array('utype'=>''))
    {
        if (!empty($id)) {

            $return     =   array();

            if (is_array($id)) {

                $ids    =   $id;

                $return['layertype'] = 1;
            } else {

                $ids    =   @explode(',', $id);
            }

            $id         =   pylode(',', $ids);

			if($data['utype'] != 'admin'){
				
				$delWhere	=	array('id' => array('in', $id),'uid'=>$data['uid']);

			}else{
				
				$delWhere	=	array('id' => array('in', $id));
			
			}
            $partList   =   $this->getList($delWhere, array('field' => 'id,uid,name'));
			if(!empty($partList)){
				$ids = array();
				foreach($partList as $key => $value){
					
					$ids[] = $value['id'];
				
				}
				$id         =   pylode(',', $ids);
			}else{
				$id			=	0;
			}
			 
            $return['id']   =   $this->delete_all('partjob', array('id' => array('in', $id)), '');

            if ($return['id']) {
                
                $this -> delete_all('part_collect', array('jobid' => array('in', $id)), '');
                
                $this -> delete_all('part_apply', array('jobid' => array('in', $id)), '');
                
                if (!empty($data['uid']) && !empty($data['usertype'])) {
                    
                    $this -> addMemberLog($data['uid'], $data['usertype'], '删除兼职招聘', 9 , 3);
                }

                if($data['utype'] == 'admin'){
                    
                    $msg    =   $uids   =   array();
                    
                    foreach ($partList as $k => $v) {
    
                        $uids[] = $v['uid'];
    
                        $msg[$v['uid']][] = '您的兼职《' . $v['name'] . '》已被管理员删除';
                    }
                    
                    include_once ('sysmsg.model.php');
                    $sysmsgM = new sysmsg_model($this->db, $this->def);
                    $sysmsgM -> addInfo(array('uid' => $uids,'usertype' => 2,'content' => $msg));
                }
                
            }

            $return['msg']      =   '兼职(ID:' . $id . ')';
            $return['errcode']  =   $return['id'] ? 9 : 8;
            $return['msg']      =   $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
        } else {

            $return['msg']      =   '请选择您要删除的兼职！';
            $return['errcode']  =   8;
        }

        return $return;
    }

    /**
     * 兼职推荐
     */
    public function addRecPart($id, $data = array())
    {
        if (! empty($id) && ! empty($data)) {

            $ids    =   @explode(',', $id);
        
            $return =   array();
            
            if (is_array($ids)) {

                // 查询职位信息，提取职位推荐时间 rec_time，uid，name
                $pList      =   $this->getList(array('id' => array('in', pylode(',', $ids))), array('field' => 'id,uid,name,rec_time'));

                if (!empty($pList)) {

                    if (intval($data['rec']) == 1) {

                        $return['id']       =   $this -> upInfo(array('rec_time' => 0), array('id' => array('in', pylode(',', $ids))));

                        $return['msg']      =   '取消兼职推荐(ID:' . $ids . ')';

                        $return['msg']      =   $return['id'] ? $return['msg'] . '成功！' : $return['msg'] . '失败！';
                        
                    } else if (intval($data['days']) > 0) {
                        
                        $gid    =   $mid    =   array();
                        
                        foreach ($pList as $v) {

                            if ($v['rec_time'] < time()) {

                                $gid[]      =   $v['id']; // 推荐日期已过期
                            } else {

                                $mid[]      =   $v['id']; // 推荐日期尚未过期
                            }
                        }

                        $time   =   intval($data['days']) * 86400;

                        if (!empty($gid)) {

                            $return['id']       =   $this -> upInfo(array('rec_time'=>(time() + $time)), array('id'=>array('in',pylode(',', $gid))));
                        }

                        if (! empty($mid)) {

                            $return['id']       =   $this -> upInfo(array('rec_time'=>array('+', $time)), array('id'=>array('in',pylode(',', $mid))));
                        }

                        $return['msg']  =   '兼职推荐(ID:' . $id . ')';
                        $return['msg']  =   $return['id'] ? $return['msg'] . '设置成功！' : $return['msg'] . '设置失败！';
                    } else {

                        $return['msg']  =   '推荐天数不能为空，请重试！';
                    }

                    if ($return['id']) {

                        $msg    =   array();
                        $uids   =   array();

                        // 提取职位uid 和职位名称
                        foreach ($pList as $v) {

                            $uids[]     =   $v['uid'];

                            if (intval($data['rec']) == 0) {

                                $msg[$v['uid']][]   =   '您的兼职<a href="parttpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已推荐';
                            } elseif (intval($data['rec']) == 1) {

                                $msg[$v['uid']][]   =   '您的兼职<a href="parttpl,'.$v['id'].'">《'.$v['name'].'》</a>被管理员取消推荐';
                            }
                        }
                        // 发送系统通知
                        include_once ('sysmsg.model.php');

                        $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                        $sysmsgM->addInfo(array('uid' => $uids,'usertype'=>2,  'content' => $msg));
                    }
                } else {

                    $return['msg']  =   '系统繁忙';
                }
            }
        }
        // 操作状态 9：成功 8:失败 配合原有提示函数
        $return['errcode']  =   $return['id'] ? 9 : 8;

        return $return;
    }

    /**
     * 兼职延期
     */
    function addPartTime($id, $data = array())
    {
        if (! empty($id) && ! empty($data)) {

            $ids        =   @explode(',', $id);

            $addtime    =   intval($data['days']) * 86400;
            
            $return     =   array();
            if (is_array($ids)) {

                $pList  =   $this -> getList(array('id' => array('in', pylode(',', $ids))), array('field' => '`id`,`state`,`edate`,`uid`,`name`'));
                
                $gid    =   $mid    =   array();
                
                foreach ($pList as $v) {

                    if ($v['edate'] != '0') {

                        if ($v['state'] == '2' || $v['edate'] < time()) {

                            $gid[]  =   $v['id'];
                        } else {

                            $mid[]  =   $v['id'];
                        }
                    }
                }

                if (! empty($gid)) {

                    $time       =   time() + $addtime;

                    $return[id] =   $this -> upInfo(array('edate' => $time), array('id' => array('in', pylode(',', $gid))));
                }

                if (! empty($mid)) {

                    $time       =   $addtime;

                    $return[id] =   $this -> upInfo(array('edate' => array('+', $addtime)), array('id' => array('in', pylode(',', $mid))));
                }

                if ($return[id]) {

                    $msg    =   $uids   =   array();

                    // 提取职位uid 和职位名称
                    foreach ($pList as $v) {

                        if ($v['edate'] != '0') {

                            if ($v['state'] == '2' || $v['edate'] < time()) {

                                $uids[] = $v['uid'];
                            } else {

                                $uids[] = $v['uid'];
                            }
                        }

                        $msg[$v['uid']][]   =   '管理员延期了您的兼职<a href="parttpl,'.$v['id'].'">《' . $v['name'] . '》</a>招聘';
                    }
                    // 发送系统通知
                    include_once ('sysmsg.model.php');

                    $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                    $sysmsgM -> addInfo(array('uid' => $uids,'usertype'=>2,  'content' => $msg));
                }

                $return['msg']  =   '兼职职位(ID:' . $id . ')延期';
                $return['msg']  =   $return['id'] ? $return['msg'] . '设置成功！' : $return['msg'] . '设置失败！';
            } else {

                $return['msg']  =   '系统繁忙！';
            }

            $return['errcode']  =   $return['id'] ? 9 : 8;

            return $return;
        }
    }

    /**
     * 兼职报名查询数目
     */
    function getPartSqNum($where = array())
    {
        $sqNum = $this->select_num('part_apply', $where);

        return $sqNum;
    }

    /**
     * 兼职报名查询，单条查询
     */
    function getPartSqInfo($where = array(), $data = array())
    {
        $select = $data['field'] ? $data['field'] : '*';

        $sqInfo = $this->select_once('part_apply', $where, $select);

        return $sqInfo;
    }

    /**
     * 兼职报名查询，多条查询
     */
    function getPartSqList($whereData = array(), $data = array())
    {
        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this -> select_all('part_apply', $whereData, $select);

        if (! empty($List)) {

            $List   =   $this -> subPartApplyInfo($List);
        }

        return $List;
    }

    // 申请兼职
    public function addPartSq($data = array())
    {
        if ($data['usertype'] != 1) {
            
            $return =   array('msg' => '只有个人用户才能申请报名！', 'status' => 8);
            return  $return;
        } else {

            $job    =   $this -> getInfo(array('id' => intval($data['jobid'])), array('field' => '`id`,`uid`,`edate`,`name`'));
            $job    =   $job['info'];
            if ($this->config['com_resume_partapply'] == 1) { // 拥有简历才能报名
                $num    =   $this -> select_num('resume_expect', array('uid' => $data['uid']));
                if ($num < 1) {
                    $return =   array('msg' => '拥有简历才可以报名兼职！', 'status' => 8);
                    return  $return;
                }
                $expectnum    =   $this -> select_num('resume_expect', array('uid' => $data['uid'],'state'=>1));
                 if($expectnum<1){
                    $return =   array('msg' => '您的简历尚未完成审核，请联系管理员加快审核进度！', 'status' => 8);
                    return  $return;
                 }
               
                
            }
            
            if ($job['edate'] < time() && $job['edate'] != 0) {
                
                $return =   array('msg' => '兼职已过期无法报名！', 'status' => 8);
                return  $return;
            }
            if ($return['msg'] == '') {
                
                $row    =   $this -> getPartSqInfo(array('uid' => $data['uid'],'jobid' => $job['id']));
                
                if (! empty($row)) {
                    
                    $return =   array('msg' => '您已经报名过该兼职！', 'status' => 8);
                    return  $return;
                } else {
                    $pdata          =   array();
                    $pdata['uid']   =   $data['uid'];
                    $pdata['jobid'] =   $job['id'];
                    $pdata['comid'] =   $job['uid'];
                    $pdata['ctime'] =   time();
                    
                    $nid	=	$this -> insert_into('part_apply', $pdata);

					$port	=	$data['port'];
                    // 发送通知
                    if ($this->config['sy_email_set'] == 1) {
                        require_once ('userinfo.model.php');
                        $userinfoM   =   new userinfo_model($this->db, $this->def);
                        $user       =   $userinfoM -> getInfo(array('uid' => $data['uid']), array('usertype' => 1));
                        $fdata      =   $userinfoM -> getUserInfo(array('uid' => $job['uid']), array('usertype' => 2, 'field' => '`name`'));
                        
                        $data               =   array();
                        $data['type']       =   'partapply';
                        $data['name']       =   $fdata['name'];
                        $data['uid']        =   $job['uid'];
                        $data['username']   =   $user['username'];
                        $data['email']      =   $user['email'];
                        $data['moblie']     =   $user['moblie'];
                        $data['jobname']    =   $job['name'];
                        
                        require_once ('notice.model.php');
                        $noticeM    =   new notice_model($this->db, $this->def);
                        $noticeM -> sendEmailType($data);
						$data['port']		=	$port;
                        $noticeM -> sendSMSType($data);
                    }
                    
                   
                    
                    include_once ('sysmsg.model.php');
                    $sysmsgM    =   new sysmsg_model($this->db, $this->def);
                    $sysmsgM -> addInfo(array(
                        'uid' => $job['uid'],
                        'usertype'=>2,
                        'content' => '兼职：<a href="parttpl,'.$job['id'].'">' . $job['name'] . '</a>有新的申请'
                    ));
                    
                    $partsq   =   $this   -> getPartSqList(array('comid'=>$job['uid'],'jobid'=>$job['id'])); 
                    $commem =   $this   -> select_once('member', array('uid'=>$job['uid']), '`uid`,`wxid`');
                    

                    $return['status'] = 9;
                    $return['msg'] = '报名成功！';
                    return $return;
                }
            }
        }
        $return['msg']  =   $return['msg'];
        return $return;
    }

    /**
     * 更新兼职状态信息
     */
    function upPartSq($whereData = array(), $data = array())
    {
        $nid    =   $this -> update_once('part_apply', $data, $whereData);

        return $nid;
    }

    /**
     * 兼职报名信息内容补充
     *
     * @param array $List
     */
    private function subPartApplyInfo($List)
    {
        $uids = $pids = array();

        foreach ($List as $v) {

            $uids[] = $v['uid'];
            $pids[] = $v['jobid'];
        }

        /* 提取个人用户名 */
        $uWhere = $uData = array();
        $uWhere['uid']  =   array('in', pylode(',', $uids));
        $uData['field'] =   '`uid`,`username`';
        $userList       =   $this -> getUserList($uWhere, $uData);

        /* 提取简历ID */
        $rWhere = $rData = array();
        $rWhere['uid']  =   array('in', pylode(',', $uids));
        $rData['field'] =   '`uid`,`def_job`,`name`,`sex`,`birthday`,`edu`,`telphone`';
        $resumeList     =   $this -> getResumeList($rWhere, $rData);

        /* 查询兼职职位名称和企业名称 */
        $pWhere = $pData =  array();
        $pWhere['id']   =   array('in', pylode(',', $pids));
        $pData['field'] =   '`id`,`name`,`com_name`,`salary`,`salary_type`,`linktel`,`cityid`,`billing_cycle`,`linktel`,`linkman`';
        $jobList        =   $this->getList($pWhere, $pData);

        // 与企业里的兼职报名里的状态一致
        $StateList      =   array('1' => '未查看','2' => '已查看','3' => '已联系');

        foreach ($List as $k => $v) {

            if ($v['status']) {
                $List[$k]['status_n']   =   $StateList[$v['status']];
            }
			
            foreach ($jobList as $jv) {
                if ($v['jobid'] == $jv['id']) {
                    $List[$k]['partname']         =   $jv['name'];
                    $List[$k]['comname']          =   $jv['com_name'];
                    $List[$k]['salary']           =   $jv['salary'] . $jv['salary_type_n'];
                    $List[$k]['linktel']    	  =   $jv['linktel'];
                    $List[$k]['city']             =   $jv['city_two_n'];
					$List[$k]['billing_cycle']    =   $jv['billing_cycle_n'];
                    $List[$k]['cityname']      	  =   $jv['city_one_n'];
                    $List[$k]['linktel']      	  =   $jv['linktel'];
                    $List[$k]['linkman']      	  =   $jv['linkman'];
					$List[$k]['ctime_n']          =   date('Y-m-d',$v['ctime']);
                }
            }

            foreach ($userList as $uv) {
                if ($v['uid'] == $uv['uid']) {
                    $List[$k]['r_username'] =   $uv['username'];
                }
            }

            foreach ($resumeList as $rv) {
                if ($v['uid'] == $rv['uid']) {
                    $List[$k]['eid']        =   $rv['def_job'];
                    $List[$k]['name_n']     =   $rv['name_n'];
                    $List[$k]['sex_n']      =   $rv['sex_n'];
                    $List[$k]['age_n']      =   $rv['age_n'];
                    $List[$k]['edu_n']      =   $rv['edu_n'];
                    $List[$k]['eid']        =   $rv['def_job'];
                    $List[$k]['telphone']   =   $rv['telphone'];
                }
            }
        }
        return $List;
    }

    /**
     * 删除兼职报名记录
     *
     * @param
     *            $id
     * @param array $data
     * @return $return
     */
    function delPartApply($id = null, $data = array())
    {
        if (! empty($id)) {
            
            $return =   array();
            
            if (is_array($id)) {

                $ids = $id;

                $return['layertype'] = 1;
            } else {

                $ids = @explode(',', $id);
            }

            $id     =   pylode(',', $ids);

			
			if($data['usertype'] == '1'){
				$delWhere	=	array('uid'=>$data['uid'],'id' => array('in', $id));
			}elseif($data['usertype'] == '2'){
				$delWhere	=	array('comid'=>$data['uid'],'id' => array('in', $id));
			}else{
				$delWhere	=	array('id' => array('in', $id));
			}
 
            $return['id']   =   $this -> delete_all('part_apply', $delWhere, '');
            
            if ($data['usertype'] == 1) {

                $this->addMemberLog($data['uid'], $data['usertype'], '删除兼职报名', 6, 3);
            }
            $return['msg']      = '兼职报名记录(ID:' . $id . ')';

            $return['errcode']  =   $return['id'] ? 9 : 8;
            $return['msg']      =   $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
        } else {
            $return['msg']      =   '请选择您要删除的数据！';
            $return['errcode']  =   8;
        }

        return $return;
    }

    /**
     * 兼职报名查询，单条查询
     */
    function getPartCollectInfo($where = null, $data = array())
    {
        $select =   $data['field'] ? $data['field'] : '*';

        $Info   =   $this -> select_once('part_collect', $where, $select);

        return $Info;
    }

    /**
     * 兼职收藏列表
     */
    public function getPartCollectList($whereData = array(), $data = array())
    {
        $select =   $data['field'] ? $data['field'] : '*';

        $List   =   $this -> select_all('part_collect', $whereData, $select);

        if (! empty($List)) {
            
            foreach ($List as $v) {
                $jobids[] = $v['jobid'];
            }

            $pWhere['id']   =   array('in', pylode(',', $jobids));
            $pData['field'] =   '`id`,`name`,`com_name`,`salary`,`salary_type`,`billing_cycle`,`cityid`,`three_cityid`,`provinceid`';
            $partlist       =   $this -> getList($pWhere, $pData);

            foreach ($List as $k => $v) {
				$List[$k]['ctime_n']        =   date('Y-m-d',$v['ctime']);
                foreach ($partlist as $val) {

                    if ($v['jobid'] == $val['id']) {

                        $List[$k]['jobname']        =   $val['name'];
                        $List[$k]['com_name']       =   $val['com_name'];
                        $List[$k]['salary']         =   $val['salary'] . $val['salary_type_n'];
                        $List[$k]['billing_cycle']  =   $val['billing_cycle_n'];
                        $List[$k]['cityname']       =   $val['city_one_n'];
                        if ($val['city_two_n']) {
                            $List[$k]['cityname']   .=  $val['city_two_n'];
                        }
                    }
                }
            }
        }

        return $List;
    }
	 /**
     * 兼职收藏查询数目
     */
    function getPartcollectNum($where = array())
    {
        $scNum = $this->select_num('part_collect', $where);

        return $scNum;
    }
    // 收藏兼职
    function addPartCollect($data = array())
    {
        if ($data['usertype'] != 1) {
            
            $return =   array('msg' => '只有个人用户才能收藏！', 'status' => 8);
            return $return;
        } else {
            
            $row    =   $this -> getPartCollectInfo(array('uid' => $data['uid'],'jobid' => intval($data['jobid'])));
            
            if (! empty($row)) {
                
                $return =   array('msg' => '您已经收藏过该兼职！', 'status' => 8);
                return $return;
            } else {
                
                $data['uid']    =   $data['uid'];
                $data['jobid']  =   intval($data['jobid']);
                $data['comid']  =   intval($data['comid']);
                $data['ctime']  =   time();
                
                $nid            =   $this -> insert_into('part_collect', $data);
                
                if($nid){
                    $return =   array('msg' => '收藏成功！', 'status' => 9);
                }else{
                    $return =   array('msg' => '收藏失败！', 'status' => 8);
                }

              
                return $return;
            }
        }
    }
	
    /**
     * 删除兼职收藏记录
     *
     * @param $id
     * @param array $data
     * @return $return
     */
    function delPartCollect($id = null, $data = array())
    {
        if (! empty($id)) {
            
            $return =   array();

            if (is_array($id)) {

                $ids = $id;

                $return['layertype']    =   1;
            } else {

                $ids    =   @explode(',', $id);
            }

            $id         =   pylode(',', $ids);

			if ($data['usertype'] == 1) {

                $return['id']   =   $this -> delete_all('part_collect', array('uid'=>$data['uid'],'id' => array('in', $id)), '');
            }else{
				$return['id']   =   $this -> delete_all('part_collect', array('id' => array('in', $id)), '');
			}
            
            
            if ($data['usertype'] == 1) {

                $this->addMemberLog($data['uid'], $data['usertype'], '删除兼职收藏', 5, 3);
            }
            $return['msg']      =   '兼职记录(ID:' . $id . ')';
            $return['errcode']  =   $return['id'] ? 9 : 8;
            $return['msg']      =   $return['id'] ? $return['msg'] . '删除成功！' : $return['msg'] . '删除失败！';
            
        } else {
            $return['msg']      =   '请选择您要删除的数据！';
            $return['errcode']  =   8;
        }

        return $return;
    }
    
        // 查询职位数目
    function getpartJobNum($Where=array()){
       return $this->select_num('partjob',$Where);
    }

    /**
     * 后台审核兼职
     *
     * @param string $id (1 | 1,2,3)
     * @param array $data
     */
    public function statusPartJob($id, $upData = array())
    {
        $pids   =   @explode(',', trim($id));

        $return =   array(
            'msg'       => '非法操作！',
            'errcode'   => 8
        );
      
        if (!empty($id)) {
           
            $pidstr =   pylode(',', $pids);

            $upData =   array(

                'state'         => intval($upData['state']),
                'statusbody'    => trim($upData['statusbody'])
            );

            $result =   $this->update_once('partjob', $upData, array('id' => array( 'in',$pidstr),'r_status' =>1));
            
            if($result){
             
                if ($upData['state'] == 1 || $upData['state'] == 3) {

                    $msg    =   $uids = array();

                    $parts  =   $this -> getList(array('id' => array('in', $pidstr)), array('field' => '`id`,`uid`,`name`'));
                   
                    foreach ($parts as $v) {

                        $uids[] =   $v['uid'];
                    }

                    require_once 'notice.model.php';
                    $noticeM    =   new notice_model($this->db, $this->def);
                    
                    $member     =   $this -> getUserList(array('uid' => array('in', pylode(',', $uids))), array('field' => '`uid`,`email`,`moblie`'));
                     
                    foreach ($parts as $k => $v) {

                        if ($upData['state'] == 3) {

                            $statusInfo     = '您的兼职<a href="parttpl,'.$v['id'].'">《' . $v['name'] . '》</a>审核未通过';

                            if ($upData['statusbody']) {
                                $statusInfo .= '，原因：' . $upData['statusbody'];
                            }

                            $msg[$v['uid']][] = $statusInfo;
                        } elseif ($upData['state'] == 1) {

                            $msg[$v['uid']][] = '您的兼职<a href="parttpl,'.$v['id'].'">《' . $v['name'] . '》</a>审核通过';
                        }
                      
                        foreach($member as $mv) {
                           
                            $sendData = array();
                            if ($v['uid'] == $mv['uid']) {
                               
                                $sendData['type']       =   $upData['state'] == 3 ? 'partshwtg' : 'partshtg';
                                $sendData['uid']        =   $v['uid'];
                                $sendData['email']      =   $mv['email'];
                                $sendData['moblie']     =   $mv['moblie'];

                                $sendData['jobname']    =   $v['name'];
                                $sendData['date']       =   date('Y-m-d H:i:s');
                                $sendData['status_info']=   $upData['statusbody'];

                                // 邮箱短信通知
                                $noticeM->sendEmailType($sendData);
								$sendData['port']	=	'5';
                                $noticeM ->sendSMSType($sendData);
                                
                            }
                        } 
                    }
                  
                    // 发送系统通知
                    require_once 'sysmsg.model.php';
                    $sysmsgM    =   new sysmsg_model($this->db, $this->def);
                    $sysmsgM -> addInfo(array('uid' => $uids,'usertype'=>2, 'content' => $msg));
                }
                $partjobwhere['id']          =     array('in',$pidstr);
                $partjobnum                  =     $this->getpartJobNum($partjobwhere);
                
                if($partjobnum>1){
                  $jobtwhere['id']           =     array('in',$pidstr);
                  $jobtwhere['r_status']     =     1;
                  $jobtnum                   =     $this->getpartJobNum($jobtwhere);
                  $jobwwhere['id']           =     array('in',$pidstr);
                  $jobwwhere['r_status']     =     array('<>',1);
                  $jobwnum              =     $this->getpartJobNum($jobwwhere);
                  if($jobwnum>0){
                    $return['msg']      =  '批量审核成功'.$jobtnum.'条，失败'.$jobwnum.'条原因:企业账户未审核';
                  }else{
                    $return['msg']      =  '批量审核成功!';
                  }
                  
                  $return['errcode']  =  9;
                }else{
                  $jobwwhere['id']           =     array('in',$pidstr);
                  $jobwwhere['r_status']     =     array('<>',1);
                  $jobtnum                   =     $this->getpartJobNum($jobwwhere);
                 
                  if($jobtnum>0){
                    $return['msg']      =  '审核兼职失败，原因:企业账户未审核';
                    $return['errcode']  =  8;
    
                  }else{
                    $return['msg']      =  '审核兼职(ID:'.$idstr.')设置成功';
                    $return['errcode']  =  9;
                  }
                }
            
            } else {

                $return['msg']      =   '审核兼职(ID:' . $pidstr . ')设置失败';
                $return['errcode']  =   8;
            
            }
        } else {

            $return['msg']          =   '请选择需要审核的兼职操作！';
            $return['errcode']      =   8;
        }
        return $return;
    }
    
    /**
     * @desc 兼职审核，企业不是已审核状态，弹出同步操作状态审核
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
            
            $partArr    =   $this->getInfo(array('id' => $id), array('field' => '`id`,`uid`,`name`'));
            
            $part       =   $partArr['info'];

            $upData     =   array(
                
                'state'     =>  intval($data['state']),
                'statusbody'=>  trim($data['statusbody']),
                'lastupdate'=>  time()
            );
            
            $uid        =   $data['uid'];
            
            $result     =   $this -> update_once('partjob', $upData, array('id' => $id, 'uid' => $uid));
            
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
                $msg        =   $data['state'] == 3 ? '您的兼职<a href="parttpl,'.$id.'">《'.$part['name'].'》</a>审核未通过；原因：'.$data['statusbody'] : '您的职位<a href="parttpl,'.$id.'">《'.$part['name'].'》</a>审核通过';
                $sysmsgM -> addInfo(array('uid' => $uid,'usertype'=>2,'content'=>$msg));
                
                require_once 'notice.model.php';
                $noticeM    =   new notice_model($this->db, $this->def);
                               
                $member     =   $this -> select_once('member', array('uid' => $uid), '`uid`,`email`,`moblie`');
                $sendData   =  array();
                
                if (!empty($member)) {
                    
                    $sendData['type']           =    $data['state'] == 3 ? 'partshwtg' : 'partshtg';
                    $sendData['uid']            =    $uid;
                    $sendData['email']          =    $member['email'];
                    $sendData['moblie']         =    $member['moblie'];
                    $sendData['jobname']        =    $part['name'];
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
     * 后台刷新兼职
     */
    function refreshPartJob($setData = array())
    {
        if (! empty($setData)) {

            $this -> upInfo(array('lastupdate' => time()), array('id' => array('in', pylode(',', trim($setData['ids'])))));

            $list = $this->getList(array('id' => array('in', pylode(',', trim($setData['ids'])))), array('field' => '`uid`,`name`'));

            if ($list) {

                $uids   =   $msg = array();
                
                foreach ($list as $v) {

                    $uids[] = $v['uid'];

                    $msg[$v['uid']][] = '管理员刷新兼职<a href="parttpl,'.$v['id'].'">《' . $v['name'] . '》</a>';
                }
                include_once ('sysmsg.model.php');

                $sysmsgM    =   new sysmsg_model($this->db, $this->def);

                $sysmsgM -> addInfo(array('uid' => $uids,'usertype'=>2,  'content' => $msg));
            }

            $this->addAdminLog("兼职职位(ID" . pylode(',', trim($setData['ids'])) . "刷新成功");
        }
    }
}
?>