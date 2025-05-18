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
class resumetpl_controller extends user{
	function index_action() {

    $TplM                 =     $this->MODEL('tpl');
    
    $StatisM              =     $this->MODEL('statis');
    
    $uid                  =     $this->uid;
    
		$where['status']	    =	    1;
   
    
		$where['orderby']	    =	    array('id,desc');
		
		$rows                 =     $TplM -> getResumetplList($where);
		
    $statis               =     $StatisM->getInfo($uid,array('usertype'=>1,'field'=>'`tpl`,`paytpls`'));
		
		if($statis['paytpls']){
      
			$paytpls=@explode(',',$statis['paytpls']);
      
			$this->yunset("paytpls",$paytpls);
      
		}  
		$this->yunset("statis",$statis);
    
		$this->yunset("rows",$rows);
    
		$this->public_action();
    
		$this->user_tpl('resumetpl'); 
	}
	function pay_action(){
			
		$tplM	=	$this -> MODEL('tpl');
    
		$id		=	intval($_GET['id']);
		
		$return	=	$tplM -> payResumetpl(array('id'=>$id,'uid'=>$this->uid));
		
		$this -> layer_msg($return['msg'],$return['errcode'],0,"index.php?c=resumetpl");
    
	}
	function settpl_action(){
    
		$tplM	=	$this -> MODEL('tpl');
    
		$id		=	intval($_GET['id']);
    
		$return	=	$tplM -> setResumetpl(array('id'=>$id,'uid'=>$this->uid));
    
		$this -> layer_msg($return['msg'],$return['errcode'],0,"index.php?c=resumetpl");
    
	}
}
?>