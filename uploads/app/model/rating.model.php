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
class rating_model extends model{
    
	/**
	 * @desc  企业会员等级新增
	 * @param number $id
	 * @param number $uid  
	 * @return array $value
	 */
	public function ratingInfo($id = 0, $uid = 0, $leijia = 2)
    {
        $id			=	intval($id);
	    $uid		=	intval($uid);
	    
	    //设置默认等级
	    if(empty($id)){
	        $id 	=	$this -> config['com_rating'];
	    }
	    
	    $acc 		=	$leijia ==1 ? 1 : $this -> config['package_data_acc']; //   1:累加  2：不累加
	    
	    if(empty($acc)){
	        
	        $acc 	=	2;
	        
	    }
 	    
	    $value		=	array();
	    
	    //获取企业账户统计信息
	    $statis = $this -> select_once('company_statis', array('uid' => $uid));
	      
	    //获取会员等级
	    $row = $this -> getInfo(array('id' => $id, 'category' => 1));
		
        if($statis['rating_type'] == $row['type'] && $row['type'] == 1 && $acc== 1){//套餐会员累加：①后台开启累加  && ②同为套餐会员
	        
	        if($row['service_time'] > 0){//有期限的套餐
	            
	            if($statis['vip_etime'] && isVip($statis['vip_etime'])){//当前会员时间剩余累加
	                
	                $time					=	$statis['vip_etime'] + 86400 * $row['service_time'];
	                
	            }else{
	                
	                $time					=	time() + 86400 * $row['service_time'];
	                
	            }
	            
	        }else{//永久套餐
	            
	            $time= 0;
	            
	        }
	        
	        $value['rating']				=	$id;
	        $value['rating_name']			=	$row['name'];
	        $value['rating_type']			=	$row['type'];
	        
	        /*套餐数据累加*/
	        if(isVip($statis['vip_etime'])){
	            $value['job_num']           =   array('+', (int)$row['job_num']);
	            $value['breakjob_num']      =   array('+', (int)$row['breakjob_num']);
	            $value['down_resume']       =   array('+', (int)$row['resume']);
	            $value['invite_resume']     =   array('+', (int)$row['interview']);
	            $value['zph_num']           =   array('+', (int)$row['zph_num']);
	            $value['urgent_num']        =   array('+', (int)$row['urgent_num']);
	            $value['rec_num']           =   array('+', (int)$row['rec_num']);
	            $value['top_num']           =   array('+', (int)$row['top_num']);
	            $value['integral']          =   array('+', (int)$row['integral_buy']);
	            $value['sons_num']          =   array('+', (int)$row['sons_num']);
	        }else{
	            $value['job_num']           =   array('=', (int)$row['job_num']);
	            $value['breakjob_num']      =   array('=', (int)$row['breakjob_num']);
	            $value['down_resume']       =   array('=', (int)$row['resume']);
	            $value['invite_resume']     =   array('=', (int)$row['interview']);
	            $value['zph_num']           =   array('=', (int)$row['zph_num']);
	            $value['top_num']           =   array('=', (int)$row['top_num']);
	            $value['urgent_num']        =   array('=', (int)$row['urgent_num']);
	            $value['rec_num']           =   array('=', (int)$row['rec_num']);
	            $value['sons_num']          =   array('=', (int)$row['sons_num']);

	        }
	        /*累加数据End*/
	        
	        $value['vip_etime']				=	$time;
	        $value['vip_stime']				=	time();
	        
	    }else if($statis['rating_type'] == $row['type'] && $row['type'] == 2 && $acc== 1){//时间会员期限累加：①后台开启累加  && ②同为时间会员
	        
	        if($row['service_time'] > 0){//有期限的套餐
	            
	            if($statis['vip_etime'] && isVip($statis['vip_etime'])){//当前会员时间剩余累加
	                
	                $time  =   $statis['vip_etime'] + 86400 * $row['service_time'];
	                
	            }else{
	                
	                $time  =   time() + 86400 * $row['service_time'];
	                
	            }
	        }else{//永久套餐
	            
	            $time= 0;
	            
	        }
	        
            $value['rating']                =	$id;
	        $value['rating_name']			=	$row['name'];
	        $value['rating_type']			=	$row['type'];
	        
	        $value['job_num']               =   array('=', (int)$row['job_num']);
	        $value['breakjob_num']          =   array('=', (int)$row['breakjob_num']);
	        $value['down_resume']           =   array('=', (int)$row['resume']);
	        $value['invite_resume']         =   array('=', (int)$row['interview']);
	        $value['zph_num']               =   array('=', (int)$row['zph_num']);
	        $value['top_num']               =   array('=', (int)$row['top_num']);
	        $value['urgent_num']            =   array('=', (int)$row['urgent_num']);
	        $value['rec_num']               =   array('=', (int)$row['rec_num']);
	        $value['sons_num']              =   array('=', (int)$row['sons_num']);
	        $value['integral']              =   array('+', (int)$row['integral_buy']);
	        
	        $value['vip_etime']				=	$time;
	        $value['vip_stime']				=	time();
  
	        
	    }else if($statis['rating_type'] != $row['type'] || $acc==2){//直接覆盖: ①时间类型和套餐类型相互转换 || ②后台未开启累加操作

	        if($row['service_time'] > 0){
	            
	            $time						=	time() + 86400 * $row['service_time'];
	            
	        }else{
	            
	            $time						=	0;
	            
	        }
	        
	        $value['rating']				=	$id;
	        $value['rating_name']			=	$row['name'];
	        $value['rating_type']			=	$row['type'];
	        
	        $value['job_num']               =   array('=', (int)$row['job_num']);
	        $value['breakjob_num']          =   array('=', (int)$row['breakjob_num']);
	        $value['down_resume']           =   array('=', (int)$row['resume']);
	        $value['invite_resume']         =   array('=', (int)$row['interview']);
	        $value['zph_num']               =   array('=', (int)$row['zph_num']);
	        $value['top_num']               =   array('=', (int)$row['top_num']);
	        $value['urgent_num']            =   array('=', (int)$row['urgent_num']);
	        $value['rec_num']               =   array('=', (int)$row['rec_num']);
	        $value['sons_num']              =   array('=', (int)$row['sons_num']);

	        $value['integral']              =   array('+', (int)$row['integral_buy']);
	        $value['vip_etime']				=	$time;
	        $value['vip_stime']				=	time();
	        $value['oldrating_name']        =	$row['name'];

	        
	    }
	    
	    
	    
	    return $value;
	    
	}


