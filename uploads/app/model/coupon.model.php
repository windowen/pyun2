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
class coupon_model extends model{
	//请右转使用qrorder类
    function upuser_statis($order){

 		
	}
	/**
     * @desc 引用log类，添加用户日志
     *
     * @param   int     $uid
     * @param   int     $usertype
     * @param   string  $content
     * @param   string  $opera
     * @param   string  $type
     * @return  boolean
     */
    private function addMemberLog($uid, $usertype, $content, $opera = '', $type = '')
    {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        
        return $LogM->addMemberLog($uid, $usertype, $content, $opera = '', $type = '');
    }
	/**
	 * @desc 获取优惠券信息
	 * $whereData		查询条件
	 * $data			自定义处理数组
	 */
	public function getInfo($whereData, $data = array()){
	
		$Coupon           =	  array();
		
		$data['field']    =	  empty($data['field']) ? '*' : $data['field'];
		
		$Coupon           =	  $this -> select_once('coupon', $whereData, $data['field']);
		
		return	$Coupon;

	}
		/**
	* @desc 获取优惠券列表
	* $whereData		查询条件
	* $data			自定义处理数组
	*/
	public function getList($whereData = array(), $data = array()){
	    
	    $ListCoupon		=	array();
	    $data['field']	=	empty($data['field']) ? '*' : $data['field'];
	    $ListCoupon		=	$this -> select_all('coupon', $whereData, $data['field']);
	    return	$ListCoupon;
	    
	}
	/**
	 * @desc 添加修改优惠券信息
	 * $whereData		查询条件
	 * $data			自定义处理数组
	 */
	public function setInfo($data = array(),$whereData = array()){
		if(trim($data['name'])==''){
			
			return array('msg'=>'优惠券名称不能为空！','errcode'=>8);
			
		}else{
			$updata	=	array(
				'name'		=>	$data['name'],
				
				'time'		=>	$data['time'],
				
				'amount'	=>	$data['amount'],
				
				'scope'		=>	$data['scope']
			);
		}
		
		if(!empty($whereData)){
			
			$return['id']	=	  $this -> update_once('coupon',$updata, $whereData);
			
			$return['msg']	=	'优惠券(ID:'.$data['id'].')更新';
		}else{
			
			$return['id']	=	  $this -> insert_into('coupon',$updata);
			
			$return['msg']	=	'优惠券(ID:'.$return['id'].')添加';
		}
		$return['errcode']	=	$return['id'] ? '9' :'8';
		
		$return['msg']		=	$return['id'] ? $return['msg'].'成功！' :$return['msg'].'失败！';
		
		return	$return;
	}
	/**
	 * @desc 删除优惠券信息
	 * $whereData		查询条件
	 * $data			自定义处理数组
	 */
	public function delInfo($delId){
	    
		if(!empty($delId)){
			
			$return['layertype']	=	0;
			
			if(is_array($delId)){
				
				$delId	=	pylode(',',$delId);
				
				$return['layertype']	=	1;
			}
		}
	 
		$return['id']		=	  $this -> delete_all('coupon',array('id'=>array('in',$delId)),'');
		
		$return['msg']		=	'优惠券(ID:'.$delId.')';
		
		$return['errcode']	=	$return['id'] ? '9' :'8';
		
		$return['msg']		=	$return['id'] ? $return['msg'].'删除成功！' :$return['msg'].'删除失败！';
		
		return	$return;
	}
	
