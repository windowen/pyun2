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

class statis_model extends model{

	/**
	 * @desc   获取账户套餐数据信息
	 * 
	 * @param int $uid
	 * @param array $data
	 * @return $statis
	 */
	function getInfo($uid, $data = array())
    {
        if (! empty($uid)) {
            
            $uid    =   intval($uid);

            $uType  =   intval($data['usertype']);

            $TBList = array(
                1 => 'member_statis',
                2 => 'company_statis'
            );

            $tb = $TBList[$uType];

            $select = $data['field'] ? $data['field'] : '*';
            $statis = $this -> select_once($tb, array('uid' => $uid), $select);
            if ($statis && is_array($statis)) {
				$statis['packfk'] 		=   sprintf('%.2f', $statis['packpay']);
                $statis['freeze_n']		=   sprintf('%.2f', $statis['freeze']);
                $statis['vip_stime_n']	=   date('Y-m-d', $statis['vip_stime']);
                $statis['vip_etime_n']	=   $statis['vip_etime'] > 0 ? date('Y-m-d', $statis['vip_etime']): '不限';
                return $statis;
            }
        }
    }
	
	/**
	 * @desc   获取账户套餐数据信息列表
	 * 
	 * @param array $whereData
	 * @param array $data
	 * @return $statis
	 */
	function getList($whereData = array(), $data = array())
    {
        if (! empty($whereData)) {

            $uType = $data['usertype'] ? intval($data['usertype']) : 2;

            $TBList = array(
                1 => 'member_statis',
                2 => 'company_statis'
                
            );

            $tb = $TBList[$uType];

            $select = $data['field'] ? $data['field'] : '*';

            $statis = $this -> select_all($tb, $whereData, $select);

            if ($statis && is_array($statis)) {
                if(in_array($uType, array(2, 3))){
                    foreach ($statis as $sk => $sv) {
                        if(isset($sv['vip_stime']) && $sv['vip_stime'] > 0){
                            $statis[$sk]['vip_stime_str']    =   date('Y-m-d', $sv['vip_stime']);
                        }
                        if($sv['vip_etime'] > 0){
                            
                            $statis[$sk]['vip_etime_str']    =   date('Y-m-d', $sv['vip_etime']);
                        }else{
                            
                            $statis[$sk]['vip_etime_str']    =   '永久';
                        }
                        if($sv['rating_type'] == 1){
                            
                            $statis[$sk]['rating_type_name'] =   '套餐模式';
                        }elseif ($sv['rating_type'] == 2) {
                            
                            $statis[$sk]['rating_type_name'] =   '时间模式';
                        }
                    }
                }

                return $statis;
            }
        }
    }
	
	/**
	 * @desc   更新账户套餐数据信息
	 * 
	 * @param array $data 更新数据
	 * @param array $whereData：uid,usertype等，其他参数需求
 	 */
	function upInfo($data = array(), $whereData = array())
    {
        if (!empty($data) && $whereData) {

            $uid        = intval($whereData['uid']);
            $uType      = intval($whereData['usertype']);
            $ratingid   = intval($data['rating']);
            
            $TBList = array(
                1 => 'member_statis',
                2 => 'company_statis'
                
            );

            $tb = $TBList[$uType];

            /* 更新职位表会员等级字段，名企数据  */
            if($uType == 2 && $ratingid){
                
                if (empty($data['rating_name']) || empty($data['vipetime'])) {
                    require_once 'rating.model.php';
                    $ratingM    =   new rating_model($this->db, $this->def);
                    
                    $ratingInfo =   $ratingM -> ratingInfo($ratingid, $uid, $this->config['package_data_acc']);
                    
                    $data['rating_name']    =   $ratingInfo['rating_name'];

                    $data['vipetime']       =   $ratingInfo['vip_etime'];
                    
                }
                $this -> update_once('company_job', array('rating' => $ratingid), array('uid' => $uid));
				$this -> update_once('company', array('rating' => $ratingid,'rating_name'=>$data['rating_name'],'vipstime'=>time(),'vipetime'=>$data['vipetime']), array('uid' => $uid));
                
            }
            
            /* 管理员修改企业，变更会员等级，记录订单数据 */
            if ($whereData['adminedit'] && $whereData['info']) {
                
                $rinfo  =   $whereData['info'];
                
                if($rinfo['time_start'] < time() && $rinfo['time_end'] > time()){
                    $price = $rinfo['yh_price'];
                }else{
                    $price = $rinfo['service_price'];
                }
                
                if($price > 0 && ($uType == 2||$uType == 3)){
                    
                    $statisInfo =   $this -> select_once($tb, array('uid' => $uid));
                    
                    if ($statisInfo['rating'] != $rinfo['id']) {
                        
                        $rinfo['usertype']	=	$uType;
                        $this -> addComOrder($rinfo, $uid, $ratingid);
                    }
                }
                
                $hData = array(
                    
                    'rating_id'     =>  $ratingid,
                    'rating'        =>  $data['name'],
                    'servide_price' =>  $price,
                    'time_start'    =>  time(),
                    'time_end'      =>  $rinfo['service_time']
                );
                
                $this -> update_once('hotjob', $hData, array('uid' => $uid));
            }

            $nid = $this -> update_once($tb, $data, array('uid'=>$uid));
            
			return $nid;
        }
    }
    
