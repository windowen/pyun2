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
class companyorder_model extends model{
	/**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type=''); 
    }
	/**
     * @desc   引用userinfo类，查询member列表信息   
     */
    private function getMemberList($whereData = array(), $data = array()) {
        require_once ('userinfo.model.php');
        $MemberM = new userinfo_model($this->db, $this->def);
        return  $MemberM -> getList($whereData , $data); 
    }
	/**
     * @desc   引用userinfo类，根据用户类型，直接查找用户信息   
     */
    private function getUserInfo($whereData = array(), $data = array()) {
        require_once ('userinfo.model.php');
        $MemberM = new userinfo_model($this->db, $this->def);
        return  $MemberM -> getUserInfo($whereData , $data); 
    }
	/**
     * @desc   获取用户姓名、企业名称   
     */
    private function getUserList($where = array()) {
        require_once ('userinfo.model.php');
        $userinfoM = new userinfo_model($this->db, $this->def);
        return  $userinfoM -> getUserList($where); 
    }
	/**
     * @desc   引用once类，查询once_job单条信息   
     */
    private function getOnceInfo($id = array(), $data = array()) {
        require_once ('once.model.php');
        $OnceM = new once_model($this->db, $this->def);
        return  $OnceM -> getOnceInfo($id , $data); 
    }
	/**
     * @desc   引用once类，查询once_job列表信息   
     */
    private function getOnceList($whereData = array(), $data = array()) {
        require_once ('once.model.php');
        $OnceM = new once_model($this->db, $this->def);
        return  $OnceM -> getOnceList($whereData , $data); 
    }
	/**
     * @desc   引用cache类
     */
    private function getCache($whereData = array()) {
        require_once ('cache.model.php');
        $CacheM = new cache_model($this->db, $this->def);
		
        return  $CacheM -> GetCache($whereData); 
    }
	
	
	/**
     * @desc   引用qrorder类，upuser_statis确认订单操作
     */
    private function upuserStatis($data) {
        require_once ('qrorder.model.php');
        $QrorderM = new qrorder_model($this->db, $this->def);
        return  $QrorderM -> upuser_statis($data); 
    }
	/**
     * 获取company_order      订单详情
     * $whereData       查询条件
     * $data            自定义处理数组
     */
	public function getInfo($where,$data=array('bank'=>'')){
	    
	    $field		=	empty($data['field']) ? '*' : $data['field'];
		
	    $info	=	$this -> select_once('company_order',$where, $field);
		
		if($info && is_array($info)){
		
		    if(!empty($data['bank'])){
		        
				$orderbank	=	@explode('@%',$info['order_bank']);
				if(is_array($orderbank)){
					foreach($orderbank as $key){
						$orderbank[]	=	$key;
					}
					$info['bank_name']	=	$orderbank[0];
					$info['bank_fname']	=	$orderbank[1];
					$info['bank_number']=	$orderbank[2];
				}
				
			}else{
				//显示用户名和姓名
				if($info['uid']){
					$member		=	$this -> select_once('member',array('uid'=>$info['uid']),'uid,username,usertype');
					$info['username']=$member['username'];
					
					if($member['usertype']=='1' || $member['usertype']=='2'){
						$user	=	$this -> getUserInfo(array('uid'=>$member['uid']),array('usertype'=>$member['usertype'],'field'=>'uid,name'));
					}elseif($member['usertype']=='3'){
						$user	=	$this -> getUserInfo(array('uid'=>$member['uid']),array('usertype'=>$member['usertype'],'field'=>'uid,realname as name'));
					}
					
					$info['comname']		=	$user['name'];					
				}elseif($info['once_id']){
					$once		=	$this -> getOnceInfo(array('id'=>$info['once_id']),array('field'=>'id,companyname,linkman'));
					$info['username']	=	$once['linkman'];
					$info['comname']	=	$once['companyname']; 
				}
				//显示银行转账信息
				$orderbank				=	@explode('@%',$info['order_bank']);
				if(is_array($orderbank)){
					foreach($orderbank as $key){
						$orderbank[]	=	$key;
					}
					$info['bankname']	=	$orderbank[0];
					$info['bankid']		=	$orderbank[1];
				}
				
				$info['order_pic']		=	checkpic($info['order_pic']);
				
				include (APP_PATH.'config/db.data.php');
				$info['type_n']			=	$arr_data['ordertype'][$info['type']];
			}
			
			return $info;
	    } 
	}
	/**
      * @desc 查询全部信息
      * @param 表：company_order
      * @param 功能说明：获取company_order表里面所有订单信息
      * @param 引用字段：$whereData：条件  2:$data:查询字段
      *
     */
	 public function getList($whereData, $data = array()) {
	     
	     $data['field']  =	empty($data['field']) ? '*' : $data['field'];

         $List			=	$this -> select_all('company_order', $whereData, $data['field']);
         
         include (CONFIG_PATH.'db.data.php');
      
         if (!empty($List)) {
			
            /* 处理后台所需数据 */
            if ($data['utype']=='admin') {
                $List	=	$this -> getDataList($List);
            } 
            
            
            
			
            
			/* 处理个人所需数据 */
            if ($data['utype']=='member') {
                
                foreach($List as $key=>$val){
                    $ord[]  =   $val['order_id'];
                }
                
			}
			
			/* 处理企业所需数据 */
            if ($data['utype']=='com') {
				include (CONFIG_PATH.'db.data.php');
				$arr_data['ordertype'][2]  =  $this->config['integral_pricename'].'充值';
              
                foreach($List as $key=>$val){
                    $List[$key]['type_n']			=	$arr_data['ordertype'][$val['type']];
					$List[$key]['dingdan_id']		=	$val['order_id'];
					$List[$key]['dingdan_state_n']	=	strip_tags($arr_data['paystate'][$val['order_state']]);
					$List[$key]['dingdan_type_n']	=	$val['order_type']?strip_tags($arr_data['pay'][$val['order_type']]):'手动';
					$List[$key]['dingdan_time_n']	=	date('Y-m-d H:i:s',$val['order_time']);
					$List[$key]['dingdan_price']	=	$val['order_price'];
					$List[$key]['dingdan_remark']	=	$val['order_remark'];
                }
            }
        }
        return $List;
    }
	/**
     * @desc    查询company_order表内没有的数据，引用相关类，查询关联表，提取列表数据所需信息
     * @param   array $List 
     */
    private function getDataList($List) 
    {
        $uids  =  $onceid  =  array();
        
        foreach ($List as $v){
			if ($v['uid'] && !in_array($v['uid'],$uids)) {
                $uids[$v['uid']]		= 	$v['uid'];
            }
            if ($v['once_id'] && !in_array($v['once_id'],$onceid)) {
                $onceid[$v['once_id']]	= 	$v['once_id'];
            }
        }
		if($uids){
			//  查询用户名
		    $mList              =   $this -> select_all('member', array('uid'=>array('in', pylode(',', $uids))), '`uid`,`username`');
			
			//  查询企业名称、姓名
			$uWhere['uid']      =   array('in', pylode(',', $uids));
			$UList              =   $this -> getUserList($uWhere);
		}
		if($onceid){
			 //  查询once_job企业名称、联系人作为企业名称、用户名显示
			$oWhere['id']      	=   array('in', pylode(',', $onceid));
			$oData['field']     =   '`id`,`companyname`,`linkman`';
			$OList              =   $this -> getOnceList($oWhere, $oData);
		}
		
		include (CONFIG_PATH.'db.data.php');
		
        foreach ($List  as  $k  =>  $v){
            //  分站did字段为空的数据
            if($v['did']    ==  ''){
                $List[$k]['did']  =   '0';
            }
			//状态
            $List[$k]['order_state_n']			=	$arr_data['paystate'][$v['order_state']];
			//支付类型
			$List[$k]['order_type_n']			=	$arr_data['pay'][$v['order_type']];
			//订单类型
			$List[$k]['type_n']					=	$arr_data['ordertype'][$v['type']];
			
            //  用户名
            if (!empty($mList)) {
                foreach ($mList as $va){
                    if ($v['uid']	==	$va['uid']) {
                        $List[$k]['username']	=	$va['username'];
                    }
                }
            }
            
            //	企业名称（姓名）
            if (!empty($UList)) {
                foreach ($UList as $va){
                    if ($v['uid']	==	$va['uid'] ) {
						$List[$k]['comname']	=	$va['name'];
                    }
                }
            }
			//店铺招聘时的企业名称、用户名
			if (!empty($OList)) {
				$CList=$OList;
                foreach ($CList as $va){
                    if ($v['once_id']	==	$va['id']) {
						$List[$k]['comname']	=	$va['companyname'];
						$List[$k]['username']	=	$va['linkman'];
                    }
                }
            }
        }
        return $List;
	}
 
	
     
    
	/**
     * @desc 更新数据
     * @param int/array     $id
     * @param array         $data
     */
    public function upInfo( $id ,$Data = array(),$uid='') {

        if (!empty($id)) {            
            if (is_array($id)) {  
                $where['id']   =   array('in' , pylode(',' ,  $id));
            } else {
                $where['id']   =   intval($id);
            }
			if($uid){
				$where['uid']   =   intval($uid);
			}
            $nid    =   $this -> update_once('company_order', $Data, $where);
            if($nid){
				return array('errcode'=>'9','msg'=>'充值记录(ID:'.$id.')修改成功！');
			}else{
				return array('errcode'=>'8','msg'=>'充值记录(ID:'.$id.')修改失败！');
			}
        }
    }
	/**
     * @desc 确认付款
     * @param int     $id
     */
    public function setPay($id) {
        
        if (!empty($id)) {
            
            $row    =   $this -> getInfo(array('id' => $id));

            if ($row['order_state'] == '1' || $row['order_state'] == '3') {
                $nid    =   $this -> upuserStatis($row);
                
                if ($nid) {
                    
                    return array( 'errcode' => '9', 'msg' => '充值记录(ID:' . $id . ')确认成功！');
                    
                } else {
                    
                    return array('errcode' => '8', 'msg' => '确认失败,请稍后再试！'  );
                }
                
            } else {
                
                return array( 'errcode' => '8', 'msg' => '订单已确认，请勿重复操作！'    );
                
            }
        }
    }
    /**
     * @desc  修改statis表
     * @param array $data
     */
    private function upStatisInfo($data = array(), $where = array()){
        
        require_once 'statis.model.php';
        
        $statisM    =   new statis_model($this->db, $this->def);
        
        return $statisM -> upInfo($data, $where);
    }
    
