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
class talent_model extends model{
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
     * 获取lt_talent	列表
     * $whereData       查询条件
     * $data            自定义处理数组 scene 场景值，定制不同场景返回的数据
     */
    public function getList($whereData, $data = array()) {
		
        $List							=	array();
        $data['field']  				=	empty($data['field']) ? '*' : $data['field'];
		$List           				=   $this -> select_all('lt_talent', $whereData, $data['field']);
		$cache							=	$this -> getClass(array('user','industry','job','city'));
		if(!empty($List)){
			foreach($List as $v){
				$ids[]		=	$v['id'];
				if($v['uid'] && !in_array($v['uid'],$uids)){
					$uids[]		=	$v['uid'];
				}
			}
			include_once('userinfo.model.php');
			$memberM			=	new userinfo_model($this->db, $this->def);
			$mWhere['uid']		=	array('in', pylode(',', $uids));
			$mData['field']		=	'`username`, `uid`';
			$memberList			= 	$memberM -> getList($mWhere,$mData);
			
			foreach($List as $k => $v){
				foreach($memberList as $val){
					if($v['uid']==$val['uid']){
						$List[$k]['user']		=	$val['username'];
					}
				}
				if($v['linktel']){
					$List[$k]['linktel']	=	$v['linktel'];
				}
				if($v['edu']){
					$List[$k]['edu_n']		=  $cache['userclass_name'][$v['edu']];
				}
				if($v['exp']){
					$List[$k]['exp_n']		=  $cache['userclass_name'][$v['exp']];
				}
				if($v['provinceid']){
					$List[$k]['city_one']	=	$cache['city_name'][$v['provinceid']];
				}
				if($v['cityid']){
					$List[$k]['city_two']	=	$cache['city_name'][$v['cityid']];
				}
				if($v['three_cityid']){
					$List[$k]['city_three']	=	$cache['city_name'][$v['three_cityid']];
				}
				if ($v['minsalary'] || $v['maxsalary']) {
                     
                    if($v['minsalary'] && $v['maxsalary']){
                        
                        $List[$k]['salary']             =  $v['minsalary'].'-'.$v['maxsalary'];
                    }elseif ($v['minsalary']){
                        
                        $List[$k]['salary']             =  $v['minsalary'];
                    }elseif ($v['maxsalary']){
                        
                        $List[$k]['salary']             =  $v['maxsalary'];
                    }
                }else{
                    
                    $List[$k]['salary']             =  '面议';
                }
			}
		}
        return $List;    
	}

    /**
     * @desc 获取lt_talent	详情
     * $whereData       查询条件
     * $data            自定义处理数组 scene 场景值，定制不同场景返回的数据
     */
    public function getInfo($whereData, $data = array()) {
		$Info			=	array();
        $data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$Info         	=   $this -> select_once('lt_talent', $whereData, $data['field']);
		if(!empty($Info)){
			$cache						=	$this -> getClass(array('user','hy','job','city'));
			if($Info['sex']){
				$Info['sex_n']			= $cache['user_sex'][$Info['sex']];
			}
			if($Info['hy']){
				$Info['hy_n']			= $cache['industry_name'][$Info['hy']];
			}
			if($Info['edu']){
				$Info['edu_n']		= $cache['userclass_name'][$Info['edu']];
			}
			if($Info['exp']){
				$Info['exp_n'] 		= $cache['userclass_name'][$Info['exp']];
			}
			if($Info['provinceid']){
				$Info['city_one_n'] 	= $cache['city_name'][$Info['provinceid']];
			}
			if($Info['provinceid']){
				$Info['city_one_n'] 	= $cache['city_name'][$Info['provinceid']];
			}
			if($Info['three_cityid']){
				$Info['city_three_n'] 	= $cache['city_name'][$Info['three_cityid']];
			}
			if($Info['jobstatus']){
				$Info['jobstatus_n'] 	= $cache['userclass_name'][$Info['jobstatus']];
			}
			if($Info['minsalary'] && $Info['maxsalary']){
            
				$Info['salary']      =   $Info['minsalary'].'-'.$Info['maxsalary'];
				
			}elseif($Info['minsalary']){
				
				$Info['salary']      =	$Info['minsalary'].'以上';
				
			}elseif($Info['maxsalary']){
				
				$Info['salary']      =	$Info['maxsalary'].'以下';
				
			}
			$Info['report'] 			=	'随时到岗';
			$Info['type'] 				=	'全职';
		
			return $Info;
		}   
    }
	/**
     * 删除lt_talent	详情
     * $whereData       查询条件
     */
    public function delTalent($whereData) {
	 

		$this -> delete_all('lt_talent', $whereData, '');
        return true;    
	}

	

