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
class admin_userlog_controller extends adminCommon{
    /**
     * 会员-个人-简历记录管理:简历下载记录（列表）
     */
	function index_action(){
	    //从企业列表更多过来的
	    if ($_GET['comid']){
	        
	        $comid                     =  intval($_GET['comid']);
	        
	        $where['comid']            =  $comid;
	        $where['usertype']         =  2;
	        $urlarr['comid']           =  $comid;
	    }
	    if($_GET['keyword']){
	        
	        $keytype  =   intval($_GET['type']);
	        
	        $keyword  =   trim($_GET['keyword']);
	        
	        if ($keytype == 1){
	            //搜企业/用户名
	            $userinfoM             =  $this -> MODEL('userinfo');
	            
	            $cw['username']        =  array('like',$keyword);
	            $cw['PHPYUNBTWSTART']  =  '';
	            $cw['usertype'][]      =  array('=',2);
	            $cw['usertype'][]      =  array('=',3,'OR');
	            $cw['PHPYUNBTWEND']    =  '';
	            
	            $mcom                  =  $userinfoM -> getList($cw,array('field'=>'uid'));
	            
	            if ($mcom){
	                $uids              =  array();
	                
	                foreach($mcom as $v){
	                    
	                    $uids[]        =  $v['uid'];
	                }
	                $where['comid']    =  array('in',pylode(',', $uids));
	            }
	            
	        }elseif ($keytype == 2){
	            //公司
	            $companyM              =  $this -> MODEL('company');
	            $com                   =  $companyM -> getList(array('name'=>array('like',$keyword)),array('field'=>'uid'));
	            $uids                  =  array();
	            if ($com){
	                foreach($com['list'] as $v){
	                    
	                    $uids[]        =  $v['uid'];
	                }
	            }
	            
	            if (!empty($uids)){
	                
	                $where['comid']    =  array('in',pylode(',', $uids));
	            }
	        }elseif ($keytype == 3){
	            //个人用户名
	            $userinfoM             =  $this -> MODEL('userinfo');
	            
	            $muser                 =  $userinfoM -> getList(array('username'=>array('like',$keyword),'usertype'=>1),array('field'=>'uid'));
	            
	            if ($muser){
	                $uids              =  array();
	                
	                foreach($muser as $v){
	                    
	                    $uids[]        =  $v['uid'];
	                }
	                $where['uid']      =  array('in',pylode(',', $uids));
	            }
	            
	        }elseif ($keytype == 4){
	            //简历名称
	            $resumeM               =  $this -> MODEL('resume');
	            
	            $resume                =  $resumeM -> getSimpleList(array('name'=>array('like',$keyword)),array('field'=>'id'));
	            
	            if ($resume){
	                $eids              =  array();
	                
	                foreach($resume as $v){
	                    
	                    $eids[]        =  $v['id'];
	                }
	                $where['eid']      =  array('in',pylode(',', $eids));
	            }
	            
	        }
	        $urlarr['keytype']	       =  $keytype;
	        
	        $urlarr['keyword']	       =  $keyword;
	    }
	    if($_GET['time']){
	        
	        $time                      =  intval($_GET['time']);
	        
	        if($time == 1){
	            
	            $where['downtime']	   =  array('>',strtotime('today'));
	            
	        }else{
	            
	            $where['downtime']	   =  array('>',strtotime('-'.$time.' day'));
	        }
	        
	        $urlarr['time']            =  $time;
	    }
	    $urlarr['page']	=	'{{page}}';
	    
	    $pageurl		=	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    $pages			=	$pageM -> pageList('down_resume',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
	        if($_GET['order']){
	            
	            $where['orderby']	    =	$_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	        }else{
	            $where['orderby']		=	'id';
	        }
	        $where['limit']				=	$pages['limit'];
	        
	        $downM  =  $this->MODEL('downresume');
	        
	        $List   =  $downM -> getList($where,array('utype'=>'admin'));
	        
	        $this -> yunset(array('list'=>$List['list'],'ccname'=>$List['com']['name']));
	    }
		
		$lotime    =  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$search[]  =  array('param'=>'time','name'=>'下载时间','value'=>$lotime);
		
		$this->yunset('search_list',$search);
		
		$this->yuntpl(array('admin/down'));
	}
	/**
	 * 会员-个人-简历记录管理:简历下载记录（删除）
	 */
	function deldown_action(){
	    
	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $id   =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $id   =  $_POST['del'];
	    }
	    $downM    =  $this->MODEL('downresume');
	    
