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
class admin_member_controller extends adminCommon{	 
	private function set_search(){
		include(CONFIG_PATH.'db.data.php');
		$source         =  $arr_data['source'];
		$search_list[]  =  array('param'=>'utype','name'=>'身份类型','value'=>array('1'=>'个人身份','2'=>'企业身份','5'=>'无身份类型'));
		$search_list[]  =  array('param'=>'status','name'=>'锁定状态','value'=>array('1'=>'已审核','2'=>'已锁定'));
		$search_list[]  =  array('param'=>'lotime','name'=>'最近登录','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
		$search_list[]  =  array('param'=>'adtime','name'=>'最近注册','value'=>array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));
		$search_list[]  =  array('param'=>'source','name'=>'数据来源','value'=>$source);
		$this->yunset('source',$source);
		$this->yunset('search_list',$search_list);
	}
	function index_action(){
		$this->set_search();
		
		$userinfoM	=	$this->MODEL('userinfo');
		if($_GET['utype']){
		    if($_GET['utype']=='5'){
		        
		        $where['usertype']    =	  '0';
		        
		    }else{
		        
		        $where['usertype']    =	  $_GET['utype'];
		        
		    }
		    $urlarr['utype']          =	  $_GET['utype'];
		}
		if($_GET['lotime']){
			if($_GET['lotime']=='1'){
				
				$where['login_date']	=	array('>',strtotime(date("Y-m-d 00:00:00")));
				
			}else{
				
				$where['login_date']	=	array('>',strtotime('-'.(int)$_GET['lotime'].'day'));
				
			}
			$urlarr['lotime']			=	$_GET['lotime'];
		}
		if($_GET['adtime']){
			if($_GET['adtime']=='1'){
				
				$where['reg_date']		=	array('>',strtotime(date("Y-m-d 00:00:00")));
				
			}else{
				
				$where['reg_date']		=	array('>',strtotime('-'.(int)$_GET['adtime'].'day'));
				
			}
			$urlarr['adtime']			=	$_GET['adtime'];
		}
		if($_GET['status']){
			
			$status						=	intval($_GET['status']);
			
			$where['status']			=	$status;
			
			$urlarr['status']			=	$status;
		}
		if($_GET['source']){
			
			$where['source']			=	intval($_GET['source']);
			
			$urlarr['source']			=	$_GET['source'];
		}
		if(trim($_GET['keyword'])){
			if($_GET['type']==1){
				
				$where['username']		=	array('like',trim($_GET['keyword']));
				
			}elseif($_GET['type']==2){
				
				$where['moblie']		=	array('like',trim($_GET['keyword']));
				
			}elseif($_GET['type']==3){
				
				$where['uid']			=	array('like',trim($_GET['keyword']));
				
			}elseif($_GET['type']==4){
				
				$where['email']			=	array('like',trim($_GET['keyword']));
				
			}
			$urlarr['keyword']			=	$_GET['keyword'];
			
			$urlarr['type']				=	$_GET['type'];
		}
		if($_GET['order']){
			$where['orderby']			=	$_GET['t'].','.$_GET['order'];
			
			$urlarr['order']			=	$_GET['order'];
			
			$urlarr['t']				=	$_GET['t'];
		}else{
			
			$where['orderby']			=	array('uid,desc');
			
		}
		
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('member',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
			
			$where['limit']	=  	$pages['limit'];
			
			$List			=	$userinfoM -> getList($where,array(),'admin');
			
			$this -> yunset('rows',$List);
		}
		$cacheM	=	$this -> MODEL('cache');
		
		$domain	=	$cacheM	-> GetCache('domain');
		
		$this -> yunset('Dname', $domain['Dname']);
		
		$this->yuntpl(array('admin/admin_member'));
	}
	
	function edit_action(){
		$memberM	=	$this->MODEL('userinfo');
		
		$cacheM		=	$this -> MODEL('cache');
		
		$member		=	$memberM->getInfo(array('uid'=>$_GET['uid']),array('sf'=>'1'));	
		
		$domain		=	$cacheM	-> GetCache('domain');
		
		$this -> yunset('Dname', $domain['Dname']);
		$this -> yunset('lasturl', 'index.php?m=admin_member');
		$this -> yunset('member', $member);
		$this -> yuntpl(array('admin/admin_member_edit'));
	}
	