	/**
	 * @desc 获取会员等级列表
	 * $whereData		查询条件
	 * $data			自定义处理数组
	 */
	public function getList($whereData, $data = array()){
		

		$ListRating							=	array();
		$data['field']						=	empty($data['field']) ? '*' : $data['field'];
		$ListRating							=	$this -> select_all('company_rating', $whereData, $data['field']);
		
		
		return	$ListRating;

	}
	/**
	 * @desc 获取会员等级详情
	 * $whereData		查询条件
	 * $data			自定义处理数组
	 */
	public function getInfo($whereData, $data = array()){

		$InfoRating							=	array();
		$data['field']						=   empty($data['field']) ? '*' : $data['field'];
		$InfoRating							=	$this -> select_once('company_rating', $whereData, $data['field']);
		
		if (!empty($InfoRating) && is_array($InfoRating)) {
		    
		    /* 处理会员图标图片 */
		    if (!empty($InfoRating['com_pic'])){
		        if (trim($InfoRating['com_pic']) != ''){
		            $InfoRating['com_pic']  = checkpic($InfoRating['com_pic']);
		        }else{
		            unset($InfoRating['com_pic']);
		        }
		    }
		}
		return	$InfoRating;

	}
	/**
     * @desc 添加company_rating    详情 
     * $addData         添加数据
     */
	public function addRating($addData){
	    
	    $return = array(
            'id'        => 0,
            'errcode'   => 8,
            'layertype' => 0,
            'msg'       => ''
        );
	    
	    if (isset($addData['category']) && $addData['category']=='1') {
	        
	        if ($addData['youhui']) {
	            
	            if ($addData['time']=='') {
	                $return['errcode']      =   8;
	                $return['msg']          =   '请选择优惠日期！';
	                
	                return $return;
	            }
	            
	            if ($addData['yh_price'] == '' || $addData['yh_price'] > $addData['service_price']) {
	                $return['errcode']      =   8;
	                $return['msg']          =   '优惠价格不得大于初始售价！';
	                
	                return $return;
	            }
	            
                $times                      =   @explode('~', $addData['time']);
                $addData['time_start']      =   strtotime($times[0].' 00:00:00');
                $addData['time_end']        =   strtotime($times[1].' 23:59:59');
	            
	        }else {
	            
                $addData['yh_price']        =   0;
                $addData['time_start']      =   0;
                $addData['time_end']        =   0;
	            
	        }
	        
	        unset($addData['youhui']);
 	        unset($addData['id']);
	        
	    }elseif (isset($addData['category']) && $addData['category']=='2'){
	        
	        if($addData['time']){
	            
	            $times                      =   @explode('~', $addData['time']);
                $addData['time_start']      =	strtotime($times[0]);
                $addData['time_end']        =	strtotime($times[1].' 23:59:59');
                
	        }
	        
 	        unset($addData['id']);
	        unset($addData['time']);
	        
	    }
	    if ($addData['file']['tmp_name']){
	        
	        $upArr    =  array(
	            'file'  =>  $_POST['file'],
	            'dir'   =>  'compic'
	        );
	        require_once ('upload.model.php');
	        $uploadM  =  new upload_model($this->db, $this->def);
	        $pic      =  $uploadM->newUpload($upArr);
	        if (!empty($pic['msg'])){
	            
	            $return['errcode']  =  8;
	            $return['msg']      =  $pic['msg'];
	            return $return;
	            
	        }elseif (!empty($pic['picurl'])){
	            
	            $addData['com_pic']  =  $pic['picurl'];
	        }
	    }
	    unset($addData['file']);
	    if (!empty($addData)){    
	        
            $return['id']                   =	$this -> insert_into('company_rating', $addData);
	        
		}
		
		$typeStr							=	$this -> categoryMap($addData['category']);
		
		$typeStr							.=	'（ID：'.$return['id'].'）添加';
		
        $return['errcode']                  =   $return['id'] ? 9 : 8;                
        $return['msg']                      =	$return['id'] ? $typeStr.'成功！' : $typeStr.'失败！';

        return $return;
		
	}
	
