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
class admin_appeal_controller extends adminCommon{
    function index_action(){
		
		$memberM						=		$this->MODEL('userinfo');
		
		$adminM							=		$this->MODEL('admin');
		
		$state							=		array('1'=>'未完成','2'=>'已完成');
		
        $search_list[]					=		array("param"=>"appealstate","name"=>'申诉情况',"value"=>$state);
       
		$where['appeal']				=		array('<>','');
		
        if(trim($_GET['keyword'])){
			
			$where['username']			=		array('like',trim($_GET['keyword']));
			
            $urlarr['keyword']			=		$_GET['keyword'];
			
        }
        if($_GET['appealstate']){
			
			$where['appealstate']		=		$_GET['appealstate']=='1'	?	1	:	2;
           
            $urlarr['appealstate']		=		$_GET['appealstate'];
        }
		
	    $urlarr['page']	=	'{{page}}';
	    
	    $pageurl		=	Url($_GET['m'], $urlarr, 'admin');
	    
	    $pageM			=	$this  -> MODEL('page');
	    
	    $pages			=	$pageM -> pageList('member', $where, $pageurl, $_GET['page']);
	    
	    if($pages['total'] > 0){
	        
	        if($_GET['order']){
	            
	            $where['orderby']		=		$_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=		$_GET['order'];
	            $urlarr['t']			=		$_GET['t'];
					
	        }else{	
					
	            $where['orderby']		=		array('appealstate,asc','appealtime,desc');
					
	        }	
				
	        $where['limit']				=		$pages['limit'];
				
	        $List    					=   	$memberM -> getList($where,array('utype'=>'admin','field'=>'uid,username,appeal,appealtime,appealstate,moblie,email'));
	        
	        $this -> yunset(array('rows'=>$List));
	        
	    }
		
		$power							=		$adminM->getPower(array('uid'=>$_SESSION["auid"]));
		
		if(in_array('141',$power['power'])){
			$this->yunset("email_promiss", '1');
			$this->yunset("moblie_promiss", '1');
		} 
		$this->yunset("search_list",$search_list);
        $this->yuntpl(array('admin/admin_appeal'));
    }
	
	
    function info_action(){
		$memberM						=		$this->MODEL('userinfo');
		
		$info 							= 		$memberM->getInfo(array('uid'=>$_GET['id']));
		
		$user 							= 		$memberM->getUserInfo(array('uid'=>$info['uid']),array('usertype'=>$info['usertype']));
		
		$this->yunset('user',$user);
		$this->yunset('info',$info);
		$this->yuntpl(array('admin/admin_appeal_info'));
	}
    function success_action(){
        if ($_GET['id']){
            $memberM					=		$this->MODEL('userinfo');
			
            $result						=		$memberM->upInfo(array('uid'=>intval($_GET['id'])),array('appealstate'=>'2'));
			
            isset($result)?$this->layer_msg('申诉确认成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('确认失败！',8,0,$_SERVER['HTTP_REFERER']);
        }
    }
    //删除
    function del_action(){
        $memberM						=		$this->MODEL('userinfo');
		
        if(is_array($_POST['del'])){
            $delid						=		$_POST['del'];
			
			$layer_type					=		1;
        }else{
            $this->check_token();
			$delid[]					=		(int)$_GET['id'];
			$layer_type					=		0;
        }
		
		if(!$delid){
            $this->layer_msg('请选择要删除的内容！',8,$layer_type,$_SERVER['HTTP_REFERER']);
        }
		
		$result 						=		$memberM->upInfo(array('uid'=>array('in',pylode(',',$delid))),array('appeal'=>'','appealtime'=>'','appealstate'=>'1'));
        
        isset($result)?$this->layer_msg('申诉(ID:'.$delid.')删除成功！',9,$layer_type,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,$layer_type,$_SERVER['HTTP_REFERER']);
    }
}