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
class look_controller extends user{
	function index_action(){
    
		$LookResumeM       =     $this->MODEL('lookresume');

		include PLUS_PATH."/com.cache.php";

		$uid                    =     $this->uid;

		$where['uid']           =     $uid;

		$where['status']        =     0;

		$where['orderby']       =     array('datetime,desc');

		$urlarr['c']      =     "look";

		$urlarr['page']	  =	    "{{page}}";

		$pageurl					=     Url('member',$urlarr);

		$pageM						=	    $this  -> MODEL('page');

		$pages						=	    $pageM -> pageList('look_resume', $where, $pageurl, $_GET['page']);

		$where['limit']   =     $pages['limit'];

		$looknew          =     $LookResumeM -> getList($where, array('uid' => $this->uid, 'usertype' => $this->usertype));

		$look             =     $looknew['list'];
		$this->yunset("js_def",2);
    
		$this->yunset("look",$look);
    
		$this->public_action();
    
		$this->user_tpl('look');
    
	}
	function del_action(){
      
      $lookresumeM    =  $this->MODEL('lookresume');
    
	    if($_GET['id'] || $_GET['del']){
	        if ($_GET['del']){
	            $id   =  $_GET['del'];
	        }elseif ($_GET['id']){
	            $id   =  $_GET['id'];
	        }
	        
	        $return   =  $lookresumeM -> delInfo(array('id'=>$id,'uid'=>$this->uid,'usertype'=>1));
	        
	        $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	    }
	}
}
?>