    /**
     * @desc    修改company_rating    详情 
     * $id      修改条件ID数据
     * $upData  修改的数据
     * $data    自定义处理数组
     */
	public function upRating($id = null, $upData = array()){
		
	    $return = array(
	        'id'        => 0,
	        'errcode'   => 8,
	        'layertype' => 0,
	        'msg'       => ''
	    );
 	    
	    if (isset($upData['category']) && $upData['category']=='1') {
 	        if ($upData['youhui']) {
 	            
 	            if ($upData['time']=='') {
                    $return['errcode']      =   8;
                    $return['msg']          =   '请选择优惠日期！';
                    
                    return $return;
  	            }
 	            
  	            if ($upData['yh_price'] == '' || $upData['yh_price'] > $upData['service_price']) {
                    $return['errcode']      =   8;
                    $return['msg']          =   '优惠价格不得大于初始售价！';
                    
                    return $return;
  	            }
  	            
                $times                      =   @explode('~', $upData['time']);
                $upData['time_start']       =   strtotime($times[0].' 00:00:00');
                $upData['time_end']         =   strtotime($times[1].' 23:59:59');
                
 	        }else {
 	            
 	            $upData['yh_price']         =   0;
 	            $upData['time_start']       =   0;
 	            $upData['time_end']         =   0;
 	            
 	        }

 	        unset($upData['youhui']);
 	        unset($upData['useradd']);
 	        unset($upData['id']);
 	        
	    }elseif (isset($upData['category']) && $upData['category']=='2'){
 	        
 	        if($upData['time']){
 	            $times                      =   @explode('~', $upData['time']);
                $upData['time_start']       =	strtotime($times[0]);
                $upData['time_end']	        =	strtotime($times[1].' 23:59:59');
 	        }
 	        
 	        unset($upData['useradd']);
 	        unset($upData['id']);
 	        unset($upData['time']);
 	    }
  	    
 	    if ($upData['file']['tmp_name']){

 	        $upArr    =  array(
 	            'file'  =>  $upData['file'],
 	            'dir'   =>  'compic'
 	        );
 	        require_once ('upload.model.php');
 	        $uploadM  =  new upload_model($this->db, $this->def);
 	        $pic      =  $uploadM->newUpload($upArr);
 	        if (!empty($pic['msg'])){
 	            
 	            $return['errcode']  =  8;
 	            $return['msg']      =  $pic['msg'];
 	            return $return;
 	            
 	        }elseif (!empty($pic['picurl'])){
 	            
 	            $upData['com_pic']  =  $pic['picurl'];
 	        }
 	    }
 	    unset($upData['file']);
	    if (!empty($upData) && !empty($id)){   
 	    
            $return['id']           =	$this -> update_once('company_rating', $upData, array('id'=>intval($id)));
	        
		}
		
		$typeStr                    =	$this -> categoryMap($upData['category']);
		
        $typeStr                    .=	'（ID：'.$id.'）更新';

        $return['errcode']          =   $return['id'] ? 9 : 8;
        
        $return['msg']              =   $return['id'] ? $typeStr.'成功！' : $typeStr.'失败！';
        
		return $return;
	}
	