	/**
     * @desc 获取coupon      优惠券记录详情
     * $whereData       查询条件
     * $data            自定义处理数组
     */
	public function getCouponList($whereData, $data = array()){

		$List			=	array();
		$data['field']	=	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('coupon_list', $whereData, $data['field']);
		if($data['source']){
			//获取赠送优惠券的企业
			if($List&&is_array($List)){
				$source	=	array();
				foreach($List as $val){
					if(in_array($val['source'],$source)==false&&$val['source']){
						$source[]	=	$val['source'];
					} 
				}
				if($source&&is_array($source)){
					$company	=	$this	->	select_all("company",array('uid'=>array('in',pylode(',',$source))),'uid,name'); 
				}
				foreach($List as $k=>$v){
					foreach($company as $val){
						if($val['uid']==$v['source']){
							$List[$k]['sourcename']	=	$val['name'];
						}
					}
					$List[$k]['ctime_n']	=	date('Y-m-d',$v['ctime']);
					$List[$k]['validity_n']	=	date('Y-m-d',$v['validity']);
				}
			}
		}elseif($data['utype']=='admin'){
			if($List&&is_array($List)){
				foreach($List as $v){
					$uids[]=$v['uid'];
				}
				$member=$this->select_all('member',array('uid'=>array('in',pylode(",",$uids))),'`uid`,`username`');
				foreach($List as $k=>$v)
				{
					foreach($member as $val)
					{
						if($v['uid']==$val['uid'])
						{
							$List[$k]['username']=$val['username'];
						}
					}
				}
			}
		}
		
		return	$List;

	}
	/**
     * @desc 获取coupon      优惠券记录详情
     * $whereData       查询条件
     * $data            自定义处理数组
     */
	public function getCouponListOne($whereData, $data = array()){

		$List			=	array();
		$data['field']	=	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_once('coupon_list', $whereData, $data['field']);
		return	$List;

	}
	/**
     * @desc 获取coupon      优惠券计数
     * $whereData       查询条件
     * $data            自定义处理数组
     */
	public function getCouponNum($whereData, $data = array()){

		$num			=	$this -> select_num('coupon_list', $whereData);
		return	$num;

	}
	/*
	 * 更新优惠券
	 * $whereData 	查询条件
	 * $addData 	更新数据 
	 
	 */
	function upCouponList($whereData=array(),$addData=array(),$data=array()){
		if($data['send']){
			//商家赠送优惠券
			if($addData['uid']==''){
				$return['msg']	=	'请选择要赠送的企业！';
				$return['cod']	=	8;
			}else{
				$row	=	$this	->	getCouponListOne(array('uid'=>$whereData['uid'],'id'=>$data['coupon'],'status'=>'1','validity'=>array('>',time())));
				if($row['id']){
					$whereData['id']=	intval($row['id']);
					$nid			=	$this	->	update_once('coupon_list',$addData,$whereData);
					$return['msg']	=	$nid	?	'操作成功！':"操作失败！";
					$return['cod']	=	$nid	?	9:8;
					
				}else{
					$return['msg']	=	"非法操作！";
					$return['cod']	=	8;
				}
			}

		}else{
			$return	=	$this	->	update_once('coupon_list',$addData,$whereData);
		}
		
		return	$return;
	}
	/*
	 * 添加优惠券
	 * $data 		自定义
	 * $addData 	更新数据 
	 
	 */
	function addCouponList($addData=array(),$data=array()){
		
		if(!empty($addData)){
			
			if($addData['username']==""){
				
				return array('msg'=>'请输入用户名！','errcode'=>8);
				
			}
			
			$member	=	$this->select_once('member',array('username'=>$addData['username']));
			
			if(empty($member['uid'])){
				
				return array('msg'=>'该用户名不存在！','errcode'=>8);
				
			}else{
				if(empty($_POST['coupon'])){
					
					return array('msg'=>'请选择优惠券！','errcode'=>8);
					
				}else{
					$coupon		=	$this->getInfo(array('id'=>$addData['coupon']));
					
					$validity	=	time()+$coupon['time']*86400;
					
					$data	=	array(
						'uid'			=>	$member['uid'],
						
						'number'		=>	time(),
						
						'ctime'			=>	time(),
						
						'coupon_id'		=>	$coupon['id'],
						
						'coupon_name'	=>	$coupon['name'],
						
						'validity'		=>	$validity,
						
						'coupon_amount'	=>	$coupon['amount'],
						
						'coupon_scope'	=>	$coupon['scope']
					);
					
					$return['id']		=	$this	->	insert_into("coupon_list",$data);
					
					$return['errcode']	=	$return['id']?'9':'8';
					
					$return['msg']		=	'赠送给“'.$addData['username'].'”'.$coupon['name'].'成功！';
				}
			}
		}
		
		return	$return;
	}
	/**
      * 删除优惠券
      * 
      *
     */
	function delCouponList($whereData=array(),$data=array()){
		 
		$result	=	$this -> delete_all('coupon_list',$whereData,'');
		if($result){
			$this	->	addMemberLog($data['uid'],$data['usertype'],"删除优惠券",24,3);
			
			$return['msg']	=	"删除成功！";
			$return['cod']	=	9;
		}else{
			$return['msg']	=	"删除失败！";
			$return['cod']	=	8;
		}
			
		
		return $return;  
		
	}
	
	/**
	 * @desc   引用statis类，获取账户套餐数据信息
	 */
	private function getStatisInfo($uid, $data = array()) {
	    
	    require_once ('statis.model.php');
	    
	    $StatisM = new statis_model($this->db, $this->def);
	    
	    return  $StatisM -> getInfo($uid , $data);
	}
	
	
	/**
	 * @desc   优惠券全额支付购买
	 * @param  array $data
	 */
	public function couponBuy($data = array()) 
	{
	    if ($data['uid']) {

	    	if($data['usertype']==2){
                $single_can = @explode(',', $this->config['com_single_can']);
            }else{
                $single_can = @explode(',', $this->config['lt_single_can']);
            }
            if($data['server']!='vip' && $data['server']!='pack' && $data['server']!='autojob'){

            	$serverCheck = $data['server'];
                if($data['server']=='sxltjob'||$data['server']=='sxpart'||$data['server']=='sxjob'){
                    $serverCheck = 'sxjob';
                }
                if($data['server']=='partrec'){
                    $serverCheck = 'jobrec';
                }
                if($serverCheck && !in_array($serverCheck,$single_can)){
                    return  array(
                        'error' => 1,
                        'msg'   => '该服务已关闭单项购买，请选择其它购买方式'
                    );
                }
            }
            
	        if ($data['server'] == 'autojob') {
	            
	            $return = $this->buyAutoJob($data);
	        } elseif ($data['server'] == 'jobtop') {
	            
	            $return = $this->buyZdJob($data);
	        } elseif ($data['server'] == 'jobrec') {
	            
	            $return = $this->buyRecJob($data);
	        } elseif ($data['server'] == 'joburgent') {
	            
	            $return = $this->buyUrgentJob($data);
	        } elseif ($data['server'] == 'sxjob') {
	            
	            $return = $this->buyRefreshJob($data);
	        } elseif ($data['server'] == 'sxltjob') {
	            
	            $return = $this->buyRefreshLtJob($data);
	        } elseif ($data['server'] == 'downresume') {
	            
	            $return = $this->downresume($data);
	        } elseif ($data['server'] == 'issuejob') {
	            
	            $return = $this->buyIssueJob($data);
	        } elseif ($data['server'] == 'invite') {
	            
	            $return = $this->buyInviteResume($data);
	        } elseif ($data['server'] == 'pack') {
	            
	            $return = $this->buyPackOrder($data);
	        } elseif ($data['server'] == 'vip') {
	            
	            $return = $this->buyVip($data);
	        } elseif ($data['server'] == 'sxpart') {
	            
	            $return = $this->buyRefreshPart($data);
	        } elseif ($data['server'] == 'partrec') {
	            
	            $return = $this->buyRecPart($data);
	        } elseif ($data['server'] == 'createson') {
	            
	            $return = $this->buyCreateSon($data);
	            
	        } elseif ($data['server'] == 'zph') {
	            
	            $return = $this->buyZph($data);
	            
	        }
	        if ($return['status'] == 1) {
	            
	            $status = 1;
	            // 订单生成成功
	            $return = array(
	                'error' => 0,
	                'msg'   => $return['msg']
	            );
	        } else {
	            $status = 2;
	            // 生成失败 返回具体原因
	            $return = array(
	                'error' => 1,
	                'msg'   => $return['error'],
	                'url'   => $return['url']
	            );
	        }
	        
	    }else{
	        $return = array(
	            'error' => 1,
	            'msg'   => '请先登录'
	        );
	    }
	    return $return;
	}
	
