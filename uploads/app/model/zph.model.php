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
class zph_model extends model{

    /**
	 * @desc   获取招聘会列表
	 * @param  $whereData:查询条件
	 * @param  $data:自定义处理数组
	 */
	public function getList($whereData , $data = array('field'=>null,'utype'=>null)) {

		$select		=	$data['field'] ? $data['field'] : '*';

		$List		=   $this -> select_all('zhaopinhui',$whereData,$select);

		$time  		=	time();

		if($List&&is_array($List)){

		    if ($data['utype'] == 'admin'){

		        foreach($List as $key => $val){

		            $zid[]	=	$val['id'];

		            $List[$key]['comnum']	=	'0';
		            $List[$key]['booking']	=	'0';

		        }

		        $all	=	$this -> getZphCompanyList(array('zid'=>array('in',pylode(',', $zid)),'groupby'=>'zid'),array('field'=>'zid,count(id) as num'));

		        $status	=	$this -> getZphCompanyList(array('zid'=>array('in',pylode(',', $zid)),'status'=>'0','groupby'=>'zid'),array('field'=>'`zid`,count(`id`) as num'));

		        foreach($List as $key => $v){

		            foreach($all as $val){

		                if($v['id'] == $val['zid']){
		                    $List[$key]['comnum']	=	$val['num'];
		                }
		            }
		            foreach($status as $val){

		                if($v['id'] == $val['zid']){
		                    $List[$key]['booking']	=	$val['num'];
		                }
		            }
		            if($v['did']<1){
		                $List[$key]['did']	=	'0';
		            }
		            
		        }
		    }
		    if ($data['utype'] == 'app'){

		    	foreach($List as $key => $val){

		            $zid[]	=	$val['id'];

		            $List[$key]['comnum']	=	0;
		            $List[$key]['jobnum']	=	0;

		        }

		    	$all	=	$this -> getZphCompanyList(array('zid'=>array('in',pylode(',', $zid)),'status'=>1),array('field'=>'zid,uid,jobid'));

                
		    	$arr_uid=	array();
		    	$job_ids=	array();
		    	foreach($all as $va){
                    
					$arr_uid[]	 =	$va['uid'];

					if($va['jobid']){

                        $job_ids = 	array_unique(array_merge($job_ids,@explode(",",$va['jobid'])));

                    }
                    
				}

		        $arr_uid  =  array_unique($arr_uid);

		        if(!empty($arr_uid)){
                    
                    $jobwhere	=	array(
                    	'uid'		=>	array('in',pylode(',',$arr_uid)),
                    	'state'		=>	1,
                    	'status'	=>	0,
                    	'r_status'	=>	1,
                    	'groupby'	=>	'uid'
                    );
                    //企业的所有职位
                    $joblist = $this->select_all("company_job",$jobwhere,"`uid`,count(*) as `num`");

                    $comalljobnum = array();

                    foreach($joblist as $val){
                        
                        $comalljobnum[$val['uid']] = $val['num'];

                    }

                    $jobidwhere	=	array(
                    	'id'		=>	array('in',pylode(',',$job_ids)),
                    	'state'		=>	1,
                    	'status'	=>	0,
                    	'r_status'	=>	1,
                    );
                    //企业参会的所有职位
                    $jobidlist = $this->select_all("company_job",$jobidwhere,"`id`");

                    $jidarr =   array();

                    foreach($jobidlist as $jidv){
                        $jidarr[] = $jidv['id'];
                    }

                    foreach($all as $k=>$v){

                        $all[$k]['jobnum'] = 0;

                        if($v["jobid"]){

                            $jobidarr = @explode(",",$v["jobid"]);

                            foreach($jobidarr as $jv){

                                if(in_array($jv,$jidarr)){

                                    $all[$k]['jobnum']++;

                                }

                            }

                        }else{

                            $all[$k]['jobnum']  =  $comalljobnum[$v['uid']] ? $comalljobnum[$v['uid']] : 0; 

                        }
                    }
                }
                
		        foreach($List as $key => $val){

		            $List[$key]['starttime']  =  date('Y-m-d',strtotime($val['starttime']));
		            $List[$key]['endtime']    =  date('Y-m-d',strtotime($val['endtime']));
		            $List[$key]['stime']      =  strtotime($val['starttime'])-$time;
		            $List[$key]['etime']      =  strtotime($val['endtime'])-$time;
		            $List[$key]['is_themb_n'] =  checkpic($val['is_themb'],$this->config['sy_zph_icon']);

		            foreach($all as $aval){
	                    if($val['id'] == $aval['zid']){
	                        $List[$key]['comnum']++;
	    			        $List[$key]['jobnum']+=$aval['jobnum'];
	                    }
	                }
		            
		        }
		    }
		}
		return $List;
	}