    /**
     * @desc 删除company_rating 详情 
     * $whereData       删除条件数据
     * $data 		    自定义处理数组
     */
	public function delRating($id = null, $data = array()){
		
	    $return 		= array(
	        'id'           => 0,
	        'errcode'      => 8,
	        'layertype'    => 0,
            'msg'           => ''
        );
		
		if (is_array($id)) {
		    
		    $ids    =	$id;
		    
		    $return['layertype']	=	1;
		    
		}else{
		    
		    $ids    =   @explode(',', $id);
		    
		    $return['layertype']	=	0;
		}
        
		$typeStr	=	$this -> categoryMap($data['category']);

	    if (!empty($ids)){       
	        
            $return['id']	=	$this -> delete_all('company_rating', array('id'=>array('in', pylode(',', $ids))), '');
            
		}
        
        $typeStr	.=	'（ID：'.$id.'）删除';
		
        $return['errcode']	=	$return['id'] ? 9 : 8;                    
        $return['msg']		=	$return['id'] ? $typeStr.'成功！' : $typeStr.'失败！';
        
		return $return;
	}
	
	/**
	 * company_rating		category 类型
	 */
	private function categoryMap($cId){
	    
		$categoryMap	=	array(
			1	=>	'企业会员等级',
			3	=>	'企业增值包'
		);
		return isset($categoryMap[$cId]) ? $categoryMap[$cId] : '';
	}
	
	/**
	 * @desc 查询 company_service表数据，单条查询
	 */
	function getComServiceInfo($where = array(), $data = array()) {
	    
	    $field      =   $data['field'] ? $data['field']  : '*';
	    
	    $info       =   $this -> select_once('company_service', $where, $field);
	    
	    return $info;
	    
	}
	
	/**
	 * @desc 查询 company_service表数据，多条查询
	 */
	function getComServiceList($whereData = array(), $data = array()) {
	    
        $field      =   $data['field'] ? $data['field']  : '*';
	    
        $List       =   $this -> select_all('company_service', $whereData, $field);
        
        if (isset($data['detail']) && $data['detail'] == 'yes') {
            
            $detailList =   $this->getComSerDetailList(array('orderby' => 'sort,desc'));
            
            if (!empty($detailList) && is_array($detailList)) {
                
                foreach ($List as $key => $value) {
                    
                    foreach ($detailList as $val){
                        
                        if ($value['id'] == $val['type']) {
                            
                            $List[$key]['detail'][] =   $val;
                        }
                    }
                }
            }
            
        }
	    
	    return $List;
	    
	}
	
	/**
	 * @desc 查询 company_service表数据，统计数量
	 * @return number
	 */
	function getComServiceNum($whereData = array()) {
	    
        return $this -> select_num('company_service', $whereData);
	    
	}
	