	function editSave_action(){
		$_POST  	=  $this->post_trim($_POST);
		
		$memberM  	=  $this -> MODEL('userinfo');
	    
	    if($_POST['submit']){
			$uData	=  array(
				'uid'  		=>  intval($_POST['uid']),
	            'usertype'  =>  intval($_POST['usertype']),
	            'lasturl'   =>  $_POST['lasturl']
			);
			
	        $mData  =  array(
	            'username'  =>  $_POST['username'],
	            'password'  =>  $_POST['password'],
	            'moblie'    =>  $_POST['moblie'],
	            'email'     =>  $_POST['email'],
				'reg_ip'	=>	$_POST['reg_ip'],
				'did'		=>	$_POST['did'],
	            'status'    =>  $_POST['status'],
				'utype'		=>	'admin'
	        );
	        
	        $return   	=  	$memberM -> upMemberInfo($uData,$mData);
	        
			$this->ACT_layer_msg($return['msg'], $return['errcode'], $return['url'], 2, 1);
			
	    }
		
	}
	
	/**
	 * 会员用户列表:获取锁定、审核未通过原因
	 */
	function lockinfo_action(){
	    
	    $userinfoM  =  $this -> MODEL('userinfo');
	    
	    $member     =  $userinfoM -> getInfo(array('uid'=> $_GET['uid']),array('field'=>'lock_info'));
	    
	    echo trim($member['lock_info']);die;
	}
	
