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
class admin_hotjob_controller extends adminCommon{
    
 	function set_search(){
 	    
        $ratingM = $this->MODEL('rating');

        $rating = $ratingM->getList(array( 'category' => '1', 'orderby' => 'sort,asc' ), array( 'field' => '`id`,`name`' ));
        
        if (! empty($rating)) {
            foreach ($rating as $k => $v) {
                $ratingarr[$v['id']] = $v['name'];
            }
        }

        $edtime = array(
            '1' => '7天内',
            '2' => '一个月内',
            '3' => '半年内',
            '4' => '一年内',
            '5' => '已到期'
        );
        
        $search_list[] = array(
            "param" => "rating",
            "name" => '会员等级',
            "value" => $ratingarr
        );
        
        $search_list[] = array(
            "param" => "time",
            "name" => '到期时间',
            "value" => $edtime
        );
        
        $this->yunset("search_list", $search_list);
	}
	
	function index_action(){
	    
	    $ComM          =   $this -> MODEL('company');
	    
	    $typeStr       =   intval($_GET['ctype']);
	    
	    $keywordStr    =   trim($_GET['keyword']);
	    
	    if (!empty($keywordStr)){
	        
	        if($typeStr == 1){
	            
	            $where['username'] =   array('like', $keywordStr);
	            
	        }else if($typeStr == 2){
	            
	            $where['beizhu']   =   array('like', $keywordStr);
	            
	        }
	        
	        $urlarr['ctype']       =	$typeStr;
	        
	        $urlarr['keyword']     =	$keywordStr;
	    }
	    
	    if($_GET['rating']){
	        
	        $where['rating_id']    =   intval($_GET['rating']);
	        
	        $urlarr['rating']      =   intval($_GET['rating']);
	        
	    }
	    
	    if($_GET['time']){
	        
            if ($_GET['time'] == '1') {
                $num = "+7 day"; // 7天
            } elseif ($_GET['time'] == '2') {
                $num = "+1 month"; // 一个月
            } elseif ($_GET['time'] == '3') {
                $num = "+6 month"; // 半年
            } elseif ($_GET['time'] == '4') {
                $num = "+1 year"; // 一年
            }
        			
            if($_GET['time']=='5'){
                
                $where['time_end']  =   array('<', time());
                
 			}else{
 			    $where['PHPYUNBTWSTART']    =   '';
 			    
 			    $where['time_end'][]    =   array('>', time());
 			    $where['time_end'][]    =   array('<', strtotime($num));
 			    
 			    $where['PHPYUNBTWEND']    =   '';
			}
			
			$urlarr['time']  =   $_GET['time'];
			
		}
		
		$urlarr['page']	=	'{{page}}';
		
		$pageurl		=	Url($_GET['m'], $urlarr, 'admin');
		
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		
		$pages			=	$pageM -> pageList('hotjob', $where, $pageurl, $_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
		    
		    //limit order 只有在列表查询时才需要
		    if($_GET['order']){
		        
		        $where['orderby']		=	$_GET['t'].','.$_GET['order'];
		        $urlarr['order']		=	$_GET['order'];
		        $urlarr['t']			=	$_GET['t'];
		        
		    }else{
		        
		        $where['orderby']		=	array('time_start,desc');
		        
		    }
		    
		    $where['limit']				=	$pages['limit'];
		    
		    $rows     =   $ComM -> getHotJobList($where, array('utype'=>'admin'));
 		    
		    $this -> yunset(array('rows'=>$rows));
		    
		}
 		
  		$this->set_search();
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_hotjob'));
	}
	
