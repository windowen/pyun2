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
class admin_resume_controller extends adminCommon{

	//设置高级搜索功能
    function set_search($CacheList = array()){

        if (!$CacheList){

			$cacheM		=	$this -> MODEL('cache');
			$CacheList	=	$cacheM -> GetCache(array('user', 'job', 'city'));

			$setArr	=	array(
				'userdata'		=>	$CacheList['userdata'],
				'userclass_name'=>	$CacheList['userclass_name'],
				'job_name'		=>	$CacheList['job_name'],
				'city_name'		=>	$CacheList['city_name']	
			);
			$this -> yunset($setArr);
        } 

		$userdata = $CacheList['userdata'];
		$userclass_name = $CacheList['userclass_name'];

		

        foreach($userdata['user_type'] as $k=>$v){
			
            $type[$v]=$userclass_name[$v];
        }
		
		foreach($userdata['user_edu'] as $k=>$v){
			
            $edu[$v]=$userclass_name[$v];
        }
		
		foreach($userdata['user_word'] as $k=>$v){
			
            $exp[$v]=$userclass_name[$v];
        }
		
        foreach($userdata['user_report'] as $k=>$v){
			
            $report[$v]=$userclass_name[$v];
        }

        include(CONFIG_PATH.'db.data.php');
        
        $source		=    $arr_data['source'];
        
		$uptime		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$adtime		=	array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
		
		$status		=	array('1'=>'已审核','2'=>'已锁定','3'=>'未通过','4'=>'未审核');
		
		$service	=	array('1'=>'置顶','2'=>'推荐');
		
		$integrity  =	$arr_data['integrity_name'];
		
		$search[]	=	array('param'=>'status','name'=>'审核状态','value'=>$status);
		
		$search[]	=	array('param'=>'uptime','name'=>'更新时间','value'=>$uptime);
		
		$search[]	=	array('param'=>'source','name'=>'数据来源','value'=>$source);
		
		$search[]	=	array('param'=>'service','name'=>'简历类型','value'=>$service);
		
		$search[]	=	array('param'=>'adtime','name'=>'添加时间','value'=>$adtime);
		
		$search[]	=	array('param'=>'type','name'=>'工作性质','value'=>$type);

		$search[]	=	array('param'=>'edu','name'=>'最高学历','value'=>$edu);

		$search[]	=	array('param'=>'exp','name'=>'工作经验','value'=>$exp);
		
		$search[]	=	array('param'=>'report','name'=>'到岗时间','value'=>$report);
		
		$search[]	=	array('param'=>'integrity','name'=>'完整度','value'=>$integrity);
		
		$this->yunset('source',$source);
		
		$this->yunset('search_list',$search);
	}
    /**
	 * 会员-个人-简历管理
	 */
    function index_action(){
		
        $resumeM	=   $this -> MODEL('resume');

        $where		=   $urlarr =   array();

      	include(CONFIG_PATH.'db.data.php');

		//搜索类型和搜索关键字
        if($_GET['keyword']){
            
            $keytype	=	intval($_GET['keytype']);
		    $keyword	=	trim($_GET['keyword']);
		    
		    if ($keytype == 1){
		        
		        $where['name']    =  array('like',$keyword);
		    }elseif ($keytype == 2){
		        
		        $where['uname']   =  array('like',$keyword);
		    }elseif ($keytype == 3){
		        
		        $where['id']      =  $keyword;
		    }elseif ($keytype == 4){
		        
		        $edu  =   $resumeM -> getResumeEdus(array('name'=>array('like',$keyword)),'eid');
		        
		        if ($edu){
		            
		            $eids =   array();
		            foreach($edu as $v){
		                
		                $eids[]   =   $v['eid'];
		            }
		            
		            $where['id']  =   array('in',pylode(',', $eids));
		        }
		    }elseif ($keytype == 5){
		        
		        $work             =   $resumeM -> getResumeWorks(array('content'=>array('like',$keyword)),'eid');
		        
		        if ($work){
		            
		            $eids =   array();
		            foreach($work as $v){
		                
		                $eids[]   =  $v['eid'];
		            }
		            
		            $where['id']  =  array('in',pylode(',', $eids));
		        }
		    }
		    
		    $urlarr['keytype']    =  $keytype;
		    $urlarr['keyword']    =  $keyword;
		}
		
		//审核状态
		if($_GET['status']){
		    
		    $status       =   intval($_GET['status']);
        
            if($status==2){
              
                $where['r_status']  =   2;
            }else{
                
                $where['state']     =   $status == 4 ? 0 : $status;
            }
		   
		    $urlarr['status']         =  $status;
		}
		
		//来源
		if ($_GET['source']){
		    
		    $where['source']          =  intval($_GET['source']);
		    
		    $urlarr['source']         =  intval($_GET['source']);
		}

		//发布时间
		if($_GET['adtime']){
		    
		    $adtime                   =  intval($_GET['adtime']);
		    
		    if($adtime == 1){
		        
		        $where['ctime']       =	 array('>',strtotime('today'));
		        
		    }else{
		        
		        $where['ctime']       =	 array('>',strtotime('-'.$adtime.' day'));
		    }
		    
		    $urlarr['adtime']	      =	 $adtime;
		}

		//更新时间
		if($_GET['uptime']){
		    
		    $uptime                   =  intval($_GET['uptime']);
		    
		    if($uptime == 1){
		        
		        $where['lastupdate']  =  array('>',strtotime('today'));
		        
		    }else{
		        
		        $where['lastupdate']  =  array('>',strtotime('-'.$uptime.' day'));
		    }
		    $urlarr['uptime']	      =   $uptime;
		}

		//工作性质
		if ($_GET['type']){
		    
		    $where['type']	=	intval($_GET['type']);
		    
		    $urlarr['type']	=	$_GET['type'];
		}
		
		//学历要求
		if ($_GET['edu']){
		    
		    $where['edu']	=	intval($_GET['edu']);
		    
		    $urlarr['edu']	=	intval($_GET['edu']);
		}

		//工作经验
		if ($_GET['exp']){
		    
		    $where['exp']	=	intval($_GET['exp']);
		    
		    $urlarr['exp']	=	intval($_GET['exp']);
		}
		//到岗时间
		if ($_GET['report']){
			
			$where['report']	=	intval($_GET['report']);
			
			$urlarr['report']	=	intval($_GET['report']);
		}
		
		//简历完整度
		if($_GET['integrity']){

		    $integrity_val		=	$arr_data['integrity_val'];
			$where['integrity']	=	array('>=',$integrity_val[$_GET['integrity']]);
		    $urlarr['integrity']=	$_GET['integrity'];
		}
		
		//简历类型
		if ($_GET['service']){
		    
		    $service                  =  intval($_GET['service']);
		    
		    if ($service == 1){
		        //置顶
		        $where['top'] 		  =  1;
		        
		        $where['topdate']	  =  array('>',time());
		    }elseif ($service == 2){
		        //推荐
		        $where['rec_resume']  =  1;
		    }
		    
		    $urlarr['service']		  =	 $service;
		}

		if($_GET['job_class']){
		    // 职位类别
		    $jwhere  =  array(
		        'job1'  =>  array('in', $_GET['job_class']),
		        'job1_son'  =>  array('in', $_GET['job_class'], 'OR'),
		        'job_post'  =>  array('in', $_GET['job_class'], 'OR')
		    );
		    
		    $jobrow  =  $resumeM->getJobclassList($jwhere);
		    
		    $jeids	 =	array();

		    if (!empty($jobrow)){
		        
		        foreach ($jobrow as $v){
		            
		            $jeids[]  =  $v['eid'];
		        }
		        $jeids  =  array_unique($jeids);
		        
		    }

		    $where['id']  =  array('in', pylode(',', $jeids));

			$urlarr['job_class']  =  $_GET['job_class'];
		}
		if($_GET['city_class']){
		    // 城市类别
		    $cwhere  =  array(
		        'provinceid'    =>  array('in', $_GET['city_class']),
		        'cityid'        =>  array('in', $_GET['city_class'], 'OR'),
		        'three_cityid'  =>  array('in', $_GET['city_class'], 'OR')
		    );
		    
		    $cityrow  =  $resumeM->getCityclassList($cwhere);
		    
		    $ceids	  =	 array();

		    if (!empty($cityrow)){
		        
		        foreach ($cityrow as $v){
		            
		            $ceids[]  =  $v['eid'];
		        }
		        $ceids  =  array_unique($ceids);
		    }
		    
		    $where['id']  =  array('in', pylode(',', $ceids));

			$urlarr['city_class']  =  $_GET['city_class'];
		}
		
		//分页链接
		$urlarr['page']	=	'{{page}}';
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('resume_expect',$where,$pageurl,$_GET['page']);
		
		//分页数大于0的情况下 执行列表查询
		if($pages['total'] > 0){
		    //limit order 只有在列表查询时才需要
		    if($_GET['order']){
		        
	            if($_GET['t']=='time'){
	                
	                $where['orderby']	=	'lastupdate,'.$_GET['order'];
    
	            }else{
	                
	                $where['orderby']	=	$_GET['t'].','.$_GET['order'];
	            }
		        
		        $urlarr['order']		=	$_GET['order'];
		        $urlarr['t']			=	$_GET['t'];
		    }else{
		        $where['orderby']		=	array('id,desc');   
		    }
		    $where['limit']				=	$pages['limit'];
		    
		    $List		=	$resumeM -> getList($where,array('cache'=>1,'utype'=>'admin'));
		    
		    $CacheList	=	$List['cache'];

		    
		    //处理导出需要的where条件
		    unset($where['limit']);
		    
		    session_start();
		    $_SESSION['resumeXls'] = $where;
			$setArr	=	array(
				'rows'			=>	$List['list'],
				'userdata'		=>	$CacheList['userdata'],
				'userclass_name'=>	$CacheList['userclass_name'],
				'job_name'		=>	$CacheList['job_name'],
				'city_name'		=>	$CacheList['city_name']	
			);
		    $this -> yunset($setArr);
		}

		//高级搜索
		$this->set_search($CacheList);
		
		$this->yuntpl(array('admin/admin_resume'));
	}
	/**
	 * 会员-个人-简历管理: 添加简历页面
	 */
	public function addResume_action(){
    
	    $cacheM  =  $this->MODEL('cache');
	       
	    $cache   =  $cacheM -> GetCache('user');
	    
	    if ($_GET['uid']){
	        
	        $where['uid'] = intval($_GET['uid']);
	        
	        $resumeM   =   $this->MODEL('resume');
	        
	        $return  =  $resumeM -> getResumeInfo($where);
	      
	    }
      
        $setarr  =  array(
	            'row'             =>  $return,
	            'user_sex'        =>  $cache['user_sex'],
	            'userdata'        =>  $cache['userdata'],
	            'userclass_name'  =>  $cache['userclass_name'],
	        );
	       
	        $this->yunset($setarr);
      
	    $this->yuntpl(array('admin/admin_resume_add'));
	}
	/**
	 * 会员-个人-简历管理: 添加简历保存
	*/
	public function saveResume_action(){
	    if($_POST['next']){
	        
	        $resumeM  =  $this->MODEL('resume');

	        $_POST    =  $this->post_trim($_POST);
			
			
			
			if($_POST['uid']){
			    
			    $uid    =  intval($_POST['uid']);
			    
			    $mData = array(
			        'email'  => $_POST['email'],
			        'moblie' => $_POST['moblie']
				);
				
			    $rData = array(
			        'name'        => 	$_POST['resume_name'],
			        'sex'         => 	$_POST['sex'],
			        'birthday'    => 	$_POST['birthday'],
			        'living'      => 	$_POST['living'],
			        'edu'         => 	$_POST['edu'],
			        'exp'         => 	$_POST['exp'],
			        'telphone'    => 	$_POST['moblie'],
			        'email'       => 	$_POST['email'],
					'description' => 	$_POST['description'],
			    );
			    
			    $result  =  $resumeM -> upResumeInfo(array('uid'=>$uid),array('mData'=>$mData,'rData'=>$rData));
 			    
			    if ($result['id']){
 			        
 			        $this -> ACT_layer_msg('下一步：填写求职意向',9,'index.php?m=admin_resume&c=editResume&uid='.$_POST['uid'].'');
 			        
 			    }elseif ($result['msg']){
 			        
 			        $this -> ACT_layer_msg($result['msg'], 8);
 			        
 			    }else{
 			        
 			        $this -> ACT_layer_msg('保存失败，请重试！',8);
 			    }
			}else{
				if($this->config['sy_uc_type']=='uc_center'){
				    
				    $this-> obj-> uc_open();
					
					$user = uc_get_user($_POST['username']);
					
					if ($user){
					    
					    $this->ACT_layer_msg('该会员已经存在！',8);
					}
				}
				$userinfoM = $this->MODEL('userinfo');
				//检测用户名、手机号、邮箱是否已被注册
				$checkData = array(
				    'username' => $_POST['username'],
				    'moblie'   => $_POST['moblie'],
				    'email'    => $_POST['email'],
				);
				$memberCheck = $userinfoM->addMemberCheck($checkData);
				
				if ($memberCheck['msg']){
				    
				    $this->ACT_layer_msg($memberCheck['msg'],8);
				}
				
				$ip    =  fun_ip_get();
				$time  =  time();
				$pass  =  $_POST['password'];
				
				if($this->config['sy_uc_type']=='uc_center'){
				    
				    $uid  =  uc_user_register($_POST['username'],$pass,$_POST['email']);
				    
				    if($uid < 0){
				        
				        switch($uid){
				            case "-1" : $data['msg']='用户名不合法!';
				            break;
				            case "-2" : $data['msg']='包含不允许注册的词语!';
				            break;
				            case "-3" : $data['msg']='用户名已经存在!';
				            break;
				            case "-4" : $data['msg']='Email 格式有误!';
				            break;
				            case "-5" : $data['msg']='Email 不允许注册!';
				            break;
				            case "-6" : $data['msg']='该 Email 已经被注册!';
				            break;
				        }
				        $this -> ACT_layer_msg($data['msg'],8);
				    }else{
				        
				        list($uid,$username,$email,$password,$salt)=uc_get_user($_POST['username'],$pass);
				    }
				}else{
					$salt      =  substr(uniqid(rand()), -6);
					
					$password  =  passCheck($pass,$salt);
				}
				
				$mdata = array(
				    'username'  =>  $_POST['username'],
				    'password'  =>  $password,
				    'usertype'  =>  1,
				    'salt'      =>  $salt,
				    'moblie'    =>  $_POST['moblie'],
				    'email'     =>  $_POST['email'],
				    'reg_date'  =>  $time,
				    'reg_ip'    =>  $ip,
				    'status'    =>  1
				);
				$udata = array(
				    'name'         =>  $_POST['resume_name'],
				    'sex'          =>  $_POST['sex'],
				    'edu'          =>  $_POST['edu'],
				    'exp'          =>  $_POST['exp'],
				    'birthday'     =>  $_POST['birthday'],
				    'email'        =>  $_POST['email'],
				    'telphone'     =>  $_POST['moblie'],
				    'description'  =>  $_POST['description'],
				    'living'       =>  $_POST['living'],
					'r_status'	   =>  1
				);
				
				$nid  =  $userinfoM -> addInfo(array('mdata'=>$mdata,'udata'=>$udata,'sdata'=>array('integral'=>0)));
				
				if($nid > 0){
				    
					$this->ACT_layer_msg('下一步：填写求职意向',9,'index.php?m=admin_resume&c=editResume&uid='.$nid.'');
					
				}else{
				    
					$this->ACT_layer_msg('会员添加失败，请重试！',8);
				}
			}
	    }
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面
	 */
	public function editResume_action(){
	    
		$uid = intval($_GET['uid']);
		
		if($_GET['id']){
		    
		    $eid = intval($_GET['id']);
		}
		$resumeM = $this->MODEL('resume');
		
		$return = $resumeM->getInfo(array('uid'=>$uid,'eid'=>$eid,'tb'=>'all','needCache'=>1));

		

		$setarr = array(
		    'uid'             =>   $uid,
		    'expect'          =>   $return['expect'],
		    'edu'             =>   $return['edu'],
		    'other'           =>   $return['other'],
		    'project'         =>   $return['project'],
		    'skill'           =>   $return['skill'],
		    'training'        =>   $return['training'],
		    'work'            =>   $return['work'],
		    'industry_index'  =>   $return['cache']['industry_index'],
		    'industry_name'   =>   $return['cache']['industry_name'],
		    'userdata'        =>   $return['cache']['userdata'],
		    'userclass_name'  =>   $return['cache']['userclass_name'],
		);

		$this->yunset($setarr);

		if(empty($return['cache']['city_type'])){
            $this   ->  yunset('cionly',1);
        }
        if(empty($return['cache']['job_type'])){
            $this   ->  yunset('jionly',1);
        }
        
		$where['uid']		=		$uid;

		$resumeinfo			=		$resumeM->getResumeInfo($where,array('field'=>'`r_status`,`uid`'));

		$this->yunset('resumeinfo',$resumeinfo);

		$this->yuntpl(array('admin/admin_resume_edit'));
	}
	/**
	 * 会员-个人-简历管理: 保存求职意向
	 */
	public function saveExpect_action(){

	    if($_POST['submit']){
	        $eid      =  (int)$_POST['eid'];
	        $uid      =  (int)$_POST['uid'];
	        $time     =  time();
	        
	        $resumeM  =  $this->MODEL('resume');
	        
	        if($eid == ''){
	            
	            $field   =  'uid,name,edu,exp,sex,birthday,idcard_status,status,photo,phototype,r_status';
	            
             	$resumewhere['uid']     =     $uid;
              
	            $resume  =  $resumeM -> getResumeInfo($resumewhere,array('field'=>$field));
	            if(is_array($resume)){
	            	if($resume['r_status']==1){
						$state			=		1;
					}else{
						$state			=		0;
					}
	            }else{
	            	$state			=		1;
	            }
				
	            $expectDate = array(
					'r_status'        =>  $resume['r_status'],
					'state'        		=>  $state,
	                'integrity'		  =>  55,
	                'lastupdate'	  =>  $time,
	                'ctime'			  =>  $time,
	                'name'			  =>  $_POST['name'],
	                'hy'			  =>  $_POST['hy'],
	                'job_classid'	  =>  $_POST['job_classid'],
	                'minsalary'		  =>  $_POST['minsalary'],
	                'maxsalary'		  =>  $_POST['maxsalary'],
	                'city_classid'	  =>  $_POST['city_classid'],
	                'type'			  =>  $_POST['type'],
	                'report'		  =>  $_POST['report'],
	                'jobstatus'		  =>  $_POST['jobstatus'],
	                'uid'			  =>  $resume['uid'],
	                'edu'             =>  $resume['edu'],
	                'exp'             =>  $resume['exp'],
	                'uname'           =>  $resume['name'],
	                'sex'             =>  $resume['sex'],
	                'birthday'        =>  $resume['birthday'],
	                'idcard_status'   =>  $resume['idcard_status'],
	                'status'          =>  $resume['status'],
	                'photo'           =>  $resume['photo'],
	                'phototype'       =>  $resume['phototype']
	               
	            );
	            $return  =  $resumeM -> addInfo(array('uid'=>$uid,'eData'=>$expectDate,'utype'=>'admin'));
	            $eid     =  $return['id'];
	        }else{
	            $expectDate  =  array(
	                'name'			=>	$_POST['name'],
	                'hy'			=>	$_POST['hy'],
	                'job_classid'	=>	$_POST['job_classid'],
	                'minsalary'		=>	$_POST['minsalary'],
	                'maxsalary'		=>	$_POST['maxsalary'],
	                'city_classid'	=>	$_POST['city_classid'],
	                'type'			=>	$_POST['type'],
	                'report'		=>	$_POST['report'],
	                'jobstatus'		=>	$_POST['jobstatus'],
	                'lastupdate'	=>	$time
	            );
	            
	            $return  =  $resumeM -> upInfo(array('id'=>$eid), array('eData'=>$expectDate,'utype'=>'admin'));
	            
	        }

	        if($return['id']){
	            
	            $expect  =  $resumeM -> getExpect(array('id'=>$eid),array('needCache'=>1));
	            
	            echo json_encode(array('error'=>1,'expect'=>$expect));die;
	            
	        }else{
	            
	            echo json_encode(array('error'=>0,'msg'=>'操作失败'));die;
	        }
	    }
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（工作经历处理）
	 */
	public function work_action(){
	    
	    $eid   =   intval($_POST['eid']);
	    
	    $id    =   intval($_POST['id']);
	    
	    $uid   =   intval($_POST['uid']);
	    
	    $resumeM   =   $this -> MODEL('resume');
	    
	    $post = array(
	        'uid'		=>  $uid,
	        'eid'		=>  $eid,
	        'name'		=>  $_POST['name'],
	        'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
	        'title'		=>  $_POST['title'],
	        'content'	=>	$_POST['content']
	    );
	    
	    if(!$id){
	        
	        $return  =  $resumeM -> addResumeWork($post,array('utype'=>'admin'));
	        
	        $id      =  $return['id'];
	    }else{
	        
	        $resumeM -> addResumeWork($post,array('where'=>array('id'=>$id),'utype'=>'admin'));
	    }
	    
	    $work  =   $resumeM -> getResumeWork(array('id'=>$id));
	    
	    echo json_encode($work);die;
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（教育经历处理）
	 */
	public function edu_action()
	{
	    $eid   =   intval($_POST['eid']);
	    
	    $id    =   intval($_POST['id']);
	    
	    $uid   =   intval($_POST['uid']);
	    
	    $resumeM   =   $this -> MODEL('resume');
	    
	    $post = array(
	        'uid'		=>  $uid,
	        'eid'		=>  $eid,
	        'name'		=>  $_POST['name'],
	        'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
	        'title'		=>  $_POST['title'],
	        'education'	=>	$_POST['education'],
	        'specialty' =>  $_POST['specialty']
	    );
	    
	    if(!$id){
	        
	        $return  =  $resumeM -> addResumeEdu($post,array('utype'=>'admin'));
	        
	        $id      =  $return['id'];
	    }else{
	        
	        $resumeM -> addResumeEdu($post,array('where'=>array('id'=>$id),'utype'=>'admin'));
	    }
	    
	    $edu  =   $resumeM -> getResumeEdu(array('id'=>$id));
	    
	    echo json_encode($edu);die;
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（培训经历处理）
	 */
	public function training_action(){
	    $eid   =   intval($_POST['eid']);
	    
	    $id    =   intval($_POST['id']);
	    
	    $uid   =   intval($_POST['uid']);
	    
	    $resumeM   =   $this -> MODEL('resume');
	    
	    $post = array(
	        'uid'		=>  $uid,
	        'eid'		=>  $eid,
	        'name'		=>  $_POST['name'],
	        'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
	        'title'		=>  $_POST['title'],
	        'content'	=>	$_POST['content']
	    );
	    
	    if(!$id){
	        
	        $return  =  $resumeM -> addResumeTrain($post,array('utype'=>'admin'));
	        
	        $id      =  $return['id'];
	    }else{
	        
	        $resumeM -> addResumeTrain($post,array('where'=>array('id'=>$id),'utype'=>'admin'));
	    }
	    
	    $train  =   $resumeM -> getResumeTrain(array('id'=>$id));
	    
	    echo json_encode($train);die;
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（职业技能处理）
	 */
	public function skill_action(){
	    $eid   =   intval($_POST['eid']);
	    
	    $id    =   intval($_POST['id']);
	    
	    $uid   =   intval($_POST['uid']);
	    
	    $resumeM   =   $this -> MODEL('resume');
	    $post = array(
	        'uid'		=>  $uid,
	        'eid'		=>  $eid,
	        'name'		=>  $_POST['name'],
			'ing'		=>  $_POST['user_ing_name'],
	        'file'		=>  $_FILES['file'],
	        'longtime'	=>	$_POST['longtime']
	    );
	    
	    if(!$id){
	        
	        $return  =  $resumeM -> addResumeSkill($post,array('utype'=>'admin'));
	        
	        $id      =  $return['id'];
	    }else{
	        
	        $return  =  $resumeM -> addResumeSkill($post,array('where'=>array('id'=>$id),'utype'=>'admin'));
	    }
	    
	    $this->ACT_layer_msg($return['msg'],$return['errcode'],'index.php?m=admin_resume&c=editResume&uid='.$uid.'&id='.$eid.'');
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（项目经历处理）
	 */
	public function project_action(){
	    $eid   =   intval($_POST['eid']);
	    
	    $id    =   intval($_POST['id']);
	    
	    $uid   =   intval($_POST['uid']);
	    
	    $resumeM   =   $this -> MODEL('resume');
	    
	    $post = array(
	        'uid'		=>  $uid,
	        'eid'		=>  $eid,
	        'name'		=>  $_POST['name'],
	        'sdate'		=>	strtotime($_POST['sdate']),
	        'edate'		=>  strtotime($_POST['edate']),
	        'title'		=>  $_POST['title'],
	        'content'	=>	$_POST['content']
	    );
	    
	    if(!$id){
	        
	        $return  =  $resumeM -> addResumeProject($post,array('utype'=>'admin'));
	        
	        $id      =  $return['id'];   
	    }else{
	        
	        $resumeM -> addResumeProject($post,array('where'=>array('id'=>$id),'utype'=>'admin'));
	    }
	    
	    $project  =   $resumeM -> getResumeProject(array('id'=>$id));
	    
	    echo json_encode($project);die;
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（其他描述处理）
	 */
	public function other_action(){
	    $eid   =   intval($_POST['eid']);
	    
	    $id    =   intval($_POST['id']);
	    
	    $uid   =   intval($_POST['uid']);
	    
	    $resumeM   =   $this -> MODEL('resume');
	    
	    $post = array(
	        'uid'		=>  $uid,
	        'eid'		=>  $eid,
	        'name'		=>  $_POST['name'],
	        'content'	=>	$_POST['content']
	    );
	    
	    if(!$id){
	        
	        $return  =  $resumeM -> addResumeOther($post,array('utype'=>'admin'));
	        
	        $id      =  $return['id'];
	    }else{
	        
	        $resumeM -> addResumeOther($post,array('where'=>array('id'=>$id),'utype'=>'admin'));
	    }
	    
	    $other  =   $resumeM -> getResumeOther(array('id'=>$id));
	    
	    echo json_encode($other);die;
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（简历附表详情）
	 */
	function getResumeFb_action(){
	    
	    $resumeM   =   $this -> MODEL('resume');
	    
	    $id    =   intval($_POST['id']);
	    
	    $table =   'resume_'.$_POST['type'];
	    
	    $info  =   $resumeM -> getFb($table,$id);
	    
	    echo json_encode($info);die;
	}
	/**
	 * 会员-个人-简历管理: 修改简历页面（简历详情页附表删除）
	 */
	function delResumeFb_action(){
	    
	    $table  =  trim($_POST['table']);
	    
	    $tables  =  array('skill','work','project','edu','training','other');
	    
	    if(in_array($table,$tables)){
	        
	        $id       =  (int)$_POST['id'];
	        
	        $eid      =  (int)$_POST['eid'];
	        
	        $resumeM  =  $this -> MODEL('resume');
	        
	        $return   =  $resumeM -> delFb($table,array('id'=>$id,'eid'=>$eid), array('utype' => 'admin'));
	        
	        if (is_array($return)) {
	            
	            $this -> layer_msg($return['msg'],$return['errcode']);
	        }else{
	            
	            $this -> layer_msg('操作成功！', 9);
	        }
	    }else{
	        
	        $this -> layer_msg('系统异常',8);
	    }
	}
	/**
	 * 会员-个人-简历管理: 审核简历
	 */
	function status_action(){
	    
	    $resumeM   =   $this -> MODEL('resume');
      
	    $post      =   array(
	        'state'        =>  intval($_POST['status']),
	        'statusbody'   =>  trim($_POST['statusbody'])
	    );

	    $return  =  $resumeM -> statusResume($_POST['id'],array('post'=>$post));
	    
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
    /**
	 * 会员-个人-简历管理: 同步审核用户
	 */
    function resumestatus_action()
    {
        if ($_POST) {
            
            $id         =   intval($_POST['cid']);
            $uid        =   intval($_POST['cuid']);
            $status     =   intval($_POST['r_status']);
            $statusbody =   trim($_POST['statusbody']);
            
            $resumeM    =   $this->MODEL('resume');
            
            $post   =   array(
                
                'uid'           =>  $uid,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );
            
            $return     =   $resumeM -> status($id, $post);
            
            $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
        }
    }
	/**
	 * 会员-个人-简历管理: 刷新简历
	 */
	function refresh_action(){
	    
	    if ($_GET['id']){
	        
	        $id   =  intval($_GET['id']);
	        
	    }elseif ($_POST['ids']){
	        
	        $id   =  trim($_POST['ids']);
	    }
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> refreshResume($id);
	    
	    $this -> layer_msg($return['msg'],$return['errcode']);
	}
	/**
	 * 会员-个人-简历管理: 推荐简历
	 */
	function rec_action(){
	    if ($_GET['id']){
	        
	        $id   =  intval($_GET['id']);
	        
	        $rec  =  $_GET['rec'];
	        
	    }elseif ($_POST['ids']){
	        
	        $id   =  trim($_POST['ids']);
	        
	        $rec  =  $_POST['rec_resume'];
	    }
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> recResume($id,$rec);
	    
	    echo $return?1:0;die;
	}
	/**
	 * 会员-个人-简历管理: 置顶简历
	 */
	function top_action(){
	    
	    $id   =  trim($_POST['eid']);
	    
	    if ($_POST['s']){
	        
	        $post  =  array(
	            'top'      =>  0,
	            'topdate'  =>  0
	        );
	    }else{
	        $post  =  array(
	            'top'      =>  1,
	            'topdate'  =>  strtotime('+'.intval($_POST['addday']).' day')
	        );
	    }
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> topResume($id,$post);
	    
	    $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-简历管理: 简历备注
	 */
	function label_action(){
	    
	    $id=(int)$_POST['id'];
	    
	    $post     =  array(
	        'label'    => (int)$_POST['label'],
	        'content'  => trim($_POST['content'])
	    );
	    
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> label($id,$post);
	    
	    $this->ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-简历管理: 删除简历
	 */
	function delResume_action(){

	    if ($_GET['del']){
	        
	        $this -> check_token();
	        
	        $id   =  intval($_GET['del']);
	        
	    }elseif ($_POST['del']){
	        
	        $id   =  $_POST['del'];
	    }
		
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $return   =  $resumeM -> delResume($id,array('utype'=>'admin', 'delAccount' => $_POST['delAccount']));
	    
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	/**
	 * 会员-个人-简历管理: 检测用户名重复性
	 */
	function checkUsername_action(){
	    
	    $userinfoM	=	$this -> MODEL('userinfo');
	    
	    $result     =   $userinfoM -> addMemberCheck(array('username'=>trim($_POST['username'])));
        
	    echo json_encode($result);die;
	}
	/**
	 * 会员-个人-简历管理: 查询审核原因
	 */
	function lockInfo_action(){
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $expect   =  $resumeM -> getExpect(array('id'=>intval($_GET['id'])),array('field'=>'statusbody'));
	    
	    echo trim($expect['statusbody']);die;
	}
	/**
	 * 会员-个人-简历管理: 数据统计查询
	 */
	function resumeNum_action(){
	    
	    $MsgNum = $this -> MODEL('msgNum');
	    
	    echo $MsgNum -> resumeNum();
	}
	/**
	 * 会员-个人-简历管理: 导出简历
	 */
	function xls_action(){
	    
	    session_start();
	    
	    $where = $_SESSION['resumeXls'] ? $_SESSION['resumeXls'] : array('orderby'=>'id');
	    
	    if(!empty($_POST['rtype'])){
	        
	        foreach($_POST['rtype'] as $v){
	            
	            if($v == 'lastdate'){
	                
	                $rtype[]  =  'lastupdate';
	            }else{
	                $rtype[]  =  $v;
	            }
	        }
	        $rfield  =  @implode(',',$rtype).',uid';
	    }else{
	        $rfield  =  'uid';
	    }
	    if($_POST['ids']){
	        
	        $ids          =  @explode(',',$_POST['ids']);
	        
	        $where['id']  =  array('in',pylode(',',$ids));
	    }
	    if($_POST['limit']){
	        
	        $where['limit']  =  intval($_POST['limit']);
	    }
	    if($_POST['section']){
			
			$num=explode(',',$_POST['section']);
			
			$where['limit']  =  array($num[0]-1,$num[1]);
		
		}
	    $resumeM  =  $this -> MODEL('resume');
	    
	    $listNew  =  $resumeM -> getList($where,array('cache'=>1,'field'=>$rfield,'utype'=>'admin'));
	    
	    $expects  =  $listNew['list'];
	    if (!empty($expects)){
	        
	        if(!empty($_POST['type'])){
	            
	            foreach($expects as $v){
	                
	                $uids[]  =  $v['uid'];
	            }
	            if(in_array('uid',$_POST['type'])){
	                
	                $field  =  @implode(',',$_POST['type']);
	                
	            }else{
	                
	                $field  =  @implode(',',$_POST['type']).',uid';
	            }
	            $resume  =  $resumeM -> getResumeList(array('uid'=>array('in',pylode(',', $uids))),array('field'=>$field));
	        }
	        foreach ($expects as $k=>$v){
	            
	            if(is_array($resume)){
	                
	                foreach($resume as $val){
	                    
	                    if($v['uid']==$val['uid']){
	                        
	                        $expects[$k]['reusme']      =  $val;
	                        
	                        $expects[$k]['marriage_n']  =  $listNew['cache']['userclass_name'][$val['marriage']];
	                    }
	                }
	            }
	        }
	        $this->yunset('list',$expects);
	        $this->yunset('type',$_POST['type']);
	        $this->yunset('rtype',$_POST['rtype']);
	        
	        $this -> MODEL('log') -> addAdminLog('导出简历信息');
	        header("Content-Type: application/vnd.ms-excel");
	        header("Content-Disposition: attachment; filename=resume.xls");
	        $this->yuntpl(array('admin/admin_resume_xls'));
	    }
	}

}
?>