	/**
	 * @desc 新增  /  修改  企业增值类型
	 */    
	function upComService($postData = array()) {
	    
	    if (isset($postData)) {
	        
	        $return 		=    array();
	         
	        $id        =   intval($postData['id']);
	        $name      =   trim($postData['name']);
	        $sort      =   intval($postData['sort']);
	        $display   =   intval($postData['display']);
	        
	        $value = array(
	            'name'     => $name,
	            'display'  => $display,
	            'sort'     => $sort
	        );
	        
	        if (!empty($id)) {
	            
	            $service   =   $this -> getComServiceList(array('name'=>$name,'id'=>array('<>',$id)));
	            
	            if (!empty($service)) {
	                
	                $return['errcode'] =   8;
	                $return['msg']     =   '增值类型名称已存在！';
	                
	                return $return;
	                
	            }else{
	            
	                $return['id']      =   $this -> update_once('company_service', $value, array('id' => $id));
	            
	                $msg               =   '企业增值服务类型（ID：'.$id.'）修改';
	                
	                $return['msg']     =   $return['id'] ? $msg.'成功！' : $msg.'失败！' ;
	                
	                $return['errcode'] =   $return['id'] ? 9 : 8 ;
	                
	                $return['url']     =   $return['id'] ? 'index.php?m=admin_comrating&c=server&id='.$id : '' ;
	                
	            }
	            
	        }else{
	            
	            $service   =   $this -> getComServiceList(array('name'=>$name));
	            
	            if (!empty($service)) {
	                
	                $return['errcode'] =   8;
	                $return['msg']     =   '增值类型名称已存在！';
	                
	                return $return;
	                
	            }else{
	                
	                $return['id']      =   $this -> insert_into('company_service', $value);
	                
	                $msg               =   '企业增值服务类型（ID：'.$return['id'].'）添加';
	                
	                $return['msg']     =   $return['id'] ? $msg.'成功！' : $msg.'失败！' ;
	                
	                $return['errcode'] =   $return['id'] ? 9 : 8 ;
	                
	                $return['url']     =   $return['id'] ? 'index.php?m=admin_comrating&c=edittc&id='.$return['id'] : '' ;
	                
	            }
	            
	        }
	        
	        return $return;
	        
	    }
	    
	}
	
	/**
	 * @desc 更新  修改类型名称、设置增值服务状态
	 */    
	function setComService($data=array(),$where=array()) {
		$nid	=	$this -> update_once('company_service', $data, $where);
		return $nid;
	}
	
	/**
	 * @desc 删除增值包 company_service 信息
	 */
	function delComService($id = null, $data = array()) {
	    
	    $return 		= array(
	        'id'           => 0,
	        'errcode'      => 8,
	        'layertype'    => 0,
	        'msg'           => ''
	    );
	    
	    if (is_array($id)) {
	        
	        $ids    =	$id;
	        
	        $return['layertype']	=	1;
	        
	    }else{
	        
	        $ids    =   @explode(',', $id);
	        
	        $return['layertype']	=	0;
	    }

	    $typeStr                    =	$this -> categoryMap($data['category']);
	    
		if (!empty($ids)){
			
	        $return['id']	=	$this -> delete_all('company_service', array('id'=>array('in', pylode(',', $ids))), '');
	        
	        if ($return['id']) {
	            
	            $this  -> delete_all('company_service_detail', array('type'=>array('in', pylode(',', $ids))), '');
	            
	        }
	        
	    }
	    
	    
	    $typeStr	.=	'（ID：'.$id.'）删除';
 	    
	    $return['errcode']	=	$return['id'] ? 9 : 8;
	    $return['msg']		=	$return['id'] ? $typeStr.'成功！' : $typeStr.'失败！';
	    
	    return $return;
	}
	
	
	/**
	 * @desc 查询 company_service_detail表数据，单条查询
	 */
	function getComSerDetailInfo($id = null, $data = array()) {
	    
	    $field      =   $data['field'] ? $data['field']  : '*';
	    
	    $info       =   $this -> select_once('company_service_detail', array('id'=>intval($id)), $field);
	    
	    return $info;
	    
	}
	
