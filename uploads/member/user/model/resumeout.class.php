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
class resumeout_controller extends user{
    function index_action(){
		$resumeM				=	$this->MODEL('resume');
		$where['uid']			=  $this -> uid;
		$urlarr['c']			=	$_GET['c'];
		$urlarr['page']			=	'{{page}}';
	    $pageurl				=	Url('member',$urlarr);

	    $pageM					=	$this   ->  MODEL('page');
	    $pages					=	$pageM  ->  pageList('resumeout',$where,$pageurl,$_GET['page']);
	    
	    if($pages['total'] > 0){
	        $where['orderby']	=	'id';
	        $where['limit']		=	$pages['limit'];
	        
	        $List   =  $resumeM  ->  getResumeOutList($where);
	    }
		
        $rows		=	$resumeM -> getSimpleList(array('uid'=>$this->uid),array('field'=>"id,name"));
       
        $this->public_action();
        $this->yunset('rows',$rows);
        $this->yunset('out',$List);
        $this->user_tpl('resumeout');
    }
    
    function send_action() {
        $resumeM	=	$this -> MODEL('resume');
        $_POST		=	$this->post_trim($_POST);
		
        $data		=	array(
          'resume'		=>	$_POST['resume'],
          'email'		=>	$_POST['email'],
          'comname'		=>	$_POST['comname'],
          'jobname'		=>	$_POST['jobname'],
          'resumename'	=>	$_POST['resumename']
        );
        
        $return		=	$resumeM  ->  addResumeOut($data);
        $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER']);
    }
    function del_action(){
       $resumeM	=	$this->MODEL('resume');
        if($_GET['id']){
          $return		=	$resumeM  ->  delResumeOut($_GET['id'],array('uid'=>$this->uid,'usertype'=>$this->usertype));
          $this -> layer_msg($return['msg'],$return['errcode']);
        }
    }
}
?>