	/**
      * 删除订单记录
      * @param 表：company_order
      * @param 功能说明：删除company_order表里面记录
      * @param 引用字段：$id:删除记录
      *
     */
	public function del($id,$data=array()){
	    
		if($data['utype']=='once'){
		
		    $oid		=	$this->getInfo(array('id'=>$id,'order_state'=>1),array('field'=>'`id`,`once_id`'));
			$id			=	$oid['id'];
			$onceid		=	$oid['once_id'];
			$getWhere	=	array();
		}elseif($data['utype']=='admin'){
			
			$getWhere	=	array();
		
		}else{
			//非后台操作 必须验证UID
			$getWhere	=	array('uid'=>$data['uid']);
		
		}
		
		$return     =   array();
		
		if(!empty($id)){
		
		    if(is_array($id)){
			
		        $ids    				=	$id;
				$return['layertype']	=	1;
			}else{
				
			    $ids   				 	=   @explode(',', $id);
				$return['layertype']	=	0;
			}
			
			$id							=   pylode(',', $ids);
			$getWhere['id']				=	array('in',$id);
			$order						=	$this -> getList($getWhere,array('field'=>'`id`,`order_id`,`order_pic`,`order_state`,`uid`,`usertype`,`order_dkjf`'));
			
			if(!empty($order)){
			    
			    $order_ids = $chekcids =   array();   
			    
			    foreach($order as $v){
			        
					if($v['order_id']){
						$order_ids[]       =	$v['order_id'];
					}
					
					$chekcids[]			=	$v['id'];
					
				}
				$id = pylode(',', $chekcids);
								
			}else{

				$return['msg']		=  '非法操作！';
				$return['errcode']	=  '8';
				return $return;  
			
			}
			
			$return['id']	=	$this -> delete_all('company_order',array('id' => array('in',$id)),'');
			
			if($return['id']){
				
				
				if($data['utype']=='once' && $onceid){
				    
					$this->delete_all('once_job',array('id'=>$onceid), '');
				}
				
				
				
				require_once ('integral.model.php');
				
				$integralM  = new integral_model($this->db, $this->def);
				
				/* 未付款订单删除，返还积分  */
				foreach ($order as $v){
				    if (!empty($v['order_dkjf']) && $v['order_state'] == '1' && $v['order_dkjf']>0) {
				        $integralM -> company_invtal($v['uid'], $v['usertype'], $v['order_dkjf'], true, '取消订单，返还'.$this->config['integral_pricename'], true, 2, 'integral', 66);
				    }
				}
				 
				//取消因跨端支付时产生的新订单，把原订单也删除
				if (!empty($order_ids)){
				    
				    $oldwhere           =   array();
			
				    foreach ($order_ids as $v){
				        if($v != ''){
							$oldwhere['order_remark'][] = array('like',$v,'or');
						}
				    }
				    if(!empty($oldwhere)){
						$this -> delete_all('company_order',$oldwhere,'');
					}
				    
				}
				
				$return['msg']      	=  '充值记录(ID:'.$id.')删除成功';
				$return['errcode']  	=  '9';
				
			}else{
			    
				$return['msg']      	=  '充值记录(ID:'.$id.')删除失败';
				$return['errcode']  	=  '8';
			}
			
		}else{
		    
			$return['msg']      		=  '请选择要删除的记录';
			$return['errcode']  		=  '8';
		}
		return $return;  
	}
	/**
      * @desc 查询全部消费记录信息
      * @param 表：company_pay
      * @param 功能说明：获取company_pay表里面所有信息
      * @param 引用字段：$whereData：条件  2:$data:查询字段
      *
     */
	public function getPayList($whereData, $data = array()) {
		$data['field']	=	empty($data['field']) ? '*' : $data['field'];
        $List			=	$this -> select_all('company_pay', $whereData, $data['field']);
		if (!empty($List)) {
            /* 处理后台所需数据 */
            if ($data['utype']=='admin') {
                foreach ($List as $v){
					if ($v['com_id'] && !in_array($v['com_id'],$uids)) {
						$uids[$v['com_id']]		= 	$v['com_id'];
					}
				}
				if($uids){
					//  查询用户名
					$mWhere['uid']      =   array('in', pylode(',', $uids));
					$mData['field']     =   '`uid`,`username`';
					$mList              =   $this -> getMemberList($mWhere, $mData);
					
					//  查询企业名称、姓名
					$uWhere['uid']      =   array('in', pylode(',', $uids));
					$UList              =   $this -> getUserList($uWhere);
				}
				include (APP_PATH.'/config/db.data.php');
				
				foreach ($List  as  $k  =>  $v){
					//  分站did字段为空的数据
					if($v['did']    ==  ''){
						$List[$k][did]  =   '0';
					}
					//状态
					$List[$k]['pay_state_n']=$arr_data['paystate'][$v['pay_state']];
					//  用户名
					if (!empty($mList)) {
						foreach ($mList as $va){
							if ($v['com_id']	==	$va['uid']) {
								$List[$k]['username']	=	$va['username'];
							}
						}
					}
					//	企业名称（姓名）
					if (!empty($UList)) {
						foreach ($UList as $va){
							if ($v['com_id']	==	$va['uid'] ) {
								$List[$k]['comname']	=	$va['name'];
							}
						}
					}
				}
			}elseif($data['utype']=='paylog'){	
                include (APP_PATH.'/config/db.data.php');
				foreach ($List  as  $k  =>  $v){
					$List[$k]['pay_time']			=	date('Y-m-d H:i:s',$v['pay_time']);
					$List[$k]['pay_state_n']    = 	$arr_data['paystate'][$v['pay_state']];
					if($v['type']=='1'){
						$List[$k]['order_price'] 	=	intval($v['order_price']);						
					}
				}	
			}elseif($data['utype']=='consume'){	
				include (APP_PATH.'/config/db.data.php');
				foreach ($List  as  $k  =>  $v){
					$List[$k]['sname']			=	$arr_data['paystate'][$v['order_state']];
					$List[$k]['order_type']		=	$arr_data['pay'][$v['order_type']];
				}		
			}else{
				include (APP_PATH.'/config/db.data.php');
				foreach($List as $k=>$v){
					$ptag						=	$v['type']==2?'元':$this->config['integral_priceunit'].$this->config['integral_pricename'];
					$List[$k]['pricename']   	= 	$this->config['integral_pricename'];
         			$List[$k]['pay_time_n']		=	date('Y-m-d H:i:s',$v['pay_time']);
					$List[$k]['order_price_n']	=	str_replace('.00','',$v['order_price']);
					$List[$k]['consume_remark'] =	$v['pay_remark'];
					$List[$k]['consume_time_n']	=	date('Y-m-d H:i:s',$v['pay_time']);
					$List[$k]['consume_id']		=	$v['order_id'];
					$List[$k]['consume_state_n']=	strip_tags($arr_data['paystate'][$v['pay_state']]);
					$List[$k]['consume_price_n']=	str_replace('.00','',$v['order_price']).$ptag;
				}
            } 

        }
        return $List;
	}
	/**
      * 删除消费记录
      * @param 表：company_pay
      * @param 功能说明：删除company_pay表里面记录
      * @param 引用字段：$id:删除记录
      *
     */
	public function delPay($id){
	    
		if(!empty($id)){
		
		    if(is_array($id)){
				$ids    				=	$id;
				$return['layertype']	=	1;
			}else{
				$ids   				 	=   @explode(',', $id);
				$return['layertype']	=	0;
			}
			
			$id							=   pylode(',', $ids);
			 
			$return['id']				=	$this -> delete_all('company_pay',array('id' => array('in',$id)),'');
			
			if($return['id']){
				$return['msg']      	=  '消费记录(ID:'.$id.')删除成功';
				$return['errcode']  	=  '9';
			}else{
				$return['msg']      	=  '消费记录(ID:'.$id.')删除失败';
				$return['errcode']  	=  '8';
			}
		}else{
			$return['msg']      		=  '请选择要删除的记录';
			$return['errcode']  		=  '8';
		}
		return $return;  
	}
	/**
      * 查询消费值的和
      * @param 表：company_pay
      */
     function getCompanyPaySumPrice($whereData){
      
        $list		=	$this	->	getPayList($whereData);
        $pricearr	=	array();
        $sumprice	=	0;
        foreach($list as $k=>$v){
        	if($v['order_price']){
        		$pricearr[]	=	$v['order_price'];
        	}
        }
        if(is_array($pricearr)){
        	$sumprice	=	array_sum($pricearr);
        }
        
        return $sumprice;
      
     }
	/**
     * @desc    后台充值
     * @param   array   $userarr 充值用户名
     * @param   array   $data
     */
	 
