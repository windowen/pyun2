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
class qrorder_model extends model{

	/**
     * @desc   引用userinfo类，根据用户类型，直接查找用户信息   
     */
    private function getUserInfo($whereData = array(), $data = array()) {
        require_once ('userinfo.model.php');
        $MemberM = new userinfo_model($this->db, $this->def);
        return  $MemberM -> getUserInfo($whereData , $data); 
    }
	/**
     * @desc   引用statis类，获取账户套餐数据信息  
     */
    private function getStatisInfo($uid, $data = array()) {
        require_once ('statis.model.php');
        $StatisM = new statis_model($this->db, $this->def);
        return  $StatisM -> getInfo($uid , $data); 
    }
	/**
     * @desc   引用statis类，更新账户套餐数据信息   
     */
    private function UpStatisInfo($data = array(), $whereData = array()) {
        require_once ('statis.model.php');
        $StatisM = new statis_model($this->db, $this->def);
        return  $StatisM -> upInfo($data , $whereData); 
    }
	/**
     * @desc   引用part类，获取兼职列表
     */
    private function getPartList($whereData = array(), $data = array()) {
        require_once ('part.model.php');
		$PartM		=	 new part_model($this->db, $this->def);
        return  $PartM -> getList($whereData , $data); 
    }
	/**
     * @desc   引用part类，兼职详情，单条查询   
     */
    private function getPartInfo($where = array(), $data = array()) {
        require_once ('part.model.php');
		$PartM		=	 new part_model($this->db, $this->def);
        return  $PartM -> getInfo($where , $data); 
    }
	/**
     * @desc   引用once类，查询once_job单条信息   
     */
    private function getOnceInfo($id = array(), $data = array()) {
        require_once ('once.model.php');
        $OnceM = new once_model($this->db, $this->def);
        return  $OnceM -> getOnceInfo($id , $data); 
    }
	/**
     * @desc   引用rating类，企业会员等级  
     */
    private function ratingInfo($id =0, $uid = 0) {
        require_once('rating.model.php');
		$RatingM 			=	 new rating_model($this -> db,$this -> def);
        return  $RatingM -> ratingInfo($id , $uid); 
    }
	
	/**
     * @desc   引用job类，职位详情，单条查询
     */
    private function getJobInfo($where, $data = array()) {
        require_once ('job.model.php');
		$JobM		=	 new job_model($this->db, $this->def);
        return  $JobM -> getInfo($where , $data); 
    }
	/**
     * @desc   引用job类，获取职位列表
     */
    private function getJobList($whereData,$data=array()) {
        require_once ('job.model.php');
		$JobM		=	 new job_model($this->db, $this->def);
		$List		=	$JobM -> getList($whereData,$data=array()); 
        return  $List['list'];
    }
	
	/**
     * @desc   引用integral类，添加消费记录 
     */
    private function insert_company_pay($integral,$pay_state,$uid,$usertype,$msg,$type,$pay_type='',$ptype=false) {
		require_once('integral.model.php');
		$IntegralM = new integral_model($this->db,$this->def);
		return  $IntegralM -> insert_company_pay($integral,$pay_state,$uid,$usertype,$msg,$type,$pay_type,$ptype);
    }
	/**
     * @desc   引用zph类，获取参会企业
     */
	private function getZphComInfo($whereData,$data=array()) {
		require_once ('zph.model.php');
		$ZphM	=	new zph_model($this->db, $this->def);
		return  $ZphM -> getZphComInfo($whereData,$data=array());
    }
    /**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type=''); 
    }
    
    /**
     * @desc    引用warning类，添加充值预警提醒
     */
    private function checkWarning($type, $uid){
        
        require_once ('warning.model.php');
        $warningM   =   new warning_model($this->db, $this->def);
        $warningM -> warning(4, $uid);//充值预警提醒
    }
    