    /**
     * @desc   管理员修改会员等级，记录订单 
     * 
     * @param array $data
     * @param int $uid 
     * @param int $rating 会员等级
     */
    private function addComOrder($data = array(), $uid = null, $rating = null) {
        
        if($data['time_start'] < time() && $data['time_end'] > time()){
            
            $price  =   floatval($data['yh_price']);
        }else{
            
            $price  =   floatval($data['service_price']);
            
        }
        
        $dingdan    =   time().rand(10000,99999);
        
        $order      =   array(
            'order_id'      =>  $dingdan,
            'order_price'   =>  $price,
            'type'          =>  1,
            'order_time'    =>  time(),
            'order_state'   =>  '2',
            'order_remark'  =>  '管理员修改会员套餐',
            'uid'           =>  intval($uid),
			'usertype'      =>  $data['usertype'],
            'did'           =>  $this->config['did'],
            'rating'        =>  intval($rating),
            'order_type'    =>  'adminpay'
        );
        
        $this ->insert_into('company_order', $order);
        
    }
	
	/**
	 * @desc   新增账户，添加账户套餐数据信息
	 * 
	 * @param array $data
	 * @param array $where
	 */ 
	function addInfo($data = array(), $where = array())
    {
        if (!empty($data)) {

            $uType = intval($where['usertype']);

            $TBList = array(
                1 => 'member_statis',
                2 => 'company_statis'
              
            );

            $tb = $TBList[$uType];

            return $this -> insert_into($tb, $data);
        }
    }
    /**
     * @desc 企业 会员套餐过期检测，并处理
     */
    public function vipOver($uid, $usertype = null){
    	
        $statis     =   $this -> getInfo($uid, array('usertype' => $usertype));//查询会员信息
        
        if($usertype==2){

        	$time	=	time();
            
        	if($this->config['sy_maturityday']>0){

        		$endtimevip	=	$time+$this->config['sy_maturityday']*86400;
            }else{
                
            	$endtimevip	=	$time+30*86400;
            }
            
            if(!isVip($statis['vip_etime'])){

            	$statis['remind']=1;
            }
        }
        
        
       
        if($statis['vip_etime'] && !isVip($statis['vip_etime'])){ //已过期会员
            //会员到期，变为过期会员
            if($this->config['com_vip_done'] == '0'){
                
                $data = array(
                    
                    'job_num'       =>  '0',
                    'breakjob_num'  =>  '0',
                    'down_resume'   =>  '0',
                    'invite_resume' =>  '0',
                    'zph_num'       =>  '0',
                    'top_num'       =>  '0',
                    'rec_num'       =>  '0',
                    'urgent_num'    =>  '0',
                    'sons_num'      =>  '0',
                	'oldrating_name'=>  $statis['rating_name'],
                    'rating_name'   =>  '过期会员',
                    'rating_type'   =>  '0',
                    'rating'        =>  '0'
                );
                
                //  过期会员，会员模式、会员等级清0
                $statis['rating_name']  =   '过期会员';
                $statis['rating_type']  =   '0';
                $statis['rating']       =   '0';
                
                $sdata  =   array(
                    
                    'job_num'       =>  $data['job_num'],
                    'breakjob_num'  =>  $data['breakjob_num'],
                    'down_resume'   =>  $data['down_resume'],
                    'invite_resume' =>  $data['invite_resume'],
                    'zph_num'       =>  $data['zph_num'],
                    'top_num'       =>  $data['top_num'],
                    'rec_num'       =>  $data['rec_num'],
                    'urgent_num'    =>  $data['urgent_num'],
                    'sons_num'      =>  $data['sons_num'],
                	'oldrating_name'=>  $data['oldrating_name'],
                    'rating_name'   =>  $data['rating_name'],
                    'rating_type'   =>  $data['rating_type'],
                    'rating'        =>  $data['rating']
                );
                
                $this -> upInfo($sdata, array('uid' => $uid,'usertype' => $usertype));
                
                $this -> update_once('company', array('rating' => $data['rating'], 'rating_name' => $data['rating_name']), array('uid' => $uid));
                $upJobArr['rating'] = 0;

				//会员到期职位下架
				if($this -> config['jobunder'] == '1'){
				
					$upJobArr['status'] = 1;
				
				}
                $this -> update_once('company_job', $upJobArr, array('uid' => $uid));
               
                
                
            }elseif ($this->config['com_vip_done'] == '1'){//会员到期，会员等级保留
                
                require_once 'rating.model.php';
                $ratingM    =  new rating_model($this->db, $this->def);
                $rat_value  =  $ratingM -> ratingInfo($this->config['com_rating'], $uid);
                
                $this -> upInfo($rat_value,array('uid' => $uid, 'usertype' => $usertype));
                
                $this -> update_once('company_job', array('rating' => $this->config['com_rating']), array('uid' => $uid));
                $this -> update_once('company', array('rating' => $this->config['com_rating'],'rating_name' => $rat_value['rating_name']), array('uid' => $uid));
                
                
                
            }
        }
        
        if($statis['autotime'] >= time()){
            
            $statis['auto']  =  1;
        }
        
        //会员未到期，永久会员（含过期会员）
        if(isVip($statis['vip_etime'])){
            
            if($statis['rating_type'] == '2'){//时间会员
            
                $addjobnum      =  '1';
                
            }else if($statis['rating_type'] == '1'){//套餐会员
                
				$addjobnum		=	$statis['job_num'] > 0 ? '1' : '2';
            }else{  //  过期
                
                $addjobnum      =  '0';
            }
        }else { //  过期
            
            $addjobnum          =  '0';
        }
        
        if($statis['rating'] > 0){
            if($statis['vip_etime'] && isVip($statis['vip_etime'])){

                $statis['days']     =  round(($statis['vip_etime']-time())/3600/24) ;
            }
        }
        $statis['addjobnum']        =  $addjobnum;
        $statis['pay_format']       =  number_format($statis['pay'],2);
        $statis['integral_format']  =  number_format($statis['integral']);
        
        return $statis;
    }

    
    