	/**
	 * @desc 查询 company_service_detail表数据，多条查询
	 */
	function getComSerDetailList($whereData = array(), $data = array()) {
	    
	    $field      =   $data['field'] ? $data['field']  : '*';
	    
	    $List       =   $this -> select_all('company_service_detail', $whereData, $field);

	    foreach($List as $k=>$v){
	    	
	    	foreach($v as $key=>$val){
	    		$v[$key]	=	$val ? $val : 0;
	    	}
	    	$List[$k]		=	$v ? $v : 0;
	    }
	    
	    if ($data['pack'] == '1') {
	        $List  =   $this -> subComSerDetailList($List);
	    }
	    
	    return $List;
	    
	}
	
	/**
	 * @desc   购买服务弹出框，增值服务包详情添加名称
	 * @param  array $List
	 */
	private function subComSerDetailList($List) 
	{
	    $packList  =   $this->getComServiceList(array('orderby' => 'sort'), array('field'=> '`id`,`name`,`display`'));
	    
	    foreach ($List as $k => $v){
	        
	        foreach ($packList as $pv){
	            if ($pv['id'] == $v['type']) {
	                // 处理后台未启用的增值包类别
	                if ($pv['display'] != 1){
	                    unset($List[$k]);
	                }else{
	                    $List[$k]['name']  =   $pv['name'];
	                }
	            }
	        }
	    }
	    
	    return $List;
	}
	
	/**
	 * @desc 查询 company_service_detaile表数据，统计数量
	 * @return number
	 */
	function getComSerDetaileNum($whereData = array()) {
	    
	    return $this -> select_num('company_service_detail', $whereData);
	    
	}
	
	/**
	 * @desc 新增  /  修改  企业增值套餐详情
	 */
	function upComSerDetail($postData = array()) {
	    
	    if (isset($postData)) {
	        
	        $return 		=    array();
	        
	        $id                =   intval($postData['tid']);
	        
	        $service_price     =   floatval($postData['service_price']);
            $job_num           =   intval($postData['job_num']);
	        $resume            =   intval($postData['resume']);
	        $interview         =   intval($postData['interview']);
            $breakjob_num      =   intval($postData['breakjob_num']);
            $zph_num           =   intval($postData['zph_num']);
            $top_num           =   intval($postData['top_num']);
            $rec_num           =   intval($postData['rec_num']);
            $urgent_num        =   intval($postData['urgent_num']);
            $type              =   intval($postData['type']);
	        $sort              =   intval($postData['sort']);
	        
	        $value             =   array(
	            
	            'service_price'    =>  $service_price,
                'job_num'          =>  $job_num,
	            'resume'           =>  $resume,
	            'interview'        =>  $interview,
	            'breakjob_num'     =>  $breakjob_num,
	            'zph_num'          =>  $zph_num,
	            'top_num'          =>  $top_num,
	            'rec_num'          =>  $rec_num,
	            'urgent_num'       =>  $urgent_num,
	            'type'             =>  $type,
	            'sort'             =>  $sort
	        );
	        
	        if (!empty($id)) {
	            
                $return['id']      =   $this -> update_once('company_service_detail', $value, array('id' => $id));
                
                $msg               =   '企业增值服务套餐（ID：'.$id.'）详情修改';
                
                $return['msg']     =   $return['id'] ? $msg.'成功！' : $msg.'失败！' ;
                
                $return['errcode'] =   $return['id'] ? 9 : 8 ;
                
                $return['url']     =   $return['id'] ? 'index.php?m=admin_comrating&c=list&id='.$type : '' ;
                
	            
	        }else{
	                
	                $return['id']      =   $this -> insert_into('company_service_detail', $value);
	                
	                $msg               =   '企业增值服务套餐（ID：'.$return['id'].'）详情添加';
	                
	                $return['msg']     =   $return['id'] ? $msg.'成功！' : $msg.'失败！' ;
	                
	                $return['errcode'] =   $return['id'] ? 9 : 8 ;
	                
	                $return['url']     =   $return['id'] ? 'index.php?m=admin_comrating&c=list&id='.$type : '' ;
	                
	             
	        }
	        
	        return $return;
	    }
	}
	
	
	/**
	 * @desc 删除增值包详情 company_service_detail 信息
	 */
	function delComSerDetail($id = null) 
	{
	    
	    $return 		= array(

	        'id'		=>	0,
	        'errcode'	=>	8,
	        'layertype'	=>	0,
	        'msg'		=>	''
	    );
	    
	    if (is_array($id)) {
	        
	        $ids	=	$id;
	        $return['layertype']	=	1;
	        
	    }else{
	        
	        $ids	=   @explode(',', $id);
	        $return['layertype']	=	0;
	    }
	    
	    if (!empty($ids)){
	        
	        $return['id']	=	$this -> delete_all('company_service_detail', array('id'=>array('in', pylode(',', $ids))), '');
		}
 	    
        $msg				=	'套餐（ID：'.$id.'）删除';
	    
        $return['errcode']	=	$return['id'] ? 9 : 8;
        
        $return['msg']		=   $return['id'] ? $msg.'成功！' : $msg.'失败！';
	    
	    return $return;
	}
	
