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
class once_model extends model{
	private function getClass($options){
	    if (!empty($options)){
	        include_once('cache.model.php');
	        $cacheM            =   new cache_model($this->db,$this->def);
	        $cache             =   $cacheM -> GetCache($options);
	        return $cache;
	    }
	}
    /**
      * 查询全部信息
      * @param 表：once_job
      * @param 功能说明：获取once_job表里面所有店铺信息
      * @param 引用字段：$whereData：条件  2:$data:查询字段
      *
     */
    public function getOnceList($whereData,$data=array()){
      $OnceNew    =   array();
      $field      =   $data['field'] ? $data['field'] : '*';
      $List   =   $this   ->  select_all('once_job',$whereData,$field);
      $cache   =   $this -> getClass(array('city'));
        if(!empty($List) && $List){
            foreach($List  as $k=>$v){
				$List[$k]['ctime_n']	  =	  date("Y-m-d",$v['ctime']);
				$List[$k]['linkman']	  =	  mb_substr($v['linkman'], 0,5,'utf-8');
                if($v['edate']  <  time()){
                    $List[$k]['status']   =   2;
                }
                if($v['provinceid']){
					$List[$k]['city_n']	=  	$cache['city_name'][$v['provinceid']];
				}
				if($v['cityid']){
					$List[$k]['city_n'].=  	'-'.$cache['city_name'][$v['cityid']];
				}
				if($v['three_cityid']){
					$List[$k]['city_n'].=  	'-'.$cache['city_name'][$v['three_cityid']];
				}
				//图片显示
				$List[$k]['pic_n']  = checkpic($v['pic'],$this->config['sy_once_icon']);
            }
		}
		return $List;
    }
    /**
      * 查询单条信息
      * @param 查询表：once_job
      * @param 功能说明：根据条$id 获取once_job表里面 单条信息
      * @param 引用字段：$id :条件 2:$data:查询字段
      *
     */
    public function getOnceInfo($where,$data=array())
    {
		$select     =   $data['field'] ? $data['field'] : '*'; 
		$Info    =   $this -> select_once('once_job', $where, $select);

		if($Info && is_array($Info)){
			$cache  =  $this -> getClass(array('city'));
			
			if (!empty($Info['pic'])){
			    $Info['pic_n']  =  checkpic($Info['pic'],$this->config['sy_once_icon']);
			}
			if($Info['require']){
				$Info['require_n'] 	= 	str_replace("\r\n","<br>",$Info['require']);
				$Info['require_n'] 	= 	str_replace("\n","<br>",$Info['require_n']);
			}
			if($Info['ctime']){
				$Info['ctime_n']	=	date("Y-m-d",$Info['ctime']);
			}
			if($Info['edate']){
				//有效期天数
				if($Info['edate']>time()){
					$Info['day_n']  =   ceil(($Info['edate']-time())/86400);
				}
				$Info['edate_n']	=	date("Y-m-d",$Info['edate']);
			}
		 
			if($Info['provinceid']){
				$Info['city_n']	=  	$cache['city_name'][$Info['provinceid']];
			}
			if($Info['cityid']){
				$Info['city_n'].=  	'-'.$cache['city_name'][$Info['cityid']];
			}
			if($Info['three_cityid']){
				$Info['city_n'].=  	'-'.$cache['city_name'][$Info['three_cityid']];
			}
		}
        return $Info;
    }
    
    /**
     * @desc    查询店铺招聘数目
     * @param array $where
     * @return boolean|string|void
     */
    public function getOnceNum($where = array()) {
        
        return $this -> select_num('once_job', $where);
        
    }
    
      /**
      * 删除信息
      * @param  表：once_job
      * @param 功能说明：根据条件$id 删除once_job表里面信息
      * @param 引用字段：$id :条件 2:$data:字段
      *
     */
    
    public function delOnce($id,$data=array()){
		if(!empty($id)){
			if(is_array($id)){

				$ids    				=	$id;    
				$return['layertype']	=	1;   
			}else{

				$ids    				=   @explode(',', $id);
				$return['layertype']	=	0;
			}
			$id           	=   pylode(',', $ids);

			$return['id']	=	$this -> delete_all('once_job',array('id' => array('in',$id)),'');
			
			if($return['id']){

				$return['msg']      =  '店铺招聘(ID:'.$id.')删除成功';
				$return['errcode']  =  '9';
			}else{
				
				$return['msg']      =  '店铺招聘(ID:'.$id.')删除失败';
				$return['errcode']  =  '8';
			}
		}else{
			
			$return['msg']      	=  '系统繁忙';
			$return['errcode']  	=  '8';
			$return['layertype']	=	0;
		}

		return $return;
  }
  
