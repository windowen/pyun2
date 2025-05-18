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
class uppic_controller extends user{
	
	function index_action(){
		
		$this -> public_action();
		
		$this -> user_tpl('uppic');
	}
	// 选择图片后上传图片
	function ajaxfileupload_action()
	{
		$UploadM  =  $this->MODEL('upload');
		if($_FILES['image']['tmp_name']){
		    
		   
		    $imginfo  =  getimagesize($_FILES['image']['tmp_name']);
		    
		    if($imginfo[0] < 80 || $imginfo[1] < 100){
		        
		        $res['s_thumb']  =  '头像尺寸比例太小,最小尺寸：宽80/高100';
		        
		    }else{
		        $upArr  =  array(
		            'file'  	=>  $_FILES['image'],
		            'dir'   	=>  'user',
		            'type'  	=>  'logo',
		            'watermark'	=> 0,
		        );
		        $return	  =  $UploadM -> newUpload($upArr);
		        
		        if(!empty($return['msg'])){
		            
		            $res['s_thumb']  =  $return['msg'];
		            
		        }else {
		            
		            $res['url']  =  checkpic($return['picurl']);
		        }
		    }
		}else{
			
			$res['s_thumb']  =  '请选择上传图片';
		}
		echo json_encode($res);die;
	}
	
	function savethumb_action(){
	    
	    $upload_path 	= 	'data/upload/user/';
	    
	    if(stripos(trim($_POST['img1']),$upload_path)===false ){
	        
	        $this->ACT_layer_msg('非法操作！',8,$_SERVER['HTTP_REFERER']);
	        
	    }
		
	    $uploadM  =  $this->MODEL('upload');
		
		$thumb    =  $uploadM -> thumb('user');
		
		
		$resumeM  =	 $this -> MODEL('resume');
     
		$return   =	 $resumeM -> upPhoto(array('uid'=>$this->uid),array('thumb'=>$thumb,'utype'=>'user'));
		
		$this->layer_msg($return['msg'],$return['errcode']);
	}
}
?>