	function fetchRatingInfo($fetData=array())
	{
		if((int)$fetData['id']<1){
			$id 						=		$this->config['com_rating'];
		}else{						
			$id							=		(int)$fetData['id'];
		}						
		$row 							= 		$this->getInfo(array('id'=>$id));
		
			
        $data['rating']				=		$id;
		$data['rating_name']		=		$row['name'];
		$data['rating_type']		=		$row['type'];
		
		$data['job_num']			=		$row['job_num'];
		$data['down_resume']		=		$row['resume'];
		$data['breakjob_num']		=		$row['breakjob_num'];
		$data['invite_resume']		=		$row['interview'];
		$data['part_num']			=		$row['part_num'];
		$data['breakpart_num']		=		$row['breakpart_num'];
		$data['zph_num']			=		$row['zph_num'];
		$data['top_num']			=		$row['top_num'];
		$data['urgent_num']			=		$row['urgent_num'];
		$data['rec_num']			=		$row['rec_num'];

		if($row['service_time']){
			$time					=		time()+86400*$row['service_time'];
			$data['vip_etime']		=		$time;
		}else{
			$data['vip_etime']		=		0;
		}
		
		$data['integral']				=		$row['integral_buy'];
		$data['vip_stime']				=		time();
		
		
		return $data;
	}
	
