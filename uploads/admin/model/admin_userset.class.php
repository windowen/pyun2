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
class admin_userset_controller extends adminCommon{
    /**
     * 会员-个人-个人设置: 个人设置
     */
	function index_action(){
	    $this -> yunset($this->MODEL('cache')-> GetCache(array('user')));
		$this -> yuntpl(array('admin/admin_user_config'));
	}
	function save_action(){
		if($_POST['config']){
		    $post  =  array(
		        'user_number'                =>  $_POST['user_number'],
		        'user_finder'                =>  $_POST['user_finder'],
		        'user_work_regiser'          =>  $_POST['user_work_regiser'],
		        'user_edu_regiser'           =>  $_POST['user_edu_regiser'],
		        'user_project_regiser'       =>  $_POST['user_project_regiser'],
		        'user_integrity_eighty'      =>  $_POST['user_integrity_eighty'],
		        'user_state'                 =>  $_POST['user_state'],
		        'resume_status'              =>  $_POST['resume_status'],
		        'user_status'                =>  $_POST['user_status'],
				'resume_open_check'          =>  $_POST['resume_open_check'],
		        'user_resume_status'         =>  $_POST['user_resume_status'],
		        'user_height_resume'         =>  $_POST['user_height_resume'],
		        'user_idcard_status'         =>  $_POST['user_idcard_status'],
		        'com_resume_partapply'       =>  $_POST['com_resume_partapply'],
		        'resume_sx'                  =>  $_POST['resume_sx'],
		        'user_photo_status'          =>  $_POST['user_photo_status'],
		        'rshow_photo_status'         =>  $_POST['rshow_photo_status'],
		        'user_name'                  =>  $_POST['user_name'],
		        'user_pic'                   =>  $_POST['user_pic'],
		        'user_sqintegrity'           =>  $_POST['user_sqintegrity'],
		        'resume_create_edu'          =>  $_POST['resume_create_edu'],
		        'resume_create_exp'          =>  $_POST['resume_create_exp'],
		        'resume_create_project'      =>  $_POST['resume_create_project'],
		        'educreate'                  =>  $_POST['educreate'],
		        'expcreate'                  =>  $_POST['expcreate']
		    );
		    $configM  =  $this -> MODEL('config');
		    
		    $configM -> setConfig($post);
		    
		    $this -> web_config();
		    
	        $this -> ACT_layer_msg('配置修改成功！',9,1,2,1);
		}
	}
	/**
	 * 会员-个人-个人设置: 头像设置
	 */
	function logo_action(){
		$this -> yuntpl(array('admin/admin_userlogo'));
	}
	/**
	 * 会员-个人-个人设置: 保存头像
	 */
	function saveLogo_action(){
	    if($_POST['submit']){
	        
	       
	        
	        $this -> web_config();
	        
	        $this -> ACT_layer_msg('会员头像配置设置成功！',9,$_SERVER['HTTP_REFERER'],2,1);
	    }
	}
 
	/**
	 * 会员-个人-个人设置: 积分设置
	 */
	function saveSet_action(){
	    
	    if($_POST['config']){
	        
	        $post  =  array(
	            'integral_add_resume'       =>  $_POST['integral_add_resume'],
	            'integral_identity'         =>  $_POST['integral_identity']
	        );
	        $configM  =  $this -> MODEL('config');
	        
	        $configM -> setConfig($post);
	        
	        $this -> web_config();
	        
	        $this -> ACT_layer_msg('配置修改成功！',9,1,2,1);
	    }
	}
	/**
	 * 激活邮件
	 */
	function jihuo_action(){
	    
	    $userinfoM  =  $this->MODEL('userinfo');
	    
	    $nid  =  $userinfoM -> upInfo(array('usertype'=>1),array('email_status'=>1));
	    
		echo $nid;
	}
	/**
	 * 会员-个人-个人设置: 消费设置
	 */
	function userspend_action(){
		$this -> yuntpl(array('admin/admin_integral_spend'));
	}
	/**
	 * 会员-个人-个人设置: 消费设置保存
	 */
	function saveSpend_action(){
	    
	    if($_POST['config']){
	        
	        $post  =  array(
	            'integral_resume_top_type'  =>  $_POST['integral_resume_top_type'],
	            'integral_resume_top'       =>  $_POST['integral_resume_top']
	        );
	        $configM  =  $this -> MODEL('config');
	        
	        $configM -> setConfig($post);
	        
	        $this -> web_config();
	        
	        $this -> ACT_layer_msg('配置修改成功！',9,1,2,1);
	    }
	}
}
?>