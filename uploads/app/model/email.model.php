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
class email_model extends model{
	
	/*
	 * 获取配置列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('admin_email',$whereData);
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
	}
	
	/*
	* 获取配置详情
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/
	function getInfo($whereData, $data = array()){
		
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List	=	$this -> select_once('admin_email',$whereData,$data['field']);
		}

		return $List;
	
	}

	/*
	* 创建配置
	* $setData 	自定义处理数组
	*
	*/
	

	function addInfo($setData){

		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('admin_email',$setData);
			
		}
		
		return $nid;
		
	
	}

	/*
	* 更新配置
	* $whereData 	查询条件
	* $data 		自定义处理数组
	*
	*/

	function upInfo($whereData, $data = array()){

		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('admin_email',$data,$whereData);
			
		}

		return $nid;
	
	}
	/*
	* 查询数量
	* $whereData 	查询条件
	*
	*/

	function getNum($whereData){

		if(!empty($whereData)){
			
			$num	=	$this -> select_num('admin_email',$whereData);
			
		}

		return $num;
	
	}
	
	function delEmail($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit		=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit		=	'';
			
		}
 
		$result			=	$this	->	delete_all('admin_email',$whereData,$limit);
		
		return	$result;
		
	}
	
	
	/*
	 * 获取配置列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getEmsgList($whereData,$data=array()){
		$ListNew			=	array();
		$List				=	$this -> select_all('email_msg',$whereData);
		
		if(!empty( $List )){
			$cuid			=	array();
			$uid			=	array();
			foreach ($List as $k => $v) {
				if($v['cuid']>0){
					$cuid[]	=	$v['cuid'];
				}
				if($v['uid']>0){
					$uid[]	=	$v['uid'];
				}
			}
			$alluids		=	array_merge($cuid,$uid);
			$alluids		=	array_unique($alluids);//发送人

			require_once ('userinfo.model.php');

            $userinfoM  	=   new userinfo_model($this->db, $this->def);

            $namelists  	=   $userinfoM  ->  getUserList(array('uid'=>array('in',pylode(',',$alluids))));

            foreach($namelists as $nk=>$nv){

                $names[$nv['uid']]  	=   $nv['name'];

            }

            foreach($List as $lk => $lv){

				$List[$lk]['fname']		=	$lv['cuid'] ? $names[$lv['cuid']] : '系统';
				
				if($lv['uid']>0){

					$List[$lk]['sname'] =	$names[$lv['uid']];

				}elseif($lv['uid']<0){

					$List[$lk]['sname'] =	'管理员';

				}else{

					$List[$lk]['sname']	=	'';
					
				}
			}

			$ListNew['list']			=	$List;
		}

		return	$ListNew;
	}
	
	function delEmailMsg($whereData,$data){
		
		if($data['type']=='one'){//单个删除
			
			$limit	=	'limit 1';
			
		}
		
		if($data['type']=='all'){//多个删除
		
			$limit	=	'';
			
		}
		if($data['norecycle'] == '1'){	//	数据库清理，不插入回收站
	
			$result	=	$this	->	delete_all('email_msg',$whereData,$limit,'','1');
		}else{
 
			$result	=	$this	->	delete_all('email_msg',$whereData,$limit);
		}
		
		return	$result;
		
	}
	
	/*
	* 查询数量
	* $whereData 	查询条件
	*
	*/

	function getEmsgNum($whereData){

		if(!empty($whereData)){
			
			$num	=	$this -> select_num('email_msg',$whereData);
			
		}

		return $num;
	
	}
	/*
	 * 获取单条数据email_msg
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	function getEmsgOnce($whereData,$data=array()){
		
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
		$List				=	$this -> select_once('email_msg',$whereData,$data['field']);
		
		if(is_array($List)&&$List){
			
			if(date('Y-m-d',$List['ctime'])==date('Y-m-d'))
			{
				$List['disabled']='1';
			}
		}
		
		return	$List;
	}
	//发送邮件
	function setSendCom($post=array()){
		$data		=	array();
		$emailtype	=	intval($post['emailtype']);
		$emailday	=	intval($post['emailday']);
		$page		=	intval($post['page']);
		$limit		=	100;
		if($page<1){
			$page	=	1;
		}	
		if($emailtype){ 
			if($emailday){
				$time=strtotime("-".$emailday." day");
			}else{
				$time=strtotime("-7 day");
			} 
			$ststrsql=($page-1)*$limit;
			if($emailtype=='2'){
				if($this->config['sy_email_vipmr']!='1'){
					$data['msg']	=	'请先开启会员服务到期提醒！'; 
					$data['stype']	=	2;  
				}else{
					$allnum	=	$this->select_num('company_statis',array('vip_etime'=>array('<',$time)),'`uid`');
					$allpage=	ceil($allnum/$limit); 
					if($allpage>$page){ 
						$rating	=	$this->select_all('company_statis',array('vip_etime'=>array('<',$time),'limit'=>$ststrsql.','.$limit),'`uid`,`vip_etime`,`rating_name`'); 
						if($rating&&is_array($rating)){
							$uids=array();
							foreach($rating as $v){
								$uids[]=$v['uid'];
							} 
							$member=$this->select_all('member',array('usertype'=>'2','email'=>array('<>',''),'uid'=>array('in',pylode(',',$uids))),'`uid`,`email`,`login_date`');
						} 
					}else{
						$data['msg']	=	'发送成功！'; 
						$data['stype']	=	2;  
					}
				} 
			}else if($emailtype=='1'){ 
				if($this->config['sy_email_comwdl']!='1'){
					$data['msg']	=	'请先开启未登录提醒！'; 
					$data['stype']	=	2;  
				}else{
					$allnum=$this->select_num('member',array('usertype'=>'2','email'=>array('<>',''),'login_date'=>array('<',$time)),'`uid`');
					
					$allpage=ceil($allnum/$limit);  
					if($allpage>$page){
						$member=$this->select_all('member',array('usertype'=>'2','email'=>array('<>',''),'login_date'=>array('<',$time),'limit'=>$ststrsql.','.$limit),'`uid`,`email`'); 
					}else{
						$data['msg']	=	'发送成功！'; 
						$data['stype']	=	2;
					}
				}  
			}
			if($data['stype']==''){ 
				if($member&&is_array($member)){ 
					$uids=$com=$ratinginfo=$ratingdate=array();
					foreach($member as $v){
						$uids[]=$v['uid'];
					}
					$company=$this->select_all('company',array('uid'=>array('in',pylode(',',$uids))),'`uid`,`name`');
					foreach($company as $v){
						$com[$v['uid']]=$v['name'];
					}
					include_once('notice.model.php');
					$notice	=	new notice_model($this->db, $this->def);
					if($emailtype=='1'){
						foreach($member as $v){ 
							$notice->sendEmailType(array("email"=>$v['email'],"uid"=>$v['uid'],"name"=>$com[$v['uid']],"date"=>date("Y-m-d H:i:s",$v['login_date']),"type"=>"comwdl"));
						}
					}elseif($emailtype=='2'){
						foreach($rating as $v){
							$ratinginfo[$v['uid']]=$v['rating_name'];
							$ratingdate[$v['uid']]=date("Y-m-d H:i:s",$v['vip_etime']);
						}
						foreach($member as $v){ 
							$notice->sendEmailType(array("email"=>$v['email'],"uid"=>$v['uid'],"name"=>$com[$v['uid']],"ratingname"=>$ratinginfo[$v['uid']],'date'=>$ratingdate[$v['uid']],"type"=>"vipmr"));
						}
					}  
					$data['msg']	=	'正在发送第'.$ststrsql.'至'.($ststrsql+$limit).'封邮件！'; 
					$data['stype']	=	1;  
				}else{
					$data['msg']	=	'未找到合适企业！'; 
					$data['stype']	=	2; 
				}
			}
		}else{
			$data['msg']	=	'非法操作！'; 
			$data['stype']	=	2;
		}
		$data['page']	=	$page+1;
		return $data;
	}
	//发送邮件
	function setSendUser($post){
		$emailtype	=	intval($post['emailtype']);
		$emailday	=	intval($post['emailday']);
		$page		=	intval($post['page']);
		$limit		=	100;
		if($page<1){
			$page	=	1;
		}	
		if($emailtype){ 
			if($emailday){
				$time=strtotime("-".$emailday." day");
			}else{
				$time=strtotime("-7 day");
			} 
			$ststrsql=($page-1)*$limit;
			if($emailtype=='2'){
				if($this->config['sy_email_userup']!='1'){
					$data['msg']	=	'请先开启未更新提醒！';  
					$data['stype']	=	2;
				}else{ 
					
					$allnum=$this->select_num('resume_expect',array('lastupdate'=>array('<',$time),'defaults'=>'1','status'=>1,'r_status'=>1,'job_classid'=>array('<>',''),'groupby'=>'uid'),'`uid`');
					
					$allpage=ceil($allnum/$limit); 
					
					if($allpage>$page){ 
						$expect=$this->select_all('resume_expect',array('lastupdate'=>array('<',$time),'defaults'=>'1','status'=>1,'r_status'=>1,'job_classid'=>array('<>',''),'groupby'=>'uid','limit'=>$ststrsql.','.$limit),'`uid`,`lastupdate`');
						if($expect&&is_array($expect)){
							$uids=$lasttime=array();
							foreach($expect as $v){
								$uids[]=$v['uid'];
								$lasttime[$v['uid']]=date("Y-m-d H:i:s",$v['lastupdate']);
							}
							$member=$this->select_all('member',array('usertype'=>'1','email'=>array('<>',''),'uid'=>array('in',pylode(',',$uids))),"`uid`,`email`,`username`");
						} 
					}else{
						$data['msg']='发送成功！'; 
						$data['stype']=2;  
					}
				}
			}else if($emailtype=='1'){
				if($this->config['sy_email_userwdl']!='1'){
					$data['msg']='请先开启未登录提醒！';  
					$data['stype']=2; 
				}else{ 
					$allnum=$this->select_num('member',array('usertype'=>'1','email'=>array('<>',''),'login_date'=>array('<',$time)),'`uid`');
					$allpage=ceil($allnum/$limit); 
					
					if($allpage>$page){ 
						$member=$this->select_all('member',array('usertype'=>'1','email'=>array('<>',''),'login_date'=>array('<',$time),'limit'=>$ststrsql.','.$limit),'`uid`,`email`');
					}else{
						$data['msg']='发送成功！'; 
						$data['stype']=2;  
					}
				} 
			} 
			if($data['stype']==''){ 
				if($member&&is_array($member)){
					$emailarr=$userinfo=$uids=array(); 
					include_once('notice.model.php');
					$notice	=	new notice_model($this->db, $this->def);
					if($emailtype=='1'){
						foreach($member as $v){ 
							$notice->sendEmailType(array("email"=>$v['email'],"uid"=>$v['uid'],"username"=>$v['username'],"date"=>date("Y-m-d H:i:s",$v['login_date']),"type"=>"userwdl"));
						}
					}else{
						foreach($member as $v){ 
							$notice->sendEmailType(array("email"=>$v['email'],"uid"=>$v['uid'],"name"=>$v['username'],'date'=>$lasttime[$v['uid']],"type"=>"userup"));
						}
					} 
					$data['msg']='正在发送第'.$ststrsql.'至'.($ststrsql+$limit).'封邮件！'; 
					$data['stype']=1;  
				}else{
					$data['msg']='未找到合适用户！';
					$data['stype']=2;		
				}
			}
		}else{
			$data['msg']='非法操作！';
			$data['stype']=2;			
		}
		$data['page']=$page+1;
		return $data;
	}
}