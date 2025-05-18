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
class report_controller extends adminCommon{
	
	function index_action(){
	    
	    $reportM       =   $this->MODEL('report');
	    
	    $where         =   array();    
	    
	    $type          =   intval($_GET['type']);
	    $ut            =   intval($_GET['ut']);
	    $keywordStr    =   trim($_GET['keyword']);
	    $ftypeStr      =   intval($_GET['f_type']);
	    $ptypeStr      =   intval($_GET['p_type']);
	    
	    if($type == 0){
	        
	        $where['usertype']     =   $ut==2 ? 2 : 1;
	        $where['type']         =   $type;
			
			if (!empty($keywordStr)){
			    
				if ($ftypeStr == 1){
				    
					$where['r_name']	=	array('like', $keywordStr);
					
				}elseif ($ftypeStr == 2){
				    
				    $where['username']	=	array('like', $keywordStr);
					
				}elseif ($ftypeStr == 3){
				    
				    $where['r_reason']	=	array('like', $keywordStr);
				}
				$urlarr['f_type']		=	$ftypeStr;
				$urlarr['keyword']      =	$keywordStr;
			}
			
			$type		=	0;
			$rowName	=	'userrows';
			$urlarr['ut']  =  $ut;
			$this->yunset('ut', $ut);
			
	    }else if($type > 0){
			
	        $where['type']     =   $type;
		
			if (!empty($keywordStr)){
				
				if ($ptypeStr == 1){
				    
					$where['r_name']		=	array('like', $keywordStr);	
				}else{
					$where['username']		=	array('like', $keywordStr);	
				}
				$urlarr['p_type']			=	$ptypeStr;
				$urlarr['keyword']			=	$keywordStr;
			}
 			$rowName		 =	'q_report';
 			$urlarr['type']  =  $type;
 		}
		//分页链接
		$urlarr['page']   =	  '{{page}}';
		$pageurl          =	  Url($_GET['m'],$urlarr,'admin');
		
		//提取分页
		$pageM            =	  $this  -> MODEL('page');
		$pages            =	  $pageM -> pageList('report',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
			
			if($_GET['order']){
				$where['orderby']	=	$_GET['t'].",".$_GET['order'];
				
			}else{
				$where['orderby']	=	'id,desc';
			}
			$where['limit']			=	$pages['limit'];
			$urlarr['order']		=	$_GET['order'];
			$urlarr['t']			=	$_GET['t'];
			$List					=	$reportM -> getReportList($where,array('utype'=>'admin','type'=>$type));
			$this->yunset($rowName,$List['list']);
		}
		$back_url	=	$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		
		$this->yunset('type',$type);
		$this->yunset('back_url',$back_url);
		
		$this->yuntpl(array('admin/admin_report_userlist'));
	}
	
