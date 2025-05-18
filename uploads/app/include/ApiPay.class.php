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

/**
 * @method  支付处理函数
 * @param   回调参数
 * @param   dingdan   系统订单ID
 * @param   total_fee 支付金额
 */

//合并所有支付API 支付成功逻辑处理函数（支付宝、财付通、微信、wap支付宝）

//孔舒程  2015-12-08

class ApiPay extends common{
    
	function payAll($dingdan,$total_fee,$paytype=''){
		
	
		//支付回调参数验证数据合法性
		if(!preg_match('/^[0-9]+$/', $dingdan)){

			  die;
		}

		//查询订单是否真实存在
		$orderM       =   $this -> MODEL('companyorder');
		$order        =   $orderM -> getInfo(array('order_id'=>$dingdan));

		
		$userinfoM    =   $this -> MODEL('userinfo');
		$resumeM      =   $this -> MODEL('resume');
		$comM         =   $this -> MODEL('company');
		$jobM         =   $this -> MODEL('job');
		$ratingM      =   $this -> MODEL('rating');
		$logM         =   $this -> MODEL('log');
		$statisM      =   $this -> MODEL('statis');
		$integralM    =   $this -> MODEL('integral');
		$packM        =   $this -> MODEL('pack');
		$warningM     =   $this -> MODEL('warning');
		
		//判断订单状态是否未处理，只处理未付款的
		
		if($order['order_state']=='1' && $order['id']){
		    
		    $uid      =   intval($order['uid']);
		    $ratingId =   intval($order['rating']);
		    $orderid  =   $order['order_id'];
		    $type     =   intval($order['type']);
		    
		    $tvalue   =   array();
		    $usertype =   intval($order['usertype']);
		    
		    $member   =   $userinfoM -> getInfo(array('uid'=> $uid), array('field'=>'`username`,`usertype`,`wxid`'));
			
		    if($usertype == 1){
			    
			    $marr    =   $resumeM -> getResumeInfo(array('uid'=>$uid), array('field'=>'`name`,`email`,`telphone` as `moblie`'));
				
		    }else if($usertype == 2){
			    
			    $tvalue['all_pay']   =   array('+', $order['order_price']);
				
			    $marr                =   $comM -> getInfo($uid, array('field'=>'`name`,`crm_uid`,`linkmail` as `email`, `linktel` as moblie'));
				
		    } 
			$emaildata               =   array();
			$emaildata['type']       =   'recharge';
			$emaildata['username']   =   $member['username'];
			$emaildata['name']       =   $marr['name'];
			$emaildata['price']      =   $order['order_price'];
			$emaildata['time']       =   date("Y-m-d H:i:s");
			$emaildata['email']      =   $marr['email'];
			$emaildata['moblie']     =   $marr['moblie'];
			$emaildata['port']		 =   $order['port'];

			$sendInfo                =   array();
			$sendInfo['wxid']        =   $member['wxid'];
			$sendInfo['first']       =   '您有一笔订单支付成功！';
			$sendInfo['username']    =   $member['username'];
			$sendInfo['order']       =   $orderid;
			$sendInfo['price']       =   $order['order_price'];
			$sendInfo['time']        =   date('Y-m-d H:i:s');
			$sendInfo['uid']         =   $uid;
			$sendInfo['usertype']    =   $member['usertype'];
			switch($paytype){
					
				case 'alipay':$sendInfo['paytype']='支付宝';
				break;
				case 'wapalipay':$sendInfo['paytype']='支付宝手机支付';
				break;
				case 'tenpay':$sendInfo['paytype']='财付通';
				break;
				default :$sendInfo['paytype']='其他支付方式';

				break;

			}

			//发送短信邮件通知站长参数
			if($type == 1 && $ratingId && $usertype != 1){
                
			    $row     =   $ratingM -> getInfo(array('id' => $ratingId));

				//购买会员
				$sendInfo['info']   = '购买：'.$row['name'];

				if($usertype == 2){
				    
					$value =   $ratingM -> ratingInfo($ratingId, $uid);
                    
					$jobM  ->  upInfo(array('rating' => $ratingId), array('uid' => $uid));
                    
                    // 企业信息表里面添加相关企业会员
                    $companydata = array(

                        'rating'        =>  $value['rating'],
                        'rating_name'   =>  $value['rating_name'],
                        'vipetime'      =>  $value['vip_etime'],
                        'vipstime'      =>  $value['vip_stime']
                    );

                    $comM -> upInfo($uid, '', $companydata);
                    
                    
           
				} 
       
				$nid    =   $statisM -> upInfo($value, array('uid' => $uid, 'usertype' => $usertype));
				
				if ($nid) {
				    
				    if (!empty($spids)) {
				        
				        $this->obj->update_once('company_statis', $sonData, array('uid' => array('in', pylode(',', $spids))));
				    }
				    
				    $order_info 	=	 unserialize($order['order_info']);
				    
				    if ($order_info['vip_integral'] && $order['integral']) { // 充值积分购买会员
				        
				        $tvalue['integral']	   =   array('+' , $order['integral']);
				        
				        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				        
				        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买会员',1,2,true);
				        
				        $statisM ->upInfo(array('integral' => array('-', $order_info['vip_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				        
				        $integralM -> insert_company_pay($order_info['vip_integral'],2,$uid,$order['usertype'],"购买会员，扣除".$this->config['integral_pricename'],1,2,false);
				        
				        $warningM -> warning(4, $uid); //充值预警提醒
				    }
				}

				$sendMail   = 1;//确定发送邮件状态
				
			}else if($type == 2 && $order['integral']){//充值积分
                
                $tvalue['integral'] =   array('+', $order['integral']);
                $nid                =   $statisM -> upInfo($tvalue, array('uid' => $uid,'usertype'=>$usertype));
                
                if ($nid) {
                    $warningM -> warning(4, $uid); //充值预警提醒
                }
                $sendMail           =   1;  
				$sendInfo['info']   =   '充值'.$this->config['integral_pricename'].'：'.$order['integral'];


			}else if($type == 5){//购买增值包
			    
				if($usertype == 2){
				    
				    $row    =   $ratingM -> getComSerDetailInfo($ratingId);
					
				    $value['job_num']           =   array('+', intval($row['job_num'])); 
				    $value['breakjob_num']      =   array('+', intval($row['breakjob_num']));  
				    $value['down_resume']       =   array('+', intval($row['resume'])); 
				    $value['invite_resume']     =   array('+', intval($row['interview'])); 
					$value['top_num']			=   array('+', intval($row['top_num']));
					$value['rec_num']			=   array('+', intval($row['rec_num']));
					$value['urgent_num']		=   array('+', intval($row['urgent_num']));
				    
				}
                
				$nid        =   $statisM -> upInfo($value, array('uid' => $uid, 'usertype' => $usertype));
				
				if ($nid) {
				    
				    $order_info 	=	 unserialize($order['order_info']);
				    
				    if ($order_info['pack_integral'] && $order['integral']) { // 充值积分购买增值服务
				        
				        $tvalue['integral']	   =   array('+' , $order['integral']);
				        
				        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				        
				        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买增值服务',1,2,true);
				        
				        $statisM ->upInfo(array('integral' => array('-', $order_info['pack_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				        
				        $integralM -> insert_company_pay($order_info['pack_integral'],2,$uid,$order['usertype'],"购买增值服务，扣除".$this->config['integral_pricename'],1,2,false);
				        
				        $warningM -> warning(4, $uid); //充值预警提醒
				    }
				    
				}
				
				$sendMail   =   1;
				$sendInfo['info']   =   '购买增值包：'.$row['name'];

			}else if($order['type']=='10'){//职位置顶
				/**
 				 * 购买置顶职位，付款成功后续操作，
				 * @var jobid days price
				 */
				$order_info = unserialize($order['order_info']);

				if($order_info['jobid']){
				    
					//查询该职位
				    $xsJob  =   $jobM -> getInfo(array('id' => intval($order_info['jobid'])), array('field' => '`id`,`xsdate`'));
				    
					if(!empty($xsJob)){
					    
					    $xsdate    =   $xsJob['xsdate'] > time() ? array('+', $order_info['days'] * 86400) : strtotime('+'.$order_info['days'].' day');
					    $nid       =   $jobM -> upInfo(array('xsdate' => $xsdate), array('uid' => $uid, 'id' => intval($order_info['jobid'])));
						
					    if ($order_info['jobzd_integral'] && $order['integral']) { // 充值积分购买职位置顶
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买职位置顶',1,2,true);
					        
					        $statisM ->upInfo(array('integral' => array('-', $order_info['jobzd_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order_info['jobzd_integral'],2,$uid,$order['usertype'],"职位置顶，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $warningM -> warning(4, $uid); //充值预警提醒
					    }
					    
					    $logM -> addMemberLog($uid, $usertype, '购买职位置顶','1','1');
					}
 				}
 				$sendInfo['info'] = '职位置顶';
			}
			else if($type == 11){//职位紧急
				/**
 				 * 购买紧急招聘，付款成功后续操作，
				 * @var jobid days price
				 */
				$order_info = unserialize($order['order_info']);
                
				if($order_info['jobid']){
				    
					//查询该职位
					$uJob  =   $jobM -> getInfo(array('id' => intval($order_info['jobid'])), array('where' => array('uid'=>$uid),'field'=>'`id`, `urgent_time`'));
					
					if(!empty($uJob)){
					    
					    $urgent_time   =   $uJob['urgent_time'] > time() ? array('+' , $order_info['days'] * 86400) : strtotime('+'.$order_info['days'].' day');
					    
					    $nid           =   $jobM   -> upInfo(array('urgent_time' => $urgent_time, 'urgent' => '1'),array('uid' => $uid, 'id' => intval($order_info['jobid'])));
						
					    if ($order_info['joburgent_integral'] && $order['integral']) { // 充值积分购买紧急招聘
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'购买职位紧急招聘',1,2,true);
					        
					        $statisM ->upInfo(array('integral' => array('-', $order_info['joburgent_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order_info['joburgent_integral'],2,$uid,$order['usertype'],"职位紧急招聘，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $warningM -> warning(4, $uid); //充值预警提醒
					    }
					    
                        $logM -> addMemberLog($uid, $usertype, '购买紧急招聘','1','1');
					}
 				}
				$sendInfo['info'] = '职位紧急';
				
			}else if($type == 12){//职位推荐
				
				/**
 				 * 购买职位推荐，付款成功后续操作，
				 * @var jobid days price
				 */
				$order_info = unserialize($order['order_info']);

				if($order_info['jobid']){
					//查询该职位
					$recJob    =   $jobM -> getInfo(array('id' => intval($order_info['jobid']), 'uid' => $uid), array('field'=>'`id`,`rec_time`'));
					
					if(!empty($recJob)){
					
					    $rec_time  =   $recJob['rec_time'] > time() ? array('+', $order_info['days']) : strtotime('+'.$order_info['days'].' day') ;

					    $nid       =   $jobM -> upInfo(array('rec_time' => $rec_time, 'rec' => '1'), array('uid' => $uid, 'id' => intval($order_info['jobid'])));
					    
					    if ($order_info['jobrec_integral'] && $order['integral']) { // 充值积分购买职位推荐
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买职位推荐',1,2,true);
					        
					        $statisM ->upInfo(array('integral' => array('-', $order_info['jobrec_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order_info['jobrec_integral'],2,$uid,$order['usertype'],"职位推荐，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $warningM -> warning(4, $uid); //充值预警提醒
					    }
					    
                        $logM -> addMemberLog($uid, $usertype, '购买职位推荐','1','1');
					}
 				}
 				$sendInfo['info'] = '职位推荐';
			}else if($type == 13){//职位自动刷新
				/**
				 * 购买自动刷新，付款成功后续操作，
				 * @var jobautoids days price
				 */
				$order_info = unserialize($order['order_info']);

				if($order_info['jobid']){
				    
					//查询该职位
				    $ListA    =   $jobM -> getList(array('uid' => $uid, 'id' => array('in', $order_info['jobid'])), array('field'=>'`id`,`autotime`'));
					
				    $autoJob  =   $ListA['list'];
				    
					if(!empty($autoJob)){
						$lastautotime =   0;
						foreach ($autoJob as $v){
						    $autotime    =   $v['autotime'] >= time() ? array('+', $order_info['days'] * 86400) : strtotime('+'.$order_info['days'].' day');
							
							if ($autotime > $lastautotime) {
								$lastautotime = $autotime;
							}
							
							$nid         =   $jobM -> upInfo(array('autotime' => $autotime, 'autotype' => '1'),array('uid' => $uid, 'id' => $v['id']));
							
						}
						
						$statisM -> upInfo(array('autotime' => $lastautotime),array('uid' => $uid,'usertype' => 2));
						
						if ($order_info['auto_integral'] && $order['integral']) { // 充值积分购买自动刷新
						    
						    $tvalue['integral']	   =   array('+' , $order['integral']);
						    
						    $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => 2));
						    
						    $integralM -> insert_company_pay($order['integral'],2,$uid,2,"充值".$this->config['integral_pricename'].'，购买职位自动刷新',1,2,true);
						    
						    $statisM ->upInfo(array('integral' => array('-', $order_info['auto_integral'])), array('uid' => $uid, 'usertype' => $usertype));
						    
						    $integralM -> insert_company_pay($order_info['auto_integral'],2,$uid,$order['usertype'],"职位自动刷新，扣除".$this->config['integral_pricename'],1,2,false);
						    
						    $warningM -> warning(4, $uid); //充值预警提醒
						}
						
						$logM -> addMemberLog($uid, $usertype, '购买职位自动刷新功能','1','2');
						
					}
 				}
 				$sendInfo['info']   = '职位刷新';
				
			}else if($type == 14){//简历置顶
				
				/**
 				 * 购买简历置顶，付款成功后续操作，
				 * @var resumeid days price
				 */
				$order_info = unserialize($order['order_info']);

				if($order_info['resumeid']){
					//查询该简历
					$zdResume  =   $resumeM -> getExpect(array('id'=>intval($order_info['resumeid']), 'uid' => $uid), array('field'=>'`id`,`topdate`'));
					
					if(!empty($zdResume)){
					    
					    $topdate   =   $zdResume['topdate'] > time() ? array('+', $order_info['days'] * 86400) : strtotime('+'.$order_info['days'].' day');
                        
					    $nid       =   $resumeM -> upInfo(array('id' => intval($order_info['resumeid'])),array('eData'=>array('topdate' => $topdate, 'top' => '1')) );
					    
					    $logM -> addMemberLog($uid, $usertype, '购买简历置顶','2','1');
					}
					
 				}
 				
				$sendInfo['info'] = '简历置顶';
				
			}else if($type ==  16){//职位刷新
				
			    $order_info = unserialize($order['order_info']);
			    
				if($order_info['jobid']){
				
				    //查询该职位
				    $sxJob  =   $jobM -> getList(array('uid'=>$uid , 'id' => array('in', $order_info['jobid'])), array('field'=>'`lastupdate`,`id`'));

					if(!empty($sxJob)){
					    
					    $nid   =   $jobM -> upInfo(array('lastupdate' => time()), array('id' => array('in', pylode(',' , explode(',', $order_info['jobid'])))));
 						
					    if ($order_info['sxjob_integral'] && $order['integral']) {
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'， 购买职位刷新',1,2,true);
					        
					        $statisM ->upInfo(array('integral' => array('-', $order_info['sxjob_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order_info['sxjob_integral'],2,$uid,$order['usertype'],"职位刷新，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $warningM -> warning(4, $uid); //充值预警提醒
					    }
					    
					    $comM  ->  upInfo($uid, '', array('lastupdate' => time()));

					    $logM  ->  addMemberLog($uid, $usertype, '职位刷新','1','4');
					}
 				}
				$sendInfo['info'] = '职位刷新';
				
			}else if($type == 17){//兼职刷新
			    
			    $partM       =   $this -> MODEL('part');
				
			    $order_info  =   unserialize($order['order_info']);
			    
			    if($order_info['jobid']){
			        //查询该职位
			        $sxPart  =   $partM -> getList(array('uid' => $uid, 'id' => array('in', $order_info['jobid'])), array('field' => '`id`,`lastupdate`'));
					
					if(!empty($sxPart)){
					    
					    $nid   =   $partM -> upInfo(array('lastupdate' => time()), array('id' => array('in', pylode(',', explode(',', $order_info['jobid'])))));
					    if ($order_info['sxpart_integral'] && $order['integral']) {
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'， 购买兼职刷新',1,2,true);
					        
					        $statisM ->upInfo(array('integral' => array('-', $order_info['sxpart_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order_info['sxpart_integral'],2,$uid,$order['usertype'],"兼职刷新，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $warningM -> warning(4, $uid); //充值预警提醒
					    }
					    $logM  ->  addMemberLog($uid, $usertype, '兼职刷新','9','4');
					}
					
 				}
 				$sendInfo['info'] = '兼职刷新';
				
			}else if($type == 19){//下载简历
			    
				$order_info =   unserialize($order['order_info']);
				
				if($order_info['eid']){
				
				    $eid    =   intval($order_info['eid']);
					
				    $expect =   $resumeM -> getExpect(array('id' => $eid), array('field' => '`id`,`uid`,`uname`'));
				    
				    if ($expect) {
				         
    				    $dData          =   array(
    				        
    				        'eid'          =>  intval($expect['id']),
    				        'uid'          =>  intval($expect['uid']),
    				        'comid'        =>  intval($order_info['uid']),
    				        'usertype'     =>  intval($usertype),
    				        'downtime'     =>  time(),
    				        'did'          =>  $this->config['did']
    				        
    				    );
    				    
    				    $downM   =  $this -> MODEL('downresume');
    				    $isDown  =  $downM -> getDownResumeInfo(array('eid' => $eid , 'comid' => $order_info['uid'],'usertype'=>$usertype));
    				    
    				    if(empty($isDown)){
    				        
    				        $nid    =   $downM -> addInfo($dData);
    				        
    				        $resumeM -> upInfo(array('id'=>$eid), array('eData'=>array('dnum' => array('+','1'))));
    				        
    				        if ($order_info['resume_integral'] && $order['integral']) { // 充值积分下载简历
    				            
    				            $tvalue['integral']	   =   array('+' , $order['integral']);
    				            
    				            $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
    				            
    				            $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，下载简历',1,2,true);
    				            
    				            $statisM ->upInfo(array('integral' => array('-', $order_info['resume_integral'])), array('uid' => $uid, 'usertype' => $usertype));
    				            
    				            $integralM -> insert_company_pay($order_info['resume_integral'],2,$uid,$order['usertype'],"下载简历，扣除".$this->config['integral_pricename'],1,2,false);
    				         
    				            $warningM -> warning(4, $uid); //充值预警提醒
    				        }
    				    }else{
    				        
    				        $this->update_once('company_order', array('order_state' => '4', 'order_remark' => '简历（ID:'.$expect['id'].'）您已经下载过，关闭无效交易订单！'), array('id'=>$order['id']));
    				    }
     					
     					$logM -> addMemberLog($uid, $usertype, '下载简历：'.$expect['uname'],3,1);
				    }
				}
				$sendInfo['info']   =   '下载简历';
				
			}else if($type == 20){//发布职位
			    
			    $order_info =   unserialize($order['order_info']);
			    
				if($usertype == 2){
 					$jobnum   =   array('+', 1);
				}
				$nid        =   $statisM -> upInfo(array('job_num' => $jobnum), array('uid' => $uid, 'usertype' => $usertype));
				
				if ($order_info['issue_integral'] && $order['integral']) { // 充值积分购买职位发布
				    
				    $tvalue['integral']	   =   array('+' , $order['integral']);
				    
				    $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				    
				    $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买职位发布',1,2,true);
				    
				    $statisM ->upInfo(array('integral' => array('-', $order_info['issue_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				    
				    $integralM -> insert_company_pay($order_info['issue_integral'],2,$uid,$order['usertype'],"发布职位，扣除".$this->config['integral_pricename'],1,2,false);
				    
				    $warningM -> warning(4, $uid); //充值预警提醒
				}
				$sendMail   =   1;
 				$sendInfo['info'] = '购买职位发布';
 				
			}else if($type == 23){//面试邀请
			    
			    $order_info      = unserialize($order['order_info']);
			    
				if($usertype == 2){
				    $inviteNum  =   array('+', 1);
				}
				$nid    =   $statisM -> upInfo(array('invite_resume' => $inviteNum), array('uid' => $uid, 'usertype' => $usertype));
				
				if ($order_info['invite_integral'] && $order['integral']) { 
				    
				    $tvalue['integral']	   =   array('+' , $order['integral']);
				    
				    $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
				    
				    $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买面试邀请',1,2,true);
				    
				    $statisM ->upInfo(array('integral' => array('-', $order_info['invite_integral'])), array('uid' => $uid, 'usertype' => $usertype));
				    
				    $integralM -> insert_company_pay($order_info['invite_integral'],2,$uid,$order['usertype'],"面试邀请，扣除".$this->config['integral_pricename'],1,2,false);
				    
				    $warningM -> warning(4, $uid); //充值预警提醒
				}
				$sendMail = 1;//确定发送邮件状态
 				$sendInfo['info'] = '购买面试邀请';
 				
			}else if($type == 24){//兼职推荐
			    
			    $order_info      = unserialize($order['order_info']);
			    
			    if($order_info['jobid']){
			        //查询该职位
			        $partM       =   $this -> MODEL('part');
			        $partA       =   $partM -> getInfo(array('id' => intval($order_info['jobid'])),array('field'=>'`id`,`name`,`rec_time`'));
			        $recJob      =   $partA['info'];    
					
					if(!empty($recJob)){
					    
					    $rec_time  =   $recJob['rec_time'] > time() ? array('+', $order_info['days'] * 86400) :  strtotime('+'.$order_info['days'].' day');
					    
					    $nid       =   $partM -> upInfo(array('rec_time' => $rec_time), array('id' => intval($order_info['jobid']), 'uid' => $uid));
                        
					    if ($order_info['recpart_integral'] && $order['integral']) {
					        
					        $tvalue['integral']	   =   array('+' , $order['integral']);
					        
					        $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，购买兼职推荐',1,2,true);
					        
					        $statisM ->upInfo(array('integral' => array('-', $order_info['recpart_integral'])), array('uid' => $uid, 'usertype' => $usertype));
					        
					        $integralM -> insert_company_pay($order_info['recpart_integral'],2,$uid,$order['usertype'],"推荐兼职，扣除".$this->config['integral_pricename'],1,2,false);
					        
					        $warningM -> warning(4, $uid); //充值预警提醒
					    }
					    
					    $logM -> addMemberLog($uid, $usertype, '购买推荐兼职：'.$recJob['name'],9,4);
 					}
 				}
				$sendInfo['info'] = '兼职推荐';
				
			}else if($type == 25){//店铺招聘
 				
			    if($order['once_id']){
 				    
 				    $onceM =   $this -> MODEL('once');
 				    $once  =   $onceM  -> getOnceInfo(array('id' => intval($order['once_id'])), array('field'=>'`pay`'));
 				    
					if(!empty($once)){
	 				
					    $nid   =   $onceM -> upOnce(array('pay' => '2'), array('id' => intval($order['once_id'])));
 					}
				}
				
 			}else if($type == 28){ //招聘会报名 
			    
                $order_info =   unserialize($order['order_info']);
                if ($order_info['zid']) {
                    $zid    =   intval($order_info['zid']);
                    $bmData =   array(
                        'comid'	    =>	$order_info['uid'],
                        'zphid'	    =>	$zid,
                        'bid'       =>  $order_info['bid'],
                        'sid'       =>  $order_info['sid'],
                        'cid'       =>  $order_info['cid'],
                        'jobid'     =>  $order_info['jobid'],
                        'ctime'     =>  time(),
                        'status'    =>  0,
                        'price'     =>  $order['order_price'],
                        'com_name'  =>  $order_info['com_name'],
                        'jobid'     =>  $order_info['jobid']
                    );
                    $zphM   =   $this -> MODEL('zph');
                     
                    $zphCom =   $zphM -> getZphComInfo(array('uid' => $uid, 'zid' => $zid));
                    
                    if (empty($zphCom)) {
                        $nid = $zphM -> addZCom($bmData);
                        
                        if ($nid) {
                            $order_info 	=	 unserialize($order['order_info']);
                            
                            if ($order_info['zph_integral'] && $order['integral']) { // 充值积分报名招聘会
                                
                                $tvalue['integral']	   =   array('+' , $order['integral']);
                                
                                $statisM -> upInfo($tvalue, array('uid' => $uid, 'usertype' => $usertype));
                                
                                $integralM -> insert_company_pay($order['integral'],2,$uid,$order['usertype'],"充值".$this->config['integral_pricename'].'，预定招聘会',1,2,true);
                                 
                                $statisM -> upInfo(array('integral' => array('-', $order_info['zph_integral'])), array('uid' => $uid, 'usertype' => $usertype));
                                
                                $integralM -> insert_company_pay($order_info['zph_integral'],2,$uid,$order['usertype'],"预定招聘会，扣除".$this->config['integral_pricename'],1,2,false);
                                
                                $warningM -> warning(4, $uid); //充值预警提醒
                            }
                        }
                    }
                }
			    $sendInfo['info'] = '报名招聘会'; 
             }
			 
			 if($nid){
				//微信通知
				$orderM -> upInfo($order['id'], array('order_state' => '2'));
				
				
				if($sendMail==1){
				    $notice =   $this->MODEL('notice');
				    $notice -> sendEmailType($emaildata);
				    $notice -> sendSMSType($emaildata);
				}
				
				if($order['type']=='2'){
					$integralM  =  $this->MODEL('integral');
					$integralM  -> insert_company_pay($order['integral'],2,$order['uid'],$order['usertype'],"购买".$this->config['integral_pricename'],1,2,true);
				}
				return 2;
			}
		}else{
			return $order['order_state'];
		
		}
	}
	function getOrder($id){
    	if (! preg_match('/^[0-9]+$/', $id)) {
            return array();
            
        } else {
            $orderM =   $this -> MODEL('companyorder');
            
            $order  =   $orderM -> getInfo(array('id' => $id));
            
            return $order;
        }
    }
}

?>