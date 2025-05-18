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
class transfer_model extends model{
    
 
	public function setTransfer($uid,$data=array()){
		
		

		$uid	=	intval($uid);
		
		if($uid && $data['oldpass'] && $data['username'] && $data['password']){
			
			include_once('userinfo.model.php');
	            
	        $userinfoM    =   new userinfo_model($this->db, $this->def);

			if(mb_strlen($data['username'])<2||mb_strlen($data['username'])>16){

				$return['msg']			=		'新用户名应在2-16位字符之间！';
			}
			elseif(CheckRegUser($data['username'])==false && CheckRegEmail($data['username'])==false){

				$return['msg']			=		'新用户名不得包含特殊字符！';
			}
			else{

				$usernameNum 				= 		$userinfoM->getMemberNum(array("username"=>$data['username']));

				if($usernameNum>0){

					$return['msg']			=		'用户名已存在，请重新输入！';
				}elseif(mb_strlen($data['password'])<6||mb_strlen($data['password'])>20){
					$return['msg']				=		'新密码长度应在6-20位！';
				}
			}
			
			if(!$return['msg']){
			
				$infoData['filed']	=	"`uid`,`username`,`password`,`salt`,`email`,`moblie`,`email_status`,`moblie_status`,`wxid`,`wxopenid`,`unionid`,`qqid`,`unionid`,`sinaid`";
				
				$memberInfo		=	$userinfoM -> getInfo(array('uid' => $uid),$infoData['filed']);
				
				$res			= 	passCheck($data['oldpass'],$memberInfo['salt'],$memberInfo['password']);
				
				if($res){
								 
					 if($data['bdtype']){

						 $jbType	=	$data['bdtype'];
						 foreach($jbType as $key=>$value){

							if($value == 'moblie'){

								$companyData['moblie_status']	= '0';
								$companyData['linktel']			= '';

								$lxData['moblie_status']	=	'0';
								$lxData['moblie']			=	'';

								$memberData['moblie']			=	'';
								$memberData['moblie_status']	=	'0';

								$adata['moblie']				=		$memberInfo['moblie'];
								$adata['moblie_status']			=		$memberInfo['moblie_status'];



							}elseif($value == 'email'){

								$companyData['email_status']	= '0';
								$companyData['linkmail']		= '';

								$lxData['email_status']			=	'0';
								$lxData['email']				=	'';

								$memberData['email']			=	'';
								$memberData['email_status']		=	'0';

								$adata['email']					=	$memberInfo['email'];
								$adata['email_status']			=	$memberInfo['email_status'];

							}elseif($value == 'qqid'){

								$memberData['qqid']			=	'';
								$memberData['qqunionid']	=	'';

								$adata['qqid']				=	$memberInfo['qqid'];
								$adata['qqunionid']			=	$memberInfo['qqunionid'];

							}elseif($value == 'wxid'){

								$memberData['wxid']			=	'';
								$memberData['wxopenid']		=	'';
								$memberData['unionid']		=	'';

								$adata['wxid']				=	$memberInfo['wxid'];
								$adata['wxopenid']			=	$memberInfo['wxopenid'];
								$adata['unionid']			=	$memberInfo['unionid'];

							}elseif($value == 'sinaid'){

								$memberData['sinaid']		=	'';

								$adata['sinaid']			=	$memberInfo['sinaid'];
								
								
							}
						}
						$this -> update_once('company',$companyData,array('uid'=>$uid));

						$this -> update_once('member',$memberData,array('uid'=>$uid));
					}

					 if(!$jbType){
						$newResume['email']			=	'';
						$newResume['email_status']	=	'0';
						$newResume['telphone']		=	'';
						$newResume['moblie_status']	=	'0';
					 }else{
						if(!in_array('email',$jbType)){
							$newResume['email']			=	'';
							$newResume['email_status']	=	'0';
						}
						if(!in_array('moblie',$jbType)){
							$newResume['telphone']			=	'';
							$newResume['moblie_status']		=	'0';
						}
					 }
					 if($newResume){
						$this -> update_once('resume',$newResume,array('uid'=>$uid));
					 }

					$salt 						= 		substr(uniqid(rand()), -6);
					$pass 						= 		passCheck($data['password'],$salt);

					$adata['username']				=		$data['username'];
					$adata['password']				=		$pass;

					$adata['did']					=		0;
					$adata['status']				=		$memberInfo['status'];
					$adata['salt']					=		$salt;
					$adata['source']				=		$memberInfo['source'];
					$adata['reg_date']				=		time();
					$adata['reg_ip']				=		$memberInfo['reg_ip'];
					$adata['usertype']				=		1;
				
					$userid							=		$this->insert_into('member',$adata);

					if(!$userid){
						$user_id 					= 		$userinfoM->getInfo(array("username"=>$data['username']),array("field"=>"uid"));
						$userid 					= 		$user_id['uid'];
					}

					if($userid){
						$nid = $this -> filterTableUid($userid,$uid);
						$return['msg']		=	'账户分离成功！';
						$return['errcode']	=	1;
					}else{
						$return['msg']	=	'账户分离失败，请重试！';
					}
						 
				}else{

					$return['msg']	=	'原账户密码错误！';
				
				}
			}
		}else{
			$return['msg']	=	'请正确填写相关信息！';
		}

		$return['errcode'] = $return['errcode'] ? $return['errcode'] :0;

		return $return;
	
	}

	//迁移数据UID
	private function filterTableUid($uid,$olduid){
		
		$tableUidList = array('resume','resume_cert','resume_cityclass','resume_doc','resume_edu','resume_expect','resume_jobclass','resume_other','resume_project','resume_show','resume_skill','resume_training','resume_work','resumeout','user_resume','userid_job','userid_msg','down_resume','fav_job','look_job','look_resume','member_statis','msg','part_apply','part_collect','talent_pool');
		

		foreach($tableUidList as $value){
			
			$this -> update_once($value,array('uid'=>$uid),array('uid'=>$olduid));
		
		}

		$tableUsertypeList = array('atn','change','company_order','evaluate_leave_message','finder','login_log','member_log','member_reg','member_withdraw','rebates');
		
		foreach($tableUsertypeList as $value){
			
			$this -> update_once($value,array('uid'=>$uid),array('uid'=>$olduid,'usertype'=>1));
		
		}

		$this -> update_once('blacklist',array('c_uid' =>$uid),array('c_uid'=>$olduid,'usertype'=>1));

		$this -> update_once('company_pay',array('com_id'=>$uid),array('com_id'=>$olduid,'usertype'=>1));

		$this -> update_once('report',array('p_uid'=>$uid),array('p_uid'=>$olduid,'usertype'=>1));

		$this -> update_once('sysmsg',array('f_uid'=>$uid),array('f_uid'=>$olduid,'usertype'=>1));

		$this -> update_once('recommend',array('uid'=>$uid),array('uid'=>$olduid,'rec_type'=>2));
  
		return $nid;

	}
}
?>