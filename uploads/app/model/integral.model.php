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
class integral_model extends model{	

	function company_invtal($uid, $usertype, $integral, $auto=true, $name='', $pay=true, $pay_state=2, $type='integral', $pay_type='',$eid = null){
		
	    if( $pay && $integral!='0'){
			
	        $integral = abs($integral);
		
			$member=$this->select_once('member',array('uid'=>$uid),'`did`');
			
			if($usertype=='1'){
			    
				$table='member_statis';
			}elseif($usertype=='2'){
			    
				$table='company_statis';
			}
            
			if($type != 'yhq'){  //  优惠券记录，不进行 statis 表数据修改
			    
    			if($auto){
    			    
    				$nid		=	$this	->	update_once($table,array($type=>array('+',$integral)),array('uid'=>$uid));
    			}else{
    			    
    			    $nid		=	$this 	->	update_once($table,array($type=>array('-',$integral)),array('uid'=>$uid));
    			    
    			    $integral	=   '-'.$integral;
    			}
			}
			
			$dingdan             =   time().rand(10000,99999);
			
			$data                =   array();
			
			$data['order_id']    =   $dingdan;
			$data['did']         =   $member['did'];
			$data['com_id']      =   $uid;
			$data['usertype']    =   $usertype;
			$data['pay_remark']  =   $name;
			$data['pay_state']   =   $pay_state;
			$data['pay_time']    =   time();
			$data['order_price'] =   $integral;
			$data['pay_type']    =   $pay_type;
			$data['eid']         =   $eid;
			
			if($type=='integral'){
			
			    $data['type']=1;
			    
			}else if($type=='yhq'){
			    
			    $data['type']=3;
			}else{
				
			    $data['type']=2;
			}
			
			$this->insert_into('company_pay',$data);
			
			return $nid;
		}else{
			return true;
		}
	}
	/**
	 * 统一判断加积分还是减积分整合函数
	 */
	function invtalCheck($uid,$usertype, $type, $msg, $pay_type = '')
	{

		$return	=	$this	->	checkOnceIntegral($uid, $type, $msg);
		
		if($return){
			if($this->config[$type.'_type'] == '1'){

				$auto  =  true;

			}else{

				$auto  =  false;

			}

			$this -> company_invtal($uid,$usertype, $this->config[$type], $auto, $msg, true, 2, 'integral', $pay_type);
		}
	}
	/**
	 * 判断是否是一个账号只能获取一次的积分
	 */
	private function checkOnceIntegral($uid, $type, $msg)
	{
		$onceitg	=	array(
		    'integral_reg',//注册
		    'integral_userinfo',//完善基本资料
		    'integral_emailcert',//邮箱认证
		    'integral_mobliecert',//手机认证
		    'integral_avatar',//上传头像
		    'integral_add_resume',//个人-发布简历
		    'integral_identity',//个人-上传身份验证
		    'integral_map',//企业-设置地图
		    'integral_banner',//企业-上传企业横幅
		    'integral_comcert'//企业-认证营业执照
		);

		if(in_array($type,$onceitg)){
			$compay	=	$this	->	getInfo(array('com_id'=>$uid,'pay_remark'=>$msg));
			if(!empty($compay)){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	function insert_company_pay($integral, $pay_state, $uid,$usertype, $msg, $type, $pay_type = '', $ptype = false){

		if($integral!='0'){
		    
		    $pay  =   array();

			if($ptype){

				$pay['order_price'] =   $integral;

			}else{

				$pay['order_price'] =   '-'.$integral;

			}

			$pay['order_id']     =   time().rand(10000,99999);
			$pay['pay_time']     =   time();
			$pay['pay_state']    =   $pay_state;
			$pay['com_id']       =   $uid;
			$pay['usertype']     =   $usertype;
			$pay['pay_remark']   =   $msg;
			$pay['type']         =   $type;
			$pay['pay_type']     =   $pay_type;
			$pay['did']          =   $this->config['did'];

			return $this->insert_into('company_pay',$pay);

		}else{

			return false;
		}
	}
	/**
	*积分任务页面的积分状态
	*$data 必传参数 type 类型（com：商家）
	*				uid 
	*				uidtype 
	*/
	function integralMission($data=array()){

		$baseInfo			= false;	//基本资料是否已填写,企业名、行业等等
		$logo				= false;	//logo是否上传
		$photo				= false;	//是否上传头像
		$signin			    = false;	//是否签到
		$emailChecked		= false;	//email是否验证
		$phoneChecked		= false;	//手机号是否验证
		$pay_remark         = false;
		$question        	= false;	//发布问题
		$answer       		= false;	//回答问题
		$answerpl           = false;	//评论回答
		
		$map				= false;	//企业地图
		$banner				= false;	//企业横幅
		$yyzz				= false;	//营业执照
		if($data['type']=='com'){
			$row	=	$this	->	select_once("company",array('uid'=>$data['uid']),'name,hy,logo,email_status,moblie_status,x,y,firmpic,yyzz_status');
			$ban	=	$this	->	select_once("banner",array('uid'=>$data['uid']),'pic');
			$row['firmpic']=$ban['pic'];
			if(is_array($row) && !empty($row)){
				if($row['name'] != '' && $row['hy'] != '' ){
					$baseInfo = true;
				}
				if($row['logo'] != ''){ 
					$logo = true;
				}
				if($row['email_status'] != 0){
					$emailChecked = true;
				}
				if($row['moblie_status'] != 0){
					$phoneChecked = true;
				}
				if($row['x'] != 0 && $row['y'] != 0){
					$map = true;
				}
				if($row['firmpic'] != ''){
					$banner = true;
				}
				if($row['yyzz_status'] != 0){
					$yyzz = true;
				}
			}
		}
		
		if($data['type']=='member'){
			$row		=	$this->select_once('resume',array('uid'=>$data['uid']),'`name`,`sex`,`birthday`,`telphone`,`email`,`edu`,`exp`,`living`,`photo`,`email_status`,`moblie_status`,`idcard_status`');
			if(is_array($row) && !empty($row)){
				if($row['name'] != '' && $row['sex'] != '' && $row['birthday'] != '' && $row['telphone'] != '' && $row['edu'] != '' && $row['exp'] != '' && $row['living'] != ''){
					$baseInfo		 =	true;
				}
				
				if($row['photo'] != ''){
					$photo			 =	true;
				}	
				
				if($row['email_status'] != 0){
					$emailChecked 	=	true;
				}
					
				if($row['moblie_status'] != 0){
					$phoneChecked 	=	true;
				}				
					
				if($row['idcard_status'] != 0){
					$identification =	true;
				}
			}
		}
		
		$date	=	date("Ymd");
		$reg	=	$this	->	select_once("member_reg",array('uid'=>$data['uid'],'usertype'=>$data['usertype'],'date'=>$date));
		if($reg['id']){
		    $signin = true;
		}

		$question	=	$this->max_time('发布问题',$data['uid'],$data['usertype']);
		$answer		=	$this->max_time('回答问题',$data['uid'],$data['usertype']);
		$answerpl	=	$this->max_time('评论问答',$data['uid'],$data['usertype']); 

		$statusList = array(
			'baseInfo'		=> 	$baseInfo,
			'logo'			=> 	$logo,
		    'signin'		=> 	$signin,
			'emailChecked'	=> 	$emailChecked,
			'phoneChecked'	=> 	$phoneChecked,
			'question'	    => 	$question,
			'answer'	    => 	$answer,
			'answerpl'	    => 	$answerpl,
			'photo'			=>	$photo,			
			'pay_remark'	=>	$pay_remark,
			'identification'=>	$identification,	//身份证	
			'map'			=> 	$map,				//企业地图
			'banner'		=>	$banner,			//企业横幅
			'yyzz'			=>	$yyzz				//营业执照
		);
			
		return $statusList;
	}

	function max_time($remark,$uid,$usertype){
	   //查询当天是否有相关类型积分记录
		$stime	=	strtotime(date('Ymd'));
		$etime	=	strtotime(date('Ymd'))+ 86400;
		// $where	=	array(
			// 'com_id'		=>	$uid,
			// 'pay_remark'	=>	trim($remark),
			// 'pay_time'		=>	array('between',array($stime,$etime)),
		// );
		$where['com_id']				=	$uid;
		$where['usertype']				=	$usertype;
		$where['pay_remark']			=	$remark;
		$where['PHPYUNBTWSTART_A']  	=   '';
		$where['pay_time'][]            =   array('>', $stime, 'AND');
		$where['pay_time'][]            =   array('<', $etime,'AND');	
		$where['PHPYUNBTWEND_A']    	=   '';
	    $num	=	$this -> select_num("company_pay",$where);
		if($num>0){
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * @desc 查询company_pay记录
	 */
	function getInfo($where = array(), $data = array()) {
	    
	    $field     =	empty($data['field']) ? '*' : $data['field'];
	   
	    $info      =	$this -> select_once('company_pay',$where, $field);
	   
	    return $info;
	}
	/**
	 * @desc 修改admin_integralclass记录
	 */
	function upIntClass($where = array(), $data = array()) {
		
		if(!empty($where)){
			
			$nid      =		$this -> update_once('admin_integralclass',$data, $where);
		
		}
	    return $nid;
	}
	
	/**
	 * @desc 查询admin_integralclass记录
	 */
	function getIntClass($where = array(), $data = array()) {
		
		if(!empty($where)){
			
			$field     	=		empty($data['field']) ? '*' : $data['field'];
			
			$List      	=		$this -> select_all('admin_integralclass',$where, $field);
		
		}
	    return $List;
	}
	
	/**
	 * @desc 添加admin_integralclass记录
	 */
	function addIntClass($data = array()) {
		
		if(!empty($data)){
			
			$nid      	=		$this -> insert_into('admin_integralclass',$data);
		
		}
	    return $nid;
	}
	
	/**
	 * @desc  删除admin_integralclass记录
	 */
	function delIntClass($ids){
		
		if(!empty($ids)){
			 
			$result	=	$this	->	delete_all('admin_integralclass',array('id' => array('in', pylode(',', $ids))), '');
		}
		return	$result;
	}
	
	
	function getList($whereData=array(), $data = array()){
		
		if(!empty($whereData)){
			
			$field     	=		empty($data['field']) ? '*' : $data['field'];
			
			$List      	=		$this -> select_all('company_pay',$whereData, $field);
		
		}
	    return $List;
		
	}
	
	
}
?>