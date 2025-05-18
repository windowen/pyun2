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
class emailconfig_controller extends adminCommon{
	function index_action(){
		
		$emailM			=	$this->MODEL('email');
		
		$where['id']	=	array('>','0');
		
		$List 			= 	$emailM->getList($where);
		
		$this->yunset('emailconfig',$List['list']);
		
		$this->yuntpl(array('admin/admin_email_config'));
		
	}
	
	//保存
	function save_action(){
		
		$emailM		=	$this->MODEL('email');
		
		$configM	=	$this->MODEL('config');
		
 		if($_POST['config']){
		
			$configM->setConfig(
				array(
					'sy_email_online'	=>	$_POST['sy_email_online'],
					'sy_email_set'		=>	'1'
				)
			);
			
			if($_POST['smtpserver']){
				
				for($i=0;$i<count($_POST['smtpserver']);$i++){
					
					$addData['smtpserver'] 	= 	$_POST['smtpserver'][$i];
					
					$addData['smtpuser'] 	= 	$_POST['smtpuser'][$i];
					
					$addData['smtppass'] 	= 	$_POST['smtppass'][$i];
					
					$addData['smtpport'] 	= 	$_POST['smtpport'][$i];
					
					$addData['smtpnick'] 	= 	$_POST['smtpnick'][$i];
					
					if($addData['smtpserver'] && $addData['smtpuser'] && $addData['smtppass']){
						
						$addData['default'] 	= 	'1';
						
						$emailM->addInfo($addData);
						
					}
				}
			}
			if($_POST['emailid']){
				foreach($_POST['emailid'] as $value){
					
					$upData['smtpserver'] 	= 	$_POST['smtpserver_'.$value];
					
					$upData['smtpuser'] 	= 	$_POST['smtpuser_'.$value];
					
					$upData['smtppass'] 	= 	$_POST['smtppass_'.$value];
					
					$upData['smtpport'] 	= 	$_POST['smtpport_'.$value];
					
					$upData['smtpnick'] 	= 	$_POST['smtpnick_'.$value];
					
					$upData['smtpnum'] 		= 	$_POST['smtpnum_'.$value];
					
					$upData['smtpnick'] 	= 	$_POST['smtpnick_'.$value];
					
					$upData['default'] 		= 	$_POST['default_'.$value];
					
					if($upData['smtpserver'] && $upData['smtpuser'] && $upData['smtppass']){
						
						$upWhere['id']		=	$value;
						
						$emailM->upInfo($upWhere,$upData);
					
					}
				
				}
			
			}
			$this->get_cache();

			$this->web_config();
			
			$this->ACT_layer_msg("邮件服务器设置成功！",9,1,2,1);
		}
		
		
		//配置阿里云邮件推送
		if($_POST['aliconfig']){
			
			$aliData['sy_email_online']	=	$_POST['sy_email_online'];

			$aliData['sy_email_set']	=	1;

			$aliData['accesskey']		=	$_POST['accesskey'];
			
			$aliData['accesssecret']	=	$_POST['accesssecret'];
			
			$aliData['ali_email']		=	$_POST['ali_email'];
			
			$aliData['ali_tag']			=	$_POST['ali_tag'];
			
			$aliData['ali_name']		=	$_POST['ali_name'];
			
			$configM->setConfig($aliData);

			$this->web_config();

			$this->ACT_layer_msg("邮件服务器设置成功！",9,1,2,1);
			
		}
		
	}
	
	function tpl_action(){
		
		$this->yuntpl(array('admin/admin_email_tpl'));
		
	}
	
	function tplsave_action(){
		
		$configM	=	$this->MODEL('config');
		
	    if($_POST['config']){
			
	        unset($_POST["config"]);
			
			$configM->setConfig($_POST);
			
			$this->web_config();
			
	        $this->ACT_layer_msg( "邮箱模板配置设置成功！",9,1,2,1);	    
	    }
	}
	
	function settpl_action(){
		
		include(CONFIG_PATH."db.tpl.php");
		
		$this->yunset("arr_tpl",$arr_tpl);
		
		$templatesM	=	$this->MODEL("templates");
		
		
		if($_POST['config']){
			
			$configNum	=	$templatesM->getNum(array('name'=>trim($_POST['name'])));
				
			$content	= 	str_replace("amp;nbsp;","nbsp;",$_POST['content']);
			
			if($configNum>0){
				
				$templatesM->upInfo(array('name'=>trim($_POST['name'])),array('content'=>$content,'title'=>trim($_POST['title'])));
				
			}else{
				
				$templatesM->addInfo(array('name'=>trim($_POST['name']),'content'=>$content,'title'=>trim($_POST['title'])));
			
			}
			
			$this->ACT_layer_msg( "模版配置设置成功！",9,$_SERVER['HTTP_REFERER'],2,1);
			
		}
		
		$row	=	$templatesM->getInfo(array('name'=>$_GET['name']));
		
		$this->yunset("row",$row);
		
		$this->yuntpl(array('admin/admin_esettpl'));
	}
	
	function ceshi_action(){
		
		$notice = $this->MODEL('notice');
		
 		if($_POST["ceshi_email"]){
			
			//发送邮件并记录入库
			$emailData['smtpServerId'] 	= 	$_POST["id"];
			$emailData['email'] 		= 	$_POST["ceshi_email"];
			
			$emailData['subject'] 		= 	$this->config[sy_webname]." - 测试邮件";
			
			$emailData['content'] 		= 	"恭喜你，该邮件帐户可以正常使用<br> ".$this->config['sy_webname']."- Powered by PHPYun.";
			
			$sendid = $notice->sendEmail($emailData);
			
			if($sendid['status'] != -1){
				
				$data['msg']='测试发送成功！';
				
				$data['type']='9';
			
			}else{
				
				$data['msg']='测试发送失败！' . $sendid['msg'];
				
				$data['type']='8';
			
			}
			
			echo json_encode($data);
		 
		 }
	}
	
	function delconfig_action(){
		
		$emailM	=	$this->MODEL('email');
		
 		if($_POST["id"]){
			
			$emailConfig	= 	$emailM->getInfo(array('id'=>(int)$_POST["id"]));
			
			//查询邮件服务器数量
			$num 			= 	$emailM->getNum(array('default'=>'1'));
			
			if($emailConfig['default']=='1' && $num<2){
				
				$data['msg']		=	'请至少保留一组可用邮箱！';
				
				$data['type']		=	'8';
				
			}else{
				
				$emailM->delEmail(array('id'=>(int)$_POST["id"]),array('type'=>'one'));
				
				$data['msg']	=	'删除成功！';
				
				$data['type']	=	'9';
				
				$this->get_cache();
			}
			
			echo json_encode($data);
			
		}
	}
	
	function get_cache(){
		
		include(LIB_PATH."cache.class.php");
		
		$cacheclass	= 	new cache(PLUS_PATH,$this->obj);
		
		$makecache	=	$cacheclass->emailconfig_cache("emailconfig.cache.php");
	}
	
}
?>