	function recommend_action(){
		$reportM	=	$this->MODEL('report');
		$logM		=	$this->MODEL('log');
		
		$data[$_GET['type']]	=	$_GET['rec'];
		$where['id']			=	$_GET['id'];
		$where['type']			=	'1';
		$nid		=	$reportM->upReport($where,$data);
		
		$logM->addAdminLog("举报问答(ID:".$_GET['id'].")设置是否处理");
		echo $nid?1:0;die;
	}
	function result_action(){
		$reportM	=	$this->MODEL('report');
		$adminM		=	$this->MODEL('admin');
		
		$info		=	$reportM->getReportOne(array('id'=>intval($_POST['id'])),array('field'=>'`result`,`rtime`,`admin`'));
		if($info['admin']){
			$adminname		=	$adminM->getAdminUser(array('uid'=>$info['admin']),array('field'=>'`name`'));
			$info['admin']	=	$adminname['name'];
			$info['rtime']	=	date('Y-m-d H:i',$info['rtime']);
		}
		echo json_encode($info);die;
	}
	function  saveresult_action(){
		$reportM   =	$this->MODEL('report');
		$resumeM   =   $this->MODEL('resume');
	    $statisM   =   $this->MODEL('statis');
	    $integralM =   $this->MODEL('integral');
	    $orderM    =   $this->MODEL('companyorder');
		$downM     =   $this->MODEL('downresume');
		$eid       =   intval($_POST['eid']);
	    $id        =   intval($_POST['pid']);
	    $uid       =   intval($_POST['uid']);
		if(intval($_POST['tongbu'])==1){
			$report	=	$reportM -> getReportList(array('eid' =>$eid));
			foreach($report['list'] as $key=>$val){
				$rids[]	=	$val['id'];
				$allcomid[]	=	$val['p_uid'];
			}
			if(intval($_POST['datafh'])==1){
				$dresume   =   $downM -> getSimpleList(array('eid' => $eid, 'comid' => array('in',pylode(",",$allcomid))));
				foreach($report['list'] as $key=>$val){
					foreach($dresume as $k=>$v){
						if($v['comid']==$val['p_uid'] && $v['eid']==$val['eid']){
							$dreport[]	=	$val;
							$comid[]	=	$val['p_uid'];
						}
					}
				}
				$order		=   $orderM -> getList(array('type' => 19,'sid' => $eid, 'order_remark' => array('like', '下载简历'), 'uid' => array('in',pylode(',',$comid))));     
				$compay		=   $integralM -> getList(array('type' => 1 ,'eid'=>$eid,'pay_type' => '12', 'pay_remark' => array('like', '简历下载'), 'com_id' => array('in',pylode(',',$comid))));
				foreach($order as $k=>$v){
					$notuid[]	=	$v['uid'];
					$integralM -> company_invtal($v['uid'],2,$v['order_price'],true,'举报简历返还金额',true,2,'packpay',99);   
				}
				foreach($compay as $k=>$v){
					$notuid[]	=	$v['com_id'];
					$integralM -> company_invtal($v['com_id'],2,abs($v['order_price']),true,'举报简历返还积分',true,2,'integral',99);  
				}
				foreach($dreport as $key=>$val){
					if(!in_array($val['p_uid'],$notuid)){
						$statisM   ->  upInfo(array('down_resume' => array('+', 1)), array('usertype' => 2, 'uid' => $val['p_uid']));  
					}
				}
			}	
			$where['id']		=	array('in',pylode(",",$rids));
		}else{
			$report    =   $reportM -> getReportOne(array('id' => $id), array('field'=> '`p_uid`'));
			$comid     =   intval($report['p_uid']);
			$dresume   =   $downM -> getDownResumeInfo(array('eid' => $eid, 'uid' => $uid, 'comid' => $comid),array('field'=>'`eid`'));
			if (!empty($dresume)) {
				$order     =   $orderM -> getInfo(array('type' => 19, 'sid' => $eid, 'order_remark' => array('like', '下载简历'), 'uid' => $comid), array('field' => '`order_price`'));     
				$compay    =   $integralM -> getInfo(array('type' => 1 ,'eid'=>$eid, 'pay_type' => '12', 'pay_remark' => array('like', '下载简历'), 'com_id' => $comid), array('field' => '`order_price`'));   
			}
			if(intval($_POST['datafh'])==1){
				if (!empty($order) && is_array($order)) {   
					$integralM -> company_invtal($comid,2,$order['order_price'],true,'举报简历返还金额',true,2,'packpay',99);   
				}
				if (!empty($compay) && is_array($compay)) {     
					$integralM -> company_invtal($comid,2,abs($compay['order_price']),true,'举报简历返还积分',true,2,'integral',99);      
				}
				if (empty($order) && empty($compay)) {     
					$statisM   ->  upInfo(array('down_resume' => array('+', 1)), array('usertype' => 2, 'uid' => $comid));  
				}
			}
			$where['id']		=	intval($_POST['pid']);
		}	
		
		$data['datafh']		=	$_POST['datafh'];
		$data['result']		=	trim($_POST['result']);
		$data['admin']		=	$_SESSION['auid'];
		$data['rtime']		=	time() ;
		
		$return		=	$reportM->upReport($where,$data);
		$this->ACT_layer_msg("操作成功！",9,$_SERVER['HTTP_REFERER']);
	}
	
