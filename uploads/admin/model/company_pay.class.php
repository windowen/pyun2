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
class company_pay_controller extends adminCommon{
	function set_search(){
		$ad_time		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		$search_list[]	=	array("param"=>"end","name"=>'发布时间',"value"=>$ad_time);
		
		$search_list[]	=	array("param"=>"pay_state","name"=>'消费状态',"value"=>array("0"=>"支付失败","1"=>"等待付款","2"=>"支付成功","3"=>"等待确认"));
		
		$this -> yunset("search_list",$search_list);
	}
	function index_action(){
		$this -> set_search();
		$OrderM							=	$this -> MODEL('companyorder');
		
		$where							=   array();
	    $urlarr							=   $_GET;
	    $keywordStr						=   trim($_GET['keyword']);
		if(!empty($keywordStr)){
			if ($_GET['type']=='1') {
				$where['order_id']		=	array('like', $keywordStr);
			}elseif ($_GET['type']=='3') {
				$where['pay_remark']	=	array('like', $keywordStr);
			}elseif($_GET['type']=='2'){
				$UserinfoM				=	$this -> MODEL('userinfo');
				$member					=	$UserinfoM -> getList(array('username'=>array('like',$keywordStr)),array('field'=>'uid'));
				
				if (is_array($member)){
					foreach ($member as $val){
						$muids[]	=	$val['uid'];
					}
					$where['com_id']	=	array('in', pylode(",",$muids));
				}
			}
			$urlarr['keyword']			=	$keywordStr;
			$urlarr['type']				=	$_GET['type'];
		}
		if(intval($_GET['comid'])){
			$where['com_id']			=	intval($_GET['comid']);
			$where['type']				=	1;
			
			$urlarr['comid']			=	$_GET['comid'];
			
			$ComM						=	$this -> MODEL('company');
			$comname					=	$ComM -> getInfo($_GET['comid'],array('field'=>'name'));
			$this -> yunset("ccname",$comname['name']);
		}
		if($_GET['pay_state']!=""){
			$where['pay_state']			=	$_GET['pay_state'];

			$urlarr['pay_state']		=	$_GET['pay_state'];
		}
		if($_GET['end']){
			if($_GET['end'] == 1){
				$where['order_time']	=	array('>=',strtotime(date("Y-m-d 00:00:00")));
			}else{
				$where['order_time']	=	array('>=',strtotime('-'.intval($_GET['end']).' day'));
			}
			$urlarr['end']				=	$_GET['end'];
		}
		$urlarr['page']					=	"{{page}}";
		
		$pageurl						=	Url($_GET['m'],$urlarr,'admin');
		
		$pageM							=	$this  -> MODEL('page');
		
		$pages							=	$pageM -> pageList('company_pay',$where,$pageurl,$_GET['page'],$limit);

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

	        $rows    					=   $OrderM -> getPayList($where,array('utype'=>'admin'));
	    }
		$this->yunset("get_type", $_GET);
		$this->yunset("rows",$rows);
		$this->yuntpl(array('admin/admin_company_pay'));
	}

	function del_action(){
		$this -> check_token();
		$OrderM		=	$this -> MODEL('companyorder');
		$delID		=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];
		$return		=	$OrderM -> delPay($delID);
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
}
?>