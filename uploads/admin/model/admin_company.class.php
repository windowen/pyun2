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
class admin_company_controller extends adminCommon{
    
	function set_search(){ 
	    
        $ratingM    =   $this -> MODEL('rating');
		
        $rating     =   $ratingM -> getList(array( 'category' => '1', 'orderby' => 'sort' ), array('field'=>'`id`,`name`'));
		
		if(!empty($rating)){
		    
		    $ratingarr    =   array();
		    
			foreach($rating  as  $k => $v){
			    
                $ratingarr[$v['id']]   =   $v['name'];
                 
			}
		}
		
        include(CONFIG_PATH.'db.data.php');
		
        $source             =   $arr_data['source'];
		
        $timeSection        =   array('1'=>'今天', '3'=>'3天内', '7'=>'7天内', '15'=>'半月内', '30'=>'1个月内', '31'=> '1-3个月', '32' => '3-6个月', '33' => '6个月-1年', '34' => '1年以上');
         
        $status             =   array('1'=>'已审核', '2'=>'已锁定', '3'=>'未通过', '4'=>'未审核');
        
        $edtime             =   array('1'=>'7天内', '2'=>'一个月内', '3'=>'半年内', '4'=>'一年内', '5'=>'已到期');
       
        $isrec              =   array('1'=>'是', '2'=>'否', '3'=>'已到期');
        
        $isgw               =   array('1'=>'已分配', '2'=>'未分配');
        
        $search_list        =   array();
        
        $search_list[]      =   array('param'=>'rating','name'=>'会员等级','value'=>$ratingarr);
        
        $search_list[]      =   array('param'=>'time','name'=>'到期时间','value'=>$edtime);
        
        $search_list[]      =   array('param'=>'status','name'=>'审核状态','value'=>$status);
        
        $search_list[]      =   array('param'=>'source','name'=>'数据来源','value'=>$source);
        
        $search_list[]      =   array('param'=>'rec','name'=>'知名企业','value'=>$isrec);
        
        $search_list[]      =   array('param'=>'gw','name'=>'企业顾问','value'=>$isgw);
        
        $search_list[]      =   array('param'=>'lotime','name'=>'最近登录','value'=>$timeSection);
        
        $search_list[]      =   array('param'=>'adtime','name'=>'最近注册','value'=>$timeSection);
        
        $this -> yunset(array('source' => $source, 'ratingarr' => $ratingarr, 'search_list' => $search_list));
		 
	}
	
