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
class admin_user_pic_controller extends adminCommon{
	function set_search()
    {
        $search_list[]  = array(
            "param"     => "status",
            "name"      => '审核状态',
            "value"     => array(
                
                '0' => '已审核',
                '1' => '未审核'
            )
        );

        $this->yunset("search_list", $search_list);
    }
    /**
     * 会员-个人-图片管理：个人头像（列表）
     */
	function index_action(){

	    $this			->	set_search();

	    $where['photo']  =  array('<>','');
	    
	    if($_GET['keyword']){
	        
	        $keytype  =   intval($_GET['type']);
	        
	        $keyword  =   trim($_GET['keyword']);
	        
	        if ($keytype == 1){
	            
	            $where['name']      =  array('like',$keyword);
	            
	        }elseif ($keytype == 2){
	            
	            $where['uid']       =  array('like',$keyword);
	        }
	        $urlarr['keytype']	    =  $keytype;
	        
	        $urlarr['keyword']	    =  $keyword;
	    }
	    if(isset($_GET['status'])){
	        
	        $status                 =   intval($_GET['status']);
	        
	        $where['photo_status']  =  $status;
	        
	        $urlarr['status']       =  $status;
	    }
	    $urlarr['page']	=	'{{page}}';
	    
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('resume',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
	        $where['orderby']		=	array('photo_status,desc','uid,desc');
	        $where['limit']			=	$pages['limit'];
	        
	        $resumeM  =  $this->MODEL('resume');
	        
	        $List  =  $resumeM -> getResumeList($where,array('utype'=>'admin','field'=>'`uid`,`name`,`sex`,`photo`,`photo_status`'));
	        
	        $this -> yunset('rows',$List);
	    }
		
		
		$this->yuntpl(array('admin/admin_user_pic'));
	}
	/**
	 * 会员-个人-图片管理：个人头像（获取审核说明）
	 */
	function getStatusBody_action(){
	    
	    $resumeM  =  $this->MODEL('resume');
	    
	    $return   =  $resumeM -> getInfo(array('uid'=>intval($_GET['uid']),'rfield'=>'photo_statusbody'));
	    
	    echo trim($return['resume']['photo_statusbody']);die;
	}
	/**
	 * 会员-个人-图片管理：个人头像（审核）
	 */
	function status_action(){
	    
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $post  =  array(
	        'photo_status'    =>  intval($_POST['status']),
	        'photo_statusbody'  =>  $_POST['statusbody']
	    );
	    
	    $return  =  $resumeM -> statusPhoto($_POST['uid'],array('post'=>$post));
	    
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-图片管理：作品案例（列表）
	 */
	function show_action(){
	    $this			->	set_search();
	    if($_GET['keyword']){
	        
	        $keytype  =   intval($_GET['type']);
	        
	        $keyword  =   trim($_GET['keyword']);
	        
	        if ($keytype == 1){
	            //姓名
	            $resumeM                  =  $this -> MODEL('resume');
	            
	            $resume                   =  $resumeM -> getResumeList(array('name'=>array('like',$keyword)),array('field'=>'uid'));
	            
	            if ($resume){
	                
	                foreach($resume as $v){
	                    
	                    $uids[]           =  $v['uid'];
	                }
	                $where['uid']         =  array('in',pylode(',', $uids));
	            }
	            
	        }elseif ($keytype == 2){
	            //用户ID
	            $where['uid']       =  array('like',$keyword);
	        }elseif ($keytype == 3){
	            //作品标题
	            $where['title']     =  array('like',$keyword);
	        }
	        $urlarr['keytype']	    =  $keytype;
	        
	        $urlarr['keyword']	    =  $keyword;
	    }

	    if(isset($_GET['status'])){
	        
	        $status                 =   intval($_GET['status']);
	        
	        $where['status']  		=  $status;
	        
	        $urlarr['status']       =  $status;
	    }

	    $urlarr['c']	=	'show';
	    $urlarr['page']	=	'{{page}}';
	    
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('resume_show',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
	        $where['orderby']		=	array('status,desc','id,desc');
	        $where['limit']			=	$pages['limit'];
	        
	        $resumeM  =  $this->MODEL('resume');
	        
	        $List  =  $resumeM -> getResumeShowList($where,array('utype'=>'admin'));
	        
	        $this -> yunset('rows',$List);
	    }
		$this->yuntpl(array('admin/admin_user_picshow'));
	}
	/**
	 * 会员-个人-图片管理：作品（获取审核说明）
	 */
	function getShowStatusBody_action(){
	    
	    $resumeM  =  $this->MODEL('resume');
	    
	    $return   =  $resumeM->getResumeShowInfo(array('id'=>intval($_GET['id'])),array('field'=>'`statusbody`'));
	    
	    echo trim($return['statusbody']);die;
	}
	/**
	 * 会员-个人-图片管理：作品（审核）
	 */
	function showStatus_action(){
	    
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $post  	  =  array(
	        'status'    	=>  intval($_POST['status']),
	        'statusbody'  	=>  $_POST['statusbody']
	    );
	    
	    $return   =  $resumeM -> statusShow($_POST['sid'],array('post'=>$post));
	    
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-图片管理：个人头像（修改）
	 */
	function savePhoto_action(){
	    
	    $file     =  $_FILES['file'];
	    
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $id       =  intval($_POST['id']);
	    
	    $return   =  $resumeM -> upPhoto(array('uid'=>$id),array('photo'=>$file));
	    
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-图片管理：作品案例（修改）
	 */
	function saveShow_action(){
		
	    $resumeM	=  $this -> MODEL('resume');
	    $post		=	array(
			'title'		=>	$_POST['title'],
			'sort'		=>	$_POST['sort'],
		);
		$data		=	array(
			'post'		=>	$post,
			'id'		=>	intval($_POST['id']),
			'file'		=>	$_FILES['file']
		);
		$return		=	$resumeM->addResumeShow($data);
		
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-图片管理：个人头像（删除）
	 */
	function delPhoto_action(){
	    
	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $id          =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $id          =  $_POST['del'];
	    }
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> delPhoto($id);
	    
	    $this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 会员-个人-图片管理：作品案例（删除）
	 */
	function delShow_action(){
	    
	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $id          =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $id          =  $_POST['del'];
	    }
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> delShow($id,array('utype'=>'admin'));
	    
	    $this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
}
?>