    /**
      * 审核信息
      * @param  表：once_job
      * @param 功能说明：根据条件$id 审核once_job表里面信息
      * @param 引用字段：$id :条件 2:$data['status']:审核状态
      *
     */
	public  function setOnceStatus($id,$data = array()){
		
		if(!empty($id)){

			$where['id']	=   array('in',pylode(',',$id));
			$updata  		= array(
				'status' =>  $data['status']==2?1:$data['status'],
			);
			if($data['status']==2){
				$this -> update_once('company_order',array('order_state'=>2),array('once_id'=>array('in',pylode(",",$id)),'order_state'=>1));
				$this -> update_once('once_job',array('pay'=>'2'),array('id'=>array('in',pylode(",",$id)),'pay'=>1));
			}
			$wfpay	=	$this->select_all('once_job',array('id'=>array('in',pylode(",",$id)),'pay'=>'1'));
			if($wfpay){
				$return['status']	=	3;   
			}else{
				$nid	=	$this -> update_once('once_job',$updata,$where);
				if($nid){
					$return['status']	=	$data['status']==2?1:$data['status']; 
				}
			}
			if($nid){
				include_once('log.model.php');
				$logM		=	new log_model($this->db, $this->def);
				$logM->addAdminLog('店铺(ID:'.$id.')审核成功');
			}
			return $return;
		}
	}
	/**
      * 更新信息
      * @param  表：once_job
      *
     */
    public function upOnce($upData = array(),$whereData = array()){
    
		return $this -> update_once('once_job',$upData,$whereData);
	}
   /**
      * 添加、更新信息
      * @param  表：once_job
      * @param 功能说明：根据条件$id 修改once_job表里面信息
      * @param 引用字段：$id :条件 2:$data:引用字段名称
      * @param  $data['password'] ：密码是否修改
      *
     */
  
