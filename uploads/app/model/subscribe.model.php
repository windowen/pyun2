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
class subscribe_model extends model{

	/**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type=''); 
    }
	/**
     *  @desc   获取缓存数据
     */
    private function getClass($options){
        
        if (!empty($options)){
            
            include_once ('cache.model.php');
            
            $cacheM     =   new cache_model($this->db, $this->def);
            
            $cache      =   $cacheM -> GetCache($options);
            
            return $cache;
        }
    }
	/**
	 * 获取订阅列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	public function getList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('subscribe',$whereData);
		
		if(!empty( $List )){
			$cache   =   $this -> getClass(array('job','city','user'));
			foreach($List as $k=>$v){
				$jobname	=	$cityname	=	array();
				$jobname[]	=	$cache['job_name'][$v['job1']];
				$jobname[]	=	$cache['job_name'][$v['job1_son']];
				if($cache['job_name'][$v['job_post']]){
					
					$jobname[]			=	$cache['job_name'][$v['job_post']];
				}
				
				$cityname[]				=	$cache['city_name'][$v['provinceid']];
				
				if($cache['city_name'][$v['cityid']]){
					$cityname[]			=	$cache['city_name'][$v['cityid']];
				}
				if($cache['city_name'][$v['three_cityid']]){
					$cityname[]			=	$cache['city_name'][$v['three_cityid']];
				} 
				
				$List[$k]['jobname']	=	@implode(',',$jobname);
				$List[$k]['city_name']	=	@implode('-',$cityname);
				if($v['minsalary'] && $v['maxsalary']){
	                if($this ->config['resume_salarytype']==1){
	                	$List[$k]['salary']  =  $v['minsalary'].'-'.$v['maxsalary'];
	                }else{
	                	if($v['maxsalary']<1000){
	                		if($this->config['resume_salarytype']==2){
	                            $List[$k]['salary']  =  '1千以下';
	                        }elseif($this->config['resume_salarytype']==3){
	                            $List[$k]['salary']  =  '1K以下';
	                        }elseif($this->config['resume_salarytype']==4){
	                            $List[$k]['salary']  =  '1k以下';
	                        }
	                	}else{
	                		$List[$k]['salary']  =  changeSalary($v['minsalary']).'-'.changeSalary($v['maxsalary']);
	                	}
	                }
	            }elseif ($v['minsalary']){
	                if($this ->config['resume_salarytype']==1){
	                	$List[$k]['salary']  =  $v['minsalary'];
	                }else{
	                	$List[$k]['salary']  =  changeSalary($v['minsalary']);
	                }
	            }elseif ($v['maxsalary']){
	                if($this ->config['resume_salarytype']==1){
	                	$List[$k]['salary']  =  $v['maxsalary'];
	                }else{
	                	$List[$k]['salary']  =  changeSalary($v['maxsalary']);
	                }
	            }else{
	                
	                $List[$k]['salary']  =  '面议';
	            }
			}
		}

		return	$List;
	}
	public function getInfo($where=array(), $data = array()) {
        
        $select     =   $data['field'] ? $data['field'] : '*';
		$Info    	=   $this -> select_once('subscribe', $where, $select);
		return $Info;
    }
	function upInfo($data=array(),$Where=array()){
		
        return $this->update_once('subscribe',$data,$Where);
    }
	function addInfo($data=array()){
		
        return $this->insert_into('subscribe',$data);
    }
	/**
	 * 获取订阅记录列表subscriberecord
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	public function getRecordList($whereData,$data=array()){
		$ListNew	=	array();
		$List		=	$this -> select_all('subscriberecord',$whereData);

		return	$List;
	}
	
	/**
	 * 删除下载简历
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	public function del($id,$data){
		
	    $limit                =  'limit 1';
	    $return['layertype']  =	 0;
	    
	    if (!empty($id)){
	        
	        if(is_array($id)){
	            
	            $id  =  pylode(',', $id);
	            $return['layertype']  =  1;
	            $limit                =  '';
	        }
			if($data['uid']){
				$delWhere	=	array('uid'=>$data['uid'],'id'=>array('in',$id));
			}else{
				$delWhere	=	array('id'=>array('in',$id));
			}

	        $nid      =  $this -> delete_all('subscribe',$delWhere,$limit);
	        
	        if ($nid){
				
				$this -> delete_all('subscriberecord',array('sid'=>array('in',$id)),$limit);
				$this -> addMemberLog($data['uid'],$data['usertype'],"删除订阅（ID:".$id."）",5,3);
	            $return['msg']      =  '删除成功';
	            $return['errcode']  =  '9';
	        }else{
	            $return['msg']      =  '删除失败';
	            $return['errcode']  =  '8';
	        }
	    }else{
	        $return['msg']      =  '请选择您要删除的记录';
	        $return['errcode']  =  '8';
	    }
	    return $return;
	}

	/**
	 * 应用中增加订阅
	 * 判断了是增加或是修改
	 * 2019-07-12
	 */
	public function createSubscribe($data = array()){

		$res	=	array(
			'errcode'	=>	8,
			'msg'		=>	''
		);

		if(empty($data['uid'])){
			$res['msg']	=	'您还未登录！';
			return $res;
		}

		//个人 企业用户才可订阅
		if(empty($data['usertype']) || !in_array($data['usertype'], array(1, 2))){
			$res['msg']	=	'只有个人和企业用户才可订阅！';
			return $res;
		}

		$sid			=	intval($data['sid']);
		$data['type']	=	intval($data['type']);

		//判断订阅类型
		if($data['usertype'] != $data['type']){
			$res['msg']	=	'订阅类型错误！';
			return $res;
		}

		//用户绑定的微信信息
		$userField	=	'`email`,`wxid`, `wxopenid`';
		$userInfo	=	$this -> select_once('member' , array('uid' => $data['uid']), $userField);
		if(empty($userInfo)){
			$res['msg']	=	'用户信息错误！';
			return $res;
		}

		//未传入订阅的id时，通过uid type判断是否已订阅
		if(empty($sid)){
			$oldField	=	array('field' => '`id`');
			$oldSub		=	$this -> getInfo(array('uid' => $data['uid'], 'type' => $data['type']), $oldField);
			if(!empty($oldSub)){
				$sid	=	$oldSub['id'];
			}
		}

		if(empty($userInfo['wxid']) && empty($userInfo['wxopenid'])){
			require_once ('weixin.model.php');
			$wxM		=	new weixin_model($this->db, $this->def);
			$qrcode		=	$wxM -> applyWxQrcode($_POST['wxloginid']);
			if(empty($qrcode)){
				$res['msg']	=	'暂未绑定微信公众号，无法订阅！';
			}else{
				$res['msg']	=	'马上绑定微信, 接收订阅信息！';
			}
			return $res;
		}

		//处理参数
		$data['email']		=	$userInfo['email'];
		$data['ctime']		=	time(); 
		if(empty($sid)){
			$data['uid']	=	$data['uid'];
			$sid			=	$this -> addInfo($data);
		}else{
			unset($data['sid']);

			$this -> upInfo($data, array('id' => $sid));
		}

		$res['errcode']		=	9;
		$res['msg']			=	'订阅成功！';
		return $res;

	}