    /**
     * @desc    后台确认订单操作
     * @param   array $order
     * @return  boolean 
     */
    public function upuser_statis($order){

 		if($order['order_state']!='2'){
 		    
 		    $uid         =   intval($order['uid']);
 		    
 		    $type        =   intval($order['type']);
 		    
 		    $orderid     =   $order['order_id'];
 		    
 		    $member      =   $this -> select_once('member',array('uid'=>$uid) ,'username,usertype,email,moblie,uid'); 
 		    
 		    $usertype    =   intval($order['usertype']);
			
 		    $tvalue      =   array();
 		    
			if($usertype == 2 || $usertype == 3){
			    
			    $tvalue['all_pay']  =  array('+' , $order['order_price']);
				
			}
			
			if($type == 1 && $order['rating'] && $usertype != 1){//会员充值（购买会员）
			    
				if ($usertype == 2) {

                    $value  =   $this->ratingInfo($order['rating'], $uid);
                    // 同步处理数据
                    $companydata    =   array(
                        'rating'        => $value['rating'],
                        'rating_name'   => $value['rating_name'],
                        'vipetime'      => $value['vip_etime'],
                        'vipstime'      => $value['vip_stime']
                    );

                    $this->update_once('company', $companydata, array('uid' => $uid));
                    
                  
                    
                } else if ($usertype == 3) {

                    $value = $this->ltratingInfo($order['rating'], $uid);
                }
                
				$status     =   $this -> UpStatisInfo($value, array('uid' => $uid, 'usertype' => $usertype));
				if ($status) {
				    
				    if (!empty($spids)) {
				        
				        $this->update_once('company_statis', $sonData, array('uid' => array('in', pylode(',', $spids))));
				    }
				    
				    $order_info 	=	 unserialize($order['order_info']);
				    
				    if ($order_info['vip_integral'] && $order['integral']) { // 充值积分购买会员
				        
				        $tvalue['integral']	   =   array('+' , $order['integral']);
				        
				        $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				        
				        $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename'].'，购买会员',1,2,true);
				        
				        $this->UpStatisInfo(array('integral' => array('-', $order_info['vip_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				        
				        $this -> insert_company_pay($order_info['vip_integral'],2,$uid,$usertype,"购买会员，扣除".$this->config['integral_pricename'],1,2,false);
				        
				        $this->checkWarning(4, $uid);
				    }
				}
				
			}else if($type == 2 && $order['integral']){//积分充值
				
				$tvalue['integral']	=	array('+' , $order['integral']);
				
				$status				=	$this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				
				if ($status) {
				    
				    $this->checkWarning(4, $uid);
				}
				
			}else if($type == 3 || $type == 4){//type=3银行转帐，type=4金额充值
				
				$tvalue['pay']	=	array('+' , $order['order_price']);
				
				$status			=	$this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' =>$usertype));
				
			}else if($type == 5){//购买增值包
				
				if($usertype==2){
					
					$row   =   $this -> select_once('company_service_detail',array('id' => intval($order['rating'])));
					
					if($row['job_num']){
						$value['job_num']			=	array('+', $row['job_num']);
					}
					if($row['breakjob_num']){
						$value['breakjob_num']		=	array('+', $row['breakjob_num']);
						}
					if($row['interview']){
						$value['down_resume']		=	array('+', $row['resume']);
						}
					if($row['interview']){
						$value['invite_resume']		=	array('+', $row['interview']);
						}
					if($row['zph_num']){
						$value['zph_num']			=	array('+', $row['zph_num']);
						}
					if($row['rec_num']){
						$value['rec_num']			=	array('+', $row['rec_num']);
						}
					if($row['top_num'])
						{$value['top_num']			=	array('+', $row['top_num']);
					}
					if($row['urgent_num'])
						{$value['urgent_num']		=	array('+', $row['urgent_num']);
					}
          			
					$value['all_pay']			=	array('+', $order['order_price']);
				}
				
				
				$status		=	$this -> UpStatisInfo($value,array('uid'=>$uid,'usertype'=>$usertype));
				
				if ($status) {
				    
				    $order_info 	=	 unserialize($order['order_info']);
				    
				    if ($order_info['pack_integral'] && $order['integral']) { // 充值积分购买会员
				        
				        $tvalue['integral']	   =   array('+' , $order['integral']);
				        
				        $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				        
				        $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename'].'，购买增值服务',1,2,true);
				        
				        $this -> UpStatisInfo(array('integral' => array('-', $order_info['pack_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				        
				        $this -> insert_company_pay($order_info['pack_integral'],2,$uid,$usertype,"购买增值服务，扣除".$this->config['integral_pricename'],1,2,false);
				        
				        $this -> checkWarning(4, $uid);
				    }
				}
				
				
			}else if($type==10){
				//购买职位置顶
				$order_info	    =   unserialize($order['order_info']);
				
				if($order_info['jobid']){
				    
				    $xsjob      =   $this -> getJobInfo(array('id' => $order_info['jobid']),array('field'=>'name,xsdate'));
				    
				    if(!empty($xsjob)){
				        
				        $xsdate =   $xsjob['xsdate'] > time() ? array('+' , $order_info['days'] * 86400) : strtotime('+'.$order_info['days'].' day');
						
				        $status =   $this -> update_once('company_job' , array('xsdate' => $xsdate),array('uid' => $uid,'id' => $order_info['jobid']));
				        
				        if ($order_info['jobzd_integral'] && $order['integral']) { // 充值积分购买职位置顶
				            
				            $tvalue['integral']	   =   array('+' , $order['integral']);
				            
				            $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				            
				            $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename'].'，购买职位置顶',1,2,true);
				            
				            $this -> UpStatisInfo(array('integral' => array('-', $order_info['jobzd_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				            
				            $this -> insert_company_pay($order_info['jobzd_integral'],2,$uid,$usertype,"职位置顶，扣除".$this->config['integral_pricename'],1,2,false);
				            
				            $this -> checkWarning(4, $uid);
				        }
					}
				}
				
 			}else if($type==11){//购买紧急招聘
				$order_info     =	unserialize($order['order_info']);
				
				if($order_info['jobid']){
				    
				    $ujob       =	$this -> getJobInfo(array('id' => $order_info['jobid']),array('field'=>'name,urgent_time'));
				    
				    if(!empty($ujob)){
					
				        $urgent_time    =   $ujob['urgent_time'] > time() ? array('+', $order_info['days'] * 86400) : strtotime('+'.$order_info['days'].' day');
				        
				        $status	        =	$this -> update_once("company_job",array('urgent_time'=>$urgent_time,'urgent'=>'1'),array('uid'=>$uid,'id'=>$order_info['jobid']));
				        
				        if ($order_info['joburgent_integral'] && $order['integral']) { // 充值积分购买职位紧急招聘
				            
				            $tvalue['integral']	   =   array('+' , $order['integral']);
				            
				            $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				            
				            $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename'].'，购买职位紧急招聘',1,2,true);
				            
				            $this -> UpStatisInfo(array('integral' => array('-', $order_info['joburgent_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				            
				            $this -> insert_company_pay($order_info['joburgent_integral'],2,$uid,$usertype,"职位紧急招聘，扣除".$this->config['integral_pricename'],1,2,false);
				            
				            $this -> checkWarning(4, $uid);
				        }
 					}
				}
 			}else if($type==12){//购买推荐职位
 			    
				$order_info     =   unserialize($order['order_info']);
				
				if($order_info['jobid']){
				    
				    $recjob     =   $this -> getJobInfo(array('id' => $order_info['jobid']),array('field'=>'name,rec_time'));
				    
					if(!empty($recjob)){
					    
					    $rec_time  =   $recjob['rec_time'] > time() ? array('+' , $order_info['days'] * 86400) : time() + $order_info['days'] *86400;
					    
					    $status    =   $this -> update_once('company_job', array('rec_time'=>$rec_time,'rec'=>'1'),array('uid'=>$uid,'id'=>$order_info['jobid']));
					    
					    if ($order_info['jobrec_integral'] && $order['integral']) {  
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $this -> insert_company_pay($order['integral'], 2, $uid, $usertype, "充值".$this->config['integral_pricename'].'，购买职位推荐',1,2,true);
					        
					        $this -> UpStatisInfo(array('integral' => array('-', $order_info['jobrec_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $this -> insert_company_pay($order_info['jobrec_integral'],2,$uid,$usertype,"职位推荐，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $this -> checkWarning(4, $uid);
					    }
					}
				}
 			}else if($type==13){//购买自动刷新职位
				
				$order_info     =	 unserialize($order['order_info']);
				
				if($order_info['jobid']){
				    
				    $autoJob    =	$this -> getJobList(array('uid'=>$uid,'id'=>array('in',$order_info['jobid'])),array('field'=>'id,autotime'));
				    if(!empty($autoJob)){
				        $lastautotime   =   0;
				        
				        foreach ($autoJob as $v){
				            
				            $autotime   =   $v['autotime'] > time() ? array('+', $order_info['days'] * 86400) : time() + $order_info['days'] * 86400;
				           
				            if ($autotime > $lastautotime) {
				                $lastautotime 	=	 $autotime;
							}
							
							$this -> update_once('company_job',array('autotime'=>$autotime,'autotype'=>'1'),array('uid'=>$uid,'id' => $v['id'] ));
							
						}
						$status   =	  $this -> UpStatisInfo(array('autotime'=>$lastautotime),array('uid'=>$uid, 'usertype' => 2));
						
						if ($order_info['auto_integral'] && $order['integral']) { // 充值积分购买自动刷新
						    
						    $tvalue['integral']	   =   array('+' , $order['integral']);
						    
						    $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => 2));
						    
						    $this -> insert_company_pay($order['integral'], 2, $uid, 2, "充值".$this->config['integral_pricename'].'，购买职位自动刷新',1,2,true);
						    
						    $this -> UpStatisInfo(array('integral' => array('-', $order_info['auto_integral'])), array('uid' => $uid, 'usertype' => $usertype));
						    
						    $this -> insert_company_pay($order_info['auto_integral'],2,$uid,$usertype,"职位自动刷新，扣除".$this->config['integral_pricename'],1,2,false);
						    
						    $this -> checkWarning(4, $uid);
						}
						
					}
				}
			}else if($type==14){//购买简历置顶
			    
				$order_info 	=	 unserialize($order['order_info']);
				
				if($order_info['resumeid']){
				    
				    $zdresume 	=	 $this -> select_once('resume_expect', array('id' => $order_info['resumeid']), '`id`,`topdate`');
					
					if(!empty($zdresume)){
					    
					    $topdate   =   $zdresume['topdate'] > time() ? array('+' , $order_info['days'] * 86400) : time() +$order_info['days']*86400;
						
					    $status	   =   $this -> update_once('resume_expect',array('topdate'=>$topdate,'top'=>'1'),array('uid'=>$uid,'id'=>$order_info['resumeid']));
					}
				}
 			}else if($type==16){//刷新职位
 			    
 			    $order_info     =   unserialize($order['order_info']);
 			    
 			    if($order_info['jobid']){
 			        
 			        $sxJob      =   $this -> getJobList(array('uid' => $uid,'id'=>array('in',$order_info['jobid'])),array('field'=>'lastupdate,id'));
 			        
 			        if(!empty($sxJob)){
 			            
 			            $status =   $this -> update_once('company_job',array('lastupdate'=>time()),array('id'=>array('in',$order_info['jobid'])));
 			            
 			            $this   ->  update_once('company',array('lastupdate'=>time()),array('uid'=>$uid));	
 			            
 			            if ($order_info['sxjob_integral'] && $order['integral']) { // 充值积分刷新职位
 			                
 			                $tvalue['integral']	   =   array('+' , $order['integral']);
 			                
 			                $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
 			                
 			                $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename'].'，购买刷新职位',1,2,true);
 			                
 			                $this -> UpStatisInfo(array('integral' => array('-', $order_info['sxjob_integral'])), array('uid' => $uid, 'usertype' => $usertype));
 			                
 			                $this -> insert_company_pay($order_info['sxjob_integral'],2,$uid,$usertype,"刷新职位，扣除".$this->config['integral_pricename'],1,2,false);
 			                
 			                $this -> checkWarning(4, $uid);
 			            }
 					}
				}
			}else if($type==17){//刷新兼职
				
				$order_info 	=	 unserialize($order['order_info']);
				
				if($order_info['jobid']){
					
					$sxPart 	=	 $this -> getPartList(array('uid'=>$uid,'id'=>array('in',$order_info['jobid'])),array('field'=>'lastupdate,id'));
					
					if(!empty($sxPart)){
 						 
						$status	=	$this -> update_once('partjob',array('lastupdate'=>time()),array('id'=>array('in',$order_info['jobid'])));
					   
						if ($order_info['sxpart_integral'] && $order['integral']) { // 充值积分刷新兼职
						    
						    $tvalue['integral']	   =   array('+' , $order['integral']);
						    
						    $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
						    
						    $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename']."，购买刷新兼职",1,2,true);
						    
						    $this -> UpStatisInfo(array('integral' => array('-', $order_info['sxpart_integral'])), array('uid' => $uid, 'usertype' => $usertype));
						    
						    $this -> insert_company_pay($order_info['sxpart_integral'],2,$uid,$usertype,"刷新兼职，扣除".$this->config['integral_pricename'],1,2,false);
						    
						    $this -> checkWarning(4, $uid);
						}
  					}
				}
			}else if($type==19){//下载简历
				
			    $order_info 	=	 unserialize($order['order_info']);
				
				if($order_info['eid']){
				    
					$eid       =   intval($order_info['eid']);
					$expect    =   $this -> select_once('resume_expect',array('id'=>$eid),'uid,id,name');
					
					if (!empty($expect)) {
					
    					$downData          =   array(
    					    
    					    'eid'          =>  intval($expect['id']),
    					    'uid'          =>  intval($expect['uid']),
    					    'comid'        =>  intval($order_info['uid']),
    					    'usertype'     =>  intval($usertype),
    					    'downtime'     =>  time(),
							'did'          =>  $this->config['did']
    					    
    					);
    					
    					$downresume 	   =	$this -> select_once('down_resume',array('eid' => $eid, 'comid' => $order_info['uid'],'usertype'=>$usertype));
    					
    					if(empty($downresume)){
    					    
    					    $status    =   $this -> insert_into('down_resume', $downData);
    					    
    					    $this      ->  update_once('resume_expect',array('dnum'=>array('+',1)),array('id'=>$eid));
    					    
    					    if ($order_info['resume_integral'] && $order['integral']) { // 充值积分下载简历
    					        
    					        $tvalue['integral']	   =   array('+' , $order['integral']);
    					        
    					        $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
    					        
    					        $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename']."，购买下载简历",1,2,true);
    					        
    					        $this -> UpStatisInfo(array('integral' => array('-', $order_info['resume_integral'])), array('uid' => $uid, 'usertype' => $usertype));
    					        
    					        $this -> insert_company_pay($order_info['resume_integral'],2,$uid,$usertype,"下载简历，扣除".$this->config['integral_pricename'],1,2,false);
                                    
    					        $this -> checkWarning(4, $uid);
    					    }
							if($status){
								$this -> addMemberLog($order_info['uid'],$usertype,'下载了简历：'.$expect['name'],3,1); 
							}
    					    
    					}else{
    					    
    					    $this->update_once('company_order', array('order_state' => '4', 'order_remark' => '简历（ID:'.$expect['id'].'）您已经下载过，关闭无效交易订单！'), array('id'=>$order['id']));
    					    
    					}
					
					}
 					
				}
				
			}else if($type==20){//发布职位
				
			    $order_info 	=	 unserialize($order['order_info']);
			    
 				if($usertype == 2){
 				    
					$status	=	$this -> UpStatisInfo(array('job_num' => array('+' , 1)),array('uid'=>$uid,'usertype'=>$usertype));
					
					if ($order_info['issue_integral'] && $order['integral']) { // 充值积分购买职位发布           
					    
					    $tvalue['integral']	   =   array('+' , $order['integral']);
					    
					    $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					    
					    $this -> insert_company_pay($order['integral'],2,$uid,$usertype,'充值'.$this->config['integral_pricename'].'，购买职位发布',1,2,true);
					    
					    $this -> UpStatisInfo(array('integral' => array('-', $order_info['issue_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					    
					    $this -> insert_company_pay($order_info['issue_integral'],2,$uid,$usertype,'职位发布，扣除'.$this->config['integral_pricename'],1,2,false);
					    
					    $this -> checkWarning(4, $uid);
					}
				}
					 
 			}else if($type==23){//邀请面试
				
 				if($usertype==2){
 				    
					$status	=	$this -> UpStatisInfo(array('invite_resume'=>array('+',1)),array('uid'=>$uid,'usertype'=>$usertype)); 
					
					if ($status) {
					    
					    $order_info 	=	 unserialize($order['order_info']);
					    
					    if ($order_info['invite_integral'] && $order['integral']) { // 充值积分购买面试邀请
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename']."，购买面试邀请",1,2,true);
					        
					        $this -> UpStatisInfo(array('integral' => array('-', $order_info['invite_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $this -> insert_company_pay($order_info['invite_integral'],2,$uid,$usertype,"购买面试邀请，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $this -> checkWarning(4, $uid);
					    }
					}
					
				}
					 
 			}else if($type==24){//购买推荐兼职
 			    
				$order_info =   unserialize($order['order_info']);
				
				if($order_info['jobid']){
				    
				    $recjob =   $this -> getPartInfo(array('id' => intval($order_info['jobid'])),array('field'=>'name,rec_time'));
				    
				    $recjob	=	$recjob['info'];
					
				    if(!empty($recjob)){
				        
				        $rec_time   =   $recjob['rec_time'] > time() ? array('+', $order_info['days'] * 86400) : time()+$order_info['days'] * 86400;
	 					
				        $status     =   $this -> update_once('partjob', array('rec_time' => $rec_time), array('uid' => $uid, 'id' => $order_info['jobid']));
				        
				        if ($order_info['recpart_integral'] && $order['integral']) {    
				            
				            $tvalue['integral']	   =   array('+' , $order['integral']);
				            
				            $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				            
				            $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename']."，购买兼职推荐",1,2,true);
				            
				            $this -> UpStatisInfo(array('integral' => array('-', $order_info['recpart_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				            
				            $this -> insert_company_pay($order_info['recpart_integral'],2,$uid,$usertype,"兼职推荐，扣除".$this->config['integral_pricename'],1,2,false);
				            
				            $this -> checkWarning(4, $uid);
				        }
					}
				}
 			}else if($type == 25){//购买店铺招聘
 			    
				if($order['once_id']){
					
				    $once 	=	 $this -> getOnceInfo(array('id'=>$order['once_id']),array('field'=>'pay'));
					
					if(!empty($once)){
					    
	 					$status	=	$this -> update_once('once_job',array('pay'=>'2'),array('id'=>$order['once_id']));
	 					
					}
					
				}
 			}else if($type=='28'){//报名招聘会
 			    
				$order_info 	=	 unserialize($order['order_info']);
				
				if($order_info['zid']){
				    $zid		= 	intval($order_info['zid']);
					$zData		=	array(
						'uid'		=>	$order_info['uid'],
						'zid'		=>	$zid,
						'bid'		=>	$order_info['bid'],
						'sid'		=>	$order_info['sid'],
						'cid'		=>	$order_info['cid'],
						'jobid'		=>	$order_info['jobid'],
						'ctime'		=>	time(),
						'status'	=>	0,
						'price'		=>	$order['order_price'],
						'com_name'	=>	$order_info['com_name'],
					    'jobid'     =>  $order_info['jobid']
					);
					
					$zphCom		=	$this -> getZphComInfo(array('uid'=>$order_info['uid'],'zid'=>$zid));
					
					if(empty($zphCom)){
					
					    $status	=	$this -> insert_into('zhaopinhui_com',$zData);
						
						if ($status) {
						    
						    $order_info 	=	 unserialize($order['order_info']);
						    
						    if ($order_info['zph_integral'] && $order['integral']) { // 充值积分报名招聘会
						        
						        $tvalue['integral']	   =   array('+' , $order['integral']);
						        
						        $this -> UpStatisInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
						        
						        $this -> insert_company_pay($order['integral'],2,$uid,$usertype,"充值".$this->config['integral_pricename']."，预定招聘会",1,2,true);
						        
						        $this -> UpStatisInfo(array('integral' => array('-', $order_info['zph_integral'])), array('uid' => $uid, 'usertype' => $usertype));
						        
						        $this -> insert_company_pay($order_info['zph_integral'],2,$uid,$usertype,"预定招聘会，扣除".$this->config['integral_pricename'],1,2,false);
						        
						        $this -> checkWarning(4, $uid);
						        
						    }
						}
					}	
				}
			}
			
			if($status){
				if($this->config['sy_msg_fkcg']=='1'||$this->config['sy_email_fkcg']=='1'){
				    
 					if($usertype=='1' || $usertype=='2'){
						$fdata	=	$this -> getUserInfo(array('uid'=>$member['uid']),array('field'=>'name,uid','usertype'=>$usertype));
					}elseif($usertype=='3'){
						$fdata	=	$this -> getUserInfo(array('uid'=>$member['uid']),array('field'=>'realname as name,uid','usertype'=>$usertype));
					}
 					$data				=	array();
					$data["date"]		=	date("Y-m-d");
					$data["uid"]		=	$uid;
					$data["name"]		=	$fdata['name'];
					$data["type"]		=	"fkcg";
					$data["order_id"]	=	$orderid;
					$data["price"]		=	$order['order_price'];
					$data['webtel']		=	$this->config['sy_freewebtel'];
					$data['webname']	=	$this->config['sy_webname'];
					if($this->config['sy_msg_fkcg']=='1' && $member['moblie'] && checkMsgOpen($this -> config)){
						$data["moblie"]	=	$member["moblie"]; 
					}
					
					if($this->config['sy_email_fkcg']=='1' && $member['email'] && $this->config['sy_email_set']=="1"){
						$data["email"]	=	$member["email"]; 
					}
 					if($data['email']||$data['moblie']){
						require_once('notice.model.php');
						$notice = new notice_model($this->db,$this->def,array('uid'=>$this->uid,'username'=>$this->username,'usertype'=>$this->usertype));

    					$nid	=	$notice -> sendEmailType($data);
						$data['port']	=	'5';
 						$nid	=	$notice -> sendSMSType($data);
					}
				}
				$this -> update_once('company_order',array('order_state'=>2, 'port' => 5),array('id'=>$order['id']));
				
				if($type=='2'){
					$this -> insert_company_pay($order['integral'],2,$uid,$usertype,"购买".$this->config['integral_pricename'],1,2,true);
				}
				
			}
  			return $status;
		}
	}
	 
}
?>