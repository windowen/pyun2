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
class admin_comset_controller extends adminCommon{	 

	function index_action(){
    
        $rating               =     $this->MODEL('rating');
        
        $rwhere['category']   =     1;
        
        $rwhere['orderby']	  =	    array('sort,desc');
        
        $qy_rows              =     $rating -> getList($rwhere);
    
		$this->yunset("qy_rows",$qy_rows);
		
		$this->yunset("com_link_no",@explode(',',$this->config['com_link_no']));
    
		$this->yuntpl(array('admin/admin_com_config'));
    
	}
	function save_action(){
    	
        $configM    =   $this->   MODEL("config");
    
 		if($_POST["config"]){
      
            unset($_POST["config"]);
			unset($_POST['pytoken']);
			if(isset($_POST['com_single_can'])){
				$_POST['com_single_can'] = $_POST['com_single_can'] ? @implode(',', $_POST['com_single_can']) : '';
			}
			
			
			$configM -> setConfig($_POST);
       
            $this->web_config();
      
            $this->ACT_layer_msg("配置修改成功！",9,1,2,1);
		}
	}
	function logo_action(){
    
		if($_POST['submit']){
      
			$this->web_config();
      
			$this->ACT_layer_msg("会员头像配置设置成功！",9,$_SERVER['HTTP_REFERER'],2,1);
		}
		$this->yuntpl(array('admin/admin_comlogo'));
	}
	function set_action(){
		
		$this->yuntpl(array('admin/admin_integral_com'));
	}
	function rating_action(){
    
        $rating               =     $this->MODEL('rating');
    
        $rwhere['category']   =     1;
    
        $rwhere['orderby']	  =	    array('sort,desc');
    
        $qy_rows              =     $rating -> getList($rwhere);
    
		$this->yunset("qy_rows",$qy_rows);

		$this->yunset("com_look",@explode(',',$this->config['com_look']));
    
		$this->yuntpl(array('admin/admin_rating_config'));
	}	
	
	function comspend_action(){
	    
		$configM 	=	$this -> MODEL('config');
		
		$row		=	$configM -> getInfo(array('name'=>'integral_down_resume_dayprice'));
		
		$marr		=	explode(':',$row['config']);
		
		foreach($marr as $v){
			
			$narr	=	explode('_',$v);
			
			$data[]	=	array('days'=>$narr[0],'price'=>$narr[1]);
		}
		
		$com_single_can = @explode(',',$this->config['com_single_can']);

		$this->yunset('data',$data);

		$this->yunset('com_single_can',$com_single_can);
		
		$this->yuntpl(array('admin/admin_integral_comspend'));
    
	}
}
?>