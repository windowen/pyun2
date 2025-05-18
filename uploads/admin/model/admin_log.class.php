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
class admin_log_controller extends adminCommon{
	
	function index_action(){
      $logM							=			$this->MODEL('log');

      if($_GET['end']){
        if($_GET['end']=='1'){
          $where['ctime'][]		=			array('>=',strtotime(date("Y-m-d 00:00:00")));
        }else{
          $where['ctime'][]		=			array('>=',strtotime('-'.(int)$_GET['end'].'day'));
        }
        $urlarr['end']				=			$_GET['end'];
      }
      if($_GET['time']){
        $time						=			@explode('~',$_GET['time']);
        $where['ctime'][]			=			array('>=',strtotime(date("Y-m-d 00:00:00")));
        $where['ctime'][]			=			array('<=',strtotime($time[1]."23:59:59"));
        $urlarr['time']				=			$_GET['time'];
      }
      if(trim($_GET['ukeyword'])){
        
        $where['username'] 			= 			array('like',trim($_GET['ukeyword']));
        
        $urlarr['ukeyword']			=			$_GET['ukeyword'];
      }
      if(trim($_GET['keyword'])){
        
        $where['content']		    =			array('like',trim($_GET['keyword']));
        
        $urlarr['keyword']			=			$_GET['keyword'];
      }		
      $urlarr['c']     				=			$_GET['c'];
	    $urlarr['page']	 				=			'{{page}}';
	    $pageurl		 				=			Url($_GET['m'],$urlarr,'admin');
	    $pageM			 				=			$this  -> MODEL('page');
	    $pages			 				=			$pageM -> pageList('admin_log',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
			$where['orderby']  			=  			'id,desc';
			
	        $where['limit']		   		=  			$pages['limit'];
	        
	        $List  						=  			$logM -> getAdminLogList($where);
	        $this -> yunset(array('list'=>$List));
	    }
		$this->yuntpl(array('admin/admin_log'));
	}

	function del_action(){
		$this->check_token();
		$logM							=			$this->MODEL('log');
		
		if($_GET["id"]=='all'){
			$where['id']				=			array('>',0);	
			
			$logM->delAdminlog($where);
			
			$this->layer_msg( "已清空管理员日志！",9,0,$_SERVER['HTTP_REFERER']);
		}else{
			if($_GET["del"]){
				$where['id']			=			array('in',pylode(',',$_GET["del"]));
			}elseif(isset($_GET["id"])){
				$where['id']			=			$_GET["id"];
			}
			$return 					=			$logM->delAdminlog($where);
			
			$this->layer_msg( $return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
			
		}
	}

}
?>