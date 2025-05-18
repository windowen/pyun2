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
class admin_company_job_controller extends adminCommon{

    // 设置高级搜索功能
    function set_search($CacheList = array()){

		include(CONFIG_PATH.'db.data.php');

		$source   =   $arr_data['source'];

		$this -> yunset('source',$source);
		
		if (!$CacheList){

			$cacheM		=	$this -> MODEL('cache');
			$CacheList	=	$cacheM -> GetCache(array('com', 'job', 'city'));

			$setArr	=	array(
				'comdata'		=>	$CacheList['comdata'],
				'comclass_name'=>	$CacheList['comclass_name'],
				'job_name'		=>	$CacheList['job_name'],
				'city_name'		=>	$CacheList['city_name']	
			);
			$this -> yunset($setArr);
        } 

		$comdata		=	$CacheList['comdata'];
		$comclass_name	=	$CacheList['comclass_name'];


		foreach($comdata['job_edu'] as $k=>$v){
			
			$edu[$v]	=	$comclass_name[$v];
        }
		
		foreach($comdata['job_exp'] as $k=>$v){
			
            $exp[$v]	=	$comclass_name[$v];
        }

		$search_list      =   array();

		$search_list[]    =   array('param' => 'state','name'=>'审核状态', 'value' =>  array('1'=>'已审核','4'=>'未审核','3'=>'未通过','2'=>'已锁定'));

		$search_list[]    =   array('param' => 'status','name'=>'招聘状态', 'value' =>  array('1'=>'已下架','2'=>'招聘中'));

		$search_list[]    =   array('param' => 'jtype','name'=>'职位类型', 'value' =>  array('urgent'=>'紧急职位','xuanshang'=>'置顶职位','rec'=>'推荐职位'));
		
		$search_list[]    =   array('param' => 'exp','name'=>'工作经验', 'value' =>  $exp);
		
		$search_list[]    =   array('param' => 'edu','name'=>'学历要求', 'value' =>  $edu);

		$search_list[]    =   array('param' => 'source','name'=>'数据来源', 'value' =>  $source);

		$search_list[]    =   array('param' => 'adtime','name'=>'发布时间', 'value' =>  array('1'=>'今天','3'=>'最近三天','7'=>'最近七天','15'=>'最近半月','30'=>'最近一个月'));

 		$this->yunset('search_list', $search_list);

	}

