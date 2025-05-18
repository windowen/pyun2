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
class finder_model extends model{
	private function getClass($options){
	    if (!empty($options)){
	        
	        include_once('cache.model.php');
	        
	        $cacheM				=   new cache_model($this->def, $this->db);
	        
	        $cache				=   $cacheM -> GetCache($options);
	        return $cache;
	    }
	}
	/**
     * @desc   引用log类，添加用户日志   
     */
    private function addMemberLog($uid,$usertype,$content,$opera='',$type='') {
        require_once ('log.model.php');
        $LogM = new log_model($this->db, $this->def);
        return  $LogM -> addMemberLog($uid,$usertype,$content,$opera='',$type=''); 
    }
	private function insertfinder($para,$id='',$name='',$noiconv='',$data=array()){
		$data['name']	=	$name;
		$data['uid']	=	$data['uid'];
		$data['para']	=	$para;
		
		if($id){
			$this -> addMemberLog($data['uid'],$data['usertype'],"修改搜索器",28,2);
			return $this -> update_once("finder",$data,array('id'=>$id,'uid'=>$data['uid']));
		}else{
			$data['usertype']	=	$data['usertype'];
			$data['addtime']	=	time();
			$this -> addMemberLog($data['uid'],$data['usertype'],"添加搜索器",28,1);
			return $this -> insert_into("finder",$data);
		}
	}
	 /**
     * 获取finder      简历搜索器详情
     * $whereData       查询条件
     * $data            自定义处理数组
     */
	public function getInfo($whereData,$data=array()){
	    $field		=	empty($data['field']) ? '*' : $data['field'];
	    
		$info	=	$this -> select_once('finder',$whereData, $field);
		
		if($info && is_array($info)){
			
			if($info['para']){
				$para		=	@explode('##',$info['para']);
				foreach($para as $val){
					$arr				=	@explode('=',$val);
					$parav[$arr['0']]	=	$arr['1'];
				}
				if($parav['jobids']){
					$jobids	=	@explode(',',$parav['jobids']);
					foreach($jobids as $val){
						$jobname[]		=	$result['job_name'][$val];
					}
					$parav['jobname']	=	@implode(',',$jobname);
				}
				$List['parav']			=	$parav;
				
			}
			
		}
		$List['info']=$info;
		$cache	=$this->getClass(array('hy','job','user','city','com','uptime'));
		$List['cache']=$cache;
		return $List;
	}
	/**
	 * 获取简历搜索器列表
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	 
	public function getList($whereData,$data=array()){
		$List		=	$this -> select_all('finder',$whereData);
		
		if(!empty( $List )){
			$cache=$this->getClass(array('job','user','city','hy','com','uptime'));
			/*
			*@$uptime、@$adtime数组，要与model/user.class.php文件中的数组相同
			*/
			
			
			foreach($List as $k=>$v){
				$jobname	=	$findername	=	$arr	=	array();
				$para		=	@explode('##',$v['para']);
				if($v['usertype']==2){
					$arr['m']='resume';
				}elseif($v['usertype']==1){
					$arr['m']='job';
				}
				
				
				foreach($para as $val){
					$parav=@explode('=',$val);
					$arr[$parav[0]]=$parav[1];
				}
				/*
				*将数据中的参数分别匹配出名称，并写出路径
				*/
				if($arr['jobids']){
					$jobids=@explode(',',$arr['jobids']);
					foreach($jobids as $val){
						$jobname[]=$cache['job_name'][$val];
					}
					$findername[]=@implode('、',$jobname);
				} 
				if($arr['keyword']){
					$findername[]	=	$arr['keyword'];
				}
				if($arr['hy']){
					$findername[]	=	$cache['industry_name'][$arr['hy']];
				}
				if($arr['job1']){
					$findername[]	=	$cache['job_name'][$arr['job1']];
				}
				if($arr['job1_son']){
					$findername[]	=	$cache['job_name'][$arr['job1_son']];
				}
				if($arr['job_post']){
					$findername[]	=	$cache['job_name'][$arr['job_post']];
				}
				if($v['usertype']==2){
					if($arr['edu']){
						$findername[]	=	$cache['userclass_name'][$arr['edu']];
					}
					if($arr['word']){
						$findername[]	=	$cache['userclass_name'][$arr['word']];
					}
					if($arr['type']){
						$findername[]	=	$cache['userclass_name'][$arr['type']];
					}
					if($arr['exp']){
						$findername[]	=	$cache['userclass_name'][$arr['exp']];
					}
				}
				if($v['usertype']==1){
					if($arr['type']){
						$findername[]	=	$cache['comclass_name'][$arr['type']];
					}
					if($arr['edu']){
						$findername[]	=	$cache['comclass_name'][$arr['edu']];
					}
					if($arr['exp']){
						$findername[]	=	$cache['comclass_name'][$arr['exp']];
					}
				}
				
				if($arr['uptime']){
					$findername[]	=	$cache['uptime'][$arr['uptime']];
				}
				
				if($arr['minsalary']&&$arr['maxsalary']){
					if($this->config['resume_salarytype']==1){
						$findername[]	=	$arr['minsalary'].'-'.$arr['maxsalary'];
					}else{
						if($arr['maxsalary']<1000){
							if($this->config['resume_salarytype']==2){
								$findername[]	=	'1千以下';
							}elseif($this->config['resume_salarytype']==3){
								$findername[]	=	'1K以下';
							}elseif($this->config['resume_salarytype']==4){
								$findername[]	=	'1k以下';
							}
						}else{
							$findername[]	=	changeSalary($arr['minsalary']).'-'.changeSalary($arr['maxsalary']);
						}
					}
				}elseif($arr['maxsalary']){
					if($this->config['resume_salarytype']==1){
						$findername[]	=	$arr['maxsalary'].'以下';
					}else{
						if($arr['maxsalary']<1000){
							if($this->config['resume_salarytype']==2){
								$findername[]	=	'1千以下';
							}elseif($this->config['resume_salarytype']==3){
								$findername[]	=	'1K以下';
							}elseif($this->config['resume_salarytype']==4){
								$findername[]	=	'1k以下';
							}
						}else{
							$findername[]	=	'￥'.changeSalary($arr['maxsalary']).'以下';
						}
					}	
				}elseif ($arr['minsalary']){
					if($this->config['resume_salarytype']==1){
						$findername[]	=	$arr['minsalary'].'以上';
					}else{
						$findername[]	=	changeSalary($arr['minsalary']).'以上';
					}
				}
				
				if($arr['provinceid']){
					$findername[]	=	$cache['city_name'][$arr['provinceid']];
				}
				if($arr['cityid']){
					$findername[]	=	$cache['city_name'][$arr['cityid']];
				}
				if($arr['three_cityid']){
					$findername[]	=	$cache['city_name'][$arr['three_cityid']];
				}
				
				if($arr['sex']){
					$findername[]	=	$cache['user_sex'][$arr['sex']];
				}
				
				$List[$k]['findername']	=	@implode('+',array_filter($findername));
				$_GET=array_merge($_GET,$arr);
				
				$List[$k]['url']		=	searchListRewrite($arr,$this->config);
			}
		}
		return	$List;
	}
	public function addInfo($post){
		$num	=	$this -> select_num('finder',array('uid'=>$post['uid']));
		if($post['id']==""){
			if($post['usertype']==2){
				$allnum	=	$this->config['com_finder'];
			}
			if($post['usertype']==1){
				$allnum	=	$this->config['user_finder'];
			}
			if($num >= $allnum && $allnum){
				$return['msg']      =  "已达到最大搜索器数量！";
				$return['errcode']  =  '8';
				$return['url']		=	"index.php?c=finder";
				return $return;
			}
		}
		$arr	=	array(
			'keyword'		=>	$post['keyword'],
			'hy'			=>	intval($post['hy']),
			'job1'			=>	intval($post['job1']),
			'job1_son'		=>	intval($post['job1_son']),
			'job_post'		=>	intval($post['job_post']),
			'provinceid'	=>	intval($post['provinceid']),
			'cityid'		=>	intval($post['cityid']),
			'three_cityid'	=>	intval($post['three_cityid']),
			'minsalary'		=>	$post['minsalary'],
			'maxsalary'		=>	$post['maxsalary'],
			'edu'			=>	intval($post['edu']),
			'exp'			=>	intval($post['exp']),
			'sex'			=>	intval($post['sex']),
			'report'		=>	intval($post['report']),
			'uptime'		=>	$post['uptime']
		);
		$para	=	[];
		foreach($arr as $key=>$val){
			if(trim($val)){
				$para[]			=	$key."=".$val;
			}
		}
		
		$paras	=	@implode('##',$para);
		
		if($paras==""){
			$return['msg']      =  "搜索器内容不能为空必须任意选一项";
			$return['errcode']  =  '8';
			return $return;
		}
	
		$nid					=	$this -> insertfinder($paras,$post['id'],$post['name'],1,array('uid'=>$post['uid'],'usertype'=>$post['usertype']));
		if($post['id']==""){
			$msg				=	'添加';
		}else{
			$msg				=	'更新';
		}
		
		if ($nid){
			$return['msg']      =  $msg."成功！";
			$return['errcode']  =  '9';
			$return['url']		=	Url('member',array('c'=>'finder'));
		}else{
			$return['msg']      =  $msg."失败！";
			$return['errcode']  =  '8';
			$return['url']		=	Url('member',array('c'=>'finder'));
		}
		return $return;
	}

	/**
	 * 删除简历搜索器
	 * $whereData 	查询条件
	 * $data 		自定义处理数组
	 */
	public function delInfo($id,$data){
	    if (!empty($id)){
			$return['layertype']  =	 0;
			 
	        $nid      =  $this->delete_all('finder',array('id'=>$id,'uid'=>$data['uid']), '');
	        
	        if ($nid){
	            $return['msg']      =  '删除成功';
	            $return['errcode']  =  '9';
	        }else{
	            $return['msg']      =  '删除失败';
	            $return['errcode']  =  '8';
	        }
	    }
	    return $return;
	}	
}
?>