    function getCom($data=array())
    {
        
        $uid        =   intval($data['uid']);
        
        $usertype   =   intval($data['usertype']);
        
        $statis		=	$this -> getInfo($uid,array('usertype' =>$usertype));
        
        if ($statis['uid'] == '') {
            
            $statis =   $this->vipOver($uid, 2);
        }
        
        if ($statis['rating_type'] == '' && $statis['rating']) {
            
            $rating =   $this -> select_once('company_rating',array('id'=>$statis['rating']),'`type`');

            $this -> upInfo(array('rating_type' => $rating['type']), array('usertype' => $usertype, 'uid' => $uid));
            
            $statis['rating_type']  =   $rating['type'];
        }
        
        if ($statis['rating_type'] && $statis['rating']) {
            
            if ($statis['rating_type'] == '1' && $statis['job_num'] > 0 && isVip($statis['vip_etime'])) {
                
                $arr    =   array(
                    'job_num' => array('-', 1)
                );
                
            } elseif ($statis['rating_type'] == '2' && isVip($statis['vip_etime'])) {
                
                $arr    =   null;
                
            } else {
                
                return array('msg'=>'你的套餐已用完！','errcode' => 8);
            }
            
            if ($arr) {
                
                $this -> upInfo($arr, array('uid' => $uid, 'usertype' => $usertype));
            }
            
        } else {
            
            return array('msg'=>'你的会员已经到期，请先购买会员！','errcode'=>8);
        }
    }
    
    
	/**
     * 计划任务：会员到期自动下架职位
     * data['unit'] all  传入此值时，更新全部的过期会员
     */
	function setVipedupjob($data = array()){
		$time		=	date('Y-m-d',strtotime('-1 day'));
		
        $endtime	=	strtotime($time.' 23:59:59');

        $statisWhere['PHPYUNBTWSTART_A']    =   '';

        if(isset($data['unit']) && $data['unit'] == 'all'){
            $statisWhere['vip_etime'][]     =   array('>', 0, 'AND');
        }else{
            $statisWhere['vip_etime'][]     =   array('>=', $endtime, 'AND');
        }
        
        $statisWhere['vip_etime'][]         =   array('<=', time(), 'AND');
        
        $statisWhere['PHPYUNBTWEND_A']      =   '';

		$num = $this -> select_num('company_statis', $statisWhere);

		if ($num>0){
			
			$comstatis = $this -> select_all('company_statis', $statisWhere ,'`uid`,`vip_etime`,`rating_name`');

			if(is_array($comstatis)){
				
				foreach($comstatis as $key=>$value){					
					$uid[] 			=	$value['uid'];
                }
                $where              =   array();
				$where['uid']		=	array('in',pylode(',', $uid));
				$where['status']	=	array('<>', 1);
				
				$jobnum = $this -> select_num('company_job', $where);

				for($i=1; $i<=ceil($jobnum/500); $i++){
					
                    $where['limit']	=	(($i-1)*500).','.($i*500);
          
					$joblist = $this -> select_all('company_job', $where, '`id`,`uid`,`name`');
					
					foreach($joblist as $k=>$v){
						$jobids[]=$v['id'];
                    }
					$this -> update_once('company_job', array('status' => 1), array('id'=>array('in', pylode(',',$jobids))));
				}
			}
		}
    }