    //  职位列表
	function index_action(){

	    //实例化职位类
        $JobM       =   $this->MODEL('job');

        if($_GET['uid']){

            $where['uid']           =   intval($_GET['uid']);

            $urlarr['uid']          =   intval($_GET['uid']);

            $ComM                   =   $this -> MODEL('company');

            $uinfo                  =   $ComM -> getInfo($where['uid'] , array('field' => 'name'));

            $this -> yunset('ccname',$uinfo['name']);
        }

        //搜索条件
        if ($_GET['state']) {
            $state      =   intval($_GET['state']);

            if ($state == 2) {

                $where['r_status']  =   2;
            } else {

                $where['state']     =   $state == 4 ? 0 : $state;
            }

            $urlarr['state']        =   $state;
        }

        if ($_GET['status']) {

            $status             =   intval($_GET['status']);

            $where['status']    =   $status == 2 ? 0 : $status;

            $urlarr['status']   =   $status;

        }

        if($_GET['jtype']){

            $jtype      =   trim($_GET['jtype']);

            if($jtype   ==  'rec'){

                $where['rec_time']      =   array('>',time());

            }else if($jtype     ==  'urgent'){

                $where['urgent_time']   =   array('>',time());

            }else if($jtype     ==  'xuanshang'){

                 $where['xsdate']       =   array('>',time());

            }

            $urlarr['jtype']    =   $jtype;

        }

		if($_GET['edu']){
			$where['edu']	=	$_GET['edu'];
			$urlarr['edu']	=   $_GET['edu'];
		}

		if($_GET['exp']){
			$where['exp']	=	$_GET['exp'];
			$urlarr['exp']	=   $_GET['exp'];
		}

        if($_GET['source']){

            $where['source']        =   intval($_GET['source']);

            $urlarr['source']       =   intval($_GET['source']);
        }

        if($_GET['adtime']){

             if($_GET['adtime']  ==  '1'){

                $where['sdate']     =   array('>',strtotime(date('Y-m-d 00:00:00')));

            }else{

                $where['sdate']     =   array('>',strtotime('-'.intval($_GET['adtime']).' day'));
            }

            $urlarr['adtime']       =   $_GET['adtime'];

        }

		if($_GET['job_class']){
		    
		    $where['PHPYUNBTWSTARTA'] = '';
		    $where['job1']	        =	array('findin', $_GET['job_class']);
		    $where['job1_son']	    =	array('findin', $_GET['job_class'], 'OR');
		    $where['job_post']	    =	array('findin', $_GET['job_class'], 'OR');
		    $where['PHPYUNBTWENDa']  = '';
		    
			$urlarr['job_class']	=	$_GET['job_class'];
		}
		if($_GET['city_class']){
            
		    $where['PHPYUNBTWSTARTB'] = '';
		    $where['provinceid']	=	array('findin', $_GET['city_class']);
		    $where['cityid']	    =	array('findin', $_GET['city_class'], 'OR');
		    $where['three_cityid']	=	array('findin', $_GET['city_class'], 'OR');
		    $where['PHPYUNBTWENDB']  = '';
			
			$urlarr['city_class']	=	$_GET['city_class'];
		}

        if($_GET['keyword']){

            if ($_GET['type']=='1'){

                $where['com_name']  =	array('like',trim($_GET['keyword']));

            }elseif ($_GET['type']=='2'){

                $where['name']      =	array('like',trim($_GET['keyword']));
            }

            $urlarr['type']			=	$_GET['type'];

            $urlarr['keyword']		=	$_GET['keyword'];
        }

        //分页链接
        $urlarr['page']	=	'{{page}}';

        $pageurl		=	Url($_GET['m'],$urlarr,'admin');

        //提取分页
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('company_job',$where,$pageurl,$_GET['page']);


        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){

            //limit order 只有在列表查询时才需要
            if($_GET['order']){

                $where['orderby']		=	$_GET['t'].','.$_GET['order'];
                $urlarr['order']		=	$_GET['order'];
                $urlarr['t']			=	$_GET['t'];

            }else{

                $where['orderby']		=	array('state,asc','lastupdate,desc');

            }

            $where['limit']				=	$pages['limit'];

            $ListJob    =   $JobM -> getList($where,array('utype'=>'admin','cache'=>'1','isurl'=>'yes'));

            unset($where['limit']);

            session_start();
            $_SESSION['jobXls'] = $where;

			$CacheList	=	$ListJob['cache'];
 
			$setArr	=	array(
				'rows'			=>	$ListJob['list'],
				'cache'			=>	$CacheList,
				'comdata'		=>	$CacheList['comdata'],
				'comclass_name' =>	$CacheList['comclass_name'],
				'job_name'		=>	$CacheList['job_name'],
				'city_name'		=>	$CacheList['city_name']	
			);
            $this -> yunset($setArr);
        }

		$this->set_search($CacheList);

