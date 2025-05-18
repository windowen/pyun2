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
class weixin_model extends model{
   
   /******微信消息函数**********/
	function myMsg($wxid='')
	{
		$userBind = $this->isBind($wxid);
		
		if($userBind['bindtype']=='1')
		{
			$Return['centerStr'] = "<Content><![CDATA[您最新没有新的消息！]]></Content>";
			
		}else{

			$Return['centerStr'] = $userBind['cenetrTpl'];
		}
		$Return['MsgType']   = 'text';
		return $Return;
	}
	
	/******微信邀请面试通知**********/
	function Audition($wxid='')
	{
		$userBind = $this->isBind($wxid);

		if($userBind['bindtype']=='1')
		{
			$Aud = $this->select_all('userid_msg', array('uid' => $userBind['uid'], 'orderby' => array('datetime, desc'), 'limit' => 5));
			
			if(is_array($Aud) && !empty($Aud))
			{
				foreach($Aud as $key=>$value)
				{
					$Info['title'] = "【".$value['fname']."】邀您面试\n邀请时间：".date('Y-m-d H:i:s',$value['datetime']);
					$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
					$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=invite";
					$List[]        = $Info;
				}
				$Msg['title'] = '面试邀请';
				$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
				$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=invite";
				$Return['centerStr'] = $this->Handle($List,$Msg);
				$Return['MsgType']   = 'news';

			}else{

				$Return['centerStr'] ='<Content><![CDATA['.'最近暂无面试邀请'.']]></Content>';
				$Return['MsgType']   = 'text';
			}
			return $Return;
		}else{
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******微信职位申请通知**********/
	function ApplyJob($wxid='')
	{
		$userBind = $this->isBind($wxid,2);
		if($userBind['bindtype']=='1')
		{
			$Apply = $this->select_all('userid_job', array('com_id' => $userBind['uid'], 'is_browse' => 1, 'orderby' => array('datetime, desc'), 'limit' => 5));
			
			if(is_array($Apply) && !empty($Apply))
			{
				foreach($Apply as $key=>$value)
				{
					$uid[] = $value['uid'];
				}
				//查询用户
				$userList = $this->select_all('resume', array('uid' => array('in', pylode(',', $uid))), '`uid`,`name`,`edu`,`exp`');
				if(is_array($userList)){
					
					foreach($userList as $key=>$value)
					{
						$resumeList[$value['uid']] = $value;
					}
				}
				include(PLUS_PATH."/user.cache.php");
				foreach($Apply as $key=>$value)
				{
					$Info['title'] = "【".$resumeList[$value['uid']]['name']."】".$userclass_name[$resumeList[$value['uid']]['edu']]."/".$userclass_name[$resumeList[$value['uid']]['exp']]."工作经验\n向您发布的职位：".$value['job_name']."\n投递一份简历\n投递时间：".date('Y-m-d H:i',$value['datetime']);
					$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
					$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=hr";
					$List[]        = $Info;
				}
				$Msg['title'] = '简历投递';
				$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
				$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=hr";
				$Return['centerStr'] = $this->Handle($List,$Msg);
				$Return['MsgType']   = 'news';

			}else{

				$Return['centerStr'] ='<Content><![CDATA['.'最近暂无简历投递'.']]></Content>';
				$Return['MsgType']   = 'text';
			}
			
			return $Return;
		}else{
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******兼职报名通知**********/
	function PartApply($wxid='')
	{
		$userBind = $this->isBind($wxid,2);
		if($userBind['bindtype']=='1')
		{
			//
			$Apply = $this->select_all('part_apply', array('comid' => $userBind['uid'], 'status' => 1, 'orderby' => array('ctime,desc'), 'limit' => 5));
			
			if(is_array($Apply) && !empty($Apply))
			{
				foreach($Apply as $key=>$value)
				{
					$uid[] = $value['uid'];
					$jobid[] = $value['jobid'];
				}
				//查询兼职职位
				$partJob = $this->select_all('partjob', array('uid' => $userBind['uid'], 'id' => array('in', pylode(',', $jobid))),'`id`,`name`');

				if(is_array($partJob)){
					
					foreach($partJob as $key=>$value)
					{
						$jobname[$value['id']] = $value['name'];
					}
				}
				//查询用户
				$userList = $this->select_all('resume', array('uid' => array('in', pylode(',', $uid))), '`uid`,`name`,`edu`,`exp`');
				if(is_array($userList)){
					
					foreach($userList as $key=>$value)
					{
						$resumeList[$value['uid']] = $value;
					}
				}
				include(PLUS_PATH."/user.cache.php");
				foreach($Apply as $key=>$value)
				{
					$Info['title'] = "【".$resumeList[$value['uid']]['name']."】".$userclass_name[$resumeList[$value['uid']]['edu']]."/".$userclass_name[$resumeList[$value['uid']]['exp']]."工作经验\n报名兼职：".$jobname[$value['jobid']]."\n报名时间：".date('Y-m-d H:i',$value['ctime']);
					$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
					$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=partapply";
					$List[]        = $Info;
				}
				$Msg['title'] = '兼职报名';
				$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
				$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=partapply";
				$Return['centerStr'] = $this->Handle($List,$Msg);
				$Return['MsgType']   = 'news';

			}else{

				$Return['centerStr'] ='<Content><![CDATA[最近暂无报名]]></Content>';
				$Return['MsgType']   = 'text';
			}
			
			return $Return;
		}else{
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******微信简历查看记录**********/
	function lookResume($wxid='')
	{
		$userBind = $this->isBind($wxid);
		if($userBind['bindtype']=='1')
		{
			$Aud = $this->select_all('look_resume', array('uid' => $userBind['uid'], 'com_id' => array('>', 0), 'orderby' => array('datetime, desc'), 'limit' => 5));
			if(is_array($Aud) && !empty($Aud))
			{
				
				foreach($Aud as $key=>$value)
				{
					$comid[] = $value['com_id'];
				}
				$comids =pylode(',',$comid);
		
				if($comids){
					$comList = $this->select_all('company', array('uid' => array('in', $comids)), '`uid`,`name`');
					if(is_array($comList)){
						foreach($comList as $key=>$value)
						{
							$comname[$value['uid']] = $value['name'];
						}
					}
					foreach($Aud as $key=>$value)
					{
						$Info['title'] = "查看企业：【".$comname[$value['com_id']]."】\n查看时间：".date('Y-m-d H:i:s',$value['datetime']);
						$Info['pic']   = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
						$Info['url']   = $this->config['sy_wapdomain']."/member/index.php?c=look";
						$List[]        = $Info;
					}
					$Msg['title'] = '最近查看我的简历';
					$Msg['pic']	= checkpic('',$this->config['sy_wx_logo']);
					$Msg['url'] = $this->config['sy_wapdomain']."/member/index.php?c=look";
					$Return['centerStr'] = $this->Handle($List,$Msg);
					$Return['MsgType']   = 'news';
				}else{
					$Return['centerStr']='<Content><![CDATA[已经很久没公司查看您的简历了！]]></Content>';
					$Return['MsgType']   = 'text';
				}
			}else{

				$Return['centerStr']='<Content><![CDATA[已经很久没公司查看您的简历了！]]></Content>';
				$Return['MsgType']   = 'text';
			}
			return $Return;

		}else{

			
			$Return['MsgType']   = 'text';
			$Return['centerStr'] = $userBind['cenetrTpl'];
			return $Return;
		}
	}
	/******微信刷新简历**********/
	function refResume($wxid='')
	{
		$userBind = $this->isBind($wxid);
		if($userBind['bindtype']=='1')
		{	
			$Resume = $this->select_num('resume_expect', array('uid' => $userBind['uid']));
			
			if($Resume>0)
			{
				$this->update_once('resume_expect', array('lastupdate' => time()), array('uid' => $userBind['uid']));
				$Return['centerStr']="<Content><![CDATA[简历刷新成功\n刷新时间:".date('Y-m-d H:i:s')."]]></Content>";

			}else{

				$Return['centerStr']='<Content><![CDATA[请先完善您的简历！]]></Content>';
				
			}
		}else{

			$Return['centerStr'] = $userBind['cenetrTpl'];
			
		}
		$Return['MsgType']   = 'text';
		return $Return;
	}

	/******微信刷新职位**********/
	function refJob($wxid='')
	{
		$userBind = $this->isBind($wxid,2);
		if($userBind['bindtype']=='1')
		{
			//查询正常职位数量
			$jobNum = $this->select_num('company_job', array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0, 'r_status' => 1));
			if($jobNum>0){
				//查询用户数量以及积分 
				$membeStatis = $this->select_once('company_statis', array('uid' => $userBind['uid']));
				$refIntegral = $this->config['integral_jobefresh']*$this->config['integral_proportion']*$jobNum;
				//判断会员模式 如果是时间会员则判断是否到期 如果套餐会员则判断有效期以及数量
				if($membeStatis['rating_type']=='2'){
				    if(isVip($membeStatis['vip_etime'])){//有效期之内
						$this->update_once('company_job', array('lastupdate' => time()), array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0 , 'r_status' => 1));
						$msg = '职位刷新完成，本次共刷新'.$jobNum."个职位！";
					}else{//不在有效期 使用积分

						$useIntegral = 1;
					}
				}else{//套餐模式
					
				    if(isVip($membeStatis['vip_etime']) && $membeStatis['breakjob_num']>=$jobNum){
						$this->update_once('company_job', array('lastupdate' => time()), array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0 , 'r_status' => 1));
 
						//扣除套餐数量
						
						$this->update_once('company_statis', array('breakjob_num' => array('-', $jobNum)), array('uid' => $userBind['uid']));

						$msg = '职位刷新完成，本次共刷新'.$jobNum."个职位！";
					}else{//数量不足 使用积分
						$useIntegral = 1;
					}
				}
				if($useIntegral=='1'){
					
					if($this->config['com_integral_online']=='1'){//开启积分模式的情况下
							
						if($this->config['integral_jobefresh_type']=='2'){//刷新职位减积分
							//判断积分是否充足
							
							if($membeStatis['integral']>=$refIntegral){

								$this->update_once('company_job', array('lastupdate' => time()), array('uid' => $userBind['uid'], 'state' => 1, 'status' => 0 , 'r_status' => 1));
								
								//扣除积分
								$this->update_once('company_statis', array('integral' => array('-', $refIntegral)), array('uid' => $userBind['uid']));
 
								$msg = '职位刷新完成，本次共刷新'.$jobNum."个职位！";

							}else{

								$msg = "本次刷新共需".$refIntegral."".$this->config['integral_pricename']."，请先充值".$this->config['integral_pricename']."！";
							}
						}else{//批量刷新不走加积分模式 不符合逻辑 屏蔽操作 防止刷积分
							$msg = "权限不足，升级会员，享受更多服务！";
						}
						
					}else{
						$msg = "权限不足，升级会员，享受更多服务！";
					}
				}
			}else{
				$msg = '您没有正在招聘的职位！';
			}
			$Return['centerStr']='<Content><![CDATA['.$msg.']]></Content>';
		}else{

			$Return['centerStr'] = $userBind['cenetrTpl'];
			
		}
		$Return['MsgType']   = 'text';
		return $Return;
	}

	/******微信职位搜索**********/
	function searchJob($keyword){
   	 	require_once ('hotkey.model.php');
    	$HotkeyM = new hotkey_model($this->db, $this->def);
		$keyword = trim($keyword);
		include(PLUS_PATH."/city.cache.php");
		if($keyword){
			$keywords = @explode(' ',$keyword);
			if(is_array($keywords)){
				foreach($keywords as $key=>$value){
					$iscity = 0;
					if($value!=''){
						foreach($city_name as $k=>$v){
							if(strpos($v,trim($value))!==false){
								$CityId[] = $k;
								$iscity = 1;
							}
						}
						if($iscity==0){
							$searchJob[] = "(`name` LIKE '%".trim($value)."%') OR (`com_name` LIKE '%". trim($value) ."%')";
						}
					}
				}
				foreach($keywords as $v){
					$keylist[] = "'".$v."'";
				}
				$hotkeynamewhere['key_name']		=   array('in', implode(',', $keylist));
				$hotkeynamewhere['type']			=   8;
				$hotkeynamelist   =  $HotkeyM->getList($hotkeynamewhere,array('field'=>'id,key_name'));
				if($hotkeynamelist && is_array($hotkeynamelist)){
					foreach($keywords as $v){
						foreach($hotkeynamelist as $val){
							if($val['key_name'] ==$v){
								$ids[]		=		$val['id'];
							}else{
								$keywordval[]	=	$v;
							}
						}
					}
				}else{
					foreach($keywords as $v){
						$keywordval[]	=	$v;
					}
				}
				$keywordfirst = array_unique($keywordval);
				if($ids){
					$upHotData   =   array( 
						'num'       =>  array('+',1),
						'wxtime'	=>	time()
					);
					$hotkeywhere['id']		=   array('in', pylode(',', $ids));
					$hotkeywhere['type']	=  8;
					$HotkeyM->upHotkey($hotkeywhere,$upHotData);
				}
				
				if($keywordfirst){
					foreach($keywordfirst as $v){
						$data        	=     array(
							'key_name'  =>    $v,
							'type'      =>    8,
							'num'       =>    1,
							'wxtime'	=>	time()
						);
						$HotkeyM->addInfo($data);
					}
					
				}
         		
				//添加
				$searchWhere = "`state`='1' AND `sdate`<='".time()."' AND `status`='0' AND `r_status`='1'";
				
				if(!empty($searchJob))
				{
					$searchWhere .=  " AND (".implode(' OR ',$searchJob).")";
				}
				if(!empty($CityId))
				{
					$City_id = pylode(',',$CityId);
					$searchWhere .= " AND (`provinceid` IN (".$City_id.") OR `cityid` IN (".$City_id.") OR `three_cityid` IN (".$City_id."))";
				}
 				$jobList = $this->DB_select_all("company_job",$searchWhere." order by `lastupdate` desc limit 5","`id`,`name`,`com_name`,`com_logo`");
 				
 				
			}
			
		}
		if(is_array($jobList) && !empty($jobList)){
			foreach($jobList as $key=>$value){
				$Info['title'] = "【".$value['name']."】\n".$value['com_name'];
				$Info['pic'] = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
				$Info['url'] = Url("wap",array('c'=>'job','a'=>'comapply','id'=>$value['id']));
				$List[]     = $Info;
			}
			$Msg['title'] = '查看与【'.$keyword. '】相关的职位';
			$Msg['pic']	= checkpic($value['com_logo'],$this->config['sy_wx_logo']);
			$Msg['url'] = Url('wap',array('c'=>'job','keyword'=>urlencode($keyword)));
			$Return['centerStr'] = $this->Handle($List,$Msg);
			$Return['MsgType']   = 'news';
		}else{
			$Return['centerStr'] = '<Content><![CDATA[未找到合适的职位！]]></Content>';
			$Return['MsgType']   = 'text';
		}
		return $Return;
	}
	/******微信职位搜索**********/
	function sendPubLink($wxloginid){
   	 	
		if(strpos($wxloginid,'jobid_')!==false){
			$jobid = str_replace('jobid_','',$wxloginid);

			$jobInfo = $this -> select_once("company_job",array('id'=>$jobid));
			if(is_array($jobInfo) && !empty($jobInfo)){
			
				$Msg['title'] = "招聘:".$jobInfo['name']." - ".$jobInfo['com_name'];
				$Msg['desc']  = strip_tags($jobInfo['description']);
				$Msg['pic']	  = checkpic($jobInfo['com_logo'],$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'job','a'=>'comapply','id'=>$jobInfo['id']));
				
			}
		}elseif(strpos($wxloginid,'companyid_')!==false){

			$comid		=	str_replace('companyid_','',$wxloginid);

			$comInfo	=	$this -> select_once("company",array('uid'=>$comid));
			if(is_array($comInfo) && !empty($comInfo)){
			
				$Msg['title'] = $comInfo['name'];
				$Msg['desc']  = strip_tags($comInfo['content']);
				$Msg['pic']	  = checkpic($comInfo['logo'],$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'company','a'=>'show','id'=>$comInfo['uid']));
				
			}
		}elseif(strpos($wxloginid,'resumeid_')!==false){

			$resid		=	str_replace('resumeid_','',$wxloginid);

			$resInfo	=	$this -> select_once("resume_expect",array('id'=>$resid));

			if(is_array($resInfo) && !empty($resInfo)){

				require_once ('resume.model.php');
				$expectM	=	new resume_model($this->db, $this->def);
				$expect		=   $expectM -> getExpect(array('id'=>$resInfo['id']),array('needCache'=>1));
				$sex		=	$expect['sex'] == '1' ? '男' : '女';
				$Msg['title'] = "意向岗位：".$resInfo['name'];
				$Msg['desc']  = "性别:".$sex."，学历:".$expect['edu_n']."，工作经验:".$expect['exp_n']."，期望薪资：".$expect['salary']."，期望工作地区:".$expect['city_classname'];
				$Msg['pic']	  = checkpic($resInfo['photo'],$this->config['sy_wx_logo']);
				$Msg['url']   = Url('wap',array('c'=>'resume','a'=>'show','id'=>$resInfo['id']));
				
			}
		}
		
		if(!empty($Msg)){
			$Return['centerStr'] = $this->Handle(array(),$Msg);
			$Return['MsgType']   = 'news';
		}else{
			$Return['centerStr'] = '<Content><![CDATA[二维码已失效！]]></Content>';
			$Return['MsgType']   = 'text';
		}
		return $Return;
	}
	/******微信关键字匹配**********/
	function searchKeyword($keyword)
	{
		$keyword = trim($keyword);
		if($keyword)
		{													
			$keywordList = $this->select_once('wxzdkeyword', array('keyword' => array('like', trim($keyword))), '`id`,`keyword`,`content`');
		}	
		if(!empty($keywordList))
		{		
			$Return['centerStr'] = '<Content><![CDATA['.$keywordList['content'].']]></Content>';
			$Return['MsgType']   = 'text';
		}		
		return $Return;				
	}
	
	
	/******微信用户绑定**********/
	function bindUser($wxid='')
	{
	
		$bindType = $this->isBind($wxid);
		$Return['MsgType']   = 'text';
		$Return['centerStr'] = $bindType['cenetrTpl'];
		return $Return;
		
	}
	
	function getWxUser($wxid){
		 global $config;
		
		//读取微信 token
		$Token = getToken($config);
	
		$Url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$Token.'&openid='.$wxid.'&lang=zh_CN';
		$CurlReturn  = CurlPost($Url);
		$UserInfo    = json_decode($CurlReturn,true);

		return $UserInfo;
	
	}
	/******微信账户绑定判断**********/
	function isBind($wxid='',$usertype=1)
	{	
	
		if($wxid){
			
			$UserInfo    = $this->getWxUser($wxid);
			$unionid	 = $UserInfo['unionid'];
			
			if (!empty($unionid)){
			    
			    $User  =  $this->select_once('member',array('wxid'=>$wxid,'unionid'=>array('=',$unionid,'OR')),'`uid`,`username`,`usertype`,`wxid`,`unionid`');
			    
			}else{
			    
			    $User  =  $this->select_once('member',array('wxid'=>$wxid),'`uid`,`username`,`usertype`,`wxid`');
			}
			
			if($User['unionid']!='' && $User['wxid']!=$wxid)//原先已绑定开放平台 但未绑定公众号
			{
			    $this->update_once('member', array('wxid' => $wxid), array('uid' => $User['uid']));
				$User['wxid']=$wxid;
			}
			if (empty($User['unionid']) && !empty($unionid)){  //原先只绑定公众号，没绑定开放平台
			    $this->update_once('member', array('unionid' => $unionid), array('uid' => $User['uid']));
			    $User['unionid']=$unionid;
			}
		}
		if($User['uid']>0)
		{   
			//$urlLogin = Url("wap",array("c"=>"login","bind"=>"1","wxid"=>$wxid,"unionid"=>$unionid));
			$urlLogin = $this->config['sy_wapdomain']."/index.php?c=login&bind=1&wxid=".$wxid."&unionid=".$unionid;
			if($User['usertype']!=$usertype)
			{
				switch($usertype){
					case '1':
						$User['cenetrTpl'] = "<Content><![CDATA[您的".$this->config['sy_webname']."帐号：".$User['username']."为企业帐号，请登录您的个人帐号进行绑定！ \n\n\n 您也可以<a href=\"".$urlLogin."\">点击这里</a>进行绑定其他帐号]]></Content>";
					break;
					case '2':
						$User['cenetrTpl'] = "<Content><![CDATA[您的".$this->config['sy_webname']."帐号：".$User['username']."为个人帐号，请登录您的企业帐号进行绑定！ \n\n\n 您可以<a href=\"".$urlLogin."\">点击这里</a>进行解绑定其他帐号]]></Content>";
					break;

				}
				
			}else{
				$User['bindtype'] = '1';
				$User['cenetrTpl'] = "<Content><![CDATA[您的".$this->config['sy_webname']."帐号：".$User['username']."已成功绑定！ \n\n\n 您也可以<a href=\"".$urlLogin."\">点击这里</a>进行解绑或绑定其他帐号]]></Content>";
			}
			
		}else{

			//$urlLogin = Url("wap",array("c"=>"login","wxid"=>$wxid,"unionid"=>$unionid));
			$urlLogin = $this->config['sy_wapdomain']."/index.php?c=login&wxid=".$wxid."&unionid=".$unionid;
			$User['cenetrTpl'] = '<Content><![CDATA[您还没有绑定帐号，<a href="'.$urlLogin.'">点击这里</a>进行绑定!]]></Content>';
		}

		return $User;
	}
	/******微信推荐职位**********/
	function recJob()
	{
		
		$time	=	time();
		$JobList	=	$this -> select_all('company_job', array('sdate' => array( '<=', $time) , 'status' => 0, 'r_status' => 1, 'rec_time' => array('>', $time), 'orderby' => 'lastupdate,desc', 'limit' => 5), '`id`,`name`,`com_name`,`lastupdate`');
		


		if(is_array($JobList) && !empty($JobList))
		{
			foreach($JobList as $key=>$value)
			{
				$Info['title'] = "【".$value['name']."】\n".$value['com_name'];
				$Info['pic'] = $this->config['sy_weburl'].'/data/upload/wx/jt.jpg';
				$Info['url'] = Url("wap",array('c'=>'job','a'=>'comapply','id'=>$value['id']));
				$List[]        = $Info;
			}
			$Msg['title'] = '推荐职位';
			$Msg['pic']	=	checkpic('',$this->config['sy_wx_logo']);
			$Msg['url'] = Url("wap",array('c'=>'job'));
			$Return['centerStr'] = $this->Handle($List,$Msg);
			$Return['MsgType']   = 'news';
			
		}else{
			$Return['centerStr'] ='<Content><![CDATA[没有合适的职位！]]></Content>';
			$Return['MsgType']   = 'text';
		}
		
		return $Return;
	}
	
	/******微信展示模板构造**********/
	function Handle($List,$Msg)
	{

		$articleTpl = '<Content><![CDATA['.$Msg['title'].']]></Content>';

		$articleTpl .= '<ArticleCount>'.(count($List)+1).'</ArticleCount><Articles>';

		$centerTpl = "<item>
		<Title><![CDATA[%s]]></Title>  
		<Description><![CDATA[%s]]></Description>
		<PicUrl><![CDATA[%s]]></PicUrl>
		<Url><![CDATA[%s]]></Url>
		</item>";
		
		$articleTpl.=sprintf($centerTpl,$Msg['title'],$Msg['desc'],$Msg['pic'],$Msg['url']); 
		if(!empty($List)){
			foreach($List as $value)
			{	
				$articleTpl.=sprintf($centerTpl,$value['title'],'',$value['pic'],$value['url']);
			}
		}
		
		$articleTpl .= '</Articles>';
		return $articleTpl;
	}
	/******微信来源验证**********/
	function valid($echoStr,$signature,$timestamp,$nonce)
  {
      if($this->checkSignature($signature,$timestamp,$nonce)){
      	echo $echoStr;	
      	exit;
      }
  }
	
	/******微信验证函数**********/
	function checkSignature($signature, $timestamp,$nonce)
	{   		
		$token = $this->config['wx_token'];
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature  && $token!=''){
			return true;
		}else{
			return false;
		}
	}
	/******微信数组转换**********/
	function ArrayToString($obj,$withKey=true,$two=false)
	{
		if(empty($obj))	return array();
		$objType=gettype($obj);
		if ($objType=='array') {
			$objstring = "array(";
			foreach ($obj as $objkey=>$objv) {
				if($withKey)$objstring .="\"$objkey\"=>";
				$vtype =gettype($objv) ;
				if ($vtype=='integer') {
				  $objstring .="$objv,";
				}else if ($vtype=='double'){
				  $objstring .="$objv,";
				}else if ($vtype=='string'){
				  $objv= str_replace('"',"\\\"",$objv);
				  $objstring .="\"".$objv."\",";
				}else if ($vtype=='array'){
				  $objstring .="".$this->ArrayToString($objv,false).",";
				}else if ($vtype=='object'){
				  $objstring .="".$this->ArrayToString($objv,false).",";
				}else {
				  $objstring .="\"".$objv."\",";
				}
			}
			$objstring = substr($objstring,0,-1)."";
			return $objstring.")\n";
		}
	}
	function markLog($wxid,$wxuser,$content,$reply){
        
	    $this->insert_into('wxlog', array('wxid' => $wxid, 'wxuser' => $wxuser, 'content' => $content, 'reply' => $reply, 'time' => time()));
	}

	
	/** 
	 * @wxid    微信公众号绑定的唯一识别ID
	 * @tempid	不同消息模板的识别ID，具体在公众号中查看
	 * @url     消息模板点击链接：一般指向触屏版
	 * @data    消息模板具体内容
     */
	function sendWxTemplate($wxid,$tempid,$url,$data,$sdata=array()){

		


	        global $config;
			if(!$config){
				include(PLUS_PATH.'config.php');
			}
		    
			if($wxid){
				//读取微信 token
				$Token = getToken($config);
				
				//模板消息发送接口链接
				$wxUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$Token;
				
				//构建模板参数
				$templateDate = array("touser"=>$wxid,
									  "template_id"=>$tempid,
									  "url"=>$url,
									  "topcolor"=>"#FF0000",
									  "data"=>$data
								);
				$CurlReturn  = CurlPost($wxUrl,json_encode($templateDate));
				$return    = json_decode($CurlReturn,true);
			}else{
				$return['errcode']= -1;
				$return['errmsg']='未关注公众号';
			}

			
			
			if($sdata['uid'] && $sdata['utype']){

				$sdata['status']  =  $return['errcode'];
		    	$sdata['msg']     =  $return['errmsg'];

		    	$this->insert_into('wx_msg',$sdata);
			}
			

			return $return;
		

    }
	
	