    public function addOnceInfo($data = array(),$type=''){

		$post	=	$data['post'];
		$id		=	intval($data['id']);
		include_once ('cache.model.php');
        
        $cacheM     =   new cache_model($this->db, $this->def);
        
        $cache      =   $cacheM -> GetCache('city');
        $citymsg 	= 	false;
        if(!empty($cache['city_type'])){
        	if($post['cityid']==''){
        		$citymsg = true;
        	}
        }else{
        	if($post['provinceid']==''){
        		$citymsg = true;
        	}
        }
		if($post['title']==''){
			return array('msg'=>'请填写职位名称！','errcode'=>8);
		}
		if ($post['title'] && mb_strlen($post['title'],'utf8')>30){
			return array('msg'=>'我想招聘标题最多30个字！','errcode'=>8);
		}
		if($post['salary']==''){
			return array('msg'=>'请填写工资！','errcode'=>8);
		}
		if($post['edate']==''){
			return array('msg'=>'请填写有效期！','errcode'=>8);
		}
		if($citymsg){
			return array('msg'=>'请选择工作地区！','errcode'=>8);
		}
		if($post['address']==''){
			return array('msg'=>'请选择工作地点！','errcode'=>8);
		}
		if($post['require']==''){
			return array('msg'=>'请填写招聘要求！','errcode'=>8);
		}
		if($post['companyname']==''){
			return array('msg'=>'请填写店铺名称！','errcode'=>8);
		}
		if($post['phone']==''){
			return array('msg'=>'请填写联系电话！','errcode'=>8);
		}
		if($post['linkman']==''){
			return array('msg'=>'请填写联系人！','errcode'=>8);
		}
		if($type!='admin'){
			include_once ('notice.model.php');
				
			$noticeM		=		new notice_model($this->db, $this->def);
			
			$result			=		$noticeM->jycheck($_POST['authcode'],'店铺招聘');
			
			if(!empty($result)){
				return array('msg'=>$result['msg'],'errcode'=>8);
			}
			
		}

		if($post['password']){
			$post['password']	=	md5($post['password']);
		}else{
			unset($post['password']);
		}

		if($post['file']['tmp_name'] || $post['base']){
		    
			$upArr    =  array(
				'file'      =>  $post['file'],
				'dir'       =>  'once',
				'base'      =>  $post['base'],
                'preview'   =>  $post['preview']
			);
			
			$result  =  $this -> upload($upArr);
		
			if (!empty($result['msg'])){

				$return['msg']		=	$result['msg'];
				$return['errcode']	=	8;
				return $return;
				
			}elseif (!empty($result['picurl'])){

				$pictures 	=  	$result['picurl'];

			}

		}

		unset($post['file']);
		unset($post['base']);
		unset($post['preview']);
		if(isset($pictures)){

			$post['pic']	=	$pictures;

		}
		
		if(!empty($id)){
			if($type!='admin'){
				
				$arr			=	$this->getOnceInfo(array('id'=>$id,'password'=>$post['password']));
				
				if(empty($arr)){
					if($data['utype']=='wap'){
						$return['url']	=	Url('wap',array('c'=>'once','a'=>'show','id'=>$id));
					}
					$return['msg']		=	'密码不正确';
					$return['errcode']	=	8;
					return $return;
				}
			}
			$nid    =   $this ->  update_once('once_job',$post,array('id'=>$id));
			
			if($nid){
				if($this->config['com_fast_status']=="0" && $type !='admin'){
					$msg="修改成功，等待审核！";
				}else{
					$msg="修改成功!";
				}
				if($data['utype']=='wap'){
					$return['url']=Url('wap',array('c'=>'once'));
				}else{
					$return['url']=Url('once');
				}
				$return['msg']		=	$msg;
				$return['errcode']	=	9;
			}else{
				$return['msg']		=	'修改失败！';
				$return['errcode']	=	8;
			}
		}else{
			if($this->config['once_pay_price'] != "0" && $this->config['once_pay_price'] != "" && $type != 'admin'){
				$post['pay']        =   '1';
			}else{
				$post['pay']        =   '2';
			}
			$s_time		=	strtotime(date('Y-m-d 00:00:00')); //今天开始时间
			$m_once		=	$this->getOnceNum(array('login_ip' => fun_ip_get(),'ctime'=>array('>',$s_time)));
			if($this->config['sy_once'] > $m_once || $this->config['sy_once']<1){
				$nid    			=   $this ->  insert_into('once_job',$post);
				$return['id']		=	$nid;
				if($nid){
				    
					$oldorder		=	$this -> select_once("company_order",array('order_state'=>1,'type'=>25,'fast'=>$data['fast']));
					
					if(is_array($oldorder)){
						
						$this -> delete_all('once_job', array('id' => $oldorder['once_id'], 'status' => 0, 'pay' => 1));
						$this -> delete_all('company_order', array('order_state' => 1, 'type' => 25,'fast' => $data['fast']));
					}
					
					if($this->config['once_pay_price'] != "0" && $this->config['once_pay_price'] != "" && $type != 'admin'){
						$return['msg']		=	'发布信息添加完成，请付款';
						$return['errcode']	=	10;
						
						if($data['utype']=='wap'){
						    $return['url']	=	Url('wap',array('c'=>'once','a'=>'pay','id'=>$nid,'price'=>$this->config['once_pay_price']*$post['edate1']));
						}else{//pc
							
						    $return['url']	=	$nid;
						}
					}
					else{
						if($this->config['com_fast_status']=="0" && $type != 'admin'){
							$msg	=	"发布成功，等待审核！";
							
						}else{
							$msg	=	"发布成功!";
						}
						if($data['utype']=='wap'){
							$return['url']=Url('wap',array('c'=>'once'));
						}else{
							$return['url']=Url('once');
						}
						$return['msg']		=	$msg;
						$return['errcode']	=	9;
					}
				}else{
					$return['msg']		=	'发布失败！';
					$return['errcode']	=	8;
				}
			}else{
				$return['msg']		=	'一天内只能发布'.$this->config['sy_once'].'次！';
				$return['errcode']	=	8;
				if($data['utype']=='wap'){
					$return['url']	=	Url('wap',array('c'=>'once'));
				}
			} 
		}
		
		return $return;
	}
	 /**
   * 处理单个图片上传
   * @param file/需上传文件; dir/上传目录; type/上传图片类型; base/需上传base64; preview/pc预览即上传
   */
    private function upload($data = array('file'=>null,'dir'=>null,'type'=>null,'base'=>null,'preview'=>null)){
      
        include_once('upload.model.php');
      
        $UploadM  =  new upload_model($this->db, $this->def);
      
        $upArr  =  array(
            'file'     =>  $data['file'],
            'dir'      =>  $data['dir'],
            'type'     =>  $data['type'],
            'base'     =>  $data['base'],
            'preview'  =>  $data['preview']
        );
        $return  =  $UploadM -> newUpload($upArr);
      
        return $return;
    }
   /**
      * 批量延迟店铺时间
      * @param  表：once_job
      * @param 功能说明：根据条件$id
      * @param 引用字段：$id :条件 2:$data['edate']:延迟时间
      *
     */
	public  function setOnceCtime($id,$data = array()){
		
		$ids		=	@explode(',',$id);
		
		if(is_array($ids)){
			
			$posttime       =   $data['endtime'] * 86400;
			
			$where['id']    =   array('in',pylode(',',$ids));
			$rows   		=   $this  ->  getOnceList($where,array('field'=>'`id`,`edate`'));
			
			foreach($rows as $value){
				
				if($value['edate'] < time()){
					
					$time			=	time()+$posttime;
				}else{
					$time			=	$value['edate']+$posttime;
				}
				
				$return['id']		=	$this -> update_once("once_job",array('edate'=>$time),array('id'=>$value['id']));
			}
			if($return['id']){
				$return['msg']		=	'店铺招聘延期(ID:'.$jobid.')设置成功！';
				$return['errcode']	=	9;
			}else{
				$return['msg']		=	'店铺招聘延期(ID:'.$jobid.')设置失败！';
				$return['errcode']	=	8;
			}
		}else{
			$return['msg']			=	'请选择要延期的店铺招聘！';
			$return['errcode']		=	8;
		}
		return $return;
	}
	//发布店铺招聘付款
	public function payOnce($data=array('from'=>'')){
		
		$id	=	intval($data['id']);
		if(!empty($id)){
			
			$ordernum 	= 	$this -> select_num('company_order',array('once_id'=>$id,'order_state'=>1));
			if($ordernum){
				$this -> delete_all('company_order',array('once_id'=>$id),'');
			}
			$row	=	$this->getOnceInfo(array('id'=>$id,'pay'=>1));
			if(is_array($row)){
				
				//生成相关订单
				$dingdan  =  time().rand(10000,99999);
				$fast     =  time().rand(10000,99999);
				
				$orderData=array(
					'type'			=>	25,
					'order_id'		=>	$dingdan,
					'order_price'	=>	$data['once_price'],
					'order_time'	=>	time(),
					'order_type'	=>	$data['pay_type'],
					'order_state'	=>	1,
					'order_remark'	=>	'店铺招聘收费',
					'did'			=>	$data['did'],
					'once_id'		=>	$id,
					'fast'			=>	$fast
				);
				$nid	=	$this -> insert_into("company_order",$orderData);
				
				if($nid && $dingdan){
					include_once('cookie.model.php');
					$cookieM     =   new cookie_model($this->db, $this->def);
					$cookieM->SetCookie("fast",$fast,time() + 86400);
					
					//订单生成成功
					return array('error'=>0,'oid'=>$nid,'orderid'=>$dingdan,'id'=>$id,'fast'=>$fast);
				}else{
					//生成失败 返回具体原因
					return array('error'=>1,'msg'=>"下单失败");
				}
			}else{
				return array('error'=>1,'msg'=>"店铺数据不存在");
			}
		}
	}
	//管理信息：刷新、修改、删除
	public function setOncePassword($data = array()){
		session_start();
		
		if (!empty($data['code'])){
		    if(md5(strtolower($data['code'])) != $_SESSION['authcode'] || empty($_SESSION['authcode'])){//验证码错误
		        unset($_SESSION['authcode']);
		        return array('msg'=>'验证码错误！','errcode'=>8,'type'=>1);
		    }
		}
		
		$jobinfo	=	$this->getOnceInfo(array('id'=>(int)$data['id'],'password'=>md5($data['password'])));
		
		if(!is_array($jobinfo) || empty($jobinfo)){
			return array('msg'=>'密码错误！','errcode'=>8,'type'=>2);
		}else{
			$_SESSION['oncepass'] = md5($data['password']);
		}
		if($data['type']==1){//刷新
			if($this->config['com_xin']>$jobinfo['sxnumber']){
				
				$this->upOnce(array('ctime'=>time(),'sxtime'=>time(),'sxnumber'=>$jobinfo['sxnumber']+1),array('id'=>(int)$jobinfo['id']));
				
				return array('msg'=>'刷新成功','errcode'=>9,'type'=>3);
			}else{
				return array('msg'=>'对不起你已达到一天最多刷新次数！','errcode'=>8,'type'=>5);
			}
			
		}elseif($data['type']==3){//删除
			
			$this -> delOnce((int)$jobinfo['id']);
			return array('msg'=>'删除成功！','errcode'=>9,'type'=>4);
			
		}else{//修改
			if($data['utype']=='pc'){
				$url	=	Url('once',array('c'=>'add','id'=>(int)$jobinfo['id']));
				return array('url'=>$url);
			}elseif($data['utype']=='wap'){
				$url	=	Url('wap',array('c'=>'once','a'=>'add','id'=>(int)$jobinfo['id']));
				return array('url'=>$url);
			}else{
				return array('msg'=>'密码正确！','errcode'=>9,'type'=>2);
			}
			
		}
	}
}
?>