	/**
	 * 发送订阅
	 * type 1-订阅职位，2-订阅人才
	 * limit 发送订阅信息的数量默认为5
	 * 2019-07-12
	 */
	public function sendSubscribe($data = array()){

		$res	=	array(
			'errcode'	=>	8,
			'msg'		=>	''
		);
		if(empty($data['type']) || !in_array($data['type'], array(1, 2))){
			$res['msg']	=	'缺少订阅类型参数!';
			return $res;
		}

		//设置默认订阅数量
		$data['limit']	=	empty($data['limit']) ? 5 : intval($data['limit']);

		//获取需要发送的订阅
		$subscribe		=	$this -> select_all('subscribe', array(
			'type'	=>	$data['type'],
			'time'	=>	array('>', 0)));
		if(empty($subscribe)){
			$res['msg']	=	'暂无订阅数据!';
			return $res;
		}

		$currentTime	=	time();
		foreach ($subscribe as $sv) {
			//距离当前的天数
			$ytime		=	$currentTime - $sv['ctime'];
			$endDays	=	ceil(bcdiv($ytime, 86400, 2));

			//发送周期的整数则发送订阅信息
			if(bccomp(bcmod($endDays, $sv['time']), 0, 2) != 0){
				continue;
			}
		
			$whereData	=	array();

			//增加更新时间条件
			if(!empty($sv['cycle_time']) && $sv['cycle_time'] > 0){
				$beginTime					=	$sv['cycle_time']*86400;
				$whereData['lastupdate']	=	array('>=', $currentTime - $beginTime);
			}

			//增加薪水条件
			if(!empty($sv['minsalary'])){
				$whereData['minsalary']		=	array('>=', $sv['minsalary']);
			}
			if(!empty($sv['maxsalary'])){
				$whereData['maxsalary']		=	array('<=', $sv['maxsalary']);
			}

			

			$whereData['limit']				=	$data['limit'];
			$whereData['orderby']			=	'lastupdate';
			//1-订阅职位，2-订阅人才
			if($sv['type'] == 2){
				//省份城市条件
				if(!empty($sv['three_cityid'])){
					$whereData['city_classid']	=	array('findin', $sv['three_cityid']);
				}elseif (!empty($sv['cityid'])) {
					$whereData['city_classid']	=	array('findin', $sv['cityid']);
				}elseif (!empty($sv['provinceid'])) {
					$whereData['city_classid']	=	array('findin', $sv['provinceid']);
				}
				if(!empty($sv['job_post'])){
					$whereData['job_classid']	=	array('findin', $sv['job_post']);
				}elseif (!empty($sv['job1_son'])) {
					$whereData['job_classid']	=	array('findin', $sv['job1_son']);
				}elseif (!empty($sv['job1'])) {
					$whereData['job_classid']	=	array('findin', $sv['job1']);
				}
				$whereData['status']			=	1;
				$whereData['r_status']			=	1;

				$subList	=	$this -> select_all('resume_expect', $whereData, '`name`');
			}elseif ($sv['type'] == 1) {
				//省份城市条件
				if(!empty($sv['three_cityid'])){
					$whereData['three_cityid']	=	$sv['three_cityid'];
				}elseif (!empty($sv['cityid'])) {
					$whereData['cityid']		=	$sv['cityid'];
				}elseif (!empty($sv['provinceid'])) {
					$whereData['provinceid']	=	$sv['provinceid'];
				}
				if(!empty($sv['job_post'])){
					$whereData['job_post']		=	$sv['job_post'];
				}elseif (!empty($sv['job1_son'])) {
					$whereData['job1_son']		=	$sv['job1_son'];
				}elseif (!empty($sv['job1'])) {
					$whereData['job1']			=	$sv['job1'];
				}
				$whereData['state']				=	1;
				$subList	=	$this -> select_all('company_job', $whereData, '`name`');				
			}
			//没查询到数据直接pass
			if(empty($subList)){
				continue;
			}

			//订阅的职位 简历名称
			$nameArr		=	array();
			foreach($subList as $val){
				$nameArr[]	=	$val['name'];
			}
			$contentStr		=	@implode(',', $nameArr);

			
			if($wxRes['errcode']==8){
				continue;
			}

			//保存相关订阅记录
			$recordArr		=	array(
				'sid'		=>	$sv['id'],
				'uid'		=>	$sv['uid'],
				'type'		=>	$sv['type'],
				'content'	=>	$contentStr,
				'time'		=>	$currentTime
			);

			$this -> insert_into('subscriberecord', $recordArr);
		}

		$res['errcode']	=	9;
		return $res;
	}
	
}
?>