	//获取用户登录二维码
	function applyWxQrcode($wxloginid='',$type=''){
	    
		global $config;
		
		if($config['wx_author']=='1'){
		    
			if($wxloginid){
				//查询识别ID对应的二维码是否存在或失效
				$wxqrcode   =   $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 0));
				
				if($wxqrcode['time'] >= (time()-86000)){//留出400秒作为容错时间
					$ticket =  "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($wxqrcode['ticket']);
					return $ticket;
				}
			}
			
			$randStr =   time().rand(1000,9999);
			
			$Url     =   'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.getToken();
			    
			$Data['expire_seconds']  =   86400;          //有效时间一天
			$Data['action_name']     =   'QR_STR_SCENE'; //临时二维码
			//$Data['action_info']['scene']['scene_id']   =   1000;   //登录场景值ID
			$Data['action_info']['scene']['scene_str']   =   $randStr;   //生成识别cookie串
			
			$CurlReturn      =   CurlPost($Url,json_encode($Data));
			
			//{" ticket":"gqen8twaaaaaaaaaas5odhrwoi8vd2vpeglulnfxlmnvbs9xlzayu1poumcwci05mluxvtrsne5wy28aagsey8nzawsauqea",
			// "expire_seconds":86400,"url":"http:\="" \="" weixin.qq.com\="" q\="" 02szhrg0r-92u1u4r4npco"}
			$Return          =   json_decode($CurlReturn,true);
			//插入数据库
			if($Return['ticket']){
				
			    $warr    =   array('wxloginid' => $randStr, 'ticket' => $Return['ticket'], 'time' => time(), 'status' => 0);
				
				if($type=='wxadminbind' && $_SESSION['auid']){
				    
					$warr['auid'] = $_SESSION['auid'];
				}else{
				    
					$warr['uid']  = $this->uid;
				}
				$this->insert_into('wxqrcode', $warr);
				//生成cookie
				if($type==''){
					$type = 'wxloginid';
				}
				setcookie($type,$randStr,time()+86000);
				
				$ticket = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($Return['ticket']);
			}
		}
		return $ticket;
    }

	//微信发布工具各类二维码
	function pubWxQrcode($scene_str){
	    
		global $config;
 
		
		//查询识别ID对应的二维码是否存在或失效
		$wxqrcode   =   $this->select_once('wxqrcode', array('wxloginid' => $scene_str));
		if(!empty($wxqrcode)){
		
			if($wxqrcode['time'] >= (time()-2590000)){//留出容错时间
				$ticket =  "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($wxqrcode['ticket']);
				return $ticket;
			}else{
				$this	->	delete_all('wxqrcode',array('wxloginid'=>$scene_str));
			}
		}
		$Url     =   'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.getToken();
		

		$Data['expire_seconds']  =   2591000;          //有效时间30天
		$Data['action_name']     =   'QR_STR_SCENE'; //临时二维码
		$Data['action_info']['scene']['scene_str']   =   $scene_str;   //场景值
		
		$CurlReturn      =   CurlPost($Url,json_encode($Data));
		

		$Return          =   json_decode($CurlReturn,true);
		//插入数据库
		if($Return['ticket']){
			
			$warr    =   array('wxloginid' => $scene_str, 'ticket' => $Return['ticket'], 'time' => time(), 'status' => 0);
			
			$this->insert_into('wxqrcode', $warr);
			
			$ticket = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($Return['ticket']);
		}
		
		return $ticket;
    }
  
	function isWxlogin($wxid,$wxloginid){
		global $config;
		$wxqrcode = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 0));

		if($wxqrcode['auid']>0){
			$user  	=  $this -> select_once('admin_user',array('uid'=>$wxqrcode['auid']));
			
			if($user){
				$this->update_once('admin_user',array('wxid'=>$wxid),array('uid'=>$user['uid']));
				$this->update_once('wxqrcode', array('status' => 1, 'wxid' => $wxid), array('wxloginid' => $wxloginid));
				return array('result'=>true,'type'=>'amminwxbind');
			}else{
				return array('result'=>false,'type'=>'amminwxbind');
			}
			
		}else{
			$userBind = $this->isBind($wxid);
			
			if($userBind['uid']>0){
			    $this->update_once('wxqrcode', array('status' => 1, 'wxid' => $wxid, 'unionid' => $userBind['unionid']), array('wxloginid' => $wxloginid));
			    // 账户已绑定微信
				if($wxqrcode['uid'] == '0'){
				    // 扫码登录的直接登录
					return true;
					
				}else if($wxqrcode['uid'] != $userBind['uid']){
				    // 扫码绑定
				    // 已绑定其他账号的，将其他账号的绑定记录清空
				    $qlwhere['wxid']  =  $wxid;
				    if (!empty($userBind['unionid'])){
				        $qlwhere['unionid']  =  array('=',$userBind['unionid'],'OR');;
				    }
				    $this->update_once('member', array('wxid'=>'', 'unionid'=>''), $qlwhere);
				    // 添加新的绑定记录
				    $this->update_once('member', array('wxid' => $wxid, 'unionid' => $userBind['unionid']), array('uid' => $wxqrcode['uid']));
				    
				    return array('result'=>true,'type'=>'userwxbind');
				}
				
			}else{
			    $this->update_once('wxqrcode', array('status' => 1, 'wxid' => $wxid, 'unionid' => $userBind['unionid']), array('wxloginid' => $wxloginid));
			    
			    if ($wxqrcode['uid'] > 0){
			        // 已登录账号绑定
			        $this->update_once('member', array('wxid' => $wxid, 'unionid' => $userBind['unionid']), array('uid' => $wxqrcode['uid']));
			        
			        return array('result'=>true,'type'=>'userwxbind');
			        
			    }else{
			        $return  =  array('result'=>false);
			        if($wxloginid){
			        	// 没有绑定的，扫码直接注册
				        if ($this->config['reg_real_name_check'] == 1){
				            // 后台设置实名注册的，要绑定手机号
				            $return['type']  =  'regphone';
				        }else{
				            
				            $return['type']  =  'regbindacount';
				        }
			        }
			        return $return;
			    }
			}
		}
	}
	function creatacount($wxloginid, $uid = ''){

		$return  =  array('status'=>0);

		if($wxloginid){
			//判断是否扫码
			$status = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 1));
			//根据扫码ID 读取用户
			if($status['wxid'] || $status['unionid']){
			    
			    if (!empty($status['unionid'])){
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid'],'unionid'=>array('=',$status['unionid'],'OR')));
			        $this->update_once('member',array('login_date'=>time(),'unionid'=>$status['unionid']),array('uid'=>$member['uid']));
			    }else{
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid']));
			        $this->update_once('member',array('login_date'=>time()),array('uid'=>$member['uid']));
			    }
			    
			    if ($uid == '' && empty($member) && $this->config['reg_real_name_check'] != 1){
			        // 非会员中心扫码绑定
			        // 未设置实名注册，微信未绑定账号的，直接注册账号
			        $wdata  =  array(
			            'openid'   =>  $status['wxid'],
			            'unionid'  =>  $status['unionid'],
			            'source'   =>  9
			        );
			        
			        require_once('userinfo.model.php');
			        $userinfoM  =  new userinfo_model($this->db,$this->def);
			        $result  =  $userinfoM->fastReg($wdata, '', 'weixin');
			        
			        if ($result['errcode'] == 9){
			            
			            $member  =  $userinfoM->getInfo(array('uid'=>$result['uid']));
			        }
			    }
			    $return  =  array('status'=>1, 'member'=>$member);
			}
		}
		return $return;
	}
	function getWxLoginStatus($wxloginid, $uid = ''){
		if($wxloginid){
			//判断是否扫码
			$status = $this->select_once('wxqrcode', array('wxloginid' => $wxloginid, 'status' => 1));
			//根据扫码ID 读取用户
			if($status['wxid'] || $status['unionid']){
			    
			    if (!empty($status['unionid'])){
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid'],'unionid'=>array('=',$status['unionid'],'OR')));
			        $this->update_once('member',array('login_date'=>time(),'unionid'=>$status['unionid']),array('uid'=>$member['uid']));
			    }else{
			        
			        $member  =  $this->select_once('member',array('wxid'=>$status['wxid']));
			        $this->update_once('member',array('login_date'=>time()),array('uid'=>$member['uid']));
			    }
			    
			   
			    $return  =  array('status'=>1, 'member'=>$member);
			}else{
			    // 未扫码
			    $return  =  array('status'=>0);
			}
		}
		return $return;
	}
	
	/**
     * 获取账号信息列表
     */
    private function getUserList($whereData,$data=array()){
        
        require_once ('userinfo.model.php');
        
        $UserinfoM = new userinfo_model($this->db, $this->def);
        
        return $UserinfoM   ->  getList($whereData,$data);
    }
	
	
	/**
	* 添加wxnav数量
	* $setData 	自定义处理数组
	*
	*/
	
	function getWxNavNum($whereData){

		if(!empty($whereData)){
			
			$num	=	$this -> select_num('wxnav',$whereData);
			
		}

		return $num;
	
	}
	
	/**
	* 添加wxnav数据
	* $setData 	自定义处理数组
	*
	*/
	function addWxNavInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('wxnav',$setData);
			
		}

		return $nid;
	
	}

	/**
	* 更新wxnav数据
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	function upWxNavInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('wxnav',$data,$whereData);
			
		}
		return $nid;
	}
	
	function getWxNavList($whereData,$data=array()){
		
		$navlist	=	array();
		
		$List		=	$this->select_all("wxnav",$whereData);
		
		if(is_array($List)){
			
			foreach($List as $value){
				
				if($value['keyid']=='0' || $value['keyid']==''){
					
					$navlist[$value['id']]			=	$value;
					
				}
			}
			
			foreach($List as $val){
				
				foreach($navlist as $key=>$v){
					
					if($v['id']==$val['keyid']){
						
						$navlist[$key]['list'][]	=	$val;
					}
				}
			}
		
		}
		
		return	$navlist;
		
	}
	
	function creatWxNavList($whereData,$data=array()){
		
		$CreatNav 	= 	array();
		
		$navList	=	$this->getWxNavList($whereData);
		
		if(is_array($navList))	{
			
			$i	=	0;
			
			foreach($navList as $key=>$value){
				
				$t	=	0;
				
				$CreatNav['button'][$i]['name']	=	urlencode(trim($value['name']));
				
				if(!empty($value['list'])){

					foreach($value['list'] as $k=>$v){
						
							$CreatNav['button'][$i]['sub_button'][$t]['name'] 		= 	urlencode(trim($v['name']));
						
						if($v['type']=='view'){
							
							$CreatNav['button'][$i]['sub_button'][$t]['type'] 		= 	'view';
							
							$CreatNav['button'][$i]['sub_button'][$t]['url'] 		= 	trim($v['url']);
							
						}elseif($v['type']=='click'){
							
							$CreatNav['button'][$i]['sub_button'][$t]['type'] 		= 	'click';
							
							$CreatNav['button'][$i]['sub_button'][$t]['key']		=	urlencode(trim($v['key']));
							
						}elseif($v['type']=='miniprogram'){
							
							$CreatNav['button'][$i]['sub_button'][$t]['type'] 		= 	'miniprogram';
							
							$CreatNav['button'][$i]['sub_button'][$t]['url']		= 	urlencode(trim($v['url']));
							
							$CreatNav['button'][$i]['sub_button'][$t]['appid']		=	trim($v['appid']);
							
							$CreatNav['button'][$i]['sub_button'][$t]['pagepath']	= 	trim($v['apppage']);
							
						}
						$t++;
					}
					
				}else{

					if($value['type']=='view'){
						
						$CreatNav['button'][$i]['type']		= 	'view';
						
						$CreatNav['button'][$i]['url'] 		= 	trim($value['url']);
						
					}elseif($value['type']=='click'){
						
						$CreatNav['button'][$i]['type'] 	= 	'click';
						
						$CreatNav['button'][$i]['key'] 		= 	urlencode(trim($value['key']));
						
					}elseif($value['type']=='miniprogram'){
						
						$CreatNav['button'][$i]['type'] 	= 	'miniprogram';
						
						$CreatNav['button'][$i]['url'] 		= 	urlencode(trim($value['url']));
						
						$CreatNav['button'][$i]['appid'] 	= 	trim($value['appid']);
						
						$CreatNav['button'][$i]['pagepath'] = 	trim($value['apppage']);
						
					}
				}

				$i++;
				
			}
 			
		}
		
		return $CreatNav;
		
	}
	
	function delWxNav($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('wxnav',$whereData,$limit);
		
		return	$result;
		
	}
	// 查询单条微信扫码
	function getWxQrcode($whereData,$data=array()){
	    
	    $row  =  $this->select_once('wxqrcode', $whereData);
	    
	    return $row;
	}
	function getWxQrcodeList($whereData,$data=array()){
		 
		//提取用户类
		
		$ListNew	=	array();
		
		$List		=	$this -> select_all('wxqrcode',$whereData);
		
		
		if(!empty( $List )){
			
			foreach($List as $k=>$v){
				
				$uids[] = $v['unionid'];
			
			}
			
			$mWhere['unionid']	=	array("in",pylode(',',$uids));
			
			$member 	= 	$this->getUserList($mWhere,array('field'=>'`unionid`,`username`,`usertype`'));
			
			foreach($List as $key=>$value){
				
				foreach($member as $v){
					
					if($value['unionid'] == $v['unionid']){
						
						$List[$key]['username']	= $v['username'];
						
						$List[$key]['usertype'] = $v['usertype'];
					
					}
				
				}
			
			}
			
			$ListNew['list']	=	$List;
		}
        
		return $ListNew;
    }
	
	function delWxqrcode($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('wxqrcode',$whereData,$limit);
		
		return	$result;
		
	}
	
	function getWxzdkeywordList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('wxzdkeyword',$whereData);
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}
	
	function getWxzdkeyword($whereData,$data=array()){
		
		$wxzdKeyword	=	$this -> select_once('wxzdkeyword',$whereData);
		
		return	$wxzdKeyword;
	
	}
	
	function addWxzdkeyword($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('wxzdkeyword',$setData);
			
		}

		return $nid;
	
	}
	
	function upWxzdkeyword($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('wxzdkeyword',$data,$whereData);
			
		}

		return $nid;
	}
	
	function delWxzdkeyword($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
		
		$result			=	$this	->	delete_all('wxzdkeyword',$whereData,$limit);
		
		return	$result;
		
	}
    public function upWxlogin($whereData,$data){
    
        $nid    =   $this -> update_once('wxqrcode', $data, $whereData);
        return $nid;
    }
    
}
?>