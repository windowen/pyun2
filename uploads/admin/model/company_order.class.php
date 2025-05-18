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
class company_order_controller extends adminCommon{
	//设置高级搜索功能
	function set_search(){
		include (APP_PATH."/config/db.data.php");
		
		$search_list[]	=	array("param"=>"typezf","name"=>'支付类型',"value"=>array("alipay"=>"支付宝","tenpay"=>"财富通","bank"=>"银行转帐"));
		
		$search_list[]	=	array("param"=>"typedd","name"=>'订单类型',"value"=>$arr_data['ordertype']);
		
		$search_list[]	=	array("param"=>"order_state","name"=>'订单状态',"value"=>array("0"=>"支付失败","1"=>"等待付款","2"=>"支付成功","3"=>"等待确认"));
		
		$lo_time		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		$search_list[]	=	array("param"=>"time","name"=>'充值时间',"value"=>$lo_time);
		
		$this -> yunset("search_list",$search_list);
	}
	function index_action(){
		$this -> set_search();
		
		$OrderM							=	$this -> MODEL('companyorder');
		
		$where							=   array();
	    $urlarr							=   $_GET;
	    $keywordStr						=   trim($_GET['keyword']);
		
		if(intval($_GET['comid'])){
			$where['uid']				=	intval($_GET['comid']);
			
			$urlarr['comid']			=	$_GET['comid'];
			
			$ComM						=	$this -> MODEL('company');
			$comname					=	$ComM -> getInfo($_GET['comid'],array('field'=>'name'));
			$this -> yunset("ccname",$comname['name']);
		}
		if($_GET['typezf']!=""){
			$where['order_type']		=	$_GET['typezf'];

			$urlarr['typezf']			=	$_GET['typezf'];
		}
		if($_GET['typedd']!=""){
			$where['type']				=	$_GET['typedd'];

			$urlarr['typedd']			=	$_GET['typedd'];
		}
		
		if($_GET['news_search']){
			if (!empty($keywordStr) && $_GET['typeca']=='1') {
				$where['order_id']		=	array('like', $keywordStr);
			}elseif(!empty($keywordStr) && $_GET['typeca']=='2'){
				$UserinfoM				=	$this -> MODEL('userinfo');
				$orderinfo				=	$UserinfoM -> getList(array('username'=>array('like',$keywordStr)),array('field'=>'uid'));
				
				if (is_array($orderinfo)){
					foreach ($orderinfo as $val){
						$orderuids[]	=	$val['uid'];
					}
					$where['uid']		=	array('in', pylode(",",$orderuids));
				}
			}
			$urlarr['keyword']			=	$keywordStr;
			$urlarr['typeca']			=	$_GET['typeca'];
			$urlarr['news_search']		=	$_GET['news_search'];
		}
		if($_GET['time']||$_GET['time_start1']!=""||$_GET['time_end1']!=""){
			$where['PHPYUNBTWSTART_B']    =   'AND';
			if($_GET['time']){
				if($_GET['time'] == 1){
					$where['order_time'][]	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
				}else{
					$where['order_time'][]	=	array('>=',strtotime('-'.intval($_GET['time']).' day'));
				}
				$urlarr['time']				=	$_GET['time'];
			}
			if($_GET['time_start1']!=""){
				$where['order_time'][]		=	array('>=',strtotime($_GET['time_start1']." 00:00:00"));

				$urlarr['time_start1']		=	$_GET['time_start1'];
			}
			if($_GET['time_end1']!=""){
				$where['order_time'][]		=	array('<',strtotime($_GET['time_end1']." 23:59:59"));

				$urlarr['time_end1']		=	$_GET['time_end1'];
			}
			$where['PHPYUNBTWEND_B']	=	'';
		}
		if($_GET['order_state']!=""){
			$where['order_state']		=	$_GET['order_state'];

			$urlarr['order_state']		=	$_GET['order_state'];
		}
		if($_GET['fb']!=""){
			$where['is_invoice']		=	$_GET['fb'];

			$urlarr['fb']				=	$_GET['fb'];
		}
		$urlarr['page']					=	"{{page}}";
		
		$pageurl						=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM							=	$this  -> MODEL('page');
		
		$pages							=	$pageM -> pageList('company_order',$where,$pageurl,$_GET['page']);
		
		if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
	        if($_GET['order']){
	            $where['orderby']		=	$_GET['t'].','.$_GET['order'];

	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	        }else{
	            $where['orderby']		=	array('id,desc');
	        }
	        $where['limit']				=	$pages['limit'];
			
	        $rows    					=   $OrderM -> getList($where,array('utype'=>'admin','field'=>'id,uid,order_id,order_price,type,order_state,order_type,order_time,once_id'));
			//处理导出需要的where条件
		    unset($where['limit']);
		    
		    session_start();
		    $_SESSION['orderXls'] = $where;
	    }
        
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_order'));
	}
	function edit_action()
	{
		$id		 =	intval($_GET['id']);
        $OrderM  =	$this -> MODEL('companyorder');
        $row	 =	$OrderM ->getInfo(array('id'=>$id));
		
		$this -> yunset("row",$row);
		$this -> yuntpl(array('admin/admin_company_order_edit'));
	}
	function save_action(){
		$id			=	intval($_POST['id']);
		$OrderM		=	$this -> MODEL('companyorder');
		 
		$mData      =   array(
            'order_price'		=>  $_POST['order_price'],
            'order_remark'		=>  $_POST['order_remark'],
            'is_invoice'		=>  $_POST['is_invoice']
        );
        $return		=	$OrderM  ->  upInfo($id, $mData);
		
		$this -> ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=company_order");
	}
	function setpay_action(){
		$this -> check_token();
		$id			=	intval($_GET['id']);
		$OrderM		=	$this -> MODEL('companyorder');
		$return		=	$OrderM -> setPay($id);
		$this -> layer_msg($return['msg'],$return['errcode'],0,$_SERVER['HTTP_REFERER']);
	}
	function xls_action(){
		$where = $_SESSION['orderXls'] ? $_SESSION['orderXls'] : array('orderby'=>'id');
		$OrderM							=	$this -> MODEL('companyorder');
		if($_POST['uid']){
			$where['id']				=	array('in',pylode(',',$_POST['uid']));
		}
		if($_GET['time_start']!=""||$_GET['time_end']!=""){
			$where['PHPYUNBTWSTART_B']    =   'AND';
			if($_GET['time_start']!=""){
				$where['order_time']		=	array('>=',substr($_POST['time_start'],0,strlen($_POST['time_start'])-3));
			}
			if($_GET['time_end']!=""){
				$where['order_time']		=	array('<',substr($_POST['time_end'],0,strlen($_POST['time_end'])-3));
			}
			$where['PHPYUNBTWEND_B']	=	'';
		}
		
		$rows    						=   $OrderM -> getList($where,array('utype'=>'admin','field'=>'id,uid,order_id,order_price,type,order_state,order_type,order_time,once_id'));
		
		if(!empty($rows)){
			$this -> yunset("list",$rows);
			$this -> MODEL('log') -> addAdminLog("导出支付订单信息");
			header("Content-Type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=order.xls");
			$this -> yuntpl(array('admin/admin_order_xls'));
		}else{
			$this -> ACT_layer_msg("没有可以导出的订单信息！",8,$_SERVER['HTTP_REFERER']);
		}
		
	}
	//删除
	function del_action(){
		$this -> check_token();
		$OrderM		=	$this -> MODEL('companyorder');
		$delID		=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
		$return		=	$OrderM -> del($delID,array('utype'=>'admin'));
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}

	function orderSum_action(){
		$MsgNum=$this->MODEL('msgNum');
		echo $MsgNum->orderSum();
	}
}
?>