	/**
	 * @desc  使用优惠券购买会员
	 * @param  array $data
	 * @return  array $return
	 */
	private function buyVip($data = array()) {
	        
        $uid        =   intval($data['uid']);
        
        $username   =   trim($data['username']);
        
        $usertype   =   intval($data['usertype']);
        
        $return     =   array();
        
        if($data['ratingid'] && $data['coupon_id']){
            
            $id         =   intval($data['ratingid']);        //  会员等级id
            
            $couponid   =   intval($data['coupon_id']);  //  优惠券id
            
            //  判断套餐ID真实性
            $ratinginfo =	$this -> select_once('company_rating',array('id'=>$id));
            
            //  判断优惠券真实性
            $coupon     =   $this -> getCouponListOne(array('id' => $couponid, 'uid' => $uid, 'status' => 1, 'validity' => array('>' , time())));
            
            if(empty($ratinginfo)){
                
                $return['error']    =	'该会员套餐已下架，请重新选择！';
                
            }else if(empty($coupon)){
                
                $return['error']    =   '优惠券不可用，请重新选择！';
                
            }else {
                
                if($ratinginfo['time_start'] < time() && $ratinginfo['time_end'] > time()){
                    
                    $price  =   $ratinginfo['yh_price'] ;    
                    
                }else{
                    
                    $price  =   $ratinginfo['service_price'];   
                }
                
                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
                    
                    require_once('rating.model.php');
                    
                    $rating = new rating_model($this->db,$this->def,array('uid'=>$uid,'username'=>$username,'usertype'=>$usertype));
                    
                    if($usertype == 2){
                        
                        $value				=	$rating	->	ratingInfo($id, $uid);
                        
                        $return['status']	=   $this -> update_once('company_statis',$value,array('uid' => $uid));
                        
                        if ($return['status']) {
                            
                            $companydata     =	array(
                                'rating'	    =>	$value['rating'],
                                'rating_name'	=>	$value['rating_name'],
                                'vipetime'		=>	$value['vip_etime'],
                                'vipstime'		=>	$value['vip_stime']
                            );
                            
                            $this -> update_once('company', $companydata, array('uid' => $uid));
                        }
                        
                        $this	->	update_once('company_job',array('rating' => $id),array('uid'=> $uid));
                        
                    }
                    
                    require_once('integral.model.php');
                    
                    $integral   =   new integral_model($this->db,$this->def,array('uid'=>$uid,'username'=>$username,'usertype'=>$usertype));
                    
                    $integral -> company_invtal($uid, $usertype, $price, false, '使用优惠券，购买会员', true, 2 , 'yhq', 27, '', $couponid);
                        
                    $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
                    
                    $return['status']   =   '1';
                    $return['msg']		=	'会员购买成功';
                    
                    $this -> addMemberLog($uid, $usertype, '会员'.$ratinginfo['name'].'购买成功');
                    
                } else{
                    
                    $return['error']   =   '优惠券不可用，请重新选择！';
                }
            }
            
        } else {
            
            $return['error'] = '参数错误，请重试！';
        }
        
