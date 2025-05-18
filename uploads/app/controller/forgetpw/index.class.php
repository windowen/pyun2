<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class index_controller extends common{
	function index_action(){
		$this->seo("forgetpw");
		$this->yun_tpl(array('index'));
	}
	function half_replace($str,$encoding='utf-8'){
        
		$strlen     	= 	mb_strlen($str, 'utf-8');
		$firstStr     	= 	mb_substr($str, 0, 1, 'utf-8');
		$lastStr    	= 	mb_substr($str, -1, 1, 'utf-8');
		return $strlen 	== 	2 ? $firstStr . str_repeat('*', mb_strlen($str, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
        
	}
	function editpw_action(){
        $username	=	$_POST['username'];
        $uid		=	$_POST['uid'];
		$mobile		=	$_POST['mobile'];
		$email		=	$_POST['email'];
		$code		=	$_POST['code'];

        if($username!='' && $uid!=''){
			$userinfoM		=	$this->MODEL("userinfo");
			$companyM		=	$this->MODEL("company");
			$noticeM		=	$this->MODEL('notice');
			$pwmsg 	   		=   regPassWordComplex($_POST['password']);
			if($email!=''){
				$info 		= 	$userinfoM->getInfo(array('email'=>$email),array("field"=>"`uid`,`username`,`email`"));
				$check		=	$info['email'];
			}elseif($mobile!=''){
				$info 		= 	$userinfoM->getInfo(array('moblie'=>$mobile),array("field"=>"`uid`,`username`,`moblie`"));
				$check		=	$info['moblie'];
			}
			$cert 			= 	$companyM->getCertInfo(array("uid"=>$info['uid'],"type"=>"7","check"=>$check,'orderby'=>'ctime,desc'),array("field"=>"`uid`,`check2`,`ctime`,`id`"));
			
			$codeTime       =   $noticeM -> checkTime($cert['ctime']);
			
			if($uid != $cert['uid']){
				$res['msg']		=	"参数错误，请重试！";
			    $res['error']	=	'8';
			    echo json_encode($res);die;
			
			}elseif (!$codeTime) {
			    
			    $res['msg']		=	"短信验证码验证超时，请重新验证！";
			    $res['error']	=	'8';
			    echo json_encode($res);die;
			    
			}else if(($code!=$cert['check2'])||(!$cert)){
				
				$res['msg']		=	"验证码错误";
				$res['error']	=	'8';
				echo json_encode($res);die;
				
			}else if($pwmsg!=''){
				$res['msg']		=	$pwmsg;
				$res['error']	=	'8';
				echo json_encode($res);die;
			}else{

				$info 		= 	$userinfoM->getInfo(array('uid'=>$uid),array("field"=>"`uid`,`username`,`email`,`moblie`,`name_repeat`"));
				
				if ($username==$info['username']){
					
					$password 		= 	$_POST['password'];
					
					if($this->config[sy_uc_type]=="uc_center" && $info['name_repeat']!="1"){
						
					    $this->obj->uc_open();
						uc_user_edit($info[username], "", $password, $info['email'],"0");
						
					}else{
						$userinfoM->upInfo(array("uid"=>$uid),array("password"=>$password));
					}
					$res['msg']		=	'密码修改成功！';
					$res['error']	=	0;
					echo json_encode($res);die;
				}else{
					$res['msg']		=	'没有该用户';
				}
			}
        }else{
            $res['msg']				=	'对不起,没有该用户';
        }
        echo json_encode($res);die;
    }
    
    function sendCode_action(){
 	    $sendtype 		= 	$_POST['sendtype'];
        $noticeM 		= 	$this->MODEL('notice');
		
	    if ($sendtype=='mobile') {
	        $sended 	= 	$_POST['mobile'];
			$type		=	'msg';
	    }elseif ($sendtype=='email'){
	        $sended 	= 	$_POST['email'];
	        $type		=	'email';
	    }
		
		$result 		= 	$noticeM->sendCode($sended, 'getpass', 1, array(), 6 , 120, $type);
	    echo json_encode($result);exit();
	}
	
	function checksendcode_action(){
		$moblie		=	$_POST['mobile'];
		$email		=	$_POST['email'];
		
		$userinfoM	=	$this->MODEL("userinfo");
		$companyM	=	$this->MODEL("company");
		$noticeM	=	$this->MODEL("notice");
		
		if($_POST['sendtype']=='email'){
			
		    $info 	= 	$userinfoM->getInfo(array('email'=>$email),array("field"=>"`uid`,`username`,`email`"));
			$check	=	$info['email'];
			
		}elseif($_POST['sendtype']=='mobile'){
			
		    $info 	= $userinfoM->getInfo(array('moblie'=>$moblie),array("field"=>"`uid`,`username`,`moblie`"));
			$check	=	$info['moblie'];
		}
		$cert		= 	$companyM->getCertInfo(array("uid"=>$info['uid'],"type"=>"7","check"=>$check,'orderby'=>'ctime,desc'),array("field"=>"`uid`,`check2`,`ctime`,`id`"));
		$codeTime   =   $noticeM -> checkTime($cert['ctime']);
		
		if (!$codeTime) {
		    
		    $res['msg']		=	"短信验证码验证超时，请重新验证！";
		    $res['error']	=	'8';
		    echo json_encode($res);die;
		    
		}else  if(($_POST['code']!=$cert['check2'])||(!$cert)){
		    $res['msg']		=	"验证码错误";
		    $res['error']	=	'8';
		    echo json_encode($res);die;
		}
		$res['msg']			=	"验证码正确！";
		$res['error']		=	0;
		$res['uid']			=	$info['uid'];
		$res['username']	=	$info['username'];
		echo json_encode($res);die;
	}
	
	function checklink_action(){
	    $_POST		=	$this->post_trim($_POST);
	    $username	=	$_POST['username'];
		$userinfoM	=	$this->MODEL("userinfo");

		if(CheckRegUser($username)==false && CheckRegEmail($username)==false){
	        $data['msg']	=	"用户名包含特殊字符！";
	        $data['error']	=	'8';
	        echo json_encode($data);die;
	    }

		$member 	= 	$userinfoM->getInfo(array('username'=>$_POST['username']), array('field' => '`uid`, `username`, `pid`'));
		
	    if(empty($member)){
			$data['msg']	=	"用户名不存在！";
			$data['error']	=	'8';
	        echo json_encode($data);die;
		}
	  
		if(!empty($member['pid'])){
			$data['msg']	=	"请联系主账号修改！";
			$data['error']	=	'8';
	        echo json_encode($data);die;
		}

	    $shensu		=	$_POST['linkman'].'-'.$_POST['linkphone'].'-'.$_POST['linkemail'];
		
	    $nid 		= 	$userinfoM->upInfo(array('uid' => $member['uid']), array('appeal'=>$shensu,'appealtime'=>time(),'appealstate'=>'1'));
		
	    if($nid){
	        $data['error']	=	'0';
	        echo json_encode($data);die;
	    }
	}
	
}
?>