    public function PayMember($userarr,$data) {
        if (!empty($userarr)) {
			$num								=	$data['price_int'];
			$msg								=	$num.$this -> config['integral_pricename']; 
			$fsmsg								=	$data['fs']==1?'充值':'扣除';
			
			if(is_array($userarr)){
				$uidarr							=	array();
				$snum							=	$fnum	=	0;
				foreach($userarr as $k=>$v){
					$member						=	$this -> select_once('member',array('username'=>$v),'uid,usertype');
					if(is_array($member)){
						$snum++;
						$uidarr[$k]['uid']		=	$member['uid'];
						$uidarr[$k]['usertype']	=	$member['usertype'];
						$uids[]					=	$member['uid'];
					}else{
						$fnum++;
						$fname[]				=	$v;
					}
				}
			}
			if(is_array($uidarr)){
				foreach($uidarr as $v){ 
					if($v['usertype']=='1'){
						$table					=	'member_statis';
					}elseif($v['usertype']=='2'){
						$table					=	'company_statis';
					}
					
					$nid						=	$this -> pay_member($table,$v['uid'],$num,$data['remark'],$v['usertype'],$data['fs']);
				}
			}
			
			if($nid){
				if($fnum){
					$nummsg						=	'，'.$fnum.'个用户名('.pylode(',',$fname).')不存在';
				}
				return array('errcode'=>'9','msg'=>$snum.'个会员(ID:'.pylode(',',$uids).')'.$fsmsg.$msg.'成功'.$nummsg.'！','url'=>$_SERVER['HTTP_REFERER']);
			}else{
				if($fnum){
					$fmsg						=	'用户名(:'.pylode(',',$userarr).')不存在，';
				}
				return array('errcode'=>'8','msg'=>$fmsg.$fsmsg.'失败！');
			}
        }
    }
	/**
     * @desc 后台充值生成订单
     */
	private function pay_member($table,$uid='',$num,$remark,$usertype,$fs){
		require_once ('statis.model.php');
		$StatisM    				=   new statis_model($this->db, $this->def);
		if($fs==2){
			$statis					=	$StatisM -> getInfo($uid,array('usertype'=>$usertype,'field'=>'pay,integral'));
			if($statis['integral']<$num){
				$num				=	$statis['integral'];
			} 
		}
					
		$dingdan					=	time().rand(10000,99999);
		if($fs==1){//增加  
			$type					=	true;
			$where['integral']		=	array('+',$num);
			$wheres['order_type']	=	'adminpay';
		}else{//扣除  
			$type=false;
			$where['integral']		=	array('-',$num);
			$wheres['order_type']	=	'admincut';
		}
		$wheres['order_id']			=	$dingdan;
		$wheres['order_price']		=	$num/$this->config['integral_proportion'];
		$wheres['order_time']		=	time();
		$wheres['order_state']		=	'2';
		$wheres['order_remark']		=	$remark;
		$wheres['uid']				=	$uid; 
		$wheres['usertype']			=	$usertype; 
		$wheres['type']				=	2; 
		$wheres['integral']			=	$num;

		$nid						=	$StatisM -> upInfo($where,array('uid'=>$uid,'usertype'=>$usertype)); 
		
		if($fs==2)$wheres['type']	=	5;
		if($nid){
			require_once ('integral.model.php');
			$IntegralM 				=	new integral_model($this->db, $this->def);
			$IntegralM -> insert_company_pay($num,2,$uid,$usertype,$remark,1,'',$type);
			
			$nid					=	$this -> insert_into('company_order',$wheres);
		}
		return $nid;
	}
	/**
     * @desc 广告订单
     */
	function adOrder($addData){
		$nid	=	null;
		if($addData['uid']&&$addData['order_id']){
			$nid	=	$this -> addOrder($addData);
		}
		
		
		return $nid;
	}
	/**
     * @desc 生成订单
     */
	public function addOrder($addData){    
		

	    if (!empty($addData) && is_array($addData)) {
            
	        if (!empty($addData['uid'])) {
	            
    	        $where =   array(
    	           
    	            'uid'			=>  intval($addData['uid']),
    	            'order_dkjf'	=>  '0',
    	            'order_price'	=>  $addData['order_price'],
    	            'order_state'	=>  1,
    	            'order_remark'	=>  $addData['order_remark'] ? $addData['order_remark'] : '',
    	            'type'			=>  $addData['type'],
    	            'rating'		=>  $addData['rating'],
    	            'integral'		=>  $addData['integral'] ? $addData['integral'] : '',
    	            'sid'			=>  '',
    	            'order_info'	=>  '',
    	            'crm_uid'		=>  '',
    	            'once_id'		=>  '',
    	            'usertype'		=>  $addData['usertype']
    	        );
    	        
    	        $this->delete_all('company_order', $where,'');
	        }
	    }
	    $addData['order_dkjf'] = $addData['order_dkjf']>0 ? $addData['order_dkjf'] : 0;
		$nid	=	$this -> insert_into('company_order',$addData);
		
		return $nid;
	}
	/**
	 * @desc 生成dingdan，充值，购买会员，增值服务，
	 */
	function addComOrder($data = array())
	{
	    
		

	    if($data['order_price'] || $data['comvip'] || $data['comservice'] || (!empty($data['price_int']) && $data['price_int'] > 0)){
	        
			$data['dkjf'] = $data['dkjf']>0 ? $data['dkjf'] : 0;

			include_once('cookie.model.php');
	        
		    $cookieM  =  new cookie_model($this->db, $this->def);
			$cookieM ->	SetCookie('delay', '', time() - 60);
			
			$oData    =  array();
			
			if($data['comvip']){
			    
			    $msg      =  '购买会员';
			    $breturn  =  $this -> buyVip($data);
			    $oData    =  $breturn;
			    
			    $oData['rating']  =	 $data['comvip'];
			    
			}elseif($data['comservice']){
			    
			    require_once 'statis.model.php';
			    $statisM  =  new statis_model($this->db, $this->def);
			    $statis   =  $statisM -> getInfo($data['uid'],array('usertype' =>$data['usertype']));
			    if (!isVip($statis['vip_etime'])) {
                    
			        return array('msg'=>'你的会员已经到期，请先购买会员！','errcode'=>8);
                }else{
                    
    			    $msg      =  '购买增值服务';
    			    $breturn  =  $this -> buyZzb($data);
    			    $oData    =  $breturn;
    			    
    			    $oData['rating']  =  $data['comservice'];
                }
			}elseif($data['price_int']){
			    
			    $msg      =  '充值'.$this->config['integral_pricename'];
			    $breturn  =  $this -> buyIntegral($data);
			    $oData    =  $breturn;
			}
			$oData['order_id']	    =  time().rand(10000,99999);
			$oData['order_type']	=  $data['paytype'];
			$oData['order_time']    =  time();
			$oData['uid']			=  $data['uid'];
			$oData['usertype']		=  $data['usertype'];
			$oData['did']			=  $data['did'];
			if (!empty($data['order_remark'])){
			    
			    $oData['order_remark'] =  trim($data['order_remark']);
			}
			if(!empty($data['dkjf'])){
			    
			    $oData['order_dkjf']    =  (int)$data['dkjf'];
			    
			    $dkjm                   =  round(($oData['order_dkjf'] / $this->config['integral_proportion']), 2);
			}
			//wap银行支付需要传递一下参数
			if($data['order_type'] == 'bank'){
			    
			    $oData['order_state']	=	'3';
			    $oData['order_type']	=	'bank';
			    $oData['order_pic']		=	$data['order_pic'];
			    $oData['order_bank']	=	$data['order_bank'];
			    $oData['bank_time']		=	$data['bank_time'];
			}else{
			    $oData['order_state']	=	'1';
			}
			
			
			$nid  =  $this -> insert_into('company_order',$oData);
			
			if($nid){
			    // 处理抵扣积分
			    if ($oData['order_dkjf'] > 0){
			        
			        include_once('integral.model.php');
			        $integralM  =  new integral_model($this->db, $this->def);
			        
			        $integralM -> company_invtal($data['uid'],$data['usertype'],$oData['order_dkjf'],false,$msg,true,2,'integral',11);
			    }
			    
			    $msg  .= ',订单ID'.$oData['order_id'];
				$this -> addMemberLog($data['uid'],$data['usertype'],$msg,88);//会员日志
				
				$return  =  array('msg'=>'下单成功，请付款！','errcode'=>9);
				//pc,wap返回区分
				if($data['type'] == 'wap'){
				    
				    if($data['paytype'] == 'alipay'){
				        
				        $url  =	 $this->config['sy_weburl'].'/api/wapalipay/alipayto.php?dingdan='.$oData['order_id'].'&dingdanname='.$oData['order_id'].'&alimoney='.$oData['order_price'];
				    
				    }
				    $return['url']  =  $url;
				    
				}else{
				    
				    $return['url']  =  'index.php?c=payment&id='.$nid;
				}
				$return['id']  =  $nid;
				
				return $return;
				
			}else{
				$return  =	array('msg'=>'提交失败，请重新提交订单！','errcode'=>8,'url'=>$_SERVER['HTTP_REFERER']);
				return $return;
			}
		}else{
			$return	 =  array('msg'=>'提交失败，请正确提交订单！','errcode'=>8,'url'=>$_SERVER['HTTP_REFERER']);
			return $return;
		}
	}
	/**
	*   银行转账支付，
	*	data内必传参数uid,id：订单id,
	*	wap端需要传递参数wap=1，上传的图片需要preview,如果wap端使用银行卡支付，需要传递isbank=1参数给上面的生成订单方法addComOrder
	*/
	function payComOrderByBank($data = array()){
		
		//数据判断
		if($data['bank_name']==''){
		    
			$return  =  array('msg'=>'请填写汇款银行', 'errcode'=>'8');
			
		}elseif($data['bank_number']==''){
		    
			$return  =  array('msg'=>'请填写汇入账号', 'errcode'=>'8');
			
		}elseif($data['bank_price']==''){
		    
			$return  =  array('msg'=>'请填写汇款金额', 'errcode'=>'8');
			
		}elseif($data['bank_time']==''){
		    
			$return  =  array('msg'=>'请填写汇款时间', 'errcode'=>'8');
			
		}else{
		    
		    $order  =  $this ->	select_once('company_order', array('id'=>$data['id']));
		    
			if ($data['file']['tmp_name'] || $data['base']){
                
                $upArr   =  array(
                    'file'     =>  $data['file'],
                    'dir'      =>  'order',
                    'base'     =>  $data['base'],
                    'preview'  =>  $data['preview']
                );
                
                $result  =  $this -> upload($upArr);
                
                if (!empty($result['msg'])){
                    
                    $return['msg']      =  $result['msg'];
                    $return['errcode']  =  '8';
                    
                    return $return;
                    
                }elseif (!empty($result['picurl'])){
                    
                    $pictures  =  $result['picurl'];
                }
               
            }
		  	unset($data['file']);
		  	unset($data['base']);
		  	unset($data['preview']);
		    if(!isset($pictures)){
		    	$pictures	=	$order['order_pic'];
		    }
		    $orderbank		=	$data['bank_name'].'@%'.$data['bank_number'].'@%'.$data['bank_price'];
		    if($data['bank_time']){
		        $banktime	=	strtotime($data['bank_time']);
		    }else{
		        $banktime	=	'';
		    }
		    $company_order	=	array(
		        'order_type'	=>	'bank',
		        'order_state'	=>	'3',
		        'order_remark'	=>	$data['order_remark'],
		        'order_pic'		=>	$pictures,
		        'order_bank'	=>	$orderbank,
		        'bank_time'		=>	$banktime
		    );
		    // wap端等    (银行转账pc端是先生成订单，然后存入转账数据; wap端生成订单时可能会选择银行转账，有所区别)
		    if($data['wap']){
		        
		        $data	 =  array_merge($data,$company_order);
		        $return  =  $this -> addComOrder($data);
		        
		        if($return['errcode']==9){
					$return['msg']='操作成功，请等待管理员审核！';
		            $return['url']='index.php?c=paylog';

		        }else{
		            $return['url']=$_SERVER['HTTP_REFERER'];
		        }
		    }else{
		        // pc端
		        if($order['id']){
		            
		            $this -> upInfo($order['id'],$company_order);
		            $return  =  array('msg'=>'操作成功，请等待管理员审核！','errcode'=>9,'url'=>'index.php?c=paylog');
		            
		        }else{
		            $return  =	array('msg'=>'非法操作！','errcode'=>8,'url'=>$_SERVER['HTTP_REFERER']);
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
     * @desc 后台充值模块，企业会员开通套餐
     * @param array         $data
     */
	 
    public function ComVip($data) {
        if ($data['username']=='' && $data['comname']==''){
			return array('errcode'=>8,'msg'=>'用户名和企业名称不能全为空');
	    }
	    if ($data['ratingid']==''){
			return array('errcode'=>8,'msg'=>'请选择开通等级');
	    }
	    if ($data['vipprice']==''){
			return array('errcode'=>8,'msg'=>'请填写开通价格');
	    }
	    if ($data['username']!=''){
			$member					=	$this -> select_once('member',array('username'=>$data['username']),'uid');
	        if (!$member){
				return array('errcode'=>8,'msg'=>'企业用户名('.$data['username'].')不存在');
	        }
	    }elseif ($data['comname']!=''){
			$member					=	$this -> select_once('company',array('name'=>$data['comname']),'uid');
	        if (!$member){
				return array('errcode'=>8,'msg'=>'企业名称('.$data['comname'].')不存在');
	        }
	    }
		require_once ('rating.model.php');
		$ratingM	=	new rating_model($this->db, $this->def);
	    $rating		=	$ratingM -> getInfo(array('id'=>$data['ratingid']));
		
	    $leijia		=	$data['leijia'];
	   
	    $newstatis	=	$ratingM -> ratingInfo($rating['id'],$member['uid'],$leijia);
		
		require_once ('statis.model.php');
		$StatisM	=	new statis_model($this->db, $this->def);
		$nid		=	$StatisM -> upInfo($newstatis,array('uid'=>$member['uid'],'usertype'=>2));

		
        $statis      =   $StatisM -> getInfo($member['uid'], array('usertype'=>2));
        $companyData =           array(
            'rating'        =>          $statis['rating'],
            'rating_name'   =>          $statis['rating_name'],
            'vipstime'      =>          $statis['vip_stime'],
            'vipetime'      =>          $statis['vip_etime']
        );
        require_once ('company.model.php');
        $companyM    =   new company_model($this->db, $this->def);
        $companyM -> upInfo($member['uid'],'',$companyData);

	    if ($nid){
	        $dingdan				=	time().rand(10000,99999);
	        $order['order_id']		=	$dingdan;
	        $order['order_price']	=	$data['vipprice'];
	        $order['type']			=	'1';
	        $order['order_time']	=	time();
	        $order['order_state']	=	'2';
	        $order['order_remark']	=	'管理员开通会员套餐';
	        $order['uid']			=	$member['uid'];
            $order['usertype']		=	2;
	        $order['did']			=	$this->config['did'];
	        $order['rating']		=	$rating['id'];
	        $order['order_type']	=	'adminpay';
	        $this -> insert_into('company_order',$order);
          return array('errcode'=>9,'msg'=>'企业会员(ID'.$member['uid'].')开通会员套餐【'.$rating['name'].'】成功','url'=>$_SERVER['HTTP_REFERER']);
	    }else{
          return array('errcode'=>8,'msg'=>'开通会员套餐失败','url'=>$_SERVER['HTTP_REFERER']);
	    }
    }
	/**
     * @desc 后台充值模块，验证开通用户名
     */
	public function SearchName($name){
		if($name){
			$member 									=	$this -> getMemberList(array('usertype'=>2,'username'=>array('like',$name)),array('field'=>'uid,username','limit'=>10));
			if ($member){
				foreach ($member as $v){
					$comid[]							=	$v['uid'];
				}
				require_once ('company.model.php');
				$ComM									=	new company_model($this->db, $this->def);
				$company 								=	$ComM -> getList(array('uid'=>array('in',pylode(',',$comid))),array('field'=>'uid,name','limit'=>10));
				
				foreach ($member as $k=>$v){
					$namelist[$k]['username']			=	$v['username'];
					foreach ($company['list'] as $val){
						if ($v['uid']==$val['uid']){
							$namelist[$k]['comname']	=	$val['name'];
						}
					}
				}
				$data['error']							=	0;
				$data['namelist']						=	$namelist;
			}else{
				$data['error']							=	-1;
			}
		}else{
			$data['error']								=	-1;
		}
		return $data;
	}
	/**
     * @desc 后台充值模块，验证开通企业名
     */	
	public function SearchCom($name){
		if($name){
			require_once ('company.model.php');
			$ComM										=	new company_model($this->db, $this->def);
			$company 									=	$ComM -> getList(array('name'=>array('like',$name)),array('field'=>'uid,name','limit'=>10));
			$company									=	$company['list'];
			if ($company){
				foreach ($company as $v){
					$comid[]							=	$v['uid'];
				}
				$member 								=	$this -> getMemberList(array('uid'=>array('in',pylode(',',$comid))),array('field'=>'uid,username','limit'=>10));
				
				foreach ($company as $k=>$v){
					$namelist[$k]['comname']			=	$v['name'];
					foreach ($member as $val){
						if ($v['uid']==$val['uid']){
							$namelist[$k]['username']	=	$val['username'];
						}
					}
				}
				$data['error']							=	0;
				$data['namelist']						=	$namelist;
			}else{
				$data['error']							=	-1;
			}
		}else{
			$data['error']								=	-1;
		}
		
		return $data;
	}
	/**
      * @desc 查询消费记录（条数）
      * @param 表：company_pay
      * @param 功能说明：查询company_pay表里面记录
      * @param 引用字段：$id:查询记录
      *
      */
    function getCompanyPayNum($whereData){
      
        $companypaynum    =   $this->select_num('company_pay',$whereData);
        
        return $companypaynum;
      
    }
    /**
      * @desc 查询订单条数
      * @param 表：company_order
      * @param 功能说明：查询whereData条件下company_order表条数
      * @param 参数：whereData:查询条件
      *
      */
    function getCompanyOrderNum($whereData){
      
        $companyordernum    =   $this->select_num('company_order',$whereData);
        
        return $companyordernum;
      
    }
    /**
      * @desc 查询设置的支付银行卡
      * @param 表：bank
      * @param 功能说明：查询whereData条件下bank信息
      * @param 参数：whereData:查询条件
      *
      */
    function getBankList($whereData=array(),$data=array()){
      
        $list    =   $this->select_all('bank',$whereData);
        
        return $list;
      
    }
	
	/**
	* 取消dingdan
	*/
	function cancelOrder($data){
		
		$return	=	array();
		
		if($data['id']){
			
			$order 	= 	$this->getInfo(array('id'=>$data['id'],'uid'=>$data['uid']),	array('field'=>'`id`,`order_id`'));
			
			if(empty($order)){
				
				$return['msg']		=	'订单不存在！';
				
				$return['errcode']	=	8;
				
				$return['url']		=	$_SERVER['HTTP_REFERER'];
				
			}else{
				require_once ('log.model.php');
				$logM 	= 	new log_model($this->db, $this->def);
				
				$nid	=	$this->del($data['id'],array('uid'=>$data['uid']));
				
				if($nid){
					$logM->member_log('取消订单',88,3);
					
					$return['msg']		=	'订单取消成功！';
				
					$return['errcode']	=	9;
					
					$return['url']		=	'index.php?c=paylist';
				}else{
					
					$return['msg']		=	'订单取消失败！';
				
					$return['errcode']	=	8;
				
					$return['url']		=	$_SERVER['HTTP_REFERER'];
				}
			}
		}else{
			$return['msg']		=	'系统超时！';
				
			$return['errcode']	=	8;
		
			$return['url']		=	$_SERVER['HTTP_REFERER'];
			
		}
		
		return $return;
		
	}
	/**
	 * 生成订单积分充值处理
	 * @param $data (price_int | integralid)
	 * @return  $return (type | price | integral)
	 */
	private function buyIntegral($data = array())
	{
	    $return      =  array();
	    
	    if(!empty($data['price_int'])){
	        
	        $integral	   =  $data['price_int'];
	        $min_recharge  =  $this->config['integral_min_recharge'];
	        // 最低充值积分非无限，且充值积分小于最低限制，积分自动为最低充值积分
	        if (!empty($min_recharge) &&  $min_recharge > 0 && $integral < $min_recharge){
	            
	            $integral  =  $this->config['integral_min_recharge'];
	        }
	        
	        // 选择已有充值类别
	        if (!empty($data['integralid'])){
	            
	            $integralid	   =  $data['integralid'];
	            $CacheMclass   =  $this -> getCache(array('integralclass'));
	            $discount      =  $CacheMclass['integralclass_discount'][$integralid]/100;
	            
	            if ($discount > 0){//充值优惠
	                $price 	   =  $integral/$this->config['integral_proportion'] * $discount;
	            }else{
	                $price	   =  $integral/$this->config['integral_proportion'];
	            }
	        }else{
	            
	            // 手动填写充值数量
	            $price	   =  $integral/$this->config['integral_proportion'];
	            
	        }
	        
	        $return['type']          =  2;
	        $return['integral']      =  $integral;
	        $return['order_price']   =  round($price,2);
			
	    }
	    return $return;
	}
	/**
	 * 生成订单购买会员套餐处理
	 * @param $data (uid | comvip | dkjf)
	 * @return  $return (type | price)
	 */
	private function buyVip($data = array())
	{
	    $return  =  array();
	    
	    if(!empty($data['comvip']) && !empty($data['uid'])){
	        
	        $rating     =  $this -> select_once('company_rating',array('id'=>(int)$data['comvip']));
	        
	        if($rating['time_start']<time() && $rating['time_end']>time()){
	            $price	=  $rating['yh_price'];
	        }else{
	            $price	=  $rating['service_price'];
	        }
	        
	        $dkjf		=	(int)$data['dkjf'];
	        
	        $statis = $this	->	select_once('company_statis',array('uid'=>(int)$data['uid']),'`integral`');
	        
	        if(!empty($statis) && ($dkjf >= (int)$statis['integral'])){
	            $dkjf	=	$statis['integral'];
	        }
	        
	        if($dkjf){
	            $dkprice	=	$dkjf / $this->config['integral_proportion'];
	            $price		=	$price - $dkprice;
	        }
	        $return['type']	         =  1;
	        $return['order_price']   =  round($price,2);
	    }
	    return $return;
	}
	/**
	 * @desc 生成订单购买增值包
	 * @param $data (uid | comservice | dkjf)
	 * @return $return (type | price)
	 */
	private function buyZzb($data = array())
	{
	    
	    $return  =  array();
	    
	    if(!empty($data['comservice']) && !empty($data['uid'])){
	        
	        $id		  =  (int)$data['comservice'];
	        $dkjf	  =  (int)$data['dkjf'];
	        
	        $statis   =  $this -> select_once('company_statis',array('uid'=>(int)$data['uid']),'`integral`,`rating`,`vip_etime`');
	        $rating   =  $this -> select_once('company_rating',array('id'=>$statis['rating']));
	        $detail   =  $this -> select_once('company_service_detail',array('id'=>$id));
	        $service  =  $this -> select_once('company_service',array('id'=>$detail['type']));
	        
	        if($rating['service_discount']>0 && $rating['service_discount']<100){
	            
	            $price	=  round($detail['service_price']*($rating['service_discount']/100),2);
	            
	        }else{
	            
	            $price	=	$detail['service_price'];
	        }
	        if(!empty($statis) && ($dkjf >= (int)$statis['integral'])){
	            $dkjf	=	(int)$statis['integral'];
	        }
	        if($dkjf){
	            $dkprice	=	$dkjf / $this->config['integral_proportion'];
	            $price		=	$price - $dkprice;
	        }
	        $return['type']	        =  5;
	        $return['order_price']  =  round($price,2);
	    }
	    return $return;
	}
	
}
?>