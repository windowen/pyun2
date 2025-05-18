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
class commsg_controller extends user{
	//企业留言
	function index_action(){
		
		$this->public_action();
		
		$msgM  =  $this -> MODEL('msg');
		
		$where['uid']	=  $this -> uid;
	    
		$urlarr['c']	=	$_GET['c'];
		
		$urlarr['page']	=	'{{page}}';
	   
	    $pageurl		=	Url('member',$urlarr);

	    $pageM			=	$this   ->  MODEL('page');
	   
 	    $pages			=	$pageM  ->  pageList('msg',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        
			$where['orderby']		=	'id';
	        
			$where['limit']			=	$pages['limit'];
	        
	        $List   =  $msgM  ->  getList($where);
			
			$this -> yunset('rows',$List['list']);
	    
		}
		

		
		$msgM -> upInfo(array('uid'=>$this->uid,'user_remind_status'=>0),array('user_remind_status'=>1));
		
		$this->user_tpl('commsg');
	
	}
	
	function del_action(){
		$id   		=  	$_GET['id'];
	    
		$msgM		=  	$this->MODEL('msg');
		
		$data		=	array(
			
			'uid'		=>	$this->uid,
			
			'usertype'	=>	$this->usertype
		);
	    
		$return		=  	$msgM -> delInfo($id,$data);
	    
		$this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	
	}
}
?>