    /**
     * 计算金额
     */
    private function calculateMoney($payMoney, $payType){
        if($payType == 'integral'){
            $payMoney   =   bcmul($payMoney, $this -> config['integral_proportion'], 2);
        }
        return floatval($payMoney);
    }
    
    public function getStatisTotal($timeBegin, $timeEnd,$userType){
      
        //调用MODEL
        require_once ('companyorder.model.php');

        $CompanyorderM          =           new companyorder_model($this->db, $this->def);

        require_once ('pack.model.php');

        $PackM                  =           new pack_model($this->db, $this->def);
 
        if($userType){

            require_once ('userinfo.model.php');

            $UserinfoM                    =           new userinfo_model($this->db, $this->def);

            $memberwhere['usertype']      =           $userType;

            $member                       =           $UserinfoM->getList($memberwhere,array('field'=>'`uid`'));
          
            foreach($member  as $val){
                
                $uidArr[]              =           $val['uid'];

            }

            $uidStr 				    = 			implode(',', $uidArr);

            $where['uid']				=			array('in',pylode(',',$uidStr));
    
            $mwhere['uid']				=			array('in',pylode(',',$uidStr));
           

        }

        $where['order_state']   =           2;

        if($timeBegin !=''){

			$where['order_time'][]         		=   			array('>=', $timeBegin);
		
			$where['order_time'][]         		=           	array('<=', $timeEnd,'AND');
        }
       
        $field 			= 				'sum(order_price) as `num`';

        $row		    =				$CompanyorderM->getList($where,array('field'=>$field));

        $in             =               isset($row[0]['num']) && $row[0]['num'] > 0 ? $row[0]['num'] : 0;

        $mwhere['order_state']					=				1;
       
        if($timeBegin != ''){

			$mwhere['time'][]         			=   			array('>=', $timeBegin);
			
			$mwhere['time'][]         			=           	array('<=', $timeEnd,'AND');

        }
        
        $mfield 								= 				'sum(order_price) as `num`';

        $mrow									=				$PackM->getList($mwhere,array('field'=>$mfield));
        
        $out 									= 				isset($mrow[0]['num']) && $mrow[0]['num'] > 0 ? $mrow[0]['num'] : 0;

        $net_income 							= 				$in - $out;
        
        return array($in, $out, $net_income);
      
    }
}
?>