	function  saveresultall_action(){
		$reportM   =	$this->MODEL('report');
		$resumeM   =   $this->MODEL('resume');
	    $statisM   =   $this->MODEL('statis');
	    $integralM =   $this->MODEL('integral');
	    $orderM    =   $this->MODEL('companyorder');
		$downM     =   $this->MODEL('downresume');
		
		if(!empty($_POST['rid'])){
			$rids	=	explode(",",$_POST['rid']);
			
			if(intval($_POST['datafh'])==1){
				$report	=   $reportM -> getReportList(array('id' => array('in',pylode(",",$rids))));
				$dresume	=	$compay	=	0;
				
				foreach($report['list'] as $key=>$val){
					$dresume	=   $downM -> getDownNum(array('eid' => $val['eid'], 'uid' => $val['c_uid'], 'comid' => $val['p_uid'],'usertype'=>2));
					
					if($dresume>0){
						$val['fhtype']	=	1;
						$comid[]	=	$val['p_uid'];
						$dreport[]	=	$val;
					}
				}
				 
				$order		=   $orderM -> getList(array('type' => 19, 'order_remark' => array('like', '下载简历'), 'uid' => array('in',pylode(',',$comid))));     
				$compay		=   $integralM -> getList(array('type' => 1 ,'pay_type' => '12', 'pay_remark' => array('like', '简历下载'), 'com_id' => array('in',pylode(',',$comid))));
				foreach($dreport as $key=>$val){
					foreach($order as $k=>$v){
						if($val['p_uid']==$v['uid'] && $val['eid']==$v['sid']){
							$dreport[$key]['fhtype']	=	2;//金额操作
							$dreport[$key]['fhprice']	=	$v['order_price'];
						}
					}
					foreach($compay as $k=>$v){
						if($val['p_uid']==$v['com_id'] && $val['eid']==$v['eid']){
							if($dreport[$key]['fhtype']	==	2){
								$dreport[$key]['fhtype']	=	4;//金额+积分操作
								$dreport[$key]['fhprice_two']	=	abs($v['order_price']);
							}else{
								$dreport[$key]['fhtype']	=	3;//积分操作
								$dreport[$key]['fhprice']	=	abs($v['order_price']);
							}
						}
					}
				}	
				
				foreach($dreport as $key=>$val){
					if ($val['fhtype']==2) { 
						$integralM -> company_invtal($val['p_uid'],2,$val['fhprice'],true,'举报简历返还金额',true,2,'packpay',99);   
					}elseif($val['fhtype']==3) {  
						$integralM -> company_invtal($val['p_uid'],2,$val['fhprice'],true,'举报简历返还积分',true,2,'integral',99);  
					}elseif($val['fhtype']==4) { 
						$integralM -> company_invtal($val['p_uid'],2,$val['fhprice'],true,'举报简历返还金额',true,2,'packpay',99);   
						$integralM -> company_invtal($val['p_uid'],2,$val['fhprice_two'],true,'举报简历返还积分',true,2,'integral',99);      	
					}else{
						$statisM   ->  upInfo(array('down_resume' => array('+', 1)), array('usertype' => 2, 'uid' => $val['p_uid']));  
					}
				}	
			}	
			$data['datafh']		=	$_POST['datafh'];
			$data['result']		=	trim($_POST['result']);
			$data['admin']		=	$_SESSION['auid'];
			$data['rtime']		=	time() ;
			$where['id']		=	array('in',pylode(",",$rids));
			
			$return		=	$reportM->upReport($where,$data);
			
			$this->ACT_layer_msg("操作成功！",9,$_SERVER['HTTP_REFERER']);
			
		}	
	}
	
    
	/**
	 * @desc 删除举报简历
	 */
	function delresume_action(){
	    
	    $reportM   =   $this->MODEL('report');
	    $resumeM   =   $this->MODEL('resume');
	    $statisM   =   $this->MODEL('statis');
	    
	    $integralM =   $this->MODEL('integral');
	    $orderM    =   $this->MODEL('companyorder');
	    $downM     =   $this->MODEL('downresume');
	    
	    $eid       =   intval($_GET['eid']);
	    $id        =   intval($_GET['id']);
	    $uid       =   intval($_GET['uid']);
	    
	    $report    =   $reportM -> getReportOne(array('id' => $id), array('field'=> '`p_uid`'));
	    $comid     =   intval($report['p_uid']);
	   
	    $dresume   =   $downM -> getDownResumeInfo(array('eid' => $eid, 'uid' => $uid, 'comid' => $comid),array('field'=>'`eid`'));
	    
		if (!empty($dresume)) {
	         
	        
	        $order     =   $orderM -> getInfo(array('type' => 19, 'sid' => $eid, 'order_remark' => array('like', '下载简历'), 'uid' => $comid), array('field' => '`order_price`'));
	        
	        $compay    =   $integralM -> getInfo(array('type' => 1 ,'eid' => $eid, 'pay_type' => '12', 'pay_remark' => array('like', '下载简历'), 'com_id' => $comid), array('field' => '`order_price`'));
	        
	    }
	    $result    =   $resumeM -> delResume($eid,array('utype'=>'admin'));
	    if ($result) {
	        
	        if (!empty($order) && is_array($order)) {
                
	            $integralM -> company_invtal($comid,2,$order['order_price'],true,'举报简历返还金额',true,2,'packpay',99);
	            
	        }
	        if (!empty($compay) && is_array($compay)) {
	            
	            $integralM -> company_invtal($comid,2,abs($compay['order_price']),true,'举报简历返还积分',true,2,'integral',99);
	            
	        }
	        if (empty($order) && empty($compay)) {
	            
	            $statisM   ->  upInfo(array('down_resume' => array('+', 1)), array('usertype' => 2, 'uid' => $comid));
	            
	        }
	        
	        $statisM   ->  upInfo(array('resume_num' => array('-' , 1)), array('usertype'=>1,'uid'=>$uid));
	        
	        $return    =   $reportM -> delReport(array('id' => $id),array('title'=>'简历'));
	        
	        $this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	        
	    }
	}
	
