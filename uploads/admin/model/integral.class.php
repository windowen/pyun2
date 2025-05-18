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
class integral_controller extends adminCommon{
	function index_action()
	{
		$this->yuntpl(array('admin/admin_integral_config'));
	}
	function user_action()
	{
		$this->yuntpl(array('admin/admin_integral_user'));
	}
	function com_action()
	{
		$this->yuntpl(array('admin/admin_integral_com'));
	}
	function lt_action()
	{
		$this->yuntpl(array('admin/admin_integral_lt'));
	}

	function save_action(){

		$configM    =   $this->   MODEL("config");
    
 		if($_POST["config"]){
      
			unset($_POST["config"]);
      
			foreach($_POST as $key=>$v){
         
				$where['name']      =  $key;
         
				$config = $configM   ->getNum($where);

				if($config==false){
           
					$data      =  array(
                
						'name'    =>  $key,
					
						'config'  =>  $v,
					);
          
					$configM  ->  addInfo($data);
           
				}else{
           
					$where['name']  =   $key;
				  
					$data           =   array(
                
						'config'		=>  $v
                
					);
           
					$configM  ->  upInfo($where,$data);
  
				}
			}
			
			$this->cache_action();
			$this->web_config();
			$this->ACT_layer_msg($this->config['integral_pricename']."配置修改成功！",9,1,2,1);
		}
	}
	function class_action(){
		$integralM		=	$this->MODEL('integral');
		
		$list			=	$integralM->getIntClass(array('id'=>array('>',0) , 'orderby'=>'integral,asc' ));
		
		$this->yunset("list",$list);
		$this->yuntpl(array('admin/admin_integral_class'));
	}
	//添加
	function add_action(){
		$integralM		=	$this->MODEL('integral');
		
	    $_POST			=	$this->post_trim($_POST);
		
		
		$list			=	$integralM->getIntClass(array('integral'=>(int)$_POST['integral']));
		
	    if(empty($list)){ 
		
			$data		=	array(
			
				'integral'	=>	(int)$_POST['integral'],
				
				'discount'	=>	$_POST['discount'],
				
				'state'		=>	(int)$_POST['state']
			
			);
			
			$add		=	$integralM->addIntClass($data);   
			
	        $msg		=	$add	?	2	:	3;
			
			$this->cache_action();
			 
	        $this->MODEL('log')->addAdminLog($this->config['integral_pricename']."类型(ID:".$add.")添加成功！");
	    }else{
			
	        $msg		=	1;
			
	    }
		
	    echo $msg;die;
	}

	//删除
	function del_action()
	{
		$integralM			=	$this->MODEL('integral');
		
		
		
		if((int)$_GET['delid'])
		{
			$this->check_token();
			
			$del			=	$_GET['delid'];
			
			$ids			=	$_GET['delid'];
			
			$type			=	2;
			
		}
		if($_POST['del'])//批量删除
		{
			$del			=	pylode(",",$_POST['del']);
			
			$ids			=	$_POST['del'];
			
			$type			=	1;
		}

		$id					=	$integralM->delIntClass($ids);
		
		if($id)
		{
			$this->layer_msg('删除成功！',9,$type,$_SERVER['HTTP_REFERER']);
		}else{
			$this->layer_msg("删除失败！",8,$type,$_SERVER['HTTP_REFERER']);
		}
		
		$this->cache_action();
		
		$this->yuntpl(array('admin/admin_integral_class'));
	}
	
	function cache_action()
	{
		include(LIB_PATH."cache.class.php");
		$cacheclass		= 	new cache(PLUS_PATH,$this->obj);
		$makecache		=	$cacheclass->integralclass_cache("integralclass.cache.php");
	}
	
	function ajax_action(){
		$integralM		=	$this->MODEL('integral');
		
		if((int)$_GET['id']&&$_GET['type']){
			
			$state		=	(int)$_GET['rec']==1 ? 1:2;
			
			$nid		=	$integralM->upIntClass(array('id' =>(int)$_GET['id']) , array($_GET['type']=>$state));
			
			$this->MODEL('log')->addAdminLog($this->config['integral_pricename']."类型(ID:".$_GET['id'].")修改状态！");
			
		}
		
		if($_POST['integral']){
			
			$integralclass		=	$integralM->getIntClass(array('integral'=>(int)$_POST['integral'] , 'id'=>array('<>',$_POST['id']) ));
		
			if($integralclass){
				
				echo 2;die;
				
			}
			
			$nid				=	$integralM->upIntClass(array('id' =>(int)$_POST['id']) , array('integral'=>$_POST['integral']));
			
			$this->MODEL('log')->addAdminLog($this->config['integral_pricename']."充值类型(ID:".$_POST['id'].")修改数量！");
		}
		
		if($_POST['discount']>=0){

			$nid				=	$integralM->upIntClass(array('id' =>(int)$_POST['id']) , array('discount'=>$_POST['discount']));
			
			$this->MODEL('log')->addAdminLog($this->config['integral_pricename']."充值类型(ID:".$_POST['id'].")修改折扣！");
		}
		
		$this->cache_action();
		
		echo $nid?1:0;
	}
}

?>