	/**
	 * 会员用户列表:会员锁定
	 */
	function lock_action(){
	    
	    $userinfoM  =  $this -> MODEL('userinfo');
	    
	    $post       =  array(
	        'status'     =>  intval($_POST['status']),
	        'lock_info'  =>  trim($_POST['lock_info'])
	    );
	    
	    $return     =  $userinfoM -> lock(array('uid'=>intval($_POST['uid'])),array('post'=>$post));
	    
	    $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员列表（页面统计数量）
	 */
	function memNum_action(){
		
		$MsgNum	=	$this->MODEL('msgNum');
		
		echo $MsgNum->memNum();
	}
	
	/**
	 * 会员列表（重置密码）
	 */
	function reset_pw_action(){
		
		$this -> check_token();
		
		$userinfoM  =  $this->MODEL('userinfo');
		
		$userinfoM -> upInfo(array('uid'=>intval($_GET['uid'])),array('password'=>'123456'));
		
		$this -> MODEL('log') -> addAdminLog('会员(ID:'.$_GET['uid'].')重置密码成功');
		
		echo '1';
	}
	function del_action(){
		
		$userinfoM	=	$this->MODEL('userinfo');
		
		if($_GET['del']){
			$del	=	$_GET['del'];
		}else{
			$del	=	$_POST['del'];
		}
		$return	=	$userinfoM->delMember($del);
			
		$this->layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 会员列表（分配分站）
	 */
	function checksitedid_action(){
		$siteM	=	$this -> MODEL('site');
		
		$return	=	$siteM -> memberSiteDid($_POST);
		
		$this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	//发送邮件
	function send_action(){ 
	
		$UserInfoM = $this->MODEL('userinfo');

		if($_POST['email_title']==''||$_POST['content']==''){
			$arr['msg']		=	"邮件标题均不能为空！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
 		} 
		
		$emailarr=$user=$com=$userinfo=array();
 		
		if($_POST['utype']!='5'){
			$userrows	=	$UserInfoM->getList(array('usertype'=>$_POST['utype']),array('field'=>'email,`uid`,`usertype`'));
		}else if($_POST['utype']=='5'){
			$email_user	=	@explode(',',$_POST['email_user']); 
			$email_user	=	array_filter($email_user);
			foreach($email_user as $v){
			    if(CheckRegEmail($v)){
			        $earr[]=$v;
			    }
			}
			if(!empty($earr)){
				$where['email']=array('in', "\"".@implode('","',$earr)."\"");
				$userrows	=	$UserInfoM->getList($where,array('field'=>'`email`,`uid`,`usertype`'));  
			}
		}
		if(is_array($userrows)&&$userrows){
			foreach($userrows as $v){
				
				if($v['usertype']=='1'){	$user[]	=	$v['uid'];}
				if($v['usertype']=='2'){	$com[]	=	$v['uid'];}
				
				$emailarr[$v['uid']]=$v["email"];
			}
 			
			if($user&&is_array($user)){
				$where['uid']	=	array('in',pylode(',',$user));
			}
			if($com&&is_array($com)){
				$where['uid']	=	array('in',pylode(',',$com));
			}
			
			$List	=	$UserInfoM->getUserList($where);
			
			foreach($List as $v){
				$userinfo[$v['uid']]=$v['name'];
			}
		} 
			
		if(!count($emailarr)){ 
			$arr['msg']		=	"没有符合条件的邮箱，请先检查！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}else{
			set_time_limit(10000);

			$pagesize	=	intval($_POST['pagelimit']);
			$sendok		=	intval($_POST['sendok']);
			$sendno		=	intval($_POST['sendno']?$_POST['sendno']:0);
			$value		=	intval($_POST['value']);

			//分批次发送
			$result		=	$this->send_email($emailarr,$_POST['email_title'],$_POST['content'],$userinfo,$pagesize,$sendok,$sendno,$value);
			
			if($result){
				$toSize = $pagesize * $result['page'];

				if(count($emailarr) > $toSize){
					$npage	=	$result['page'];
					$spage	=	$npage*$pagesize+1;
					$topage	=	($npage+1)*$pagesize;
					$name	=	$spage."-".$topage;
					
					$this->get_return("3",$npage,"正在发送".$name."封邮件",$result['sendok'],$result['sendno']);
				 
				}else{
					$this->get_return("1",$npage,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
 				}
			}
		}
 	}

	function send_email($email=array(),$emailtitle="",$emailcoment="",$userinfo=array(),$pagesize,$snedok,$snedno,$value){
		
		$notice = $this->MODEL('notice');
		$sendok = intval($sendok);
		$sendno = intval($sendno);

		if($value=='0'){
			$i = 1;
			foreach($email as $key => $v){
					
				if($i <=$pagesize){
					$emailData['email']		=	$v;
					$emailData['subject']	=	$emailtitle;
					$emailData['content']	=	stripslashes($emailcoment);
					$emailData['uid']		=	$key;
					$emailData['name']		=	$userinfo[$key];
					$emailData['cname']		=	"系统";
					if($v){
						$sendid = $notice->sendEmail($emailData);
					}
					if($sendid['status'] != -1){
						$state=1;
						$sendok++;
					}else{
						$state=0;
						$sendno++;
					}
				}
				$i++;
			}
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{
			$page	=	$value;
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			$i=1;

			foreach($email as $key=>$v){

				if($i > $start && $i <= $end){
					
					$emailData['email']		=	$v;
					$emailData['subject']	=	$emailtitle;
					$emailData['content']	=	stripslashes($emailcoment);
					$emailData['uid']		=	$key;
					$emailData['name']		=	$userinfo[$key]['name'];
					$emailData['cname']		=	"系统";
						
					if($v){
						$sendid = $notice->sendEmail($emailData);
					}
					if($sendid['status'] != -1){
						$state=1;
						$sendok++;
					}else{
						$state=0;
						$sendno++;
					}
				}
				$i++;
			}

			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
			 
		}
		return $result;
	}
	//发送短信
	function msgsave_action(){
		
		$userinfoM	=	$this->MODEL('userinfo');
		
		if(!checkMsgOpen($this -> config)){
			$arr['msg']		=	"还没有配置短信！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}
		if(trim($_POST['content'])==''){
			$arr['msg']		=	"请输入短信内容！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}
		if($_POST['userarr']=='' && $_POST['utype']=='5'){
			$arr['msg']		=	"手机号码不能为空！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}
		$uidarr=array();
		if($_POST['utype']==5){
			$mobliesarr	=	@explode(',',$_POST['userarr']);
			$userrows	=	$userinfoM->getList(array('moblie'=>array('in',$_POST['userarr'])),array('field'=>'`moblie`,`uid`,`usertype`'));		 
			$moblies	=	array();
			foreach($userrows as $v){
				$moblies[]=$v['moblie'];
			}  
		}else{
			$userrows	=	$userinfoM->getList(array('usertype'=>$_POST['utype']),array('field'=>'`moblie`,`uid`,`usertype`'));
		}

		if(is_array($userrows)&&$userrows){
			$user=$com=$userinfo=array();
			foreach($userrows as $v){
				
				if($v['usertype']=='1'){	$user[]	=	$v['uid'];}
				if($v['usertype']=='2'){	$com[]	=	$v['uid'];}
				
				$uidarr[$v['uid']]	=	$v["moblie"];
			}
			if($user&&is_array($user)){
				$where['uid']	=	array('in',pylode(',',$user));
			}
			if($com&&is_array($com)){
				$where['uid']	=	array('in',pylode(',',$com));
			}
			
			$List	=	$userinfoM->getUserList($where);
			
			foreach($List as $v){
				$userinfo[$v['uid']]=$v['name'];
			}
		}
		if($_POST['utype']==5){
			foreach($mobliesarr as $v){
				if(in_array($v,$moblies)==false&&CheckMoblie($v)){
					$uidarr[]=$v;
				}
			}
		}
 
		if(!count($uidarr)){ 
			$arr['msg']		=	"没有符合条件号码，请先检查！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}else{
			set_time_limit(10000);

			$pagesize	=	intval($_POST['pagelimit']);
			$sendok		=	intval($_POST['sendok']);
			$sendno		=	intval($_POST['sendno']?$_POST['sendno']:0);
			$value		=	intval($_POST['value']);

			//分批次发送
			$result		=	$this->sendDivMsg($uidarr,$_POST['content'],$userinfo,$pagesize,$sendok,$sendno,$value);
			
			if($result){
				$toSize = $pagesize * $result['page'];

				if(count($uidarr) > $toSize){
					$npage	=	$result['page'];
					$spage	=	$npage*$pagesize+1;
					$topage	=	($npage+1)*$pagesize;
					$name	=	$spage."-".$topage;
					
					$this->get_return("3",$npage,"正在发送".$name."条短信",$result['sendok'],$result['sendno']);
				}else{
					$this->get_return("1",$npage,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
				}
			}
			
 			
		}
	}

	function sendDivMsg($uidarr=array(),$content="",$userinfo=array(),$pagesize,$snedok,$snedno,$value){

		$notice = $this->MODEL('notice');
		$sendok = intval($sendok);
		$sendno = intval($sendno);
	   
		if($value=='0'){
			$i = 1;
			foreach($uidarr as $key => $v){
					
				if($i <= $pagesize){
					$msgData['mobile']	=	$v;
 					$msgData['content']	=	$content;
					$msgData['uid']		=	$key;
					$msgData['name']	=	$userinfo[$key];
					$msgData['cname']	=	"系统";
					$msgData['port']	=	'5';
					
					if($v){
						$sendid = $notice->sendSMS($msgData);
 					}
					if($sendid['status'] != -1){
						$sendok++;
					}else{
						$sendno++;
					}
				}
				$i++;
			}
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{
			$page	=	$value;
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			$i=1;

			foreach($uidarr as $key=>$v){

				if($i > $start && $i <= $end){
					
					$msgData['moblie']	=	$v;
 					$msgData['content'] =	$content;
					$msgData['uid']		=	$key;
 					$msgData['name']	=	$userinfo[$key]['name'];
					$msgData['cname']	=	"系统";
					$msgData['port']	=	'5';	

					if($v){
						$sendid = $notice->sendSMS($msgData);
					}
					if($sendid['status'] != -1){
 						$sendok++;
					}else{
 						$sendno++;
					}
				}
				$i++;
			}

			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
			 
		}
		return $result;
	}
	
	//Other
	function get_return($status,$value,$msg,$sendok,$sendno){
		$data['status']	=	$status;
		$data['value']	=	$value;
		$data['msg']	=	$msg;
		$data['sendok']	=	$sendok;	
		$data['sendno']	=	$sendno;
		echo json_encode($data);die;
	}
	function userchange_action(){
		$userinfoM			=			$this->MODEL('userinfo');
		$uid				=			$_POST['uid'];
		$where['uid']		=			$uid;
		$rows				=			$userinfoM->getInfo($where,array('field'=>'`uid`,`username`,`usertype`,`pid`'));
		if($rows['uid'] && $rows['username'] && $rows['usertype']>0){
			$userwhere['uid']			=			$_POST['uid'];
			$userwhere['pres_usertype']	=			$rows['usertype'];
			$userwhere['status']		=			0;
			$suser		=		$userinfoM->getUserChangeInfo($userwhere,array('field'=>'`id`,`uid`,`pres_usertype`'));
			
			if($suser['id']>0){
				$this->ACT_layer_msg('当前存在转换会员申请,请确认',8,'index.php?m=admin_userchange&id='.$suser['id'].'',2,1);
			}else{
				
				if($_POST['applyusertype']==""){
					$this->ACT_layer_msg('请选择转换会员类型',8,$_SERVER['HTTP_REFERER'],2,1);
				}elseif($rows['usertype']==$_POST['applyusertype']){
					$this->ACT_layer_msg('选择转换会员类型与当前会员类型一致，无须切换',8,$_SERVER['HTTP_REFERER'],2,1);
				}
				$data						=			array(
					'uid'					=>			$rows['uid'],
					'name'					=>			$rows['username'],
					'pres_usertype'			=>			$rows['usertype'],
					'did'					=>			$rows['did'],
					'apply_usertype'		=>			$_POST['applyusertype'],
					'apply_time'			=>			time(),
					'lastupdate'			=>			time(),
					'status'				=>			1,
					'statusbody'			=>			$_POST['statusbody'],
					'type'					=>			2,
					'state'					=>			0,
				);
				$res						=			$userinfoM->addUserChange($data);
				if($res){
					$userinfoM->actUserchange($res,$uid,$_POST['applyusertype'],$rows['username'],1,$rows['moblie'],'5');
					$this->ACT_layer_msg('转换会员类型成功',9,$_SERVER['HTTP_REFERER'],2,1);
				}else{
					$this->ACT_layer_msg('转换会员类型失败',8,$_SERVER['HTTP_REFERER'],2,1);
				}
			}
		}else{
      if($rows['uid']){
        $this->ACT_layer_msg('当前会员用户没有相关类型，无须转换身份',8,$_SERVER['HTTP_REFERER'],2,1);
      }else{
        $this->ACT_layer_msg('会员数据不存在',8,$_SERVER['HTTP_REFERER'],2,1);
      }
			
		}
	
	}
}
?>