	function telStatus($id,$tel,$code){
		if($id && $tel){
			if(!CheckMoblie($tel)){
				$error['msg'] = '手机号格式错误';
			}else{
				//查询当前号码是否被使用（仅考虑已被验证的情况）
				$telNum = $this->select_num('lt_talent',array('linktel'=>$tel,'telstatus'=>1));
				if($telNum>0){
					$error['msg'] = '手机号已被授权！';
				}else{
					$row=$this->select_once("company_cert",array('uid'=>$this->uid,'check'=>$tel,'type'=>2,'orderby'=>array('id,desc')),'check2');
					if(!empty($row)){
						if($row['check2']!=$code){
							$error['msg'] = '手机验证码不正确';
						}else{
							//已被他人填写但是未验证的手机号清空 以最新验证的为准
							$this->update_once("lt_talent",array('linktel'=>''),array('linktel'=>$tel));
							//更新当前验证的手机号
							$this->update_once("lt_talent",array('linktel'=>$tel,'telstatus'=>1),array('uid'=>$this->uid,'id'=>intval($id)));
							$error['error'] = '1';
							$error['msg'] = '验证成功';
						}
					}else{
						$error['msg'] = '验证码错误';
					}
				}
			}
		}else{
			$error['msg'] = '数据错误';
		}
		return $error;
	}
	function addTalent($data){
		if($data['id']){
			$info = $this->getInfo(array('id'=>intval($data['id']),'uid'=>$data['uid']),array('field'=>'`id`,`telstatus`'));
		}
		include_once ('cache.model.php');
        
        $cacheM     =   new cache_model($this->db, $this->def);
        
        $cache      =   $cacheM -> GetCache('city');
        $citymsg 	= 	false;
        if(!empty($cache['city_type'])){
        	if(empty($data['cityid'])){
        		$citymsg = true;
        	}
        }else{
        	if(empty($data['provinceid'])){
        		$citymsg = true;
        	}
        }
		$error['error'] = '0';
		//判断参数是否齐全
		if($data['id'] && empty($info)){
			$error['msg'] = '参数错误';
		}elseif(empty($data['name'])){
			$error['msg'] = '请填写姓名';
		}elseif(empty($data['sex'])){
			$error['msg'] = '请选择性别';
		}elseif(empty($data['age'])){
			$error['msg'] = '请填写年龄';
		}elseif(empty($data['edu'])){
			$error['msg'] = '请选择最高学历';
		}elseif(empty($data['exp'])){
			$error['msg'] = '请选择工作经验';
		}elseif(empty($data['minsalary'])){
			$error['msg'] = '请填写最低薪资需求';
		}elseif(!empty($data['maxsalary']) && $data['maxsalary']<=$data['minsalary']){
			$error['msg'] = '最高薪资必须大于最低薪资';
		}elseif($citymsg){
		    $error['msg'] = '请选择期望城市';
		}
		elseif(empty($data['living'])){
			$error['msg'] = '请填写现居住地';
		}elseif(empty($data['jobname'])){
			$error['msg'] = '请填写意向岗位';
		}elseif(empty($data['hy'])){
			$error['msg'] = '请选择所属行业';
		}elseif(empty($data['jobstatus'])){
			$error['msg'] = '请选择当前求职状态';
		}elseif(empty($data['expinfo'])){
			$error['msg'] = '请填写相关工作经历';
		}elseif(empty($data['eduinfo'])){
			$error['msg'] = '请填写相关教育经历';
		}elseif(empty($data['jobstatus'])){
			$error['msg'] = '请选择当前求职状态';
		}else{
			if($info['telstatus']!='1'){
				if(empty($data['telphone'])){
					$error['msg'] = '请输入求职者手机号';
				}elseif(!CheckMoblie($data['telphone'])){
					$error['msg'] = '手机号格式错误';
				}else{
					//检查手机号是否重复
					if($data['id']){
						$where['id']	=	array('<>',intval($data['id']));
					}
					$where['linktel']	=	$data['telphone'];
					$num 				=	$this->select_num('lt_talent',$where);
					if($num>0){
						$error['msg'] 	=	'相同简历已存在，手机号已被使用';
					}
				}
			}
		}
		if(empty($error['msg'])){
			$fieldData['name']			= trim($data['name']);
			$fieldData['sex']			= intval($data['sex']);
			$fieldData['age']			= intval($data['age']);
			$fieldData['exp'] 			= intval($data['exp']);
			$fieldData['edu'] 			= intval($data['edu']);
			$fieldData['living'] 		= $data['living'];
			$fieldData['jobname']		= $data['jobname'];
			$fieldData['hy'] 			= intval($data['hy']);
			$fieldData['minsalary'] 	= intval($data['minsalary']);
			$fieldData['maxsalary'] 	= intval($data['maxsalary']);
			//多选城市
			$fieldData['provinceid']	= intval($data['provinceid']);
			$fieldData['cityid'] 		= intval($data['cityid']);
			$fieldData['three_cityid']	= intval($data['three_cityid']);

			$fieldData['jobstatus']		= $data['jobstatus'];
			if($info['telstatus']!='1'){
				$fieldData['linktel']	= $data['telphone'];
			}
			$fieldData['expinfo'] 		= $data['expinfo'];
			$fieldData['eduinfo'] 		= $data['eduinfo'];
			$fieldData['projectinfo'] 	= $data['projectinfo'];
			$fieldData['skillinfo'] 	= $data['skillinfo'];
			
			if($data['id']){
				$nid = $this->update_once('lt_talent',$fieldData,array('id'=>intval($data['id']),'uid'=>$data['uid']));
				$error['msg'] = $nid?'更新简历成功':'更新简历失败';
			}else{
				
				$fieldData['did']	=	$data['did'];
				$fieldData['uid']	=	$data['uid'];
				
				$nid = $this->insert_into('lt_talent',$fieldData);
				$error['msg'] = $nid?'添加简历成功':'添加简历失败';
			}
			if($nid){
				$error['error']='1';
			}
		}
		return $error;
	}
	
	function getTalentNum($whereData = array()){
        return $this -> select_num('lt_talent', $whereData);
	}
}
?>