	/**
	* @desc 获取招聘会列表详细信息
	*/
	public function getInfo($whereData,$data = array()){
        $select     =   $data['field'] ? $data['field'] : '*';

        $Info       =   $this -> select_once('zhaopinhui', $whereData, $select);

        if (!empty($Info)) {

            if ($Info['starttime']) {
                $Info["stime"]          =   strtotime($Info['starttime']) - time();
            }

            if ($Info['endtime']) {
                $Info['etime']          =   strtotime($Info['endtime']) - time();
            }

            if ($Info['reserved']) {
                $Info['reserved_n']     =   @explode(',', $Info['reserved']);
            }
            if (!empty($Info['body'])){

                $body  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $Info['body']);
                $body  =  htmlspecialchars_decode($body);

                preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$body,$res);
                if(!empty($res[3])){
                    foreach($res[3] as $v){
                        if(strpos($v,'http:')===false && strpos($v,'https:')===false){
                            $body  =  str_replace($v,$this->config['sy_ossurl'].$v,$body);
                        }
                    }
                }
                $Info['body']  =  $body;
            }
            if (!empty($Info['media'])){

                $media  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $Info['media']);
                $media  =  htmlspecialchars_decode($media);

                preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$media,$res);
                if(!empty($res[3])){
                    foreach($res[3] as $v){
                        if(strpos($v,'http:')===false && strpos($v,'https:')===false){
                            $media  =  str_replace($v,$this->config['sy_ossurl'].$v,$media);
                        }
                    }
                }
                $Info['media']  =  $media;
            }
            if (!empty($Info['packages'])){

                $packages  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $Info['packages']);
                $packages  =  htmlspecialchars_decode($packages);

                preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$packages,$res);
                if(!empty($res[3])){
                    foreach($res[3] as $v){
                        if(strpos($v,'http:')===false && strpos($v,'https:')===false){
                            $packages  =  str_replace($v,$this->config['sy_ossurl'].$v,$packages);
                        }
                    }
                }
                $Info['packages']  =  $packages;
            }
            if (!empty($Info['booth'])){

                $booth  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $Info['booth']);
                $booth  =  htmlspecialchars_decode($booth);

                preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$booth,$res);
                if(!empty($res[3])){
                    foreach($res[3] as $v){
                        if(strpos($v,'http:')===false && strpos($v,'https:')===false){
                            $booth  =  str_replace($v,$this->config['sy_ossurl'].$v,$booth);
                        }
                    }
                }
                $Info['booth']  =  $booth;
            }
            if (!empty($Info['participate'])){

                $participate  =  str_replace(array('&quot;','&nbsp;','<>'), array('','',''), $Info['participate']);
                $participate  =  htmlspecialchars_decode($participate);

                preg_match_all('/<img(.*?)src=("|\'|\s)?(.*?)(?="|\'|\s)/',$participate,$res);
                if(!empty($res[3])){
                    foreach($res[3] as $v){
                        if(strpos($v,'http:')===false && strpos($v,'https:')===false){
                            $participate  =  str_replace($v,$this->config['sy_ossurl'].$v,$participate);
                        }
                    }
                }
                $Info['participate']  =  $participate;
            }
            if (!empty($data['pic'])) { // 缩略图
                $Info['is_themb_n']  =  checkpic($Info['is_themb'], $this->config['sy_zph_icon']);
            }

            if (!empty($data['banner'])) { // 横幅
                $Info['banner_n']  =  checkpic($Info['banner'], $this->config['sy_zphbanner_icon']);
            }

			$Info['comnum']		=	$this->getZphComNum(array('zid'=>$Info['id'],'status'=>1));

			$com		=	$this->select_all('zhaopinhui_com',array('zid'=>$Info['id'],'status'=>1));

			$jobnum		=	0;
			$job_ids	=	array();
			foreach($com as $key=>$val){

				if($val['jobid']){

					$job_ids = 	array_unique(array_merge($job_ids,@explode(",",$val['jobid'])));

				}else{
					$uids[]	=	$val['uid'];
				}

			}
			if(!empty($job_ids)){
				$jobidwhere	=	array(
	            	'id'		=>	array('in',pylode(',',$job_ids)),
	            	'state'		=>	1,
	            	'status'	=>	0,
	            	'r_status'	=>	1,
	            );
	            $jobnum = $this->select_num('company_job',$jobidwhere);
			}
			$jobwhere	=	array(
				'uid'		=>	array('in',pylode(',',$uids)),
				'state'		=>	1,
				'status'	=>	0,
				'r_status'	=>	array('<>',2),
			);

			$allchoosejobnum = $this->select_num('company_job',$jobwhere);



			$Info['jobnum']	 =	$jobnum+$allchoosejobnum;
        }
        return $Info;
    }

	/**
	* @desc 添加招聘会
	*/
	public function addInfo($data	=	array()){

		$AddData	=	array(

			'title'			=>	$data['title'],

			'sid'			=>	$data['sid'],

			'address'		=>	$data['address'],

			'traffic'		=>	$data['traffic'],

			'phone'			=>	$data['phone'],

			'organizers'	=>	$data['organizers'],

			'user'			=>	$data['user'],

			'starttime'		=>	$data['starttime'],

			'endtime'		=>	$data['endtime'],

			'body'			=>	$data['body'],

			'media'			=>	$data['media'],

			'packages'		=>	$data['packages'],

			'booth'			=>	$data['booth'],

			'participate'	=>	$data['participate'],

			'ctime'			=>	time(),

			'status'		=>'0',

			'did'	        =>	$data['did'],

			'reserved'		=>	$data['reserved']

            // 'sort'          =>  $data['sort']
		);
		if($data['is_themb']){

			$AddData['is_themb']	=	$data['is_themb'];

		}
		if($data['banner']){

			$AddData['banner']	=	$data['banner'];

		}

		if ($AddData && is_array($AddData)){

			$nid	=	$this -> insert_into('zhaopinhui',$AddData);

		}

		return $nid;

	}

	/**
	* @desc 修改招聘会
	*/
	public function upInfo($whereData, $data = array()){

		if(!empty($whereData)) {

			$PostData	=	array(

				'title'			=>	$data['title'],

				'sid'			=>	$data['sid'],

				'address'		=>	$data['address'],

				'traffic'		=>	$data['traffic'],

				'phone'			=>	$data['phone'],

				'organizers'	=>	$data['organizers'],

				'user'			=>	$data['user'],

				'starttime'		=>	$data['starttime'],

				'endtime'		=>	$data['endtime'],

				'body'			=>	$data['body'],

				'media'			=>	$data['media'],

				'packages'		=>	$data['packages'],

				'booth'			=>	$data['booth'],

				'participate'	=>	$data['participate'],

				'did'			=>	$data['did'],

				'reserved'		=>	$data['reserved']

                // 'sort'          =>  $data['sort']
			);
			if($data['is_themb']){

				$PostData['is_themb']	=	$data['is_themb'];

			}
			if($data['banner']){

				$PostData['banner']	=	$data['banner'];

			}

			if ($PostData && is_array($PostData)){

				$nid	=	$this -> update_once('zhaopinhui',$PostData,array('id'=>$whereData['id']));

			}

			return $nid;

		}

	}

	/**
	* @desc 删除招聘会
	*/
	public function delZph($delId){

        if (empty($delId)) {

            $return     =   array( 'errcode' => 8, 'msg' => '请选择要删除的数据！');

        } else {

            if (is_array($delId)) {

                $delId  =   pylode(',', $delId);

                $return['layertype']    =   1;

            } else {

                $return['layertype']    =   0;
            }

            $delid      =   $this -> delete_all('zhaopinhui', array('id' => array('in', $delId)), '');

            if ($delid) {

                $this -> delZphPic(array( 'zid' => array( 'in', $delId)));

                $this -> delete_all('zhaopinhui_com', array('zid' => array('in', $delId)), '');

                $return['msg']      =   '招聘会';

                $return['errcode']  =   $delid ? '9' : '8';

                $return['msg']      =   $delid ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';
            }

        }

        return $return;
    }

	/**
	* @desc 获取参会企业列表
	*/
	public function getZphCompanyList($whereData , $data=array()){

		$select		=	$data['field'] ? $data['field'] : '*';

		$List		=	$this -> select_all('zhaopinhui_com' , $whereData , $select);

		if($List && is_array($List)){

		    $jobids = $jobuid = $uid  =  $zid  =  array();

			foreach($List as $v){

				if($v['uid'] && !in_array($v['uid'] , $uid)){
					$uid[]  =  $v['uid'];
				}

				if($v['zid'] && !in_array($v['zid'],$zid)){
					$zid[]  =  $v['zid'];
				}

				if($v['jobid']){

					$jobarr 	=	@explode(',',$v['jobid']);
					$jobids		=	array_merge($jobids,$jobarr);

				}else{

					if(!in_array($v['uid'] , $jobuid)){
						$jobuid[]	=	$v['uid'];
					}
				}
			}

			$company  =  $this -> select_all('company', array('uid'=>array('in',pylode(',',$uid))),'`uid`,`name`,`logo`,`logo_status`');

			$jobwhere =	 array(
				'state' 	=>	1,
				'status' 	=>	0,
				'r_status'	=>	1
			);
			$jobwhere['PHPYUNBTWSTART'] = 1;
			$jobwhere['uid']	=	array('in',pylode(',',$jobuid));
			$jobwhere['id']	=	array('in',pylode(',',$jobids),'OR');
			$jobwhere['PHPYUNBTWEND'] = 1;
			$listA	  =  $this -> select_all('company_job',$jobwhere,'`id`,`uid`,`name`');

			$zph	  =  $this -> select_all('zhaopinhui',array('id'=>array('in',pylode(',',$zid))),'`id`,`title`,`address`,`starttime`,`endtime`');

			$space	  =  $this -> getZphSpaceList("zhaopinhui_space");

			foreach($space as $val){
				$spacename[$val['id']]	=	$val['name'];
			}
			foreach($List as $k => $v){

				foreach($zph as $val){

					if($v['zid'] == $val['id']){
						$List[$k]['zphname']	=	$val['title'];
						$List[$k]['title']		=	$val['title'];
						$List[$k]['address']	=	$val['address'];
						$List[$k]['starttime']	=	$val['starttime'];
						$List[$k]['endtime']	=	$val['endtime'];
						if(strtotime($val['starttime'])>time()){
							$List[$k]['notstart']	=	1;
						}
					}
				}
				if ($spacename[$v['sid']]){
				    $List[$k]['sidname']        =   $spacename[$v['sid']];
				}
				if ($spacename[$v['cid']]){
				    $List[$k]['cidname']        =   $spacename[$v['cid']];
				}
				if ($spacename[$v['bid']]){
				    $List[$k]['bidname']	    =	$spacename[$v['bid']];
				}

				foreach($company as $val){

					if($v['uid'] == $val['uid']){
						$List[$k]['comname']	=	$val['name'];
						if($val['logo_status']=='0'){
							$List[$k]['logo']	=	checkpic($val['logo'],$this->config['sy_unit_icon']);
						}else{
							$List[$k]['logo']	=	checkpic($this->config['sy_unit_icon']);
						}
						if($v['status']!=1){
							//控制取消按钮
							$List[$k]['notstart']	=	1;
						}
					}
				}

 				$jobname	=	array();
 				$jobid		=	array();
 				$jidarr		=	array();
 				if($v['jobid']){
 					$jidarr	=	@explode(',',$v['jobid']);
 				}

				foreach($listA as $val){

					if($v['jobid']){

						if(in_array($val['id'],$jidarr)){
							// 控制职位显示数量
					        if (count($jobname) < 20){
					            $List[$k]['job'][]		=	$val;
					            $jobname[]		    	= 	$val['name'];
					            $List[$k]['jobname']    =	@implode(",",$jobname);

					            $jobid[]				= 	$val['id'];
					            $List[$k]['jobid']    	=	@implode(",",$jobid);
					        }
						}

					}else{

						if ($v['uid'] == $val['uid']) {
	                        // 控制职位显示数量
					        if (count($jobname) < 20){
					            $List[$k]['job'][]		=	$val;
					            $jobname[]		    	= 	$val['name'];
					            $List[$k]['jobname']    =	@implode(",",$jobname);

					            $jobid[]				= 	$val['id'];
					            $List[$k]['jobid']    	=	@implode(",",$jobid);
					        }
					    }

					}

				}
				$List[$k]['bmctime_n']	=	date('Y-m-d',$v['ctime']);
			}
		}
		return $List;
	}

	/**
	* @desc 获取参会企业详细信息
	*/
	public function getZphComInfo($whereData,$data	=	array()){

		$select		=	$data['field'] ? $data['field'] : '*';

		$ZComInfo	=	$this -> select_once('zhaopinhui_com', $whereData, $select);

		return $ZComInfo;

	}
	/**
	* @desc 获取参会企业数量
	*/
	public function getZphComNum($whereData=array()){

		return $this -> select_num('zhaopinhui_com', $whereData);

	}

	/**
	* @desc 添加参会企业
	*/
	public function addZCom($data	=	array()){

		$AddData	=	array(

			'uid'		=>	$data['comid'],

			'zid'		=>	$data['zphid'],

			'ctime'		=>	time(),

			'status'	=>	isset($data['status']) ? $data['status'] : 1 ,

			'sid'		=>	$data['sid'],

			'cid'		=>	$data['cid'],

			'bid'		=>	$data['bid'],

		    'jobid'     =>  $data['jobid']
		);

		if ($AddData && is_array($AddData)){

			$nid	=	$this -> insert_into('zhaopinhui_com',$AddData);

		}

		return $nid;

	}

	/**
	 * @desc 获取招聘会场地
	 */
	public function getZphSpaceList($whereData="" , $data=array()) {

        $select     =   $data['field'] ? $data['field'] : '*';

        $List       =   $this -> select_all('zhaopinhui_space', $whereData, $select);

        if ($data['utype'] == 'index') {

            if (is_array($List)) {

                foreach ($List as $v) {
                    $keyid[] = $v['id'];
                }

                $keyid          =   pylode(',', $keyid);
                $spacelistall   =   $this -> getZphSpaceList(array('keyid' => array('in', $keyid), 'orderby' => 'sort,asc'));

                if (!empty($data['id'])){
                    // 查询后台设定的不可预订展位
                    $zph  =  $this->getInfo(array('id'=>$data['id']),array('field'=>'reserved'));

                    if (!empty($zph)){

                        $reserved  =  explode(',', $zph['reserved']);
                    }

                    $comlist        =   $this -> getZphCompanyList(array('zid' => $data['id']));

                    if (is_array($comlist)) {

                        foreach ($comlist as $val) {
                            $uids[]     =   $val['uid'];
                        }

                        $companylist    =   $this -> select_all('company', array('uid' => array('in', pylode(',', $uids))), '`uid`,`name`,`shortname`');

                        foreach ($comlist as $k => $v) {
                            foreach ($companylist as $val) {
                                if ($v['uid'] == $val['uid']) {
                                    if ($val['shortname']) {
                                        $comlist[$k]['name']    =   $val['shortname'];
                                    } else {
                                        $comlist[$k]['name']    =   $val['name'];
                                    }
                                }
                            }
                        }
                        foreach ($spacelistall as $k => $v) {
                            $spacelistall[$k]['comstatus']      =   '-1';

                            if ($v['price'] > 0){
                                if ($this->config['com_integral_online'] == 3){
                                    $spacelistall[$k]['price_n']    =   $v['price'];
                                    $spacelistall[$k]['unit']       =   $this->config['integral_priceunit'].$this->config['integral_pricename'];
                                }else{
                                    $spacelistall[$k]['price_n']    =   $v['price']/$this->config['integral_proportion'];
                                    $spacelistall[$k]['unit']       =   '元';
                                }
                            }else{
                                $spacelistall[$k]['price_n']    =   '免费';
                            }
                            if (!empty($reserved) && in_array($v['id'], $reserved)){
                                $spacelistall[$k]['comstatus']  =   3;
                            }
                            foreach ($comlist as $val) {
                                if ($v['id'] == $val['bid']) {
                                    $spacelistall[$k]['comstatus']  =   $val['status'];
                                    $spacelistall[$k]['uid']        =   $val['uid'];
                                    $spacelistall[$k]['comname']    =   $val['name'];
                                    $spacelistall[$k]['joblist']    =   $val['joblist'];
                                }
                            }
                        }
                    }
                    foreach ($List as $k => $v) {
                        foreach ($spacelistall as $val) {
                            if ($v['id'] == $val['keyid']) {
                                $List[$k]['list'][] = $val;
                            }
                        }
                    }
                }
            }
        }
        return $List;
    }

	/**
	* @desc 添加招聘会场地
	*/
	public function addZphSpaceInfo($data = array()){


	    $name  =   array();

	    foreach ($data['name'] as $val){
	        if ($val) {
	            $name[]    =   $val;
	        }
	    }

	    $addData   =   array();

	    foreach ($name as $k => $v){
	        $addData[$k]['keyid']      =   $data['keyid'];
	        $addData[$k]['price']      =   $data['price'];
	        $addData[$k]['name']       =   $v;
	        $addData[$k]['sort']       =   $data['sort'];
	        $addData[$k]['content']    =   $data['content'];
	        if($data['pic']){
	            $addData[$k]['pic']	   =   $data['pic'];
	        }
	    }

	    $nid   =   $this->DB_insert_multi('zhaopinhui_space', $addData);

		return $nid;

	}

	/**
	* @desc 修改招聘会场地
	*/
	public function upZphSpaceInfo($whereData, $data = array()){

		if(!empty($whereData)) {

			$PostData	=	array(

				'keyid'		=>	$data['keyid'],

				'price'		=>	$data['price'],

				'name'		=>	$data['name'],

				'sort'		=>	$data['sort'],

				'content'	=>	$data['content'],

			);
			if($data['pic']){
				$PostData['pic']	=	$data['pic'];
			}
			if ($PostData && is_array($PostData)){

				$nid	=	$this -> update_once('zhaopinhui_space',$PostData,array('id'=>$whereData['id']));

			}

			return $nid;

		}

	}

	function upZphSpaceInfos($whereData, $data = array()){

		if(!empty($whereData)){

			$nid	=	$this -> update_once('zhaopinhui_space',$data,$whereData);

		}

		return $nid;

	}

	/**
	* @desc 删除招聘会场地
	*/
	public function delZphSpace($delId){

		if(empty($delId)){

			return	array(

			  'errcode' => 8,

			  'msg' 	=> '请选择要删除的数据！',
            );

		}else{
			if(is_array($delId)){

				$delId	=	pylode(',',$delId);

				$return['layertype']	=	1;

			}else{

				$return['layertype']	=	0;
			}

			$delid	=	$this -> delete_all('zhaopinhui_space',array('id' => array('in',$delId)),'');

			if($delid){

				$return['msg']		=	'招聘会场地';

				$return['errcode']	=	$delid ? '9' :'8';

				$return['msg']		=	$delid ? $return['msg'].'删除成功！' : $return['msg'].'删除失败！';

			}

		}

		return $return;

	}

	/**
	 * @desc 获取招聘会场地详细信息
	 */
	public function getZphSpaceInfo($whereData , $data = array('field'=>null)){

	    $select =   $data['field'] ? $data['field'] : '*';

        $Info   =   $this -> select_once('zhaopinhui_space', $whereData, $select);

        if (!empty($Info)) {

            if ($Info['content']) {

                $subject            =   strip_tags($Info['content']); // 去除html标签
                $pattern            =   '/\s/'; // 去除空白
                $Info['content_n']  =   preg_replace($pattern, '', $subject);
            }

            if (!empty($data['pic'])) { // 缩略图
                $Info['pic_n']      =   checkpic($Info['pic']);
            }
        }

        return $Info;
    }

	/**
	 * @desc   修改参会企业表信息（审核，分站）
	 */
	public  function upZphCom($id , $data = array()){
        $where  =   array();

        if (! empty($id)) {

            $where['id']    =   array( 'in', pylode(',', $id));

            if ($data['status']) {

                $updata     =   array('status' => $data['status'],'statusbody' => $data['statusbody']);
            }else if ($data['did']) {

                $updata     =   array('did' => $data['did']);
            }

            $nid            =   $this->update_once('zhaopinhui_com', $updata, $where);

            if (!empty($data['status'])) {

                $List           =   $this -> getZphCompanyList($where, array('field' => '`zid`,`uid`,`com_name`'));

                /* 消息前缀 */
                $tagName        =   '参会企业';

                if (! empty($List)) {

                    foreach ($List as $v) {

                        $uids[] =   $v['uid'];

                        if ($updata['status'] == 2) {

                            $statusInfo         =   $tagName . ':<a href="zphtpl,'.$v['zid'].'">'.$v['com_name'].'</a>,审核未通过';

                            if ($updata['statusbody']) {

                                $statusInfo     .= ' , 原因：' . $updata['statusbody'];
                            }

                            $msg[$v['uid']]     =   $statusInfo;
                        } elseif ($updata['status'] == 1) {

                            $msg[$v['uid']]     =   $tagName . ':<a href="zphtpl,'.$v['zid'].'">'.$v['com_name'].'</a>,已审核通过';
                        }
                    }
                    // 发送系统通知
                    include_once ('sysmsg.model.php');
                    $sysmsgM    = new sysmsg_model($this->db, $this->def);
                    $sysmsgM -> addInfo(array('uid' => $uids,'usertype'=>2, 'content' => $msg));
                }
            }

            return $nid;
        }
    }

	/**
	 * @desc   修改参会企业表信息排序
	 */
	public  function upZphComSort($id , $data = array()){
        $where  =   array();

        if (! empty($id)) {

            $where['id']    =   $id;

            $nid            =   $this->update_once('zhaopinhui_com', $data, $where);

            return $nid;
        }
    }

	/**
	* @desc  删除参会企业
	*/
	public function delZphCom($delId = null, $data = array()){

        if (empty($delId)) {

            $return         =   array('errcode' => 8, 'msg' => '请选择要删除的数据！');
        } else {

            if (is_array($delId)) {

                $delId                  =   pylode(',', $delId);

                $return['layertype']    =   1;
            } else {

                $return['layertype']    =   0;
            }
			if($data['utype'] != 'admin'){

				$delWhere	=	array('id' => array('in', $delId),'uid'=>$data['uid']);
			}else{
				$delWhere	=	array('id' => array('in', $delId));
			}
            $return['id']       =   $this -> delete_all('zhaopinhui_com', $delWhere, '');

            $return['errcode']  =   $return['id'] ? 9 : 8;

            $msg                =   '招聘会参会企业（ID：'.$delId.'）';

            $return['msg']      =   $return['id'] ? $msg.'删除成功！' : '删除失败！';

        }

        return $return;
    }

	/**
	* @desc  招聘会图片
	*/
	public function getZphPicList($whereData,$data=array()) {

		$select		=	$data['field'] ? $data['field'] : '*';

		$List	=   $this -> select_all('zhaopinhui_pic',$whereData,$select);

		if($List&&is_array($List)){
			foreach($List as $key => $v){
				if ($v['pic']) { // 缩略图
					$List[$key]['pic_n']     =   checkpic($v['pic']);
				}
			}
		}
		return $List;
	}

	/**
	* @desc  招聘会图片详细信息
	*/
	public function getZphPicInfo($whereData,$data	=	array()){

		$select		=	$data['field'] ? $data['field'] : '*';

		$Info	=	$this -> select_once('zhaopinhui_pic', $whereData, $select);

		return $Info;

	}

	/**
	* @desc  添加招聘会图片
	*/
	public function addZphPicInfo($data	=	array()){

		$AddData	=	array(

			'title'		=>	$data['title'],

			'sort'		=>	$data['sort'],

			'zid'		=>	$data['zid'],

			'did'		=>	$data['did'],

		);
		if($data['pic']){
			$AddData['pic']	= $data['pic'];
		}
		if ($AddData && is_array($AddData)){

			$nid	=	$this -> insert_into('zhaopinhui_pic',$AddData);

		}

		return $nid;

	}

	/**
	* @desc  修改招聘会图片
	*/
	public function upZphPicInfo($whereData, $data = array()){

		if(!empty($whereData)) {

			$PostData	=	array(

				'title'		=>	$data['title'],

				'sort'		=>	$data['sort'],

			    'did'		=>	$data['did']

			);
			if($data['pic']){
				$AddData['pic']	= $data['pic'];
			}
			if ($PostData && is_array($PostData)){

				$nid  =  $this -> update_once('zhaopinhui_pic',$PostData,array('id'=>$whereData['id']));

			}

			return $nid;

		}

	}

	/**
	* @desc  删除招聘会图片
	*/
	public function delZphPic($where=array()){
        $delid  =   0;

        if (! empty($where)) {

            $delid  =   $this -> delete_all('zhaopinhui_pic', $where, '');
        }

        return $delid;
    }

	/**
	* @desc  招聘会设置缩略图
	*/
	public function getSetThembInfo($whereData,$data = array()){

		$select				=	$data['field'] ? $data['field'] : '*';

		$SetThembInfo		=	$this -> select_once('zhaopinhui_pic', $whereData, $select);

		if($SetThembInfo['pic']){

				$this -> update_once('zhaopinhui_pic',array('is_themb'=>''),array('zid'=>$SetThembInfo['zid']));

				$pic='.'.$SetThembInfo['pic'];

				$this -> update_once('zhaopinhui',array('is_themb'=>$pic),array('id'=>$SetThembInfo['zid']));

				$this -> update_once('zhaopinhui_pic',array('is_themb'=>'1'),array('id'=>$SetThembInfo['id']));

		}

		return $SetThembInfo;

	}

	/**
	 * @desc   招聘会预定
	 */
	public function ajaxZph($data = array())
	{

	    $uid        =   intval($data['uid']);
	    $spid		=   intval($data['spid']);
		$jobid		=   $data['jobid'];
	    $usertype   =   intval($data['usertype']);

	    $did        =   $data['did'] ? intval($data['did']) : $this->config['did'];

	    $zid        =   $data['zid'] ? intval($data['zid']) : '';
	    $id         =   $data['id'] ? intval($data['id']) : '';

	    $return     =   array();

	    require_once ('statis.model.php');
	    $statisM    =   new statis_model($this->db, $this->def);

	    require_once ('company.model.php');
	    $companyM   =   new company_model($this->db, $this->def);

	    $online     =   $this->config['com_integral_online'];

	    //判断后台是否开启该单项购买
        $single_can =   @explode(',', $this->config['com_single_can']);
        $serverOpen =   1;
        if(!in_array('zph',$single_can)){
            $serverOpen =   0;
        }

	    if (empty($uid) || empty($usertype)) {

	        $return['login']    =   1;
	    } else if ($usertype != 2) {

	        $return['msg']      =   '企业用户才可以预定招聘会！';
	    } else {

	        $comInfo  =  $this->select_once('company',array('uid' => $uid), '`name`,`r_status`');

	        if(empty($comInfo['name'])){

	            $return['msg']	=	'请先完善基本资料！';
    	   }else{

    	        $zph    =   $this->getInfo(array('id' => $zid), array('field' => '`starttime`,`endtime`'));

    	        if (strtotime($zph['starttime']) < time()) {

    	            $return['msg']  =   '招聘会已经开始！';
    	        } else if (strtotime($zph['endtime']) < time()) {

    	            $return['msg']  =   '招聘会已经结束！';
    	        } else {
    	            if ($comInfo['r_status'] != 1) {
    	                $return['msg']  =   '您的账号未通过审核，请联系管理员加速审核进度！';
    	            } else {

    	                // 判断当天是否已达到最大报名次数
    	                $result     =   $companyM->comVipDayActionCheck('zph', $uid);

    	                if ($result['status'] != 1) {
    	                    return $result;
    	                }
    	                $zphzw  =  $this->getZphComInfo(array('bid' => $id, 'zid' => $zid));

    	                if (!empty($zphzw)){

    	                    $return['status']  =  3;
    	                    $return['msg']     =  '展位已有其他企业报名，请重新选择展位';

    	                    return $return;
    	                }

    	                $zphcom  =   $this->getZphComInfo(array('uid' => $uid, 'zid' => $zid));

    	                if (!empty($zphcom)) {

    	                    if ($zphcom['status'] == 2) {

    	                        $return['msg'] = '您的报名未通过审核，请联系管理员！';
    	                    } else {

    	                        $return['msg'] = '您已报名该招聘会！';
    	                    }

    	                    return $return;
    	                } else {

    	                    $suid   =   !empty($spid) ? $spid : $uid;

    	                    $statis =   $statisM -> getInfo($suid, array('usertype' => $usertype, 'field' => '`rating_type`,`vip_etime`,`zph_num`,`integral`'));

    	                    $space  =   $this -> getZphSpaceInfo(array('id' => $id));

    	                    $com    =   $companyM -> getInfo($uid, array('field' => '`name`'));

    	                    $zData              =   array();
    	                    $zData['uid']       =   $uid;
    	                    $zData['usertype']  =   $usertype;
    	                    $zData['com_name']  =   $com['name'];
    	                    $zData['status']    =   1;
    	                    $zData['did']       =   $did;
							$zData['jobid']		=   $jobid;
    	                    $zData['id']        =   $id;
    	                    $zData['zid']       =   $zid;

    	                    $zData['fuid']      =   $uid;

    	                    $price  =   floatval($space['price'] / $this->config['integral_proportion']); // 展位价格

    	                    if (isVip($statis['vip_etime'])) {

    	                        if ($statis['rating_type'] == 1) {

    	                            if ($price == 0 && $statis['zph_num'] == 0) { // 免费报名，直接预定

    	                                $return = $this->reserveZph($zData);

    	                                return $return;
    	                            }

    	                            // 没有招聘会报名次数
    	                            if ($statis['zph_num'] == 0) {

    	                                if(empty($spid)){

    	                                    if ($online != 4) { // 套餐模式

    	                                        if ($online == 3) { // 积分消费
    	                                        	if($serverOpen){
    	                                            	$return['msg']      =   "您的等级特权已经用完，继续报名将消费 <span style=color:red;>" . $space['price'] . "</span> ".$this->config['integral_pricename']."，是否继续？";
    	                                            }else{
    	                                            	$return['msg']          =   "您的等级特权已经用完，可以<a href=\"" . $this->config['sy_weburl'] . "/wap/member/index.php?c=rating\" style=\"color:red;cursor:pointer;\">购买会员</a>！";
    	                                            }
    	                                            $return['url']      =   $this->config['sy_weburl'] . 'wap/member/index.php?c=getserver&id=' . $uid . '&server=15';
    	                                            $return['jifen']    =   $space['price'];
    	                                            $return['integral'] =   intval($statis['integral']);
    	                                            $return['pro']      =   $this->config['integral_proportion'];
    	                                        } else {

    	                                            $return['url']      =   $this->config['sy_weburl'] . 'wap/member/index.php?c=getserver&id=' . $uid . '&server=15';
    	                                            if($serverOpen){
    	                                            	$return['msg']      =   "您的等级特权已经用完，继续报名将消费 <span style=color:red;>" . $price . "</span> 元，是否继续？";
    	                                            }else{
    	                                            	$return['msg']          =   "您的等级特权已经用完，可以<a href=\"" . $this->config['sy_weburl'] . "/wap/member/index.php?c=rating\" style=\"color:red;cursor:pointer;\">购买会员</a>！";
    	                                            }
    	                                            $return['price']    =   $price;
    	                                        }
    	                                    } else {
    	                                    	$return['price']    =   $price;
    	                                        $return['url']          =   $this->config['sy_weburl'] . 'wap/member/index.php?c=rating';
    	                                        $return['msg']          =   "您的等级特权已经用完，可以<a href=\"" . $this->config['sy_weburl'] . "/wap/member/index.php?c=rating\" style=\"color:red;cursor:pointer;\">购买会员</a>！";
    	                                    }
    	                                    $return['zid']      =   $zid;
    	                                    $return['bid']      =   $id;
    	                                    $return['status']   =   2;
    	                                }else{

    	                                    $return['msg']  =   '当前账户套餐余量不足，请联系主账户增配！';
    	                                }

    	                                return $return;
    	                            } else {

    	                                $statisM -> upInfo(array('zph_num' => array('-', 1)), array('uid' => $suid, 'usertype' => $usertype));
    	                                $return     =   $this->reserveZph($zData);
    	                                return $return;
    	                            }
    	                        } else if ($statis['rating_type'] == 2) {

    	                            $return         =   $this->reserveZph($zData);
    	                            return $return;
    	                        }
    	                    } else { // 过期会员

    	                        if ($price == 0) {

    	                            $return     =   $this->reserveZph($zData);
    	                            return $return;
    	                        }

    	                        if(empty($spid)){

    	                            if ($online != 4) {

    	                                if ($online == 3) { // 积分消费

     	                                    $return['jifen']    =   $space['price'];
    	                                    $return['integral'] =   intval($statis['integral']);
    	                                    $return['pro']      =   $this->config['integral_proportion'];
    	                                } else {
    	                                    $return['price']    =   $price;
    	                                }
    	                                $return['msg']	=   "你的会员已到期，请先购买会员！";
    	                            } else {
    	                                $return['url']  =   $this->config['sy_weburl'] . 'wap/member/index.php?c=rating';
    	                                $return['msg']  =   "你的会员已到期，你可以<a href='" . $this->config['sy_weburl'] . "/wap/member/index.php?c=rating' style='color:red;cursor:pointer;'>购买会员</a>！";
    	                            }
    	                            $return['zid']      =   $zid;
    	                            $return['bid']      =   $id;
    	                            $return['status']   =   2;

    	                        }else{

    	                            $return['msg']  =   '当前账户会员已过期，请联系主账户升级！';
    	                        }
    	                        return $return;
    	                    }
    	                }
    	            }
    	        }
    	    }
	    }
	    return $return;
	}

	/**
	 * @Desc   招聘会报名
	 *
	 * @param array $data
	 */
	private function reserveZph($data = array()){

	    $bid       =   intval($data['id']);    // 展位ID

	    $space     =   $this -> getZphSpaceInfo(array('id' => $bid));

	    $sid       =   $this -> getZphSpaceInfo(array('id' => $space['keyid']));

	    $sql       =   array(

	        'uid'          =>  $data['uid'],
	        'com_name'     =>  $data['com_name'],
	        'zid'          =>  $data['zid'],
	        'ctime'        =>  time(),
	        'status'       =>  0,
	        'did'          =>  $data['did'],
			'jobid'		   =>  $data['jobid'],
	        'sid'          =>  $sid['keyid'],
	        'cid'          =>  $space['keyid'],
	        'bid'          =>  $bid
	    );

	    $nid       =   $this->insert_into('zhaopinhui_com', $sql);

	    $return    =   array();
	    if ($nid){
	        require_once ('log.model.php');
	        $logM              =   new log_model($this->db, $this->def);
	        $logM -> addMemberLog($data['fuid'], $data['usertype'],'报名招聘会,ID:'.$data['zid'].',展位：'.$bid,14,1);

	        $return['status']  =   1;
	        $return['msg']     =   '报名成功,等待管理员审核！';

	    }else{
	        $return['msg']     =   '报名失败,请稍候重试！';
	    }

	    return $return;
	}
}
?>