		$this->yuntpl(array('admin/admin_company_job'));
	}

    // 招聘/下架操作
	function checkstate_action(){

	    if($_POST['id']   &&  $_POST['state']){

	        if($_POST['state'] == 2){

	            $_POST['state'] =   0;

	        }

	        $JobM                  =   $this -> MODEL('job');

	        $id                    =   intval($_POST['id']);

	        $postData['status']     =   intval($_POST['state']);

	        $JobM    ->  upInfo($postData, array('id' => $id));
	    }

	    echo 1;

	}

    // 	职位置顶
	function xuanshang_action(){

        $id     =   trim($_POST['pid']);

        $data   =   array(

            'top'   =>  intval($_POST['s']),
            'days'  =>  intval($_POST['days'])

        );

        $JobM   =   $this -> MODEL('job');

        $arr    =   $JobM -> addTopJob($id, $data);

        $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}

	//  职位推荐
	function recommend_action(){

	    $id     =   trim($_POST['pid']);

	    $data   =   array(

	        'rec'   =>  intval($_POST['s']),
	        'days'  =>  intval($_POST['days'])

	    );

	    $JobM   =   $this -> MODEL('job');

	    $arr    =   $JobM -> addRecJob($id, $data);

	    $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}

	//  职位紧急招聘
	function urgent_action(){

	    $id     =   trim($_POST['pid']);

	    $data   =   array(

	        'urgent'   =>  intval($_POST['s']),
	        'days'     =>  intval($_POST['days'])

	    );

	    $JobM   =   $this -> MODEL('job');

	    $arr    =   $JobM -> addUrgentJob($id, $data);

	    $this  ->  ACT_layer_msg( $arr['msg'],$arr['errcode'],$_SERVER['HTTP_REFERER'],2,1);

	}

    /**
     * 职位审核
     */
    function status_action()
    {
        $jobM       =   $this->MODEL('job');

        $statusData = array(

            'state'         =>  intval($_POST['status']),
            'statusbody'    =>  trim($_POST['statusbody'])
        );

        $return = $jobM -> statusJob($_POST['pid'], $statusData);

        $this->ACT_layer_msg($return['msg'], $return['errcode'], "index.php?m=admin_company_job", 2, 1);
    }


    function cjobstatus_action()
    {

        if ($_POST) {

            $id         =   intval($_POST['cid']);
            $uid        =   intval($_POST['cuid']);
            $status     =   intval($_POST['r_status']);
            $statusbody =   trim($_POST['statusbody']);

            $jobM   =   $this->MODEL('job');

            $post   =   array(

                'uid'           =>  $uid,
                'state'         =>  $status,
                'statusbody'    =>  $statusbody
            );

            $return     =   $jobM -> status($id, $post);

            $this -> ACT_layer_msg($return['msg'], $return['errcode'], $_SERVER['HTTP_REFERER'], 2, 1);
        }
    }

    /**
     * @desc 后台 -- 会员 -- 企业 -- 企业管理 / 职位管理 -- 新增  /  修改
     */
	function show_action(){
        $cacheM     =   $this->MODEL('cache');

        $options    =   array('job','com','city','hy','user');

        $cache      =   $cacheM -> GetCache($options);

        $this       ->  yunset('cache',  $cache);

        $JobM       =   $this->MODEL('job');

        $companyM	=	$this->MODEL('company');
        // 获取职位详情
        if($_GET['id']){

		    $id   =   intval($_GET['id']);

		    $info =   $JobM   ->  getInfo(array('id' => $id), array('lang' => 'isarray'));
            
            // var_dump($info);exit;
 		    $this ->  yunset('show',  $info);

		    $this ->  yunset('lasturl',   $_SERVER['HTTP_REFERER']);
       		$uid	=	$info['uid'];
      	}
        if(intval($_GET['uid'])){

          $uid			=		intval($_GET['uid']);
        }

		$company		=		$companyM->getInfo($uid,array('field'=>'`uid`,r_status'));

		$this->yunset('company',$company);

		$this->yunset('uid',$uid);

        if($_POST['update']){

		    $description	=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'background-color:','background-color:','white-space:'),$_POST['content']);

			$post	=	array(
				'name'			=>	$_POST['name'],

				'job1'          =>  intval($_POST['job1']),
                'job1_son'      =>  intval($_POST['job1_son']),
                'job_post'      =>  intval($_POST['job_post']),

				'provinceid'    =>  intval($_POST['provinceid']),
                'cityid'        =>  intval($_POST['cityid']),
				'three_cityid'  =>  intval($_POST['three_cityid']),

				'minsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['minsalary']),
				'maxsalary'     =>  intval($_POST['salary_type']) == 1 ? 0 : intval($_POST['maxsalary']),

				'description'	=>	$description,
				'r_status'      =>	$company['r_status'],
				'hy'            =>  intval($_POST['hy']),
				'number'        =>  intval($_POST['number']),
                'exp'           =>  intval($_POST['exp']),
                'report'        =>  intval($_POST['report']),
                'age'           =>  intval($_POST['age']),
                'sex'           =>  intval($_POST['sex']),
                'edu'           =>  intval($_POST['edu']),
				'is_graduate'   =>  intval($_POST['is_graduate']),
                'marriage'      =>  intval($_POST['marriage']),
				'lang'          =>  trim(pylode(',', $_POST['lang'])),
				'state'      	=> 	$company['r_status']==1 ? 1:0,
				'lastupdate'	=>	time(),
				'jobhits'       =>  intval($_POST['jobhits']),

                'exp_req'       =>  trim($_POST['exp_req']),
                'edu_req'       =>  trim($_POST['edu_req']),
                'sex_req'       =>  trim(pylode(',', $_POST['sex_req'])),
			);
			$data	=	array(
				'post'			=>	$post,
				'id'			=>	intval($_POST['id']),
				'uid'			=>	$_POST['uid'],
				'utype'			=>	'admin'
			);

			$return	=	$JobM->addJobInfo($data);

			if($return['errcode']==9){

				$this -> ACT_layer_msg($return['msg'],$return['errcode'],'index.php?m=admin_company_job');
			}else{
				$this -> ACT_layer_msg($return['msg'],$return['errcode']);
			}
		}
		$this->yuntpl(array('admin/admin_company_job_show'));

	}

    //  返回审核原因
	function lockinfo_action(){

	    $JobM  =   $this -> MODEL('job');

	    $info  =   $JobM ->    getInfo(array('id' => intval($_POST['id'])), array('field'=>'`statusbody`'));

	    echo $info['statusbody'];die;

	}

	// 转移类别
    function saveclass_action(){

        $JobM   =   $this -> MODEL('job');

		if($_POST['hy']   ==  ''){
			$this -> ACT_layer_msg('请选择行业类别！',8,$_SERVER['HTTP_REFERER']);
		}

		if($_POST['job1'] ==  ''){
			$this -> ACT_layer_msg('请选择职位类别！',8,$_SERVER['HTTP_REFERER']);
		}

		$data['hy']       =   $_POST['hy'];
		$data['job1']     =   $_POST['job1'];
		$data['job1_son'] =   $_POST['job1_son'];
		$data['job_post'] =   $_POST['job_post'];

		$id               =   @explode(',',$_POST['jobid']);

		$listA            =   $JobM -> getList(array('id' => array('in', pylode(',',$id))), array('cache'=>'1','field'=>'id,uid,name'));

		$nid              =   $JobM -> upInfo($data, array('id' => array('in', pylode(',',$id))));

		$job              =   $listA['list'];

		$cache            =   $listA['cache'];

		if($job){

		    $msg          =   array();
		    $uids         =   array();

		    //  提取职位uid 和职位名称
		    foreach ($job   as  $k => $v){

                $uids[] =  $v['uid'];

                $msg[$v['uid']][]  =  '您的职位<a href="comjobtpl,'.$v['id'].'">《'.$v['name'].'》</a>管理员已修改，行业类别为：'.$cache[industry_name][$_POST['hy']].'，职位类别为：'.$cache[job_name][$_POST['job1']];

                if($_POST['job1_son']){
                    $msg[$v['uid']][]  .= ''.$cache[job_name][$_POST['job1_son']];
                }
                if($_POST['job_post']){
                    $msg[$v['uid']][]  .= ''.$cache[job_name][$_POST['job_post']];
                }
		    }

		    $sysmsgM    =   $this -> MODEL('sysmsg');

		    $sysmsgM    ->  addInfo(array('uid'=>$uids,'usertype'=>2, 'content'=>$msg));

		}


		$nid?$this->ACT_layer_msg('职位类别(ID:'.$_POST['jobid'].')修改成功！',9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg('修改失败！',8,$_SERVER['HTTP_REFERER']);
	}

	// 删除职位
	function del_action(){

		$this->check_token();
		
		$JobM	=	$this -> Model('job');
		$PackM	=	$this->Model('pack');

		$delID	=	is_array($_GET['del']) ? $_GET['del'] : $_GET['id'];

		if (is_array($_GET['del'])) {
                
			$layer_type = 1; // 提示方式
		} else if ($_GET['del']) {
			
			$layer_type = 0; // 提示方式
		}
		
		$numwhere['jobid'] = array('in',pylode(',', $delID));
  
		$addArr  =  $JobM -> delJob($delID, array('utype'=>'admin'));
		$this->layer_msg( $addArr['msg'],$addArr['errcode'],$addArr['layertype'],$_SERVER['HTTP_REFERER'],2,1);
 	}

	function refresh_action(){

	    $JobM      =   $this -> MODEL('job');

	    $ids       =   @explode(',', $_POST['ids']);

	    $return    =   $JobM -> upInfo(array('lastupdate'=>time()), array('id' => array('in', pylode(',', $ids))));

        $this->MODEL('log')->addAdminLog("职位(ID".$_POST['ids']."刷新成功");

	}

	// 导出职位列表数据
	function xls_action(){

	    session_start();

	    $where = $_SESSION['jobXls'] ? $_SESSION['jobXls'] : array('orderby'=>'id');


	    if(!empty($_POST['type'])){

	        foreach($_POST['type'] as $v){

	            if($v == 'lastdate'){

	                $type[]  =  'lastupdate';

	            }else{

	                $type[]  =  $v;

	            }

	        }

	        $field  =  @implode(',', $type).',uid';

	    }else{

	        $field  =  'uid';

	    }

	    if($_POST['pid']){

	        $ids          =  @explode(',',$_POST['pid']);

	        $where['id']  =  array('in',pylode(',',$ids));

 	    }
	    if($_POST['limit']){

	        $where['limit']  =  intval($_POST['limit']);
	    }

	    $jobM      =   $this -> MODEL('job');

	    $listNew   =   $jobM -> getList($where,array('cache'=>1,'field'=>$field));

	    $jobs      =   $listNew['list'];
	    $cache     =   $listNew['cache'];


	    if (!empty($jobs)){

	        foreach($jobs as $k => $v){

	            $langs = array();

	            if($v['lang']!=""){

 	                $lang =   @explode(",",$v['lang']);

	                foreach($lang as $val){

	                    $langs[]   =   $cache[comclass_name][$val];

	                }

	                $jobs[$k]['lang_info'] = @implode(",",$langs);
	            }

	        }

 	        $this->yunset("cache",$cache);
	        $this->yunset("list",$jobs);
	        $this->yunset("type",$_POST['type']);

	        $this->MODEL('log')->addAdminLog("导出职位信息");
	        header("Content-Type: application/vnd.ms-excel");
	        header("Content-Disposition: attachment; filename=job.xls");
	        $this->yuntpl(array('admin/admin_job_xls'));
	    }

	}

	/* 职位匹配简历 */
	function matching_action(){

		if($_GET['id']){

            $id     =   intval($_GET['id']);

            $where[status]          =   '1';
            $where[r_status]        =   '1';
            $where[defaults]        =   '1';

            $JobM       =   $this -> MODEL('job');

            $jobinfo    =   $JobM -> getInfo(array('id' => $id), array('field'=>'uid,job1,job1_son,job_post,provinceid,cityid,three_cityid'));

			$this->yunset('comid', $jobinfo['uid']);

			if($jobinfo){

			    $where['PHPYUNBTWSTART_A']       =    '';

                $where[city_classid][]  =   array('findin', $jobinfo[provinceid],'OR');

                $where[city_classid][]  =   array('findin', $jobinfo[cityid], 'OR');

                $where[city_classid][]  =   array('findin', $jobinfo[three_cityid], 'OR');

                $where['PHPYUNBTWEND_A']    =   '';


                $where['PHPYUNBTWSTART_B']       =   '';

                $where[job_classid][]   =   array('<>','','');

                $where[job_classid][]   =   array('findin', $jobinfo[job1],'OR');

                $where[job_classid][]   =   array('findin', $jobinfo[job1_son], 'OR');

                $where[job_classid][]   =   array('findin', $jobinfo[job_post], 'OR');

                $where['PHPYUNBTWEND_B']    =   '';

	        }




	        $ResumeM   =   $this -> MODEL('resume');

	        $record    =   $ResumeM -> getResTsList(array('jobid'=>$id),array('field'=>'eid'));

	        if(!empty($record)){

    			foreach($record as $v){

    				$eids[] =   $v['eid'];

    			}

    			$where[id]          =   array('notin', pylode(',', $eids));
	        }

	        $blackM		=	$this -> MODEL('black');

            $black      =   $blackM -> getBlackList(array('p_uid' => $jobinfo['uid']));

            if(!empty($black)){

    			foreach($black as $v){

    			    $buids[] =   $v['c_uid'];

    			}

                $where[uid]         =   array('notin', pylode(',', $buids));

            }

            //分页链接
            $urlarr['page']	    =	'{{page}}';

            $pageurl            =   Url('admin_company_job&c=matching&id='.$id.'',$urlarr,'admin');

            //提取分页
            $pageM              =	$this  -> MODEL('page');
            $pages              =	$pageM -> pageList('resume_expect',$where,$pageurl,$_GET['page']);


            //分页数大于0的情况下 执行列表查询
            if($pages['total'] > 0){

                //limit order 只有在列表查询时才需要
                $where['orderby']       =   'lastupdate';

                $where['limit']         =	$pages['limit'];


                $List                   =	$ResumeM -> getList($where,array('cache'=>1));


                $CacheList    =   $List['cache'];

                $this -> yunset(array('resumes'=>$List['list']));
            }

	        $this->yuntpl(array('admin/admin_matching'));
	    }
	}

	function jobNum_action(){
		$MsgNum=$this->MODEL('msgNum');
		echo $MsgNum->jobNum();
	}

}
?>