	/**
	 * @desc 批量删除举报简历
	 */
	function delresumeall_action(){
		$reportM   =	$this->MODEL('report');
		$resumeM   =   	$this->MODEL('resume');
	    $statisM   =   	$this->MODEL('statis');
	    $integralM =   	$this->MODEL('integral');
	    $orderM    =   	$this->MODEL('companyorder');
		$downM     =   	$this->MODEL('downresume');
		
		if(!empty($_POST['rid'])){
			$rids	=	explode(",",$_POST['rid']);
			$report	=   $reportM -> getReportList(array('id' => array('in',pylode(",",$rids))));
			$dresume	=	$compay	=	0;
			
			foreach($report['list'] as $key=>$val){
				$dresume	=   $downM -> getDownNum(array('eid' => $val['eid'], 'uid' => $val['c_uid'], 'comid' => $val['p_uid'],'usertype'=>2));
				
				if($dresume>0){
					$val['fhtype']	=	1;
					$comid[]	=	$val['p_uid'];
					$dreport[]	=	$val;
				}
			}
			 
			$order		=   $orderM -> getList(array('type' => 19, 'order_remark' => array('like', '下载简历'), 'uid' => array('in',pylode(',',$comid))));     
			$compay		=   $integralM -> getList(array('type' => 1 ,'pay_type' => '12', 'pay_remark' => array('like', '简历下载'), 'com_id' => array('in',pylode(',',$comid))));
			foreach($dreport as $key=>$val){
				foreach($order as $k=>$v){
					if($val['p_uid']==$v['uid'] && $val['eid']==$v['sid']){
						$dreport[$key]['fhtype']	=	2;//金额操作
						$dreport[$key]['fhprice']	=	$v['order_price'];
					}
				}
				foreach($compay as $k=>$v){
					if($val['p_uid']==$v['com_id'] && $val['eid']==$v['eid']){
						if($dreport[$key]['fhtype']	==	2){
							$dreport[$key]['fhtype']	=	4;//金额+积分操作
							$dreport[$key]['fhprice_two']	=	abs($v['order_price']);
						}else{
							$dreport[$key]['fhtype']	=	3;//积分操作
							$dreport[$key]['fhprice']	=	abs($v['order_price']);
						}
					}
				}
			}	
			foreach($dreport as $key=>$val){
				if ($val['fhtype']==2) { 
					$integralM -> company_invtal($val['p_uid'],2,$val['fhprice'],true,'举报简历返还金额',true,2,'packpay',99);   
				}elseif($val['fhtype']==3) {  
					$integralM -> company_invtal($val['p_uid'],2,$val['fhprice'],true,'举报简历返还积分',true,2,'integral',99);  
				}elseif($val['fhtype']==4) { 
					$integralM -> company_invtal($val['p_uid'],2,$val['fhprice'],true,'举报简历返还金额',true,2,'packpay',99);   
					$integralM -> company_invtal($val['p_uid'],2,$val['fhprice_two'],true,'举报简历返还积分',true,2,'integral',99);      	
				}else{
					$statisM   ->  upInfo(array('down_resume' => array('+', 1)), array('usertype' => 2, 'uid' => $val['p_uid']));  
				}
			}	
			
			$return		=	$reportM -> delReport(array('id' => $rids),array('title'=>'简历'));
			echo json_encode($return);die;
		}	
	}
	
	function deljob_action(){
		$reportM	=	$this->MODEL('report');
		$jobM		=	$this->MODEL('job');
		$jobM		->	delJob(array('id'=>$_GET['eid']), array('utype'=>'admin'));
		$return 	=	$reportM->delReport(array('id'=>$_GET['id']),array('title'=>'职位'));
		
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	}
	
	function del_action(){
		$reportM	=	$this->MODEL('report');
		$this->check_token(); 
		
		if($_GET['type']=="pldel"){
			$one    =   $reportM -> getReportOne(array('id' => $_GET['del']), array('field'=> '`eid`'));
			$report	=	$reportM -> getReportList(array('eid' =>$one['eid']));
			foreach($report['list'] as $key=>$val){
				$rids[]	=	$val['id'];
			}
			$where['id']	=	$rids;
		}else{
			$where['id']	=	$_GET['del'];
		}
		$return 	=	$reportM->delReport($where,array('title'=>'举报'));
		if($_GET['type']=="pldel"){
			$return['layertype']	=	0;
		}
		$this	->	layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	}
	function delquestion_action(){		
		if($_GET['del']){
			$askM	=	$this->MODEL('ask');
			$askM->DeleteQuestion($_GET['del']);
			$this->layer_msg('问答(ID:'.$_GET['del'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']);		
		}
	}
	
	function show_action(){
		if($_POST['id']){
			$reportM			=	$this->MODEL('report');
			$row				=	$reportM->getReportOne(array('id'=>$_POST['id']),array('field'=>'`r_reason`'));
			$data['r_reason']	=	$row['r_reason'];
			
			echo json_encode($data);die;
		}
	}
}

?>