	    $return   =  $downM -> delInfo($id,array('utype'=>'admin'));
	    
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 会员-个人-简历记录管理:简历推送记录（列表）
	 */
	function trust_action(){
	    //从企业列表更多过来的
	    if ($_GET['comid']){
	        
	        $comid                     =  intval($_GET['comid']);
	        
	        $where['comid']            =  $comid;
	        
	        $urlarr['comid']           =  $comid;
	    }
	    if($_GET['keyword']){
	        
	        $keytype  =   intval($_GET['type']);
	        
	        $keyword  =   trim($_GET['keyword']);
	        
	        if ($keytype == 1){
	            //简历名称
	            $resumeM             =  $this -> MODEL('resume');
	            
	            $expect              =  $resumeM -> getSimpleList(array('name'=>array('like',$keyword)),array('field'=>'id'));
	            
	            if ($expect){
	                
	                foreach($expect as $v){
	                    
	                    $ids[]       =  $v['id'];
	                }
	                $where['eid']    =  array('in',pylode(',', $ids));
	            }
	            
	        }elseif ($keytype == 2){
	            //企业名称
	            $companyM            =  $this -> MODEL('company');
	            $com                 =  $companyM -> getList(array('name'=>array('like',$keyword)),array('field'=>'uid'));
	            
	            if ($com){
	                foreach($com['list'] as $v){
	                    
	                    $uids[]      =  $v['uid'];
	                }
	                $where['comid']  =  array('in',pylode(',', $uids));
	            }
	        }elseif ($keytype == 3){
	            
	            $jobM                =  $this -> MODEL('job');
	            $job                 =  $jobM -> getList(array('name'=>array('like',$keyword)),array('field'=>'id'));
	            
	            if ($job){
	                foreach($job['list'] as $v){
	                    
	                    $ids[]       =  $v['id'];
	                }
	                $where['jobid']  =  array('in',pylode(',', $ids));
	            }
	            
	        }
	        $urlarr['keytype']	     =  $keytype;
	        
	        $urlarr['keyword']	     =  $keyword;
	    }
	    if($_GET['time']){
	        
	        $time                      =  intval($_GET['time']);
	        
	        if($time == 1){
	            
	            $where['ctime']	       =  array('>',strtotime('today'));
	            
	        }else{
	            
	            $where['ctime']	       =  array('>',strtotime('-'.$time.' day'));
	        }
	        
	        $urlarr['time']            =  $time;
	    }
	    $urlarr['page']	 =	'{{page}}';
	    
	    $urlarr['c']	=	$_GET['c'];
	    
	    $pageurl		 =	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			 =	$this  -> MODEL('page');
	    $pages			 =	$pageM -> pageList('user_entrust_record',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
	        $where['orderby']  =  'id';
	        $where['limit']    =  $pages['limit'];
	        
	        $userEntrustM  =  $this -> MODEL('userEntrust');
	        
	        $List          =  $userEntrustM -> getRecordList($where,array('utype'=>'admin'));
	        
	        $this -> yunset(array('list'=>$List['list'],'ccname'=>$List['com']['name']));
	    }
	    
	    $lotime    =  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
	    
	    $search[]  =  array('param'=>'time','name'=>'推送时间','value'=>$lotime);
	    
	    $this->yunset('search_list',$search);
	    
		$this->yuntpl(array('admin/admin_trust_record'));
	}
	/**
	 * 会员-个人-简历记录管理:简历推送记录（删除）
	 */
	function deltrust_action(){
	    
	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $id   =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $id   =  $_POST['del'];
	    }
	    $userEntrustM  =  $this -> MODEL('userEntrust');
	    
	    $return        =  $userEntrustM -> delRecord($id);
	    
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	/**
	 * 会员-个人-简历记录管理:简历浏览记录（列表）
	 */
	function lookresume_action(){
	    //从企业列表更多过来的
	    if ($_GET['comid']){
	        
	        $comid                     =  intval($_GET['comid']);
	        
	        $where['comid']            =  $comid;
	        
	        $urlarr['comid']           =  $comid;
	    }
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
	            //简历名称
	            $resumeM                  =  $this -> MODEL('resume');
	            
	            $expect                   =  $resumeM -> getSimpleList(array('name'=>array('like',$keyword)),array('field'=>'id'));
	            
	            if ($expect){
	                
	                foreach($expect as $v){
	                    
	                    $ids[]             =  $v['id'];
	                }
	                $where['resume_id']    =  array('in',pylode(',', $ids));
	            }
	        }elseif ($keytype == 3){
	            //公司名称
	            $companyM                  =  $this -> MODEL('company');
	            $com                       =  $companyM -> getList(array('name'=>array('like',$keyword)),array('field'=>'uid'));
	            $uids                      =  array();
	            if ($com){
	                foreach($com['list'] as $v){
	                    
	                    $uids[]            =  $v['uid'];
	                }
	            }
	            
	            if (!empty($uids)){
	                
	                $where['com_id']       =  array('in',pylode(',', $uids));
	            }
	            
	        }
	        $urlarr['keytype']	           =  $keytype;
	        
	        $urlarr['keyword']	           =  $keyword;
	    }
	    if($_GET['time']){
	        
	        $time                          =  intval($_GET['time']);
	        
	        if($time == 1){
	            
	            $where['datetime']	       =  array('>',strtotime('today'));
	            
	        }else{
	            
	            $where['datetime']	       =  array('>',strtotime('-'.$time.' day'));
	        }
	        
	        $urlarr['time']                =  $time;
	    }
	    $urlarr['page']	 =	'{{page}}';
	    
	    $urlarr['c']	=	$_GET['c'];
	    
	    $pageurl		 =	Url($_GET['m'],$urlarr,'admin');
	    
	    //提取分页
	    $pageM			 =	$this  -> MODEL('page');
	    $pages			 =	$pageM -> pageList('look_resume',$where,$pageurl,$_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        //limit order 只有在列表查询时才需要
	        if($_GET['order']){
	            $where['orderby']       =  $_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	        }else{
	            $where['orderby']		=	'id';
	        }
	        $where['limit']    =  $pages['limit'];
	        
	        $lookresumeM      =  $this -> MODEL('lookresume');
	        
	        $List              =  $lookresumeM -> getList($where,array('utype'=>'admin'));
	        
	        $this -> yunset(array('list'=>$List['list'],'ccname'=>$List['com']['name']));
	    }
	    
	    $lotime    =  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
	    
	    $search[]  =  array('param'=>'time','name'=>'浏览时间','value'=>$lotime);
	    
	    $this->yunset('search_list',$search);
	    
		$this->yuntpl(array('admin/look_resume'));
	}
	/**
	 * 会员-个人-简历记录管理:简历浏览记录（删除）
	 */
	function dellook_action(){
	    
	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $id   =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $id   =  $_POST['del'];
	    }
	    $lookresumeM  =  $this -> MODEL('lookresume');
	    
	    $return        =  $lookresumeM -> delInfo(array('id'=>$id));
	    
	    $this -> layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
}
?>