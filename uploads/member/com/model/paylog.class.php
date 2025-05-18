<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class paylog_controller extends company{
	function index_action(){
		include(CONFIG_PATH."db.data.php");
		$this	->	yunset("arr_data",$arr_data);
		$this	->	public_action();
		$this	->	company_satic();
		
		
		$comorderM		=	$this		->	MODEL('companyorder');
		$allprice		=	$comorderM	->	getCompanyPaySumPrice(array('com_id'=>$this->uid,'usertype'=>2,'type'=>'1','order_price'=>array('<','0')));
		$this	->	yunset("integral",number_format(str_replace("-","", $allprice)));

		$pageM		=	$this  -> MODEL('page');
		if($_GET['consume']=="ok"){
			$urlarr				=	array("c"=>"paylog","consume"=>"ok","page"=>"{{page}}");
			$pageurl			=	Url('member',$urlarr);
			$where['com_id']	=	$this->uid;
			$where['usertype']	=	$this->usertype;
      $where['orderby']	=	array('id,desc','pay_time,desc');
			
			$pages				=	$pageM -> pageList('company_pay',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);
			
			if($pages['total'] > 0){
				
				$where['limit']		=	$pages['limit'];
				
				$rows				=	$comorderM -> getPayList($where);
				
				$this	->	yunset("rows",$rows);
			}

			$this	->	yunset("ordertype","ok");
			
		}else{
			$urlarr				=	array("c"=>"paylog","page"=>"{{page}}");
			$pageurl			=	Url('member',$urlarr);
			
			if ($_GET['order_state']){
			    
			    $where['order_state'] = (int)$_GET['order_state'];
			}
			$where['uid']		=	$this	->	uid;
			$where['usertype']	=	$this	->	usertype;
			$where['orderby']	=	array('order_time,desc','order_state,asc');

			$pages				=	$pageM -> pageList('company_order',$where,$pageurl,$_GET['page'],$this->config['sy_listnum']);
			
			if($pages['total'] > 0){
				
				$where['limit']		=	$pages['limit'];
				
				$rows				=	$comorderM -> getList($where);
				
				$this				->	yunset("rows",$rows);
			}

		} 
		if($_POST['submit']){
			if(trim($_POST['order_remark'])==""){
				$this	->	ACT_layer_msg("备注不能为空！",8,$_SERVER['HTTP_REFERER']);
			}
			$return	=	$comorderM -> upInfo((int)$_POST['id'],array('order_remark'=>trim($_POST['order_remark'])),$this->uid);
			if($return['errcode']==9){
				$this ->MODEL('log')-> addMemberLog($this -> uid, 2,"修改订单备注",88,2);//会员日志
			}
			$this	->	ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);

		}

		$this	->	com_tpl('paylog');
	}
	
	
	function del_action(){
	    
		if($this->usertype!='2' || $this->uid==''){
		
		    echo '0';die;
		}else{
			
		    $comorderM	=	$this	->	MODEL('companyorder');
			
		    $oid		=	$comorderM	->	getList(array('uid'=>$this->uid,'id'=>(int)$_GET['id'],'order_state'=>'1'));
			
			if(empty($oid[0])){
			    
				echo '0';die;
			}else{
			    
				$comorderM	->	del($oid[0]['id'],array('uid'=>$this -> uid));
				echo '1';die;
			}
		}
	}
	

}
?>