	/**
	 * @desc 后台 企业列表
	 */
	function index_action(){
	    
	    $ComM          =   $this -> MODEL('company');
	    
	    $where         =   array();
	    
	    $mwhere        =   array();
	    
	    $urlarr        =   $_GET;
	    
		if($_GET['keyword']){

			$keywordStr	   =   trim($_GET['keyword']);
	    
			$typeStr       =   intval($_GET['type']);

			if (!empty($keywordStr) && $typeStr == 1) {
	        
				$where['name']          =   array('like', $keywordStr);
				
			}elseif (!empty($keywordStr) && $typeStr == 2){
	        
				$mwhere['username']     =   array('like', $keywordStr);
				
			}else if (!empty($keywordStr) && $typeStr == 3) {
	        
				$where['linkman']       =   array('like', $keywordStr);
				
			}else if (!empty($keywordStr) && $typeStr == 4) {
	        
				$where['linktel']       =   array('like', $keywordStr);
				
			}else if (!empty($keywordStr) && $typeStr == 5) {
	        
				$where['linkmail']      =   array('like', $keywordStr);
				
			}else  if (!empty($keywordStr) && $typeStr == 6){

				$where['uid'][]         =   array('like', $keywordStr);

			}

			$urlarr['type']			=	$typeStr;
	        
	    	$urlarr['keyword']		=	$keywordStr;

		}
	    
	    if ($_GET['status']) {
            
            $status                 =   intval($_GET['status']);
	        
            $where['r_status']      =   $status == 4 ? 0 : $status;
	        
            $urlarr['r_status']     =   $status;
	        
	    }
	    
	    if ($_GET['adtime']) {
	        
	        $adtime    =   intval($_GET['adtime']);
	        
	        if($adtime == 1){
	        
                $mwhere['reg_date']     =   array('>',  strtotime('today'));
	        
	        }else if ($adtime < 31){
	            
	            $mwhere['reg_date']  =   array('>',  strtotime('-'.$adtime.' day'));
	            
	        }else if ($adtime == 31){// 1 - 3 个月
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['reg_date'][]  =   array('<',  strtotime('-1 month'));
	            $mwhere['reg_date'][]  =   array('>=',  strtotime('-3 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($adtime == 32){// 3 - 6个月
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['reg_date'][]  =   array('<',  strtotime('-3 month'));
	            $mwhere['reg_date'][]  =   array('>=',  strtotime('-6 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($adtime == 33){// 6个月 - 1年
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['reg_date'][]  =   array('<',  strtotime('-6 month'));
	            $mwhere['reg_date'][]  =   array('>=',  strtotime('-12 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($adtime == 34){// 1年以上
	            $mwhere['reg_date']  =   array('<',  strtotime('-1 year'));
	        }
	        
	        $urlarr['adtime']       =   $adtime;
	        
	    }
	    
	    if($_GET['lotime']){
	        
	        $lotime    =   intval($_GET['lotime']);
	        
	        if($lotime ==  1){
	            
	            $mwhere['login_date']  =   array('>',  strtotime('today'));
	            
	        }else if ($lotime < 31){
	            
	            $mwhere['login_date']  =   array('>',  strtotime('-'.$lotime.' day'));
	            
	        }else if ($lotime == 31){ 
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['login_date'][]  =   array('<',  strtotime('-1 month'));
	            $mwhere['login_date'][]  =   array('>=',  strtotime('-3 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($lotime == 32){ 
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['login_date'][]  =   array('<',  strtotime('-3 month'));
	            $mwhere['login_date'][]  =   array('>=',  strtotime('-6 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($lotime == 33){ 
	            $mwhere['PHPYUNBTWSTART_C']    =   '';
	            $mwhere['login_date'][]  =   array('<',  strtotime('-6 month'));
	            $mwhere['login_date'][]  =   array('>=',  strtotime('-12 month'));
	            $mwhere['PHPYUNBTWEND_C']      =   '';
	        }else if ($lotime == 34){
	            $mwhere['login_date']  =   array('<',  strtotime('-1 year'));
	        }
	        
	        $urlarr['lotime']          =   $lotime;
	        
	    }
	     
	    if($_GET['source']){
	        
	        $mwhere['source']          =   $_GET['source'];
	        
	        $urlarr['source']          =   $_GET['source'];
	        
	    }
	    
	    $mUids		=	array();
	    
	    $UserinfoM	=	$this -> MODEL('userinfo');
	    
	    if(!empty($mwhere)){
	        
	        $uidList	=	$UserinfoM  ->  getList(array_merge(array('usertype' => 2), $mwhere), array('field' => '`uid`'));
	        
	        if(!empty($uidList)){
	            
	            foreach($uidList as $uv){
	                
	                $mUids[]	=	$uv['uid'];
	                
	            }
	            
	        }else{
	            
	            $mUids			=	array(0);
	            
			}
			
			$where['uid'][] =	array('in', pylode(',',$mUids));
	        
	    }
	    
        if($_GET['rating']){
            
            $where['rating']   =   $_GET['rating'];
            
            $urlarr['rating']   =   $_GET['rating'];
            
	    }
	    $toDay	=	strtotime(date('Y-m-d'));
	    if($_GET['time']){
	        
	        $time  =   intval($_GET['time']);
	        
	        if($time == 5){
	            
	            $where['PHPYUNBTWSTART_A']    =   '';
	            
	            $where['vipetime'][]         =   array('>', '0','AND');
	            $where['vipetime'][]         =   array('<',  $toDay,'AND');
	            
	            $where['PHPYUNBTWEND_A']      =   '';
	            
	        }else{
	           
	            if($time == 1){
	                
	                $num   =   '+7 day';
	                
	            }elseif($time == 2 ){
	                
	                $num   =   '+1 month';
	                
	            }elseif($time == 3){
	                
	                $num   =   '+6 month';
	                
	            }elseif($time == 4){
	                
	                $num='+1 year';
	                
	            }
	            
	            $where['PHPYUNBTWSTART_B']    =   '';
	            
	            $where['vipetime'][]         =   array('>', time(),'AND');
	            $where['vipetime'][]         =   array('<', strtotime($num),'AND');
	            
	            $where['PHPYUNBTWEND_B']      =   '';
	            
	        }
	        
	        $urlarr['time']    =   $time;
	        
	    }
	    
        if($_GET['rec']){
            
            $rec    =   intval($_GET['rec']);
            
            if($rec == 1){
                
                $where['rec']       =   '1';
                
                $where['hottime']   =   array('>', time());
                
            }elseif ($rec == 2){
                
                $where['rec']       =   '0';
                
            }elseif ($rec == 3){
                
                $where['rec']       =   '1';
                
                $where['hottime']   =   array('<', time());
                
            }
            
            $urlarr['rec']          =   $rec;
            
	    }
	    
	    if($_GET['gw']){
	        
	        if(intval($_GET['gw']) == 1){
	            
                $where['crm_uid']     =   array('<>', '0');

	        }else{
	            
	            $where['crm_uid']     =   '0';
	        }
	        
            $urlarr['gw']           =   $_GET['gw'];
	        
	    }
	    
	    $urlarr['page']	=	'{{page}}';
	    
	    $pageurl		=	Url($_GET['m'], $urlarr, 'admin');
	    
	    //提取分页
	    $pageM			=	$this  -> MODEL('page');
	    
	    
	    $pages			=	$pageM -> pageList('company', $where, $pageurl, $_GET['page']);
	    
	    //分页数大于0的情况下 执行列表查询
	    if($pages['total'] > 0){
	        
	        //limit order 只有在列表查询时才需要
	        if($_GET['order']){
	            
	            $where['orderby']		=	$_GET['t'].','.$_GET['order'];
	            $urlarr['order']		=	$_GET['order'];
	            $urlarr['t']			=	$_GET['t'];
	        }else if($_GET['time'] == '5'){
	            
	            $where['orderby']		=	array('vipetime,desc','uid,desc');
	            
	        }else{
	            
	            $where['orderby']		=	array('r_status,asc','uid,desc');
	        }
	        
	        $where['limit']				=	$pages['limit'];
 	        
	        $ListNew    =   $ComM -> getList($where,array('utype'=>'admin'));
	        unset($where['limit']);
	        
	        session_start();
	        
	        $_SESSION['comXls'] = $where;
	        
	        $this -> yunset(array('rows'=>$ListNew['list']));
	        
	    }

	    //提取分站内容
	    $cacheM					         =	$this -> MODEL('cache');
	    $domain					         =	$cacheM	-> GetCache('domain');
	    
	    $this -> yunset('Dname', $domain['Dname']);
	    
	    //提取顾问信息
	    $adminM                        =   $this->MODEL('admin');
	    
		if($this->config['did']>0){
			
			$gwhere['did']	=	$this->config['did'];
		}
		$gwhere['is_crm']	=	'1';
 	    $gwInfo  =   $adminM -> getList($gwhere);
	    $this -> yunset('gwInfo',$gwInfo);
	    
	    $this -> set_search();
	    $this -> yunset('today', $toDay);
        $this -> yuntpl(array('admin/admin_company'));
	}
	
	/**
	 * @desc   后台企业列表 --  添加企业  -- 提交表单
	 */
	function add_action(){
	    
	    $cacheM     =   $this->MODEL('cache');
	    
	    $options    =   array('com','city','hy');
	    
	    $cache      =   $cacheM -> GetCache($options);
	    
	    $this       ->  yunset('cache',  $cache);
	    
	    $ratingM    =   $this -> MODEL('rating');
	    
	    $rating_list   =   $ratingM -> getList(array( 'category' => '1'), array('field'=>'`id`,`name`'));
	    
	    
	    if ($_POST['submit']) {
	        
	        if($_POST['username']==''||mb_strlen($_POST['username'])<2||mb_strlen($_POST['username'])>16){
	            
	            $this -> ACT_layer_msg('用户名格式错误',8);
	            
	        }elseif($_POST['password']==''||mb_strlen($_POST['password'])<6||mb_strlen($_POST['password'])>20){
	            
	            $this -> ACT_layer_msg('密码格式错误',8);
	            
	        } 
	        
	        $mPost = array(
	            'username' =>  trim($_POST['username']),
	            'moblie'   =>  trim($_POST['moblie']),
	            'email'    =>  trim($_POST['email'])
	        );
	        
	        $userinfoM  =  $this -> MODEL('userinfo');
	        
	        $result  =  $userinfoM -> addMemberCheck($mPost);
	        
	        if ($result['msg']){
	            
	            $this -> ACT_layer_msg($result['msg'],8);
	        }
	        
	        if($this->config['sy_uc_type']=='uc_center'){
	            
	            $this -> obj-> uc_open();
	            
	            $user  =  uc_get_user($_POST['username']);
	            
	            if(is_array($user)){
	                
	                $this -> ACT_layer_msg('该会员已经存在！',8);
	                
	            }
	            
	        }
	        $time  =  time();
	        $ip    =  fun_ip_get();
	        $pass  =  $_POST['password'];
	        
	        if($this->config['sy_uc_type'] == 'uc_center'){
	            
	            $uid  =  uc_user_register($_POST['username'],$pass,$_POST['email']);
	            
	            if($uid < 0){
	                
	                switch($uid){
	                    
	                    case '-1' : $data['msg']='用户名不合法!';
	                    break;
	                    case '-2' : $data['msg']='包含不允许注册的词语!';
	                    break;
	                    case '-3' : $data['msg']='用户名已经存在!';
	                    break;
	                    case '-4' : $data['msg']='Email 格式有误!';
	                    break;
	                    case '-5' : $data['msg']='Email 不允许注册!';
	                    break;
	                    case '-6' : $data['msg']='该 Email 已经被注册!';
	                    break;
	                }
	                
	                $this -> ACT_layer_msg($data['msg'],8);
	                
	            }else{
	                
	                list($uid, $username, $email, $password, $salt)    =   uc_get_user($_POST['username'], $pass);
	                
				}
				
	        }else{
	            $pwdRes    =   $userinfoM -> generatePwd(array('password' => $pass));
	            
	            $salt      =  $pwdRes['salt'];
	            $password  =  $pwdRes['pwd'];
	            
	        }
	        
	        $mdata = array(
	            
	            'username'  =>  trim($_POST['username']),
	            'password'  =>  $password,
	            'usertype'  =>  2,
	            'salt'      =>  $salt,
	            'address'   =>  $_POST['address'],
	            'moblie'    =>  $_POST['moblie'],
	            'email'     =>  $_POST['email'],
	            'reg_date'  =>  $time,
	            'reg_ip'    =>  $ip,
	            'status'    =>  1
	            
	        );
	           
            if($_POST['areacode'] && $_POST['telphone']){
        
                $_POST['phone'] =   $_POST['areacode'].'-'.$_POST['telphone'];
        
                if($_POST['exten']){
                    
                    $_POST['phone'] .= '-'.$_POST['exten'];
            
                }
            }
            
 	        $udata = array(
	            'name'         =>  $_POST['name'],
	            'shortname'    =>  $_POST['shortname'],
	            'hy'           =>  $_POST['hy'],
	            'pr'           =>  $_POST['pr'],
	            'mun'          =>  $_POST['mun'],
	            'provinceid'   =>  $_POST['provinceid'],
	            'cityid'       =>  $_POST['cityid'],
	            'three_cityid' =>  $_POST['three_cityid'],
	            'address'      =>  $_POST['address'],
	            'linkman'      =>  $_POST['linkman'],
	            'linktel'      =>  $_POST['moblie'],
	            'linkphone'    =>  $_POST['phone'],
	            'linkmail'     =>  $_POST['email'],
	            'content'      =>  $_POST['content'],
				'lastupdate'   =>  time(),
				'r_status'     =>  1
	        );
	        
	        $sdata 				= 	array(
	            
	            'integral'     =>  intval($_POST['integral']),
	            'rating'       =>  intval($_POST['rating_name'])
	            
	        );
			
	        $nid  =  $userinfoM -> addInfo(array('mdata' => $mdata,'udata' => $udata, 'sdata' => $sdata));
	        
	        if($nid > 0){
	            
	            $this->ACT_layer_msg('企业会员(ID:'.$nid.')添加成功！',9,'index.php?m=admin_company');
	            
	        }else{
	            
	            $this->ACT_layer_msg('企业会员添加失败，请重试！',8);
	        }
	    }
	    
	    $this->yunset("rating_list",$rating_list);
	    
	    $this->yuntpl(array('admin/admin_member_comadd'));
	    
	}
	
	/**
	 * @desc 企业列表 修改操作
	 */
    function edit_action(){
	    
        if($_GET['id']){
		
            
            $uid            =   intval($_GET['id']);
            
            $ComM           =   $this -> MODEL('company');
            $row            =   $ComM ->getInfo($uid, array('edit'=>1));
            
            $UserinfoM      =   $this -> MODEL('userinfo');
            $com_info       =   $UserinfoM -> getInfo(array('uid'=>$uid));
            
            $StatisM        =   $this -> MODEL('statis');
            $statis         =   $StatisM -> getInfo($uid, array('usertype'=>2));
            
            $ratingM        =   $this -> MODEL('rating');
            $rating_list    =   $ratingM -> getList(array( 'category' => '1'), array('field'=>'`id`,`name`'));
            
            $cacheM         =   $this->MODEL('cache');
            $options        =   array('com','city','hy');
            $cache          =   $cacheM -> GetCache($options);
            
            $this -> yunset(array('cache' => $cache, 'statis' => $statis, 'com_info' => $com_info, 'rating_list' => $rating_list, 'row' => $row, ''));
 	        $this->yunset("rating",$_GET['rating']);
	        $this->yunset("lasturl",$_SERVER['HTTP_REFERER']);
 	        
	    }
	    $this->yuntpl(array('admin/admin_member_comedit'));
	}
	
	/**
	 * @desc   后台企业列表 --  修改 --点击修改会员级别
	 */
	function rating_action(){
	    
	    $ratingid       =   intval($_POST['ratingid']);
	    $uid            =   intval($_POST['uid']);
	    
	    $statisM        =   $this -> MODEL('statis');
	    
	    $statis         =   $statisM -> getInfo($uid, array('usertype'=>2));
	    
	    $ratingM        =   $this -> MODEL('rating');
	    
	    $rating_info    =   $ratingM -> getInfo(array( 'id' => $ratingid));
	        
	    if($ratingid    !=  $statis['rating']){
	        
	        if($statis && is_array($statis)){
	            
	            $value  =   $ratingM -> ratingInfo($ratingid, $uid);
	            
	            $statisM -> upInfo($value , array('uid' => $uid, 'usertype' => 2, 'adminedit' => '1', 'info' => $rating_info));
	            
	            $this -> MODEL('log') -> addAdminLog("企业会员(ID".$_POST['uid'].")更新为【".$rating_info['name']."】");
	            
	        } 
	        
	        echo 1;die;
	        
	    }else{
	        
	        echo 0;die;
	        
		}
		
	}
	
	/**
	 * @desc 后台企业列表 -- 修改 -- 修改用户名
	 */
	function saveusername_action(){
	    
	    $M	=	$this -> MODEL("userinfo");
	    
	    $postData  =   $_POST;
	    
	    $postData['admin']	=	'1';
	    
	    $result    =   $M -> saveUserName($postData);
	    
	    if($result['errcode'] != '1'){
	        
	        echo $result['error']=='101' ? 2 : 1; die;
	        
	    } else{
	        $sysmsgM    =   $this -> MODEL('sysmsg');
	        
	        $sysmsgM    ->  addInfo(array('uid' => @explode(',',$_POST['uid']),'usertype'=>2, 'content' => '管理员为您修改用户名：<a href="comtpl,'.$_POST['uid'].'">'.trim($_POST['username'].'</a>')));
	        
	        echo 0;die;
	    }
	    
	}
	
	/**
	 * @desc   后台企业列表 --  修改 -- 基本资料 -- 提交表单
	 */
	function comeditsave_action(){

		$companyM	=	$this->MODEL('company');
	     
		if($_FILES['file']['tmp_name']){
			$upArr    =  array(
				'file'  =>  $_FILES['file'],
				'dir'   =>  'company'
			);
		}

		$uploadM	=	$this -> MODEL('upload');
		
		if(!empty($upArr)){
			$pic	=	$uploadM->newUpload($upArr);
		}
		
		if(!empty($pic['picurl'])){
			$picture	=	$pic['picurl'];
		}
	    
		$comData   =  array(
	        'name' 			=> 	$_POST['name'],
	        'shortname' 	=> 	$_POST['shortname'],
	        'hy' 			=> 	$_POST['hy'],
	        'pr' 			=> 	$_POST['pr'],
	        'mun' 			=> 	$_POST['mun'],
	        'provinceid' 	=>	$_POST['provinceid'],
	        'cityid' 		=> 	$_POST['cityid'],
	        'three_cityid' 	=> 	$_POST['three_cityid'],
	        'address' 		=>	$_POST['address'],
	        'linkman'		=> 	$_POST['linkman'],
	        'linktel'		=>	$_POST['moblie'],
	        'linkphone'		=> 	$_POST['linkphone'],
	        'linkmail' 		=> 	$_POST['email'],
	        'sdate' 		=> 	$_POST['sdate'],
	        'moneytype' 	=> 	$_POST['moneytype'],
	        'money' 		=>	$_POST['money'],
	        'linkjob' 		=> 	$_POST['linkjob'],
	        'linkqq' 		=> 	$_POST['linkqq'],
	        'website' 		=> 	$_POST['website'],
	        'busstops' 		=> 	$_POST['busstops'],
	        'infostatus' 	=> 	$_POST['infostatus'],
	        'welfare' 		=> 	$_POST['welfare'],
			'r_status' 		=> 	$_POST['r_status'],
	        'lastupdate'	=>  time(),
	        'content'		=> 	str_replace(array('&amp;','background-color:#ffffff','background-color:#fff','white-space:nowrap;'),array('&','background-color:','background-color:','white-space:'),$_POST['content']),
	        'admin_remark'  =>  $_POST['admin_remark'],
			'comqcode'		=>	$picture,
	    );
        
	    $row	=	$companyM -> getInfo($_POST['uid'], array('edit'=>1));
        
		if($row['comqcode'] && $picture==""){
			$comData['comqcode']	=	$row['comqcode'];
		}
		
		//	处理账号信息
		$mData      =   array(
	        
	        //'username'      =>  trim($_POST['username']),
	        //'password'      =>  $_POST['password'],
	        'email'         =>  $_POST['email'],
	        'moblie'        =>  $_POST['moblie'],
	        //'status'        =>  $_POST['status'],
	        'address'       =>  trim($_POST['address']),
	        //'lock_info'     =>  $_POST['lock_info']
	    );
	   

	    $return    =  $companyM->setCompany(array('uid'=>intval($_POST['uid'])),array('mData'=>$mData,'comData'=>$comData,/*'sData'=>$sData,*/'utype'=>'admin'));
        
        delfiledir('../data/upload/tel/'.$_POST['uid']);
        
        $lasturl  =  str_replace('&amp;','&',$_POST['lasturl']);
        
        if ($return['errcode'] == 8){
            
            $this->ACT_layer_msg($return['msg'], 8);
        }else{
            
            $this->ACT_layer_msg($return['msg'], $return['errcode'], $lasturl, 2, 1);
		}
		
	}

	/**
	 * @desc 后台企业列表 --  修改 -- 会员套餐 -- 提交表单
	 */
	 function saveRating_action(){
	 
	 	if($_POST){

			$cuid		=	intval($_POST['uid']);

			$comM		=	$this->MODEL('company');

			$sData		=	array(
			
				'rating'            =>  $_POST['ratingid'],
				'vip_etime'         =>  $_POST['vip_etime'] ? strtotime($_POST['vip_etime']) : 0,
				'job_num'           =>  intval($_POST['job_num']),
				'breakjob_num'      =>  intval($_POST['breakjob_num']),
				'down_resume'       =>  intval($_POST['down_resume']),
				'invite_resume'     =>  intval($_POST['invite_resume']),

 				'zph_num'           =>  intval($_POST['zph_num']),
				'top_num'			=>	intval($_POST['top_num']),
				'urgent_num'		=>	intval($_POST['urgent_num']),
				'rec_num'			=>	intval($_POST['rec_num']),

				'sons_num'			=>	intval($_POST['sons_num'])
			);
			
			$return		=	$comM -> setStatisInfo($cuid, $sData);
			
			if ($return['errcode'] == 8){
            
				$this->ACT_layer_msg($return['msg'], 8);
			}else{
				
				$this->ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
			}
		}
		
	}

	/**
	 * @desc 后台企业列表 --  修改 -- 账户信息 -- 提交表单
	 */
	 function saveUser_action(){
	 
	 	if($_POST){

			$cuid		=	intval($_POST['uid']);

			$userInfoM	=	$this->MODEL('userinfo');

			$data		=	array(
			
				'username'	=>	$_POST['username'],
				'password'	=>	$_POST['password'],
				'status'	=>	$_POST['status'],
				'lock_info'	=>	$_POST['lock_info']
			);

			$result		=	$userInfoM -> addMemberCheck($data, $cuid, 'admin');

			if(!empty($result['msg'])){

				$this->ACT_layer_msg($result['msg'], 8);
			}else{
				
				$return =	$userInfoM -> upInfo(array('uid' => $cuid), $data);

				$this->ACT_layer_msg('更新成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1);
			}
			 
		}
		
	 }
	
	/**
	 * @desc   后台企业列表 -- 会员审核
	 */
	function status_action(){
	    
	    $userinfoM  =  $this -> MODEL('userinfo');

	    $post       =  array(

			'status'     =>  intval($_POST['status']),
			
	        'lock_info'  =>  trim($_POST['statusbody'])
	    );
	   
        $uids       =   @explode(',', $_POST['uid']);
 
        $return     =   $userinfoM -> status(array('uid' => array('in', pylode(',', $uids)),'usertype'=>2), array('post' => $post));
		
		if($return['errcode']==9){
			
			$this  ->  ACT_layer_msg($return['msg'],$return['errcode'],"index.php?m=admin_company",2,1);
	
		}else{
			
			$this  ->  ACT_layer_msg($return['msg'],$return['errcode']);
		
		} 
	
	}
	
	/**
	 * @desc   企业列表 删除
	 */
	function del_action(){
	    
		if($_GET['del']){

			$this -> check_token();
			
			if (is_array($_GET['del'])){
	        
				$uid	=	$_GET['del'];
			}else{

				$uid	=	intval($_GET['del']);
			}

	    }else if($_POST['del']){
			
			$uid	=	$_POST['del'];
		}

        $userinfoM  =   $this -> MODEL('userinfo');
	    
        $return     =   $userinfoM -> delInfo($uid, 2, $_POST['delAccount']);
	    
	    $this -> ACT_layer_msg($return['msg'],$return['errcode'],$_SERVER['HTTP_REFERER'],2,1);
	}
	
	/**
	 * @desc 后台企业列表  --  修改会员等级  返回当前会员套餐数据
	 */
	function getstatis_action(){
	    
        $statisM    =   $this -> MODEL('statis');
		
		$ComM		=   $this -> MODEL('company');
	    
        if($_POST['uid']){
			
            $uid	=	intval($_POST['uid']);        
		    
		    $rating	=	$statisM -> getInfo($uid, array('usertype'=>'2'));
			
			$hotjob	=	$ComM -> getHotJob(array('uid'=>$uid));
			
			if($hotjob['time_start']<=time() && $hotjob['time_end']>=time()){
				$rating['hotjob']     =  1;
			}else{
				$rating['hotjob']     =  2;
			}
			
			if($rating['vip_etime']>0){
			    
				$rating['vipetime']     =   date('Y-m-d',$rating['vip_etime']);
				
			}else{
			    
				$rating['vipetime']     =   '不限';
				
			}
			echo json_encode($rating);
		}
	}
	
	/**
	 * @desc 后台企业列表  --  修改会员等级  -- 弹出框  --选择会员等级返回套餐时间
	 */
	function getrating_action(){
	    
        $ratingM        =   $this -> MODEL('rating');
	    
		if($_POST['id']){
            $statisM    =   $this->MODEL('statis');
			
            $id         =   intval($_POST['id']); 
			
			$uid		=   intval($_POST['uid']);
        		    
            $rating	    =   $ratingM -> changeRatingInfo($id,$uid);
			
			if($rating['vip_etime']>0){
			    
				$rating['oldetime'] = $rating['vip_etime'];
				$rating['vipetime'] = date('Y-m-d',$rating['vip_etime']);
				
			}else{
			    
				$rating['oldetime'] = 0;
				$rating['vipetime'] = '不限';
				
			}
			echo json_encode($rating);
		}
	}
	
	/**
	 * @desc 后台企业列表  --  修改会员等级  -- 弹出框  --确认修改会员等级
	 */
	
	function uprating_action()
    {

    	
        $rid = intval($_POST['rating']);
        $uid = intval($_POST['ratuid']);

        $ratingM        =   $this->MODEL('rating');
        $rating_info    =   $ratingM->getInfo(array('id' => $rid));
        
        if (empty($rid)) {

            $this->ACT_layer_msg('请选择会员等级！', 8, $_SERVER['HTTP_REFERER']);
        } else if ($uid) {

            $statisM    =   $this->MODEL('statis');
			$statis     =   $statisM->getInfo($uid, array('usertype' => 2));
			
            
			$companyM   =   $this->MODEL('company');
			 
			$_POST['vip_etime']	=	$_POST['vipetime'] != date('Y-m-d') ? strtotime($_POST['vipetime']) : 0;
			
            $data		=   array();

			$sData		=	array('rating','rating_name','integral','vip_etime','job_num','breakjob_num','down_resume','invite_resume','zph_num','top_num','urgent_num','rec_num','sons_num');
			
			$msg		=	array();

            foreach ($_POST as $key => $value) {

				if(in_array($key, $sData)){

					$value		=	$value ? $value : 0;					

					$data[$key] =   $value;

					if($key == 'integral' && $value != $statis[$key]){

						$msg[]	=	" 会员".$this->config['integral_pricename']."：".$statis[$key]." -> ".$value;
					}else if($key == 'vip_etime' && $value != $statis[$key]){

						$vEtime	=	$value ? date('Y-m-d', $value) : '不限';
						$msg[]	=	" 会员到期时间：".$statis['vip_etime_n']." -> ".$vEtime;
					}else if($key == 'job_num' && $value != $statis[$key]){

						$msg[]	=	" 发布职位数：".$statis[$key]." -> ".$value;
					}else if($key == 'breakjob_num' && $value != $statis[$key]){

						$msg[]	=	" 刷新职位数：".$statis[$key]." -> ".$value;
					}else if($key == 'down_resume' && $value != $statis[$key]){

						$msg[]	=	" 下载简历数：".$statis[$key]." -> ".$value;
					}else if($key == 'invite_resume' && $value != $statis[$key]){

						$msg[]	=	" 邀请面试数：".$statis[$key]." -> ".$value;
					}else if($key == 'zph_num' && $value != $statis[$key]){

						$msg[]	=	" 招聘会报名：".$statis[$key]." -> ".$value;
					}else if($key == 'top_num' && $value != $statis[$key]){

						$msg[]	=	" 职位置顶数：".$statis[$key]." -> ".$value;
					}else if($key == 'urgent_num' && $value != $statis[$key]){

						$msg[]	=	" 紧急招聘数：".$statis[$key]." -> ".$value;
					}else if($key == 'rec_num' && $value != $statis[$key]){

						$msg[]	=	" 职位推荐数：".$statis[$key]." -> ".$value;
					}
				}
            }
			
			if(!empty($msg)){
				$msgContent	=	@implode('，', $msg);
			}

            $data['rating_type']	=	$rating_info['type'];
            
            $data['vip_stime']		=	$statis['vip_stime'] ? $statis['vip_stime'] : time();


            $company	=	$companyM->getInfo($uid);
            
            if ($_POST['hotjob']) {
                if (empty($company['name'])) {
                    
                    $this->ACT_layer_msg('请先完善企业资料！', 8, $_SERVER['HTTP_REFERER']);
                }
            }

            if ($statis['rating'] != $_POST['rating'] || ($statis['rating'] == $_POST['rating'] && !isVip($statis['vip_etime']) && (int) $_POST['addday'] == 0)) {

                $data['vip_stime']  =   time();

                $statisM -> upInfo($data, array('uid' => $uid, 'usertype' => 2, 'adminedit' => '1', 'info' => $rating_info));
			
				$sName		=	!isVip($statis['vip_etime']) ? '过期会员' : $statis['rating_name'];
				
                $mContent	=	'会员等级：'.$sName.' -> '.$rating_info['name'];
				
				$msgContent	=	$msgContent ? $mContent.'，'.$msgContent : $mContent;	
                
            } else {
                
                unset($data['rating']);
                
                $statisM -> upInfo($data, array('uid' => $uid, 'usertype' => 2));
				
				$msgContent	=	$msgContent;
            }

            if (!empty($spids) && !empty($sonData)) {

                $this->obj->update_once('company_statis', $sonData, array('uid' => array('in', pylode(',', $spids))));
            }

            if ($_POST['hotjob']) {
                
                $hotData    =   array(
                    'uid'           =>  $uid,
                    'username'      =>  $company['name'],
                    'rating'        =>  $data['rating_name'],
                    'time_start'    =>  $data['vip_stime'] ? $data['vip_stime'] : time(),
                    'time_end'      =>  $data['vip_etime'] == 0 ? strtotime("2029-12-30") : $data['vip_etime'],
                    'rating_id'     =>  $data['rating'],
                    'did'           =>  $company['did']
                );
                $hotjob     =   $companyM->getHotJob(array('uid' => $uid));

                if (!empty($hotjob)) {

                    $hotData['sort']        =   $hotjob['sort'];
                    $companyM -> upHotJob($uid, $hotData);
                } else {
                    
                    $hotData['uid']         =   $uid;
                    $hotData['username']    =   $company['name'];
                    $hotData['did']         =   $company['did'];
                    $companyM->addHotJob($hotData);
                }
            } else {
                
                if ($company['rec'] == 1) {
                    
                    $companyM->delHotJob($uid);
                }
            }

            $companyData = array(
                
                'rating'        =>  $_POST['rating'],
                'rating_name'   =>  $_POST['rating_name'],
                'vipetime'      =>  $_POST['vip_etime']
            );

            if ($data['vip_stime']) {
                $companyData['vipstime']    =   $data['vip_stime'];
            }

            if ($_POST['hotjob']) {
                
                $companyData['hotstart']    =   $data['vip_stime'];
                $companyData['hottime']     =   $data['vip_etime'] == 0 ? strtotime("2029-12-30") : $data['vip_etime'];
            } else {
                
                $companyData['hotstart']    =   0;
                $companyData['hottime']     =   0;
            }
            
            $companyM->upInfo($uid, '', $companyData);

			$logM	=	$this->MODEL('log');

			if($msgContent){

				$msgContent	=	'企业(ID:' . $uid . ')修改成功；'.$msgContent;
				$logM -> addAdminLog($msgContent);
			}

            $this->ACT_layer_msg('企业(ID:' . $uid . ')修改成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1);
        } else {
            
            $this->ACT_layer_msg('非法操作！', 8, $_SERVER['HTTP_REFERER']);
        }
    }
	
	/**
	 * @desc  查询会员审核状态原因
	 */
	function lockinfo_action(){
		
		
	    $userinfoM  =  $this -> MODEL('userinfo');
	    
	    $member     =  $userinfoM -> getInfo(array('uid'=>$_POST['uid']),array('field'=>'lock_info'));
	    
	    echo trim($member['lock_info']);die;
	}
	
	/**
	 * @desc 分配顾问
	 */
	function checkguwen_action(){
	    
        $ComM       =   $this->MODEL('company');

        $adminM     =   $this->MODEL('admin');

        $gid        =   intval($_POST['gid']);

        $comid      =   trim($_POST['comid']);

        $uid        =   @explode(',', $comid);

        $uids       =   pylode(',', $uid);
    
        $crmUser    =   $adminM->getAdminUser(array( 'uid' => $gid ));

        if (! is_array($crmUser)) {
            $this -> ACT_layer_msg('请选择分配顾问！', 8, $_SERVER['HTTP_REFERER']);
        }

        $gData      =   array( 'crm_uid' => $gid, 'crm_time' => time() );

        $whereData  =   array( 'crm_uid' => $gid, 'uid' => $uids );
        
        $arr        =   $ComM->setComGw($gData, $whereData);

        $this->ACT_layer_msg($arr['msg'], $arr['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
    }
 	
	/**
	 * @desc 会员企业列表，点击企业用户名成，跳转企业会员中心
	 * 
	 * @param  $_GET['type']，跳转招聘统计分析页面
	 */
	function Imitate_action(){
	    
	    $userinfoM  =  $this->MODEL('userinfo');
		
	    $member     =  $userinfoM -> getInfo(array('uid'=> intval($_GET['uid'])),array('field'=>'`uid`,`username`,`salt`,`email`,`password`,`usertype`,`did`'));
	    
	    $this -> cookie->unset_cookie();
	    
	    $this -> cookie->add_cookie($member['uid'],$member['username'],$member['salt'],$member['email'],$member['password'],2,$this->config['sy_logintime'],$member['did']);
		
		$typeStr 	=	trim($_GET['type']);
		
		$url		=	'';
		
	    if(!empty($typeStr)){
	        
	        if ($typeStr == 'job') {
	        
	            $url = 'index.php?c='.$typeStr;	
	        }else{
	            
	            $url = 'index.php?c='.$typeStr;	
	        }
					
		}
		$logM  		=  $this->MODEL('log');
		
		$content	=	'管理员'.$_SESSION['ausername'].'登录个人账户'.$member['username'].'成功！';
		
		$adminLo	=	$logM -> addAdminLog($content);
		
		header('Location: '.$this->config['sy_weburl'].'/member/'.$url);
	}
	
	/**
	 * @desc  导出企业数据
	 */
	function xls_action(){
	    
	    session_start();
	    
	    $where = $_SESSION['comXls'] ? $_SESSION['comXls'] : array('orderby'=>'uid');
	    
	    if(!empty($_POST['type'])){
	        
	        foreach($_POST['type'] as $v){
	            
	            if($v == 'lastdate'){
	                
	                $type[]  =  'lastupdate';
	                
	            }else if ($v == 'money'){
	                
	                $type[]  = 'money';
	                $type[]  = 'moneytype';  
	                
	            }else if ($v != 'rating' && $v != 'vip_stime'){
	                
	                $type[]  =  $v;
	                
	            }
	            
	        }
	        
	        $field  =  @implode(',', $type).',uid';
	        
	    }else{
	        
	        $field  =  'uid';
	        
	    }
	    
	    if($_POST['uid']){
	        
	        $uids          =  @explode(',',$_POST['uid']);
	        
	        $where['uid']  =  array('in',pylode(',',$uids));
	        
	    }
	    if($_POST['limit']){
	        
	        $where['limit']  =  intval($_POST['limit']);
	    }
	    
	    $ComM      =   $this -> MODEL('company');
	    
	    
	    
	    $listNew   =   $ComM -> getList($where,array('statis'=>1,'cache'=>1,'field'=>$field));
	    
	    $list      =   $listNew['list'];
	    $cache     =   $listNew['cache'];
	    
	    if (!empty($list)){
	        
	        $this->yunset('list',$list);
	        $this->yunset('cache',$cache);
	        $this->yunset('type',$_POST['type']);
	        
	        $this->MODEL('log')->addAdminLog('导出公司信息');
	        header("Content-Type: application/vnd.ms-excel");
	        header("Content-Disposition: attachment; filename=company.xls");
	        $this->yuntpl(array('admin/admin_company_xls'));
	    }
 	}
	
	/**
	 * @desc  企业列表  分站设置
	 */
	function checksitedid_action(){
	    
	    $uid		 =	trim($_POST['uid']);
	    $did		 =	intval($_POST['did']);
	    
	    if(empty($uid)){
	        
	        $this -> ACT_layer_msg('参数不全请重试！', 8);
	    }
	    
	    $uids		 =	@explode(',',$_POST['uid']);
	    $uid 		 =	pylode(',',$uids);
	    
	    if(empty($uid)){
	        
	        $this -> ACT_layer_msg('请正确选择需分配用户！', 8);
	    }
	    
	    $siteM       =  $this->MODEL('site');
	    
	    $didData	 =    array('did' => $did);
	    
	    $Table = array(
            'member',
            'company',
            'company_statis',
            'company_job',
            'company_cert',
            'company_news',
            'company_order',
            'company_product',
            'partjob',
            'hotjob'
        );
	    
	    $siteM -> updDid(array('report'), array('p_uid' => array('in', $uid),'usertype'=>2), $didData);
	    
	    $siteM -> updDid(array('userid_msg'), array('fid' => array('in', $uid)),$didData);
	    
	    $siteM -> updDid(array('company_pay','look_resume'),array('com_id'=>array('in', $uid),'usertype'=>2),$didData);
	    
	    $siteM -> updDid(array('down_resume'),array('comid'=>array('in', $uid),'usertype'=>2),$didData);

	    $siteM -> updDid($Table,array('uid'=>array('in', $uid)),$didData);
	    
	    $this->ACT_layer_msg('会员(ID:'.$_POST['uid'].')分配站点成功！', 9, $_SERVER['HTTP_REFERER'],2,1);
	    
	}
	
	/**
	 * @desc 后台    - 企业列表  - 更多操作
	 */
	function getinfo_action(){
	    
	    if($_POST['comid']){
	        
	        $comid =   intval($_POST['comid']);
	        
	        $ComM  =   $this -> MODEL('company');
	        
	        $info  =   $ComM -> getInfo($comid);
 	        
	        $uid   =   intval($info['uid']);
	        
	        $UsernfoM  =   $this -> MODEL('userinfo');
	        
	        $member    =   $UsernfoM -> getInfo(array('uid'=> $uid), array('field'=>'username, reg_ip, status'));
	        
	        $statisM   =   $this -> MODEL('statis');
	        
	        $statis    =   $statisM -> getInfo($uid, array('usertype'=>'2', 'field'=>'rating'));

	        $yyzz      =   $ComM -> getCertInfo(array('uid'=>$uid, 'type'=>'3'), array('field'=>'`check`'));
	        
	        if ($info['crm_name']){
				
	            $info['adviser']    =   $info['crm_name'];
	        }else{
                $info['adviser']    =   null;
	        }
            $info['username']       =   $member['username'];
            $info['reg_ip']         =   $member['reg_ip'];
            $info['status']         =   $member['status'];
            $info['rating']         =   $statis['rating'];
            $info['yyzzurl']        =   $yyzz['old_check'];
            
            if ($info['linktel']){
                
                $info['phone']      =    $info['linktel'];
                
            }else{
                
                $info['phone']      =    $info['linkphone'];
                
            }
            $comOrderM              =   $this -> MODEL('companyorder');
            
            $integralNum            =   $comOrderM -> getCompanyPayNum(array('com_id'=>$uid, 'type'=>'1' ,'usertype'=>'2'));
            
            $info['integralNum']    =   $integralNum;
            
            $orderNum               =   $comOrderM -> getCompanyOrderNum(array('uid'=>$uid , 'usertype' => 2));
            
            $info['orderNum']       =   $orderNum;
            
            
            $downResumeM            =   $this -> MODEL('downresume');
            
            $downNum                =   $downResumeM -> getDownNum(array('comid'=>$uid,'usertype'=>2));
            
            $info['downNum']        =   $downNum;
            
            $jobM                   =   $this -> MODEL('job');
            
            $applyNum               =   $jobM -> getSqJobNum(array('com_id'=>$uid));
            
            $info['applyNum']       =   $applyNum;
			
            $inviteNum              =   $jobM -> getYqmsNum(array('fid'=>$uid));
            
            $info['inviteNum']      =   $inviteNum;
            
            $showNum                =   $ComM -> getComShowNum(array('uid'=>$uid));
            
            $info['showNum']        =   $showNum;
			
            $jobNum                 =   $jobM -> getJobNum(array('uid'=>$uid));
            
            $info['jobNum']         =   $jobNum;
			
			
            echo json_encode($info);
            
	    }
	}
	
	/**
	 * @desc 日志高级搜索
	 */
	function log_search(){
	    
        $opera          =   array('1'=>'职位','9'=>'兼职','2'=>'财务','3'=>'下载简历','23'=>'举报','4'=>'邀请面试','5'=>'收藏关注','6'=>'申请报名','7'=>'基本信息','8'=>'修改密码','11'=>'修改账号','13'=>'认证绑定','12'=>'账号解绑','14'=>'招聘会/专题','15'=>'地图设置','16'=>'上传图片','17'=>'积分兑换','18'=>'消息','19'=>'问答','24'=>'优惠券','26'=>'浏览/屏蔽');
        $parr           =   array('1'=>'增加','2'=>'变更','3'=>'删除','4'=>'刷新');
        $ad_time        =   array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月');
        
        $search_list    =   array();
        $search_list[]  =   array('param'=>'operas','name'=>'操作类型','value'=>$opera);
        $search_list[]  =   array('param'=>'parrs','name'=>'操作内容','value'=>$parr);
        $search_list[]  =   array('param'=>'end','name'=>'操作时间','value'=>$ad_time);
        
		$this->yunset('search_list',$search_list);
	}
	
	/**
	 * @desc 企业会员日志
	 */
	function member_log_action(){
	    
		$this->log_search();
		
        $where['usertype']  =   '2';
		
 		if(intval($_GET['uid']) > 0){
 		    
            $where['uid']   =   intval($_GET['uid']);
 		    
            $UserinfoM      =   $this -> MODEL('userinfo');
            
            $uinfo          =   $UserinfoM -> getInfo(array('uid'=> $where['uid']) , array('field' => 'username'));
 		    
            $this -> yunset('uinfo',$uinfo);
			
            $urlarr['uid']  =   intval($_GET['uid']);
			
		}

		if($_GET['keyword']){
		    
            $type           =   intval($_GET['type']);
		    
            $keyword        =   trim($_GET['keyword']);
		    
            if($type == 1){
                
                $where['uid']   =   array('like', $keyword);
                
			}else if($type == 2){
			    
                $ComM           =   $this->MODEL('company');
			    
                $listC          =   $ComM -> getList( array('name' => array('like', $keyword)),array('field'=>'uid'));
			    
                $minfo          =   $listC['list'];
                
                if ($minfo){
			        
                    $uids       =   array();
			        
                    foreach($minfo as $mv){
			            
                        $uids[] =  $mv['uid'];
			        }
			        
                    $where['uid']   =   array('in', pylode(',', $uids));
			    }
				
			}else if($type == 3){
			    
			    $where['content']    =   array('like', $keyword);
				
			}
			
			$urlarr['type']      =   $type;
			$urlarr['keyword']   =   $keyword;
			
		}
		
		if($_GET['operas']){
		    
		    $operas   =   intval($_GET['operas']);
        
		    
		    $where['PHPYUNBTWSTART_A']     =  '';
		    $where['opera']         =  $operas;
		    if ($operas == 2) {
		        
		        $where['opera']     =  '88';
		        $where['content']   =  array('like','订单','OR');
		        
		    }elseif ( $operas == 26 ){
		        
		        $where['content']   =  array('like','浏览','OR');
		        
		    }elseif ( $operas == 24){
		        
		        $where['content']   =  array('like','优惠券','OR');
		        
		    }elseif ( $operas == 23){
		        
		        $where['content']   =  array('like','举报','OR');
		        
		    }elseif ( $operas == 19){
		        
		        $where['content']   =  array('like','问答','OR');
		        
		    }elseif ( $operas == 13){
		        
		        $where['content']   =  array('like','认证','OR');
		        
		    }elseif ( $operas == 6){
		        
		        $where['content']   =  array('like','申请','OR');
		        
		    }elseif ( $operas == 5){
		        
		        $where['content']   =  array('like','收藏','OR');
		        
		    }
		    
		    $where['PHPYUNBTWEND_A']  =  '';
		    
        $urlarr['operas']       =  $operas;
		    
		}
	
		if($_GET['parrs']){
		    
            $where['type']          =   intval($_GET['parrs']);
		    
            $urlarr['parrs']        =   $_GET['parrs'];
		}
		
		

	    if($_GET['end']){
	        
	        if($_GET['end']=='1'){
	            
	            $where['ctime']  =   array('>=', strtotime(date("Y-m-d 00:00:00")));
	            
	        }else{
	            
	            $where['ctime']   =   array('>=', '-'.strtotime((int)$_GET['end'].'day'));
	            
	        }
	        
            $urlarr['end']          =   $_GET['end'];
	    }
         
        $urlarr['c']    =   'member_log';
		
        $urlarr['page'] =   "{{page}}";
	    
        $pageurl        =   Url($_GET['m'], $urlarr, 'admin');
	    
        //提取分页
        $pageM          =	$this  -> MODEL('page');
        $pages          =	$pageM -> pageList('member_log', $where, $pageurl, $_GET['page']);
        
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            
            //limit order 只有在列表查询时才需要
            if($_GET['order']){
                
                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];
                
            }else{
                
                $where['orderby']		=	array('id,desc');
                
            }
            
            $where['limit']         =	$pages['limit'];
            
            $logM       =   $this -> MODEL('log');
            
            $List       =   $logM -> getMemlogList($where,array('utype'=>'admin'));
            
            $this -> yunset(array('rows' => $List));
        }
        
	    $this->yuntpl(array('admin/admin_company_member_log'));
	}
    
	/**
	 * @desc 会员日志删除操作
	 */
	function memberlogdel_action(){
	    
	    if (is_array($_GET['del'])){
	        
	        $id        =   pylode(',', $_GET['del']);
	        
	        $where     =   array('id' => array('in', $id));
	        
	    }else{
	        
	        $this      ->  check_token();
	        
	        $where     =   array('id' => intval($_GET['del']));
	    }
	    
	    $logM    =  $this -> MODEL('log');
	    
	    $return  =  $logM -> delMemlog($where);
	    
	    $this  ->  layer_msg($return['msg'], $return['errcode'], $return['layertype'],$_SERVER['HTTP_REFERER']);
	    
	}

	/**
	 * @desc 会员-企业-企业用户列表:（重置密码）
	 */
	function reset_companypassword_action(){
	    
	    $this -> check_token();
	    
	    $userinfoM  =  $this->MODEL('userinfo');
	    
	    $userinfoM -> upInfo(array('uid'=>intval($_GET['uid'])),array('password'=>'123456'));
	    
	    $this -> MODEL('log') -> addAdminLog('会员(ID:'.$_GET['uid'].')重置密码成功');
	    
	    echo '1';
	     
	}
	
	/**
	 * @desc 会员-企业-企业列表:（统计数量）
	 */
	function companyNum_action(){
		$MsgNum=$this->MODEL('msgNum');
		echo $MsgNum->companyNum();
	}
	
	/**
	 * @desc 企业模板
	 */
	function mcomtpl_action(){
	    
	    $tplM      =   $this -> MODEL('tpl');
	    
	    $comid     =   intval($_GET['comid']);
	    
	    $where     =   array();
	    
	    $where['status']               =   '1';
	    
	    $where['PHPYUNBTWSTART_A']     =    '';
	    
	    $where[service_uid][]          =   array('=', '0','OR');
	    
	    $where[service_uid][]          =   array('findin', $comid, 'OR');
	    
	    $where['PHPYUNBTWEND_A']       =   '';
	    
	    $where['orderby']              =   'id,desc';
	    
	    $list                          =   $tplM -> getComtplList($where);
	    
	    $this->yunset("list",$list);
	    
	    $this->yunset("comid",$comid);
	    
	    $statisM   =   $this -> MODEL('statis');
	    
	    $statis    =   $statisM -> getInfo($comid, array('usertype'=>'2', 'field'=>'comtpl'));

	    $this->yunset('statis',$statis);
	
	    $this->yuntpl(array('admin/admin_company_mcomtpl'));
	}
	
	/**
	 * @desc 设置企业模板
	 */
	function msettpl_action(){
	    
	    $this      ->  check_token();
	    
	    $uid       =   intval($_GET['comid']);
	    
	    $tplM      =   $this -> MODEL('tpl');
	    
	    $id        =   intval($_GET['id']);
	    
	    $tpl       =   $tplM -> getComtpl(array('id'=>$id), array('field'=>'url'));
 
	    $statisM   =   $this -> MODEL('statis');
	    
	    $nid       =   $statisM -> upInfo(array('comtpl'=>$tpl['url']), array('uid'=>$uid, 'usertype'=>'2'));

	    if ($nid){
	        
 	        
	        $sysmsgM    =   $this -> MODEL('sysmsg');
	        
	        $sysmsgM    ->  addInfo(array('uid' => $uid, 'usertype'=>2, 'content' => '管理员为您设置企业模板：<a href="comtpl,'.$uid.'">'.trim($tpl['url'].'</a>')));
	        
 	        $this->layer_msg('设置成功！',9);
	        
	    }else{
	        
	        $this->layer_msg('设置失败！',9);
	        
	    }
	}
	
	/**
	 * @desc 职位匹配简历推送
	 */
	function directrecom_action(){
	    
	    $where  =  array(
	        'eid'      =>  intval($_GET['eid']),
	        'jobid'    =>  intval($_GET['id']),
	        'comid'    =>  intval($_GET['comid'])
	    );
	    
	    $userEntrustM  =  $this -> MODEL('userEntrust');
	    
	    $return        =  $userEntrustM -> sendRecord($where);
	    
	    $err           =   array(
	        
	        'msg'      =>  $return['msg'],
	        'type'     =>  $return['errcode']
	        
	    );
	    
	    echo json_encode($err);die;
 	    
	 }
	 //邮箱认证
	 function emaillock_action(){
			 
			//查询当前邮箱或者当前数据是否存在

			
			$CompanyM					=			$this->MODEL('company');

			$UserinfoM					=			$this->MODEL('userinfo');
			
			$uid						=			$_POST['uid'];
			
			$status                     =           $_POST['estatus'];

			if($_POST['comemail']==""){

				$this->ACT_layer_msg("请填写邮箱",8);	
	
			}elseif(CheckRegEmail($_POST['comemail'])==false){
	
				$this->ACT_layer_msg("邮箱格式错误",8);	
			}

			$rows						=			$CompanyM->getInfo($uid,array('field'=>'`email_status`'));
			if($rows){
				//进行认证管理
				$data					=			array(
					
				    'email_status'		=>			$status,

					'linkmail'			=>			$_POST['comemail']

				);

				$emaildata					=			array(

					'email'			=>  $_POST['comemail'],
          
				    'email_status'  =>  $status
	
				);

				$emailwhere['uid']			=			$uid;

				$nid					=			$CompanyM->upInfo($uid,'',$data);

				$UserinfoM->upInfo($emailwhere,$emaildata);


				if($nid){
				    if($status==1){

						$this->ACT_layer_msg("邮箱认证成功",9,$_SERVER['HTTP_REFERER']);

					}else{

						$this->ACT_layer_msg("邮箱取消认证成功",9,$_SERVER['HTTP_REFERER']);
					}
				}else{

				    if($status==1){

						$this->ACT_layer_msg("邮箱认证失败",8,$_SERVER['HTTP_REFERER']);

					}else{

						$this->ACT_layer_msg("邮箱取消认证失败",9,$_SERVER['HTTP_REFERER']);
					}
				}
			}else{
				
				$this->ACT_layer_msg("当前数据错误",8,$_SERVER['HTTP_REFERER']);
			}
	  }


	  //手机认证
	  function phonelock_action(){
			 
		//查询当前邮箱或者当前数据是否存在

		$CompanyM	 =  $this->MODEL('company');

		$UserinfoM   =  $this->MODEL('userinfo');
		
		$uid		 =  $_POST['uid'];
		
		$status      =  $_POST['mstatus'];
		//linktel
		if($_POST['comlinktel']==""){

			$this->ACT_layer_msg("请填写手机号码",8);	

		}elseif(CheckMoblie($_POST['comlinktel'])==false){

			$this->ACT_layer_msg("手机号码格式错误",8);	
		}

		$rows						=			$CompanyM->getInfo($uid,array('field'=>'`moblie_status`'));
		if($rows){
			//进行认证管理
			$data					=			array(

			    'moblie_status'		=>			$status,

				'linktel'			=>			$_POST['comlinktel'],

			);

			$mobliedata  =  array(

				'moblie'		 =>  $_POST['comlinktel'],
        
			    'moblie_status'	 =>  $status

			);

			$mobliewhere['uid']		=			$uid;

			$nid					=			$CompanyM->upInfo($uid,'',$data);

			$UserinfoM->upInfo($mobliewhere,$mobliedata);

			if($nid){
			    if($status==1){

					$this->ACT_layer_msg("手机认证成功",9,$_SERVER['HTTP_REFERER']);

				}else{

					$this->ACT_layer_msg("手机取消认证成功",9,$_SERVER['HTTP_REFERER']);
				}
			}else{
			    if($status==1){

					$this->ACT_layer_msg("手机认证失败",8,$_SERVER['HTTP_REFERER']);

				}else{

					$this->ACT_layer_msg("手机取消认证失败",9,$_SERVER['HTTP_REFERER']);
				}
			}
		}else{
			
			$this->ACT_layer_msg("当前数据错误",8,$_SERVER['HTTP_REFERER']);
		}
	}
	//批量认证
	function batchfirm_action(){

		$CompanyM	=  $this->MODEL('company');
        $UserinfoM  =  $this->MODEL('userinfo');
        $status     =  $_POST['plstatus'];
        
		if($_POST['comname_email'] ==""  && $_POST['comname_moblie']==""  && $_POST['comname_yyzz'] ==""){
			$this->ACT_layer_msg("请选择认证类型",8);
		}
		if($_POST['uid']==""){
			$this->ACT_layer_msg("非法操作",8);
		}
		if($status==""){

			$this->ACT_layer_msg("请选择认证状态",8);
		}

		if($_POST['comname_email'] || $_POST['comname_moblie']){
			$where['uid']		=		array('in',pylode(',',$_POST['uid']));

			$rows				=		$CompanyM->getChCompanyList($where,array('field'=>'`uid`,`linktel`,`linkmail`,`moblie_status`,`email_status`'));

		

			if(is_array($rows) && $rows){

				if($_POST['comname_email']){

					foreach($rows  as $val){

						if($val['linkmail'] || $val['email_status']==1){

							$emailuid[]		=		$val['uid'];	

						}

					}

					$emaildata				=		array(

					    'email_status'		=>			$status,
				
					);
					$emailwhere['uid']		 =			array('in',pylode(',',$emailuid));
        
                    $UserinfoM->upInfo($emailwhere,$emaildata);
          
					$nid					=			$CompanyM->upInfo($emailuid,'',$emaildata);

				}

				if($_POST['comname_moblie']){

					foreach($rows  as $val){
						
						if($val['linktel'] || $val['moblie_status']==1){

							$moblieuid[]		=		$val['uid'];	

						}

					}

					$mobliedata					=		array(

					    'moblie_status'			=>			$status,
				
					);
                    $mobliewhere['uid']		 =			array('in',pylode(',',$moblieuid));
					$UserinfoM->upInfo($mobliewhere,$mobliedata);
					$nid						=			$CompanyM->upInfo($moblieuid,'',$mobliedata);


				}
			}
		}
		if($_POST['comname_yyzz']){
			//营业执照
		    if($status!=0){
				//已认证
				$yyzzwhere['uid']	=		array('in',pylode(',',$_POST['uid']));

				$yyzzwhere['type']	=		3;	
		
				$yyzz				=		$CompanyM->getCertList($yyzzwhere,array('field'=>'`uid`,`check`'));
				
				if(is_array($yyzz) &&  $yyzz){
	
					foreach($yyzz as $val){

					    if($val['check'] ){
					        
					        $checkuid[]		=		$val['uid'];
					        
					    }
					}
				}
			}else{

				$checkuid[]		=		$_POST['uid'];	
			}
			
			$yyzzkdata					=		array(

			    'yyzz_status'			=>			$status
				
			);
					
			$nid						=			$CompanyM->upInfo($checkuid,'',$yyzzkdata);

			$checkdata					=		array(

			    'status'				=>			$status

			);

			$checwhere['uid']			 =			array('in',pylode(',',$checkuid));

			$checwhere['type']		 	=			3;

			$CompanyM->upCertInfo($checwhere,$checkdata,array('utype'=>'admin'));
		}


		$this->ACT_layer_msg("批量认证成功",9,$_SERVER['HTTP_REFERER']);

	}
	/**
	 * @desc 后台     -   企业认证审核
	 */
	function comStatus_action(){
	    
	    $companyorder   =   $this->MODEL('companyorder');
	    
	    $companyM       =   $this->MODEL('company');
	    
	    $status         =   intval($_POST['r_status']);
	    
	    if ($_POST['noyyzz'] == '1') {
	        
	        $this->ACT_layer_msg('该企业尚未上传营业执照，无法审核！', 8, $_SERVER['HTTP_REFERER']);
	    }
	    
	    if ($status == '') {
	        
	        $this->ACT_layer_msg('请选审核状态！', 8, $_SERVER['HTTP_REFERER']);
	    }
	    
	    if ($_POST['uid']) {
	        
	        $uid  =  $_POST['uid'];
	        
	        if ($status != 1) {
	            
	            $yyzz_status = 2;
	            
	        } else {
	            
	            $yyzz_status = 1;
	            
	            // 如果是“审核通过”，判断之前是否有过“审核通过的记录”，没有则增加营业执照审核通过的积分（只有第一次审核通过才加积分）
	            
	            if (is_array($uid) && ! empty($uid)) {
	                
	                $comids     =   @explode(',', $uid);
	                
	                $paywhere['com_id'] = array('in', pylode(',', $comids));
	                
	                $paywhere['pay_remark'] = '认证营业执照';
	                
	                $companypay = $companyorder->getPayList($paywhere, array('field' => 'com_id'));
	                
	                foreach ($companypay as $k => $v) {
	                    
	                    if (in_array($v, $uid)) {
	                        
	                        unset($uid[$k]);
	                    }
	                }
	                foreach ($uid as $v) {
	                    
	                    $this->MODEL('integral')->invtalCheck($v, 2,'integral_comcert', '认证营业执照',21);
	                }
	                
	            } elseif ($uid != '') {
	                
	                $paywhere['com_id'] = $uid;
	                
	                $paywhere['pay_remark'] = '认证营业执照';
	                
	                $num = $companyorder->getCompanyPayNum($paywhere);
	                
	                if ($num < 1) {
	                    
	                    $this->MODEL('integral')->invtalCheck($uid, 2,'integral_comcert', '认证营业执照',21);
	                }
	            }
	        }
	        
	        $companyData = array('yyzz_status' => $yyzz_status);
	        
	        $companyM   ->  upInfo($uid, '', $companyData);
	        
	        $companycertData = array(
	            
	            'status'     => $status,
	            
	            'statusbody' => $_POST['statusbody']
	        );
	        
	        $id = $companyM -> upCertInfo(array('type'=>'3','uid' => array('in', pylode(',', $uid))), $companycertData,array('utype'=>'admin'));
	        
	        
	        // 职位免审核开启，管理勾选同步审核职位，未审核职位同步审核成功
	        if ($this->config['com_free_status'] == '1' && $_POST['job_status']) {
	            
	            $jobM   =   $this -> MODEL('job');
	            $jobM   ->  upInfo(array('state'=>'1','r_status'=>1), array('state'=>'0', 'uid'=>array('in',pylode(',', $uid))));
	        }
	        
	        $ComA       =   $companyM->getList(array('uid'=>array('in',pylode(',', $uid))), array('field' => 'uid,name,linkmail'));
	        
	        $company    =   $ComA['list'];
	        
	        if ($this->config['sy_email_set'] == '1') {
	            
	            if (is_array($company)) {
	                
	                $notice = $this->MODEL('notice');
	                
	                foreach ($company as $v) {
	                    if ($this->config['sy_email_comcert'] == '1' && $status > 0) {
	                        if ($status == '1') {
	                            $certinfo = '营业执照审核通过！';
	                        } else {
	                            $certinfo = '营业执照审核未通过！';
	                        }
	                        $notice->sendEmailType(array(
	                            'email' => $v['linkmail'],
	                            'certinfo' => $certinfo,
	                            'comname' => $v['name'],
	                            'uid' => $v['uid'],
	                            'name' => $v['name'],
	                            'type' => 'comcert'
	                        ));
	                    }
	                }
	            }
	        }
	        
	        /* 消息前缀 */
	        foreach($company as $v){
	            
	            $uids[]  =  $v['uid'];
	            
	            /* 处理审核信息 */
	            if ($status == 2){
	                
	                $statusInfo  =  '很遗憾 , 贵公司营业执照未能通过审核';
	                
	                if($_POST['statusbody']){
	                    
	                    $statusInfo  .=  ' , 原因：'.$_POST['statusbody'];
	                    
	                }
	                
	                $msg[$v['uid']]  =  $statusInfo;
	                
	            }elseif($status == 1){
	                
	                $msg[$v['uid']]  =  '贵公司营业执照审核通过，招聘人才更轻松！';
	                
	            }
	        }
	        //发送系统通知
	        $sysmsgM	=	$this->MODEL('sysmsg');
	        
	        $sysmsgM -> addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));
	        
	        if ($id) {
	            
	            $this->ACT_layer_msg('企业认证审核(UID:' . $uid . ')设置成功！', 9, $_SERVER['HTTP_REFERER'], 2, 1);
	        } else {
	            
	            $this->ACT_layer_msg('设置失败！', 8, $_SERVER['HTTP_REFERER']);
	        }
	    } else {
	        
	        $this->ACT_layer_msg('非法操作！', 8, $_SERVER['HTTP_REFERER']);
	    }
	}
	

	/**
	 * @desc 企业微海报
	 */
	function mwhb_action(){
	    
	    $comid	=   intval($_GET['comid']);
		$this -> yunset('comid', $comid);	
	    $this->yuntpl(array('wap/hb/admin_whb'));
	}
	 
	function hbshow_action(){

		if ($_GET['id']){
            
            $id			=   intval($_GET['id']);
            
            $CacheM     =   $this->MODEL('cache');
            
            $CacheList  =   $CacheM->GetCache(array('job', 'com', 'city'));
            
            $this       ->  yunset($CacheList);
            
            $jobM   =   $this -> MODEL('job');
            
            if ($_GET['jobids']) {
                $jobids  =  @explode(',', $_GET['jobids']);
            }
            
            $where  =   array();
            
            $where['uid']       =   $id;
            $where['status']    =   '0';
            $where['state']     =   '1';
            $where['orderby']   =   'lastupdate,desc';
            if ($jobids) {
                $where['id']    =   array('in', pylode(',', $jobids));
            }
            
            $jobA   =   $jobM -> getList($where, array());
            
            $this   ->  yunset('jobs', $jobA['list']);
            
            $comM   =   $this -> MODEL('company');
            
            $com    =   $comM -> getInfo($id, array('field' => '`name`'));
            
            $this   ->  yunset('comname', $com['name']);
            
            $hb     =   $_GET['hb'];

			if($_GET['out']){
				$this->yunset('out', $_GET['out']);
			}
            
            $this   ->  yunset('hb', $hb);
        }else{
            
            $this->ACT_msg_wap(Url('wap'), '参数错误，请重试！', 2, 3);
        }
		 
        $this->yuntpl(array('wap/hb/'.$hb.'/tpl'));
	}
}
?>