	public function changeRatingInfo($id = 0, $uid = 0)
    {
        $id			=	intval($id);
	    $uid		=	intval($uid);
	    
	    //设置默认等级
	    if(empty($id)){
	        $id 	=	$this -> config['com_rating'];
	    }
	    
	    $acc 		=	$this -> config['package_data_acc']; //   1:累加  2：不累加
	    
	    if(empty($acc)){
	        
	        $acc 	=	2;
	        
	    }
 	    
	    $value		=	array();
	    
	    //获取企业账户统计信息
	    $statis = $this -> select_once('company_statis', array('uid' => $uid));
	      
	    //获取会员等级
	    $row = $this -> getInfo(array('id' => $id, 'category' => 1));
		
        if($statis['rating_type'] == $row['type'] && $row['type'] == 1 && $acc== 1){//套餐会员累加：①后台开启累加  && ②同为套餐会员
	        
	        if($row['service_time'] > 0){//有期限的套餐
	            
	            if($statis['vip_etime'] && isVip($statis['vip_etime'])){//当前会员时间剩余累加
	                
	                $time					=	$statis['vip_etime'] + 86400 * $row['service_time'];
	                
	            }else{
	                
	                $time					=	time() + 86400 * $row['service_time'];
	                
	            }
	            
	        }else{//永久套餐
	            
	            $time= 0;
	            
	        }
	        
	        $value['rating']				=	$id;
	        $value['rating_name']			=	$row['name'];
	        $value['rating_type']			=	$row['type'];
	        /*套餐数据累加*/
	        if(isVip($statis['vip_etime'])){
	            $value['job_num']           =   $statis['job_num'] + (int)$row['job_num'];     
	            $value['breakjob_num']      =   $statis['breakjob_num'] + (int)$row['breakjob_num'];
	            $value['down_resume']       =   $statis['down_resume'] + (int)$row['resume'];      
	            $value['invite_resume']     =   $statis['invite_resume'] + (int)$row['interview'];   
	            $value['zph_num']           =   $statis['zph_num'] + (int)$row['zph_num'];     
	            $value['urgent_num']        =   $statis['urgent_num'] + (int)$row['urgent_num'];  
	            $value['rec_num']           =   $statis['rec_num'] + (int)$row['rec_num'];     
	            $value['top_num']           =   $statis['top_num'] + (int)$row['top_num'];     
	            $value['integral']          =   $statis['integral'] + (int)$row['integral_buy'];
	            $value['sons_num']          =   $statis['sons_num'] + (int)$row['sons_num'];    
	        }else{
	            $value['job_num']           =  	(int)$row['job_num'];     
	            $value['breakjob_num']      =  	(int)$row['breakjob_num'];
	            $value['down_resume']       =  	(int)$row['resume'];      
	            $value['invite_resume']     =  	(int)$row['interview'];  
	            $value['zph_num']           =  	(int)$row['zph_num'];     
	            $value['top_num']           =  	(int)$row['top_num'];  		
	            $value['urgent_num']        =  	(int)$row['urgent_num'];  
	            $value['rec_num']           =  	(int)$row['rec_num'];     
	            $value['sons_num']          =  	(int)$row['sons_num'];		
	        }                                   
	        /*累加数据End*/
	        
	        $value['vip_etime']				=	$time;
	        $value['vip_stime']				=	time();
	        
	    }else if($statis['rating_type'] == $row['type'] && $row['type'] == 2 && $acc== 1){//时间会员期限累加：①后台开启累加  && ②同为时间会员
	        
	        if($row['service_time'] > 0){//有期限的套餐
	            
	            if($statis['vip_etime'] && isVip($statis['vip_etime'])){//当前会员时间剩余累加
	                
	                $time  =   $statis['vip_etime'] + 86400 * $row['service_time'];
	                
	            }else{
	                
	                $time  =   time() + 86400 * $row['service_time'];
	                
	            }
	        }else{//永久套餐
	            
	            $time= 0;
	            
	        }
	        
            $value['rating']                =	$id;
	        $value['rating_name']			=	$row['name'];
	        $value['rating_type']			=	$row['type'];
			 
	        $value['job_num']               =   (int)$row['job_num'];
	        $value['breakjob_num']          =   (int)$row['breakjob_num'];
	        $value['down_resume']           =   (int)$row['resume'];
	        $value['invite_resume']         =   (int)$row['interview'];
	        $value['zph_num']               =   (int)$row['zph_num'];
	        $value['top_num']               =   (int)$row['top_num'];
	        $value['urgent_num']            =   (int)$row['urgent_num'];
	        $value['rec_num']               =   (int)$row['rec_num'];
	        $value['sons_num']              =   (int)$row['sons_num'];
	        
	        $value['vip_etime']				=	$time;
	        $value['vip_stime']				=	time();
  
	        
	    }else if($statis['rating_type'] != $row['type'] || $acc==2){//直接覆盖: ①时间类型和套餐类型相互转换 || ②后台未开启累加操作

	        if($row['service_time'] > 0){
	            
	            $time						=	time() + 86400 * $row['service_time'];
	            
	        }else{
	            
	            $time						=	0;
	            
	        }
	        
	        $value['rating']				=	$id;
	        $value['rating_name']			=	$row['name'];
	        $value['rating_type']			=	$row['type'];
			 
	        $value['job_num']               =   (int)$row['job_num'];
	        $value['breakjob_num']          =   (int)$row['breakjob_num'];
	        $value['down_resume']           =   (int)$row['resume'];
	        $value['invite_resume']         =   (int)$row['interview'];
	        $value['zph_num']               =   (int)$row['zph_num'];
	        $value['top_num']               =   (int)$row['top_num'];
	        $value['urgent_num']            =   (int)$row['urgent_num'];
	        $value['rec_num']               =   (int)$row['rec_num'];
	        $value['sons_num']              =   (int)$row['sons_num'];
	        $value['vip_etime']				=	$time;
	        $value['vip_stime']				=	time();
	        $value['oldrating_name']        =	$row['name'];
	    }
	    
	    return $value;
	    
	}
	
	
}
?>