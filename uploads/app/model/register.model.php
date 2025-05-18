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
class register_model extends model{
	/**
     * @desc   引用log类，添加用户日志
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        
        require_once ('log.model.php');
        
        $LogM = new log_model($this->db, $this->def);
        
        return  $LogM -> addMemberLog($uid, $usertype, $content, $opera, $type);
        
    }
	/**
	 * 验证注册输入内容：username、email、realname
	 * $data 自定义处理数组
	 */
	public function ajaxReg($data=array()){
		
		$post		=	$data['post'];
		if($post['username']){
			$key_name 	=	'username';
		}elseif($post['email']){
			$key_name 	=	'email';
		}elseif($post['password']){
			$key_name 	=	'password';
		}
		if($key_name=="username"){//验证注册用户名
			
			$username		= 	$post['username'];

			$username 	=	str_replace('！', '!', $username);
			
			$msg		=	regUserNameComplex($username);//检测用户名复杂度

			if($this->config['sy_uc_type']=="uc_center"){
				include APP_PATH.'data/api/uc/config.inc.php';
				include APP_PATH.'/api/uc/include/db_mysql.class.php';
				include APP_PATH.'/api/uc/uc_client/client.php';
				$user 		= 	uc_get_user($username);
			}else{
				$user 		= 	$this->select_num('member',array("username"=>$username));
			}

			if($msg!=''){
				$return['msg']		=	$msg;
				$return['errcode']	=	4;
			}elseif($user){
			
				$return['msg']		=	'用户名已存在！';
				$return['errcode']	=	1;
			}elseif(CheckRegUser($username)==false && CheckRegEmail($username)==false){

				$return['msg']		=	'用户名不得包含特殊字符！';
				$return['errcode']	=	2;
			}elseif($this->config['sy_regname']!=""){//限制注册用户名
			
				$regname	=	@explode(",",$this->config['sy_regname']);
				
				if(in_array($username,$regname)){
					$return['msg']		=	'该用户名已被禁止注册！';
					$return['errcode']	=	3;
				}else{
					$return['msg']		=	'填写正确！';
					$return['errcode']	=	0;
				}
			}else{
				$return['msg']		=	'填写正确！';
				$return['errcode']	=	0;
			}
		}elseif($key_name=="email"){//验证注册邮箱
			
			$user 		= 	$this->select_num('member',array("email" => $post['email'], "username" => array('=', $post['email'], 'OR')));
			
			if(CheckRegEmail($post['email'])==false){
				$return['msg']		=	'邮箱格式错误！';
				$return['errcode']	=	2;
			}elseif($user){
				$return['msg']		=	'邮箱已被使用！';
				$return['errcode']	=	1;
			}else{
				$return['msg']		=	'填写正确！';
				$return['errcode']	=	0;
			}
		}elseif($key_name == 'realname'){//验证注册真实姓名
			
			$realname=$post['realname'];
			
			if($this->config['sy_regname']!=""){//限制注册用户名
			
				$regname=@explode(",",$this->config['sy_regname']);
				
				if(in_array($realname,$regname)){
					$return['msg']		=	'该姓名已被禁止使用！';
					$return['errcode']	=	3;
				}
			}
			if($this->config['sy_fkeyword']!=""){//过滤关键词
				
				$fkeyword =@explode(",",$this->config['sy_fkeyword']);
				
				if(in_array($realname,$fkeyword)){
					$return['msg']		=	'该真实姓名包含敏感词！';
					$return['errcode']	=	3;
				}
			}
		}else if($key_name=="password"){//验证密码复杂度
			
			$password		= 	$post['password'];

			$msg	=	regPassWordComplex($password);//检测密码复杂度

			if($msg!=''){
				$return['msg']		=	$msg;
				$return['errcode']	=	4;
			}else{
				$return['msg']		=	'填写正确！';
				$return['errcode']	=	0;
			}
		}
		
		return	$return;
	}
	function regMoblie($post=array()){
	    if($post['moblie'] || $post['email']){
			if($this->config['sy_web_mobile']!="" && $post['moblie']){//限制会员手机号
				
				$regnamer=@explode(";",$this->config['sy_web_mobile']);
				
				if(in_array($post['moblie'],$regnamer)){
					$return['errcode']	=	2;//该手机号已被禁止使用！
				
					return $return;
				}
			}
			require_once ('userinfo.model.php');
			$userinfoM = new userinfo_model($this->db, $this->def);
			
			if($post['moblie']){//验证moblie

				$resume_info 	= 	$userinfoM->getUserInfo(array('telphone'=>$post['moblie'],'moblie_status'=>1),array('usertype'=>'1','field'=>"uid,name"));
				
				$company_info 	= 	$userinfoM->getUserInfo(array('linktel'=>$post['moblie'],'moblie_status'=>1),array('usertype'=>'2','field'=>"uid,name"));
				
				
				$m_info 		= 	$userinfoM->getInfo(array('moblie'=>$post['moblie'],'username'=>array('=',$post['moblie'],'OR')),array('field'=>"`uid`,`usertype`,`username`,`moblie`"));
				
			}elseif($post['email']){//验证email
				
				$resume_info 	= 	$userinfoM->getUserInfo(array('email'=>$post['email'],'email_status'=>1),array('usertype'=>'1','field'=>"uid,name"));
			
				$company_info 	= 	$userinfoM->getUserInfo(array('linkmail'=>$post['email'],'email_status'=>1),array('usertype'=>'2','field'=>"uid,name"));
				
				
				$m_info 		= 	$userinfoM->getInfo(array('email'=>$post['email'],'username'=>array('=',$post['email'],'OR')),array('field'=>"`uid`,`usertype`,`username`,`email`"));
			}
			if($resume_info){
				
				$data['name']		=	$resume_info['name'];
				$data['uid']		=	$resume_info['uid'];
				$data['usertype']	=	'1';
				
			}else if($company_info){
				
				$data['name']		=	$company_info['name'];
				$data['uid']		=	$company_info['uid'];
				$data['usertype']	=	'2';
				
			}else if($m_info){
				
				if($m_info['usertype']!='3'){
					
					$info 	= 	$userinfoM->getUserInfo(array("`uid`= '".$m_info['uid']."'"),array('usertype'=>$m_info['usertype'],'field'=>"name"));
					
					$data['name']	=	$info['name'];
					
				}else{
					
					$info 	= 	$userinfoM->getUserInfo(array("`uid`= '".$m_info['uid']."'"),array('usertype'=>$m_info['usertype'],'field'=>"realname"));
					
					$data['name']	=	$info['realname'];
					
				}
				$data['uid']		=	$m_info['uid'];
				$data['usertype']	=	$m_info['usertype'];
			}
			if(!empty($data)){
				$return['errcode']	=	9;
				$return['data']		=	$data;
			}else{
				$return['errcode']	=	0;//填写正确！
			}
			
			return $return;
		}
	}
	function writtenOff($data=array()){
		if($data['code']){
			session_start();
		
			if(md5(strtolower($data['code']))!=$_SESSION['authcode'] || empty($_SESSION['authcode'])){
				unset($_SESSION['authcode']);
				$return['errcode']	=	3;
				$return['msg']		=	'验证码错误！';
			}
		}
		$user 	= 	$this->select_once('member',array("uid" => $data['zyuid']),'username,moblie,email,uid,password,salt,usertype');
		
		if(!passCheck($data['pw'],$user['salt'],$user['password'])){
			
			$return['errcode']	=	2;
			$return['msg']		=	'密码错误！';
		}else{
			
			if($data['mobile']!=""){
				
				if($user['username'] == $data['mobile']){
					$str 	= 	md5(uniqid(mt_rand(), true));   
					$new 	= 	"yun".substr($str,0,8);
					$newid 	= 	$this->update_once("member",array('username'=>$new,'restname'=>0),array('uid'=>$data['zyuid']));
				}
				
				if($user['moblie'] == $data['mobile']){
					
					$nid	=	$this->update_once('member', array('moblie'=>'', 'moblie_status'=>0), array('uid'=>$data['zyuid']));
					$this->update_once('resume',array('telphone'=>'', 'moblie_status'=>0), array('uid'=>$data['zyuid']));
					
					$this->update_once('resume_expect',array('state' => 0), array('uid'=>$data['zyuid']));
					
					$this->update_once('company',array('linktel'=>'', 'moblie_status'=>0), array('uid'=>$data['zyuid']));
				}
				
				if($newid){
					
					$this -> addMemberLog($user['uid'],$user['usertype'],'账号解绑：修改用户名《'.$user['username'].'》，新用户名《'.$new.'》',12,2);
				}
				if($nid){
					$this -> addMemberLog($user['uid'],$user['usertype'],'解除绑定手机：'.$data['mobile'],12,2);
				}
				
			}else if($data['email']!=""){
				
				if($user['username'] == $data['email']){
					$str 	= 	md5(uniqid(mt_rand(), true));   
					$new 	= 	"yun".substr($str,0,8);
					$newid 	= 	$this->update_once("member",array('username'=>$new,'restname'=>0),array('uid'=>$data['zyuid']));
				}
				
				if($user['email'] == $data['email']){
					
					$nid    =   $this->update_once("member",array('email'=>'','email_status' => 0),array('uid'=>$data['zyuid']));
					$this->update_once("resume",array('email'=>'','email_status'=>0),array('uid'=>$data['zyuid']));
			        $this->update_once("company",array('linkmail'=>'','email_status'=>0),array('uid'=>$data['zyuid']));
				}
				if($newid){
					
					$this -> addMemberLog($user['uid'],$user['usertype'],'账号解绑：修改用户名《'.$user['username'].'》，新用户名《'.$new.'》',12,2);
				}
				if($nid){
					$this -> addMemberLog($user['uid'],$user['usertype'],'解除绑定邮箱：'.$data['email'],12,2);
				}
			}
			$return['errcode']	=	1;
			$return['msg']		=	'解绑成功！';
		}
		return $return;
	}
	function regComName($data=array()){

		if($data['comname']){
			
			$company 	= 	$this-> select_all("company",array('name'=>array('like',trim($data['comname']))),"`uid`,`name`,`linkman`,`linkphone`,`linktel`");
			
			foreach ($company as $val){
				$uids[]	= 	$val['uid'];
			}
			
			$Member 	= 	$this-> select_all("member",array('uid'=>array('in',pylode(',',$uids))),"`uid`,`claim`,`source`,`email`"); 
						
			foreach($company as $key=>$val){
				foreach($Member as $k => $v){
					if($val['uid']==$v['uid']){
						$company[$key]['claim']		=	$v['claim'];
						$company[$key]['source']	=	$v['source'];
						$company[$key]['email']		=	$v['email'];
 					}
				}
			}
			$html	=	'';
 			if($company && is_array($company)){
 				foreach($company as $val){
 					
 					$val['name']		= 	str_replace($data['comname'],"<font color=\"#FF0000\">".$data['comname']."</font>",$val['name']);
 					$val['linkman']		= 	mb_substr($val['linkman'], 0,1)."**";
 					
 					if ($val['linktel']){
					    $val['linktel']	=	substr_replace($val['linktel'],'****',4,4);
					}elseif ($Info['linkphone']){
					    $val['linktel']	=	substr_replace($val['linkphone'],'****',4,4);
					}
					if($data['utype']=='pc'){
						$html.="<div class=\"reg_com_name_list\"><span class=\"reg_comname\">".$val['name']."</span><span class=\"reg_comuser\">".$val['linkman']."</span><span class=\"reg_comtel\">".$val['linktel']."</span><span class=\"reg_comcz\"><a href=\"".Url('forgetpw')."\" class=\"reg_combth\">申诉</a></span></div>";
					}elseif($data['utype']=='wap'){
						$html	.=	"<div class=\"reg_wap_comlist\"><div class=\" \">".$val['name']."</div><div class=\"reg_wap_comlist_tel\">联系人：".$val['linkman']."</br><div class=\"reg_wap_comlist_tel_n\">联系电话：".$val['linktel']."</div></div><a href=\"".Url('wap',array('c'=>'forgetpw'))."\" class=\"reg_wap_combth\">申诉</a></div>";
					}
				} 
				$return['data']		=	$html;
				$return['errcode']	=	9;
 			}else{
				$return['errcode']	=	0;
			}
			return $return;
		}
	}
	
}
?>