        return $return;
	}
	
	/**
	 * @desc   优惠券购买增值服务
	 * @param  array $data
	 * @return string[]
	 */
	private function buyPackOrder($data){
	    
	    $uid      =   intval($data['uid']);
	    
	    $usertype =   intval($data['usertype']);
	    
	    $username =   trim($data['username']);
	    
	    $return   =   array();
	    
	    if($data['tcid']){
	        
	        $tid        =	intval($data['tcid']);
	        
	        $couponid   =   intval($data['coupon_id']);
	        
	        if($tid){
	            
	            //判断套餐ID真实性
	            $tb_service =   $usertype == 2 ? 'company_service_detail' : 'lt_service_detail' ;
	            
	            $service	=	$this -> select_once($tb_service , array('id' => $tid));
	            
	            if(empty($service)){
	                
	                $return['error']		=	'请选择正确增值套餐！';
	                
	            }else {
	                
	                $statis	=	$this -> getStatisInfo($uid, array('usertype' => $usertype, 'field'=>'`rating`,`vip_etime`'));
	                
	                if(!isVip($statis['vip_etime'])){
	                    
	                    
	                    $return['error'] =	'您的会员已到期，请先购买会员！';
	                    
	                }else{
	                    
	                    $rating			=	$this -> select_once('company_rating',array('id'=>$statis['rating']),'service_discount');//增值服务折扣
	                    
	                    if($rating['service_discount']){
	                        
	                        $discount	=	intval($rating['service_discount']);
	                        
	                        $price		=	$service['service_price'] * $discount * 0.01 ;
	                        
	                    }else{
	                        
	                        $price		=	$service['service_price'];
	                        
	                    }
	                    
	                    /*  优惠券额度优先扣除 */
	                    $couponWhere=   array(
	                        
	                        'id'            =>  $couponid,
	                        'uid'           =>  $uid,
	                        'status'        =>  1,
	                        'validity'      =>  array('>', time()),
	                        'coupon_amount' =>  array('>=', $price),
	                        'coupon_scope'  =>  array('<=', $price)
	                    );
	                    
	                    $coupon    =   $this->getCouponListOne($couponWhere);
	                    
	                    if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                        
	                        if($usertype == 2){
	                            
	                            $value		=	array(
	                                
	                                'job_num'		=>	array('+', $service['job_num']?$service['job_num']:0),
	                                'breakjob_num'	=>	array('+', $service['breakjob_num']?$service['breakjob_num']:0),
	                                'down_resume'	=>	array('+', $service['resume']?$service['resume']:0),
	                                'invite_resume'	=>	array('+', $service['interview']?$service['interview']:0),
	                                'zph_num'	    =>	array('+', $service['zph_num']?$service['zph_num']:0),
	                                'top_num'	    =>	array('+', $service['top_num']?$service['top_num']:0),
	                                'rec_num'	    =>	array('+', $service['rec_num']?$service['rec_num']:0),
	                                'urgent_num'    =>	array('+', $service['urgent_num']?$service['urgent_num']:0),
	                            );
	                            
	                            $status	=	$this	->	update_once('company_statis',$value,array('uid' => $uid));
	                            
	                        }
	                        
	                        if($status){
	                            
	                            require_once('integral.model.php');
	                            
	                            $integral    =   new integral_model($this->db,$this->def,array('uid'=>$uid,'username'=>$username,'usertype'=>$usertype));
	                            
	                            $integral    ->	company_invtal($uid, $usertype, $price, false, '使用优惠券，购买增值服务', true, 2 , 'yhq', 27, '', $couponid);
	                            
	                            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                            
	                            $return['status']  =   '1';
    	                            
	                            $return['msg']     =	'增值包购买成功';
	                            
	                            $this	->	addMemberLog($uid,$usertype,'增值包购买成功');
	                            
	                        }
	                        
	                    }else{
	                        
	                        $return['error']   =   '优惠券不可用，请重新选择！';
	                    }
	                }
	            }
	        }else{
	            
	            $return['error']	=	'请正确选择增值服务套餐！';
	        }
	    } else {
	        
	        $return['error']	=	'参数填写错误，请重新设置！';
	    }
	    
	     
	    return $return;
	}
	
	/**
	 * @desc   优惠券使用，购买职位发布
	 * @param  array $data
	 * @return string[]
	 */
	private function buyIssueJob($data)
	{
	    
	    $uid        =   intval($data['uid']);
	    $usertype   =   intval($data['usertype']);
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	    
	    require_once ('statis.model.php');
	    $StatisM    =   new statis_model($this->db, $this->def);
	     
	    $price      =   $this->config['integral_job'];
	    
	    $couponid   =   intval($data['coupon_id']);
	    
	    /*  优惠券额度优先扣除 */
	    $couponWhere=   array(
	        
	        'id'            =>  $couponid,
	        'uid'           =>  $uid,
	        'status'        =>  1,
	        'validity'      =>  array('>', time()),
	        'coupon_amount' =>  array('>=', $price),
	        'coupon_scope'  =>  array('<=', $price)
	    );
	    
	    $coupon    =   $this->getCouponListOne($couponWhere);
	    
	    if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	        
	        // 优惠券购买，会员发布职位套餐加1
	        $sValue =   array('job_num' => array('+', 1));
	        
	        $status =   $StatisM -> upInfo($sValue, array('uid' => $uid, 'usertype' => $usertype));
	        
	        if ($status) {
	            
	            require_once('integral.model.php');
	            
	            $integral    =   new integral_model($this->db,$this->def,array('uid'=>$uid,'username'=>$username,'usertype'=>$usertype));
	            
	            $integral -> company_invtal($uid, $usertype, $price, false, '使用优惠券，购买职位发布', true, 2 , 'yhq', 12, '', $couponid);
	            
	            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	            
	            $return['status']   =   '1';
	            
	            $return['msg']      =   '购买职位发布成功';
	            
	            $this->addMemberLog($uid, $usertype, '购买职位发布成功', 1, 1);
	        }
	        
	    }else{
	        
	        $return['error']   =   '优惠券不可用，请重新选择！';
	    }
	    
	     
	    return $return;
	}
	
	/**
	 * @desc   优惠券购买，职位刷新
	 * @param  array $data
	 * @return string[]
	 */
	private function buyRefreshJob($data)
	{
	    $uid        =   intval($data['uid']);
	    
	    $usertype   =   intval($data['usertype']);
	    
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	     
	    if ($data['sxjobid']) {
	        
	        $sxjobid   =   pylode(',', @explode(',', $data['sxjobid']));
	        
	        if ($sxjobid) {
	            
	            $statis     =   $this -> getStatisInfo($uid,array('usertype' => $usertype, 'field'=>'`breakjob_num`'));
	            
	            $breakjob_num   =   intval($statis['breakjob_num']);
	            
	            // 判断职位ID真实性
	            $jobs   =  $this -> select_all('company_job', array('uid' => $uid, 'id' => array('in', $sxjobid)), '`id`,`name`');
	            
	            if (empty($jobs)) {
	                
	                $return['error'] = '请选择正确的职位刷新！';
	            } else {
	                
	                $jobnum     =   $this->select_num('company_job', array('uid' => $uid, 'id' => array('in', $sxjobid)));
	                
	                // 优先扣除套餐
	                
	                if ($breakjob_num) {
	                    
	                    $jobnum =  $jobnum - $breakjob_num;
	                }
	                
	                $price      =   $jobnum * $this->config['integral_jobefresh'];
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon    =   $this->getCouponListOne($couponWhere);
	                 
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    // 优惠券购买，直接职位刷新
	                    $status =   $this->update_once('company_job', array('lastupdate' => time()), array('id' => array('in', $sxjobid)));
	                    
	                    $this -> update_once('company', array('lastupdate' => time()), array('uid' => $uid));
	                    
	                    if ($breakjob_num) {
	                        
	                        $this->update_once('company_statis', array('breakjob_num' => '0'), array('uid' => $uid));
	                    }
	                    
	                    if ($status) {
	                        
	                        $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                        require_once ('integral.model.php');
	                        
	                        $integral = new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
                            
	                        $integral -> company_invtal($uid, $usertype, $price, false, '使用优惠券，购买刷新职位', true, 2, 'yhq', 12, '', $couponid);
	                        
	                        if ($jobnum == 1) {
	                            
	                            $this -> addMemberLog($data['uid'], $data['usertype'], '刷新职位《'.$jobs[0]['name'].'》', 1, 4);
	                        } else {
	                            
	                            $this -> addMemberLog($data['uid'], $data['usertype'], '批量刷新职位', 1, 4);
	                        }
	                        
	                        $return['status'] = '1';
	                        
	                        $return['msg'] = '职位刷新成功';
	                    }
	                } else{
	                    
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请先选择职位！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	    return $return;
	}
	
	/**
	 * @desc 优惠券购买，兼职刷新
	 * @param array $data
	 * @return string[]
	 */
	private function buyRefreshPart($data)
	{
	    
	    $uid        =   intval($data['uid']);
	    $usertype   =   intval($data['usertype']);
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	     
	    if ($data['sxpartid']) {
	        
	        $sxpartid   =   pylode(',', @explode(',', $data['sxpartid']));
	        
	        if ($sxpartid) {
	            
	            $statis     =   $this -> getStatisInfo($uid,array('usertype' => $usertype, 'field'=>'`integral`,`breakjob_num`'));
	            
	            $breakjob_num   =   intval($statis['breakjob_num']);
	            
	            // 判断职位ID真实性
	            $parts  =   $this->select_all('partjob', array('uid' => $uid,'id' => array('in', $sxpartid)), '`id`,`name`');
	            
	            if (empty($parts)) {
	                
	                $return['error'] = '请选择正确的职位刷新！';
	            } else {
	                
	                $partnum = $this->select_num('partjob', array('uid' => $uid,'id' => array('in', $sxpartid)));
	                
	                // 优先扣除套餐
	                if ($breakjob_num) {
	                    
	                    $partnum = $partnum - $breakjob_num;
	                }
	                
	                $price      =   $partnum * $this->config['integral_jobefresh'];
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon    =   $this->getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    // 积分抵扣，直接刷新兼职
	                    $status =   $this->update_once('partjob', array('lastupdate' => time()), array('id' => array('in', $sxpartid)));
	                    
	                    if ($status) {
	                        
	                        if ($breakjob_num) {
	                            
	                            $this->update_once('company_statis', array('breakjob_num' => '0'), array('uid' => $uid));
	                        }
	                        
 	                        $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                        
	                        require_once ('integral.model.php');
	                        
	                        $integral = new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
 	                            
	                        $integral -> company_invtal($uid, $usertype, $price, false, '使用优惠券，购买兼职刷新', true, 2, 'yhq', 12, '', $couponid);
	                        
	                        if ($partnum == 1) {
	                            
	                            $this->addMemberLog($uid, $data['usertype'], '刷新兼职《'.$parts[0]['name'].'》', 9, 4);
	                        } else {
	                            
	                            $this->addMemberLog($uid, $data['usertype'], '批量刷新职位', 9, 4);
	                        }
	                        
	                        $return['status'] = '1';
	                        
	                        $return['msg'] = '兼职刷新成功';
	                    }
	                    
	                } else {
	                    
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请正确选择的职位刷新！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	    
	    return $return;
	}
	
	
	
	/**
	 * @desc   优惠券下载简历
	 * @param array $data
	 * @return string[]
	 */
	private function downresume($data){
	    
	    $uid	  =	  intval($data['uid']);
	    
	    $usertype =   intval($data['usertype']);
	    
	    $username =   trim($data['username']);
	    
	    $did      =   $data['did'] ? $data['did'] : $this -> config['did'];
	    
	    $return   =   array();
	    
	    require_once('integral.model.php');
	    
	    $integral  =   new integral_model($this->db,$this->def,array('uid' => $uid,'username' => $username,'usertype' => $usertype));
	    
	    require_once ('resume.model.php');
	    
	    $resumeM   =   new resume_model($this->db, $this->def);
	     
	    if($data['eid']){
	        
	        $eid   =   intval($data['eid']);
	        
	        if($eid){
	            
	            $isDownresume   =   $this->select_once('down_resume', array('eid' => $eid, 'comid' => $uid,'usertype'=>$usertype));
	            
	            if (!empty($isDownresume)) {
	                
	                $return['msg']      =   '您已经下载过该份简历，请直接查看！';
	                $return['status']   =   '1';
	                
	                return $return;
	            }
	            
	            //判断简历ID真实性
	            $user       =  $this->select_once('resume_expect',array('id'=>$eid), '`id`,`uid`,`name`');
	            
	            $downdata   =   array();
	            
	            $downdata['eid']        =   $user['id'];
	            $downdata['uid']        =   $user['uid'];
	            $downdata['usertype']   =   $usertype;
	            $downdata['comid']      =   $uid;
	            $downdata['did']        =   $did;
	            $downdata['downtime']   =   time();
	            
	            if(empty($user)){
	                
	                $return['error']    =   '请选择正确的简历下载！';
	                
	            }else {
	                
	                $price      =   $resumeM -> setDayprice($eid);
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon     =   $this -> getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    // 优惠券购买，直接下载简历
	                    $nid = $this -> insert_into('down_resume',$downdata);
	                    
	                    if($nid){
	                            
	                        $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                        
                            $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券，购买下载简历', true, 2, 'yhq', 12, $eid, $couponid);
                        
	                        $this -> update_once('resume_expect',array('dnum'=>array('+','1')),array('id'=>$eid));
	                        
	                        $this -> addMemberLog($uid, $usertype,'下载了简历：'.$user['name'], 3, 1);
	                        
	                        $return['status']   =   '1';
	                        
	                        $return['msg']      =   '购买简历下载成功';
	                    }
	                    
	                } else {
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重试！';
	    }
	    return $return;
	}
	
    /**
     * @desc    优惠券购买邀请面试
     * @param array $data
     * @return string[]
     */
	private function buyInviteResume($data)
	{
	    $uid        =   intval($data['uid']);
	    $usertype   =   intval($data['usertype']);
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	    
	    require_once ('statis.model.php');
	    
	    $StatisM    = new statis_model($this->db, $this->def);
	    
	    require_once ('integral.model.php');
	    
	    $integral   = new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
	    
	    if (!$data['uid']) {
	        
	        $return['error'] = '用户不存在，请重新登录';
	    } else {
	        
	        $price      =   $this->config['integral_interview'];
	        
	        $couponid   =   intval($data['coupon_id']);
	        
	        /*  优惠券额度优先扣除 */
	        $couponWhere=   array(
	            
	            'id'            =>  $couponid,
	            'uid'           =>  $uid,
	            'status'        =>  1,
	            'validity'      =>  array('>', time()),
	            'coupon_amount' =>  array('>=', $price),
	            'coupon_scope'  =>  array('<=', $price)
	        );
	        
	        $coupon     =   $this -> getCouponListOne($couponWhere);
	        
	        if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	            
	            $status = $StatisM -> upInfo(array('invite_resume' => array('+', 1)), array('uid' => $uid, 'usertype' => $usertype));
	            
	            if ($status) {
	                
	                $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                
	                $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，购买邀请面试', true, 2, 'yhq', 12, '', $couponid);
	                
	                $return['status']   =   '1';
	                
	                $return['msg']      =   '购买面试邀请成功';
	                
	                $this->addMemberLog($data['uid'], $uid, '购买邀请面试', 4, 1);
	            }
	        }
	    }
	    return $return;
	}
	
	/**
	 * @desc   优惠券购买职位置顶
	 * @param array $data
	 * @return string[]
	 */
	private function buyZdJob($data)
	{
	    $uid        =   intval($data['uid']);
	    
	    $usertype   =   intval($data['usertype']);
	    
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	    
	    if ($data['zdjobid'] && ($data['days'] || $data['xdays'])) {
	        
	        $jobid  =   $data['zdjobid'];
	        
	        // 判断置顶天数
	        $xsdays =   intval($data['days']) > 0 ? intval($data['days']) : (intval($data['xdays']) > 1 ? intval($data['xdays']) : 1);
	        
	        if ($xsdays > 0 && $jobid) {
	            
	            // 判断职位ID真实性
	            $job    =   $this -> select_once('company_job', array('uid' => $uid, 'id' => $jobid));
	            
	            if (empty($job)) {
	                
	                $return['error'] = '请选择正确的职位置顶！';
	                
	            } else {
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                $price      =   $xsdays * $this->config['integral_job_top'];
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon     =   $this -> getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    $xsjob  =   $this->select_once('company_job', array('id' => $jobid), 'name,xsdate');
	                    
	                    if (! empty($xsjob)) {
	                        
	                        if ($xsjob['xsdate'] > time()) {
	                            
	                            $xsdate = $xsjob['xsdate'] + $xsdays * 86400;
	                        } else {
	                            
	                            $xsdate = strtotime('+' . $xsdays . ' day');
	                        }
	                        
	                        $status     =   $this->update_once('company_job', array('xsdate' => $xsdate), array('uid' => $uid, 'id' => $jobid));
	                        
	                        if ($status) {
	                            
	                            require_once ('integral.model.php');
	                            
	                            $integral   =   new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
	                                
	                            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                            
                                $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，购买职位置顶', true, 2, 'yhq', 12, '', $couponid);
                             
	                            $return['status'] = '1';
	                            
	                            $return['msg'] = '职位置顶购买成功';
	                            
	                            $this->addMemberLog($uid, $usertype, '职位置顶购买成功', 1, 4);
	                        }
	                    }
	                } else {
	                    
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请正确选择职位置顶以及置顶的天数！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	    return $return;
	}
	
	/**
	 * @desc   优惠券购买职位推荐
	 * @param array $data
	 * @return string[]
	 */
	private function buyRecJob($data)
	{
	    $uid        =   intval($data['uid']);
	    
	    $usertype   =   intval($data['usertype']);
	    
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	    
	    if ($data['recjobid'] && ($data['days'] || $data['xdays'])) {
	        
	        $jobid      =   $data['recjobid'];
	        
	        // 判断推荐天数
	        $recdays    =   intval($data['days']) > 0 ? intval($data['days']) : (intval($data['xdays']) > 1 ? intval($data['xdays']) : 1);
	        
	        if ($recdays > 0 && $jobid) {
	            
	            // 判断职位ID真实性
	            $job    =   $this -> select_once('company_job', array('uid' => $uid, 'id' => $jobid));
	            
	            if (empty($job)) {
	                
	                $return['error']    =   '请选择正确的职位推荐！';
	            } else {
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                $price      =   $recdays * $this->config['com_recjob'];
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon     =   $this -> getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    $recjob     =   $this -> select_once('company_job', array('id' => $jobid), '`name`,`rec_time`');
	                    
	                    if (! empty($recjob)) {
	                        
	                        if ($recjob['rec_time'] > time()) {
	                            
	                            $rec_time = $recjob['rec_time'] + $recdays * 86400;
	                        } else {
	                            
	                            $rec_time = time() + $recdays * 86400;
	                        }
	                        
	                        $status =   $this->update_once('company_job', array('rec_time' => $rec_time, 'rec' => '1' ), array('uid' => $uid, 'id' => $jobid));
	                        
	                        if ($status) {
	                            
	                            require_once ('integral.model.php');
	                            
	                            $integral   =   new integral_model($this->db, $this->def, array('uid' => $uid,  'username' => $username, 'usertype' => $usertype));
	                            
	                            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                            
                                $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，购买职位推荐', true, 2, 'yhq', 12, '', $couponid);
	                            
	                            $return['status']   =   '1';
	                            
	                            $return['msg']      =   '职位推荐购买成功';
	                            
	                            $this -> addMemberLog($uid, $usertype, '职位推荐购买成功', 1, 4);
	                        }
	                    }
	                } else {
	                    
	                     $return['error']  =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请正确选择职位推荐以及推荐的时长！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	    
	    return $return;
	}
	
	/**
	 * @desc   优惠券购买兼职推荐
	 * @param array $data
	 * @return string[]
	 */
	private function buyRecPart($data)
	{
	    
	    $uid        =   intval($data['uid']);
	    
	    $usertype   =   intval($data['usertype']);
	    
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	     
	    if ($data['recpartid'] && ($data['days'] || $data['xdays'])) {
	        
	        $partid     =   $data['recpartid'];
	        
	        // 判断推荐天数
	        $recdays    =   intval($data['days']) > 0 ? intval($data['days']) : (intval($data['xdays']) > 1 ? intval($data['xdays']) : 1);
	        
	        if ($recdays > 0 && $partid) {
	            
	            // 判断职位ID真实性
	            $part   =   $this->select_once('partjob', array('uid' => $data['uid'], 'id' => $partid));
	            
	            if (empty($part)) {
	                
	                $return['error'] = '请选择正确的职位推荐！';
	                
	            } else {
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                $price      =   $recdays * $this->config['com_recjob'];
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon     =   $this -> getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    $recjob     =   $this->select_once('partjob', array('id' => $partid), '`name`,`rec_time`');
	                    
	                    if (! empty($recjob)) {
	                        
	                        if ($recjob['rec_time'] > time()) {
	                            
	                            $rec_time = $recjob['rec_time'] + $recdays * 86400;
	                        } else {
	                            
	                            $rec_time = time() + $recdays * 86400;
	                        }
	                        
	                        $status     =    $this->update_once('partjob', array('rec_time' => $rec_time), array('uid' => $uid, 'id' => $partid));
	                        
	                        if ($status) {
	                            
	                            require_once ('integral.model.php');
	                            
	                            $integral   =   new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
	                                
	                            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                            
                                $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，购买兼职推荐', true, 2, 'yhq', 9, '', $couponid);
                            
	                            $return['status']   =   '1';
	                            
	                            $return['msg']      =   '兼职推荐购买成功';
	                            
	                            $this->addMemberLog($uid, $data['usertype'], '兼职推荐购买成功', 9, 4);
	                        }
	                    }
	                } else {
	                    
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请正确选择职位推荐以及推荐的时长！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	    
	    return $return;
	}
	
	/**
	 * @desc   优惠券购买紧急招聘
	 * @param array $data
	 * @return string[]
	 */
	private function buyUrgentJob($data)
	{
	    
	    $uid        =   intval($data['uid']);
	    
	    $usertype   =   intval($data['usertype']);
	    
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	    
	    if ($data['ujobid'] && ($data['days'] || $data['xdays'])) {
	        
	        $jobid  =   $data['ujobid'];
	        
	        // 判断紧急招聘天数
	        $udays  =   intval($data['days']) > 0 ? intval($data['days']) : (intval($data['xdays']) > 1 ? intval($data['xdays']) : 1);
	        
	        if ($udays > 0 && $jobid) {
	            
	            // 判断职位ID真实性
	            $job    =   $this -> select_once('company_job', array('uid' => $uid, 'id' => $jobid));
	            
	            if (empty($job)) {
	                
	                $return['error']    =   '请选择正确的职位！';
	            } else {
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                $price      =   $udays * $this->config['com_urgent'];
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon     =   $this -> getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    $ujob   =   $this -> select_once('company_job', array('id' => $jobid), '`name`,`urgent_time`');
	                    
	                    if (! empty($ujob)) {
	                        
	                        if ($ujob['urgent_time'] > time()) {
	                            
	                            $urgent_time = $ujob['urgent_time'] + $udays * 86400;
	                        } else {
	                            
	                            $urgent_time = strtotime('+' . $udays . ' day');
	                        }
	                        
	                        $status =   $this -> update_once('company_job', array('urgent_time' => $urgent_time, 'urgent' => '1'), array('uid' => $uid, 'id' => $jobid));
	                        
	                        if ($status) {
	                            
	                            require_once ('integral.model.php');
	                            
	                            $integral   =   new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
	                            
	                            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                            
                                $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，购买紧急招聘', true, 2, 'yhq', 12, '', $couponid);
	                             
	                            $return['status'] = '1';
	                            
	                            $return['msg'] = '紧急职位购买成功';
	                            
	                            $this->addMemberLog($uid, $usertype, '紧急职位购买成功', 1, 4);
	                        }
	                    }
	                } else {
	                    
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请正确选择职位以及紧急招聘天数！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	    
	    return $return;
	}
	
	/**
	 * @desc   优惠券购买，预定招聘会
	 * @param array $data
	 * @return string[]|number[]
	 */
	private function buyZph($data = array()){
	    
	    $uid       =   intval($data['uid']);
	    
	    $username  =   trim($data['username']);
	    
	    $usertype  =   intval($data['usertype']);
	    
	    $return    =   array();
	    
	    require_once ('company.model.php');
	    $comM      =   new company_model($this->db, $this->def);
	    
	    require_once('integral.model.php');
	    $integralM =   new integral_model($this->db,$this->def,array('uid'=>$uid,'username'=>$username,'usertype'=>$usertype));
	    
	    require_once('zph.model.php');
	    $zphM      =   new zph_model($this->db,$this->def);
	    
	    if($data['zid'] && $data['bid']){
	        
	        $zid     =   $data['zid'] ? intval($data['zid']) : '';
	        $bid     =   $data['bid'] ? intval($data['bid']) : '';
	        
	        $com     =   $comM -> getInfo($uid, array('field' => '`name`'));
	        $zph     =   $zphM -> getInfo(array('id' => $zid));
	        
	        $zphcom  =   $zphM -> getZphComInfo(array('uid' => $uid, 'zid' => $zid));
	        
	        if ($zphcom && is_array($zphcom)) {
	            
	            if ($zphcom['status'] == 2) {
	                
	                $return['error'] = '您的报名未通过审核，请联系管理员！';
	            } else {
	                
	                $return['error'] = '您已报名该招聘会！';
	            }
	            
	        } else if (empty($zph)) {
	            
	            $return['error']     =	'参数错误，请重新预定 ！';
	        }else{
	            
	            $space               =   $zphM -> getZphSpaceInfo(array('id' => $bid));
	            $sid                 =   $zphM -> getZphSpaceInfo(array('id' => $space['keyid']));
	            $zData               =   array();
	            
	            $zData['uid']        =   $uid;
	            $zData['com_name']   =   $com['name'];
	            $zData['zid']        =   $zid;
	            $zData['ctime']      =   time();
	            $zData['status']     =   0;
	            $zData['sid']        =   $sid['keyid'];
	            $zData['cid']        =   $space['keyid'];
	            $zData['bid']        =   $bid;
	            
	            $price      =   ceil($space['price'] / $this->config['integral_proportion']);
	            
	            $couponid   =   intval($data['coupon_id']);
	            
	            /*  优惠券额度优先扣除 */
	            $couponWhere=   array(
	                
	                'id'            =>  $couponid,
	                'uid'           =>  $uid,
	                'status'        =>  1,
	                'validity'      =>  array('>', time()),
	                'coupon_amount' =>  array('>=', $price),
	                'coupon_scope'  =>  array('<=', $price)
	            );
	            
	            $coupon     =   $this -> getCouponListOne($couponWhere);
	            
	            if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                
	                $status          =   $this->insert_into('zhaopinhui_com', $zData);
	                
	                if($status){
	                    
	                    $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                    
	                    $integralM->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，预定招聘会', true, 2, 'yhq', '', $couponid);
	                    
	                    $this->addMemberLog($uid, $usertype,'报名招聘会,ID:'.$data['zid'].',展位：'.$bid,14,1);
	                    
	                    
	                    $return['status']  =   1;
	                    $return['msg']     =   '报名成功,等待管理员审核！';
	                }
	                
	            }else{
	                
	                $return['error']  =   '优惠券不可用，请重新选择！';
	            }
	        }
	    }else{
	        $return['error']	=	'参数异常';
	    }
	    
	    
	    return $return;
	}
	
	/**
	 * @desc 优惠券购买自动刷新
	 * @param array $data
	 * @return string[]
	 */
	private function buyAutoJob($data)
	{
	    
	    $uid        =   intval($data['uid']);
	   
	    $usertype   =   intval($data['usertype']);
	    
	    $username   =   trim($data['username']);
	    
	    $return     =   array();
	    
	    if ($data['jobautoids'] && ($data['days'] || $data['xdays'])) {
	        
	        $jobautoids =   pylode(',', @explode(',', $data['jobautoids']));
	        
	        // 判断自动刷新天数
	        $autodays   =   intval($data['days']) > 0 ? intval($data['days']) : (intval($data['xdays']) > 1 ? intval($data['xdays']) : 1);
	        
	        if ($autodays > 0 && $jobautoids) {
	            
	            // 判断职位ID真实性
	            $jobs   =   $this->select_all('company_job', array('uid' => $uid, 'id' => array( 'in', $jobautoids)), '`autotime`,`id`');
	            
	            if (empty($jobs)) {
	                
	                $return['error'] = '请选择正确的刷新职位！';
	            } else {
	                
	                $jobnum     =   $this->select_num('company_job', array('uid' => $uid, 'id' => array('in', $jobautoids)));   // 计算自动刷新职位数量
	                
	                $couponid   =   intval($data['coupon_id']);
	                
	                $price      =   $autodays * $jobnum * $this->config['job_auto'];
	                
	                /*  优惠券额度优先扣除 */
	                $couponWhere=   array(
	                    
	                    'id'            =>  $couponid,
	                    'uid'           =>  $uid,
	                    'status'        =>  1,
	                    'validity'      =>  array('>', time()),
	                    'coupon_amount' =>  array('>=', $price),
	                    'coupon_scope'  =>  array('<=', $price)
	                );
	                
	                $coupon     =   $this -> getCouponListOne($couponWhere);
	                
	                if($coupon['coupon_amount'] >= $price && $coupon['coupon_scope'] <= $price){
	                    
	                    // 积分抵扣，直接完成自动刷新购买
	                    $autoJob=   $this->select_all('company_job', array('uid' => $uid, 'id' => array('in', $jobautoids)), '`autotime`,`id`');
	                    
	                    if (! empty($autoJob)) {
	                        
	                        $lastautotime   =   0;
	                        
	                        foreach ($autoJob as $v) {
	                            
	                            if ($v['autotime'] >= time()) {
	                                
	                                $autotime = $v['autotime'] + $autodays * 86400;
	                            } else {
	                                
	                                $autotime = time() + $autodays * 86400;
	                            }
	                            
	                            if ($autotime > $lastautotime) {
	                                
	                                $lastautotime = $autotime;
	                            }
	                            
	                            $status =   $this->update_once('company_job', array('autotime' => $autotime, 'autotype' => '1'), array('uid' => $uid, 'id' => $v['id']));
	                        }
	                        
	                        $this -> update_once('company_statis', array('autotime' => $lastautotime), array('uid' => $uid));
	                        
	                        if ($status) {
	                            require_once ('integral.model.php');
	                            
	                            $integral   =   new integral_model($this->db, $this->def, array('uid' => $uid, 'username' => $username, 'usertype' => $usertype));
	                            
	                            $this -> upCouponList(array('id' => $couponid, 'uid' => $uid), array('status' => 2, 'xf_time'=>time()));   //  更新优惠券状态 2 已使用
	                            
                                $integral->company_invtal($uid, $usertype, $price, false, '使用优惠券 ，购买职位自动刷新', true, 2, 'yhq', 12, '', $couponid);
	                            
	                            
	                            $return['status'] = '1';
	                            
	                            $return['msg'] = '自动刷新职位购买成功';
	                            
	                            $this->addMemberLog($data['uid'], $data['usertype'], '自动刷新职位购买成功', 1, 4);
	                        }
	                    }
	                } else {
	                    
	                    $return['error']   =   '优惠券不可用，请重新选择！';
	                }
	            }
	        } else {
	            
	            $return['error'] = '请正确选择自动刷新职位以及刷新天数！';
	        }
	    } else {
	        
	        $return['error'] = '参数填写错误，请重新设置！';
	    }
	    
	     
	    return $return;
	}
	
	
}
?>