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
class favorite_controller extends user{
	//收藏职位
	function index_action(){
		$JobM			  =   $this -> MODEL('job');
		$statisM		=   $this -> MODEL('statis');
		$this -> public_action();
		$this -> member_satic();
	
		
		$where['uid']	=  $this -> uid;

		$urlarr['c']	=	$_GET['c'];
        $urlarr['page']	=	'{{page}}';
        
        $pageurl		=	Url('member',$urlarr);

        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('fav_job',$where,$pageurl,$_GET['page']);
		
        if($pages['total'] > 0){
			
            if($_GET['order']){
                
                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
            }else{
                
                $where['orderby']		=	array('id,desc');
            }
            $where['limit']				=	$pages['limit'];
            
            $rows    	=   $JobM -> getFavJobList($where,array('datatype'=>'moreinfo'));
        }
		
		$num			=	$JobM -> getFavJobNum(array('uid'=>$this->uid));
		$statisM -> upInfo(array('fav_jobnum'=>$num),array('uid'=>$this->uid,'usertype'=>$this->usertype));
		
		
		$this -> yunset("rows",$rows);
		$this -> user_tpl('favorite');
	}
	function del_action(){		
  	$JobM   =   $this -> MODEL('job');
		if($_GET['del']||$_GET['id']){
		
			if(is_array($_GET['del'])){
				$id =   $_GET['del'];
			}else{
				$id =   intval($_GET['id']);
			}
			$arr    =   $JobM -> delFavJob($id,array('utype'=>'user','uid'=>$this->uid,'usertype'=>$this->usertype));
			$this ->  layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);			
		}
	}
}
?>