	/**
	 * @desc 后台  企业  名企设置/修改弹出
	 */
	function hotjobinfo_action(){
        
	    $ComM      =   $this -> MODEL('company');
	    
	    $id        =   intval($_GET['id']);
	    $uid       =   intval($_GET['uid']);
	    
		if(!empty($id)){
			
		    $hotjob   =   $ComM -> getHotJob(array('uid'=>$id));
		    
		}else if(!empty($uid)){
		    
		    $statisM  =   $this -> MODEL('statis');
		    $statis   =   $statisM -> getInfo($uid, array('usertype'=>'2','field'=>'`rating` as `rating_id`,`rating_name` as `rating`'));
		    $company  =   $ComM -> getInfo($uid,array('logo'=>1,'utype'=>'admin','field'=>'`name` as `username`, `uid`,`logo`'));

		    $company['hot_pic']   =   $company['logo'];
		    unset($company['logo']);
 		    
		    $hotjob   =   array_merge($statis, $company);
		    
 			$hotjob['time_start']=time();
		}
 		$this->yunset("hotjob",$hotjob);
		
		$this->yuntpl(array('admin/admin_hotjob_info'));
	}
	
	/**
	 * @desc 取消（删除）名企招聘
	 */
	function del_action(){

	    $this->check_token();
	    
	    if (is_array($_GET['del'])){
	        
	        $layer_type    =   1;
	        $uid           =   $_GET['del'];
	        
	    }elseif ($_GET['del']){
	      
	        $layer_type    =   0;
	        $uid           =   intval($_GET['del']);
	        
	    }
	    
	    $ComM      =   $this -> MODEL('company');
	    
	    $return    =   $ComM -> delHotJob($uid);
	    
	    $return ? $this->layer_msg('名企(ID:'.$uid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']) : $this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
	    
	}
	
	/**
	 * @desc 设置/修改名企 
	 */
	function save_action(){
 	    
        
	    
        if($_FILES['file']['tmp_name']!="" && is_uploaded_file($_FILES['file']['tmp_name'])){
            
	 		$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'hotpic'
			);

			$uploadM  =  $this->MODEL('upload');

			$picr      =  $uploadM->newUpload($upArr);
			
			if (!empty($picr['msg'])){

				$this->ACT_layer_msg($picr['msg'],8);

			}elseif (!empty($picr['picurl'])){

				$pic 	=  	$picr['picurl'];
			}
		 	
			
 		}
  		
		$ComM     =   $this -> MODEL('company');
		
		$hotJob   =   $ComM -> getHotJob(array('uid' => $_POST['uid']));
		
		if(!$hotJob){
		    
		    $com      =   $ComM -> getInfo(intval($_POST['uid']),array('field'=>'`logo`,`did`','logo'=>1));
		    
		    $hotJob['hot_pic']	=	$com['logo_n'];
		    $hotJob['did']		=	$com['did'];
		}
	
		$value    =   array(
		    'uid'             =>  intval($_POST['uid']),
		    'username'        =>  trim($_POST['username']),
		    'rating'          =>  trim($_POST['rating']),
		    'hot_pic'         =>  trim($pic)?trim($pic):$hotJob['hot_pic'],
		    'service_price'   =>  intval($_POST['service_price']),
		    'time_start'      =>  strtotime($_POST['time_start']),
		    'time_end'        =>  strtotime($_POST['time_end']),
		    'sort'            =>  intval($_POST['sort']),
		    'beizhu'          =>  trim($_POST['beizhu']),
		    'rating_id'       =>  intval($_POST['rating_id'])
		);

		if($_POST['hotad']){
		    
		    if (empty($hotJob['hot_pic']) && $_FILES['file']['tmp_name']==''){
		        
		        $this  ->  ACT_layer_msg('请上传企业展示LOGO',8,'index.php?m=admin_company',2,1);
		    }
		    
		    $value['did'] =	  intval($hotJob['hot_pic']);
			
		    $arr          =   $ComM -> addHotJob($value);
			
		}elseif($_POST['hotup']){

		    $arr  =   $ComM -> upHotJob($_POST['uid'], $value);  
		}
		
		$this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],'index.php?m=admin_hotjob',2,1);
	}

	function hotNum_action(){
	    
		$MsgNum=$this->MODEL('msgNum');
		
		echo $MsgNum->hotNum();
		
	}
	
}
?>