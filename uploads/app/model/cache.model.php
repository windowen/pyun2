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
class cache_model extends model{
	/** 加载缓存，只返回数据，不做向smarty模板赋值的操作
     * Summary of GetCache
     * @param mixed $CacheType 缓存的类型：user、com、job、city、hy
     * @param mixed $CacheType 单个缓存的名称，如“city”，或包含多个缓存的数组，如“array('city','job','user')”
     * @param mixed $Options
     * 返回数据的格式：以单个缓存city为例：array('city_index'=>$city_index,'city_type'=>$city_type,'city_name'=>$city_name)
     * 返回数据的格式：以多个缓存city，job为例：array(array('city_index'=>$city_index,'city_type'=>$city_type,'city_name'=>$city_name),array('job_index'=>$job_index,'job_type'=>$job_type,'job_name'=>$job_name))
     * 返回数据的格式：数组中数据的顺序与参数$CacheType中的顺序相同，已对应序号0，1，2...调用
     */
	function GetCache($CacheType,$Options=array('needreturn'=>true,'needassign'=>true)){
        if(($Options['needreturn']!=true)&&($Options['needassign']!=true)){
            return;
        }
        if(!is_array($CacheType)){
            $CacheTypeList=array($CacheType);
        }else{
            $CacheTypeList=$CacheType;
        }
        if(count($CacheTypeList)!=count(array_unique($CacheTypeList))){
            return '参数重复！';
        }
        $ReturnCacheList=array();
        foreach($CacheTypeList as $v){
            switch($v){
                case 'user':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->user_cache($Options));
                    break;
                case 'com':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->com_cache($Options));
                    break;
                case 'job':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->job_cache($Options));
                    break;
                case 'city':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->city_cache($Options));
                    break;
                case 'hy':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->industry_cache($Options));
                    break;
                case 'introduce':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->introduce_cache($Options));
                    break;	
				case 'redeem':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->redeem_cache($Options));
                    break;
                case 'reason':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->reason_cache($Options));
                    break;
                case 'keyword':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->keyword_cache($Options));
                    break;
                case 'domain':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->domain_cache($Options));
                    break;
                case 'menu':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->menu_cache($Options));
                    break;
				case 'moblienav':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->moblienav_cache($Options));
                    break;	
				case 'ask':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->ask_cache($Options));
                    break;
                case 'part':
                    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->part_cache($Options));
                    break;
				case 'group':
					$ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->group_cache($Options));
				break;
				case 'integralclass':
				    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->integralclass_cache($Options));
				    break;
				case 'uptime':
			        $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->uptime_cache($Options));
			        break;
				case 'cityename':
				    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->cityename_cache($Options));
				    break;
				case 'cityfs':
				    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->cityfs_cache($Options));
				    break;
				case 'jobename':
				    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->jobename_cache($Options));
				    break;
				case 'jobfs':
				    $ReturnCacheList=array_merge_recursive($ReturnCacheList,$this->jobfs_cache($Options));
				    break;
                default:break; 
            }
        }
        return $ReturnCacheList;
	}
	private function user_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include PLUS_PATH.'user.cache.php';
		include CONFIG_PATH.'db.data.php';
		//更新时间
        if($Options['needreturn']==true){
            unset($arr_data['sex'][3]);	
            return array('userdata'=>$userdata,'userclass_name'=>$userclass_name,'user_sex'=>$arr_data['sex']);
        }
	}
	private function uptime_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		//更新时间
		$uptime=array(1=>'今天',3=>'最近3天',7=>'最近7天',30=>'最近一个月',90=>'最近三个月');
        if($Options['needreturn']==true){
            return array('uptime'=>$uptime);
        }
	}
    private function reason_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include PLUS_PATH.'reason.cache.php';
        if($Options['needreturn']==true){
            return $reason;
        }
	}
    private function keyword_cache($Options=array('needreturn'=>false,'needassign'=>true)){
        include PLUS_PATH.'keyword.cache.php';
        if($Options['needreturn']==true){
            return $keyword;
        }
    }
	private function com_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include PLUS_PATH.'com.cache.php';
		include CONFIG_PATH.'db.data.php';
        if($Options['needreturn']==true){
            return array('comdata'=>$comdata, 'comclass_name'=>$comclass_name, 'com_sex'=>$arr_data['sex']);
        }
	}
   
	private function group_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include PLUS_PATH.'group.cache.php';
		if($Options['needreturn']==true){
			return array('group_rec'=>$group_rec,'group_recnews'=>$group_recnews,'group_index'=>$group_index,'group_type'=>$group_type,'group_name'=>$group_name);
		}
	}
	private function part_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include PLUS_PATH.'part.cache.php';
		include CONFIG_PATH.'db.data.php';
		$morning    =   array('0101','0201','0301','0401','0501','0601','0701');
        $noon       =   array('0102','0202','0302','0402','0502','0602','0702');
        $afternoon  =   array('0103','0203','0303','0403','0503','0603','0703');
        if($Options['needreturn']==true){
            return array('partdata'=>$partdata, 'partclass_name'=>$partclass_name, 'part_sex'=>$arr_data['sex'],'part_morning'=>$morning,'part_noon'=>$noon,'part_afternoon'=>$afternoon);
        }
	}
	private function city_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'city.cache.php');
        if($Options['needreturn']==true){
            return array('city_type'=>$city_type,'city_index'=>$city_index,'city_name'=>$city_name);
        }
	}
	private function job_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'job.cache.php');
        if($Options['needreturn']==true){
            return array('job_type'=>$job_type,'job_index'=>$job_index,'job_name'=>$job_name);
        }
	}
	
	
	private function industry_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'industry.cache.php');
        if($Options['needreturn']==true){
            return array('industry_index'=>$industry_index,'industry_name'=>$industry_name);
        }
	}
	
	private function introduce_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'introduce.cache.php');
        if($Options['needreturn']==true){
            return array('introduce_index'=>$introduce_index,'introduce_content'=>$introduce_content,'introduce_name'=>$introduce_name);
        }
	}
	private function redeem_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'redeem.cache.php');
        if($Options['needreturn']==true){
            return array('redeem_index'=>$redeem_index,'redeem_type'=>$redeem_type,'redeem_name'=>$redeem_name);
        }
	}
	private function ask_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'ask.cache.php');
        if($Options['needreturn']==true){
            return array('ask_index'=>$ask_index,'ask_type'=>$ask_type,'ask_pic'=>$ask_pic,'ask_intro'=>$ask_intro,'ask_name'=>$ask_name);
        }
	}
    
	private function integralclass_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'integralclass.cache.php');
        if($Options['needreturn']==true){
            return array('integralclass_index'=>$integralclass_index,'integralclass_name'=>$integralclass_name,'integralclass_names'=>$integralclass_names,'integralclass_discount'=>$integralclass_discount);
        }
	}
	
    private function domain_cache($Options=array('needreturn'=>false,'needassign'=>true,'needall'=>false)){
		include(PLUS_PATH.'domain_cache.php');
        if($Options['needreturn']==true){
            //后台分配分站所需
			if($Options['needall']==true){
				$Dname[-1]  =  '全站';
			}
			$Dname[0]	=  '主站';
            if(is_array($site_domain)){
                foreach($site_domain as $k=>$v){
                    $Dname[$v['id']]  =  $v['webname'];
                }
            }
            
            return array('site_domain'=>$site_domain,'Dname'=>$Dname);
        }
	}
    private function menu_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'menu.cache.php');
        if($Options['needreturn']==true){
            return array('menu_name'=>$menu_name);
        }
	}
	
	private function moblienav_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'moblienav.cache.php');
        if($Options['needreturn']==true){
            return array('moblienav_index'=>$moblienav_index,'moblienav_pic'=>$moblienav_pic,'moblienav_url'=>$moblienav_url,'moblienav_name'=>$moblienav_name);
        }
	}
	private function cityename_cache($Options=array('needreturn'=>false,'needassign'=>true)){
	    include(PLUS_PATH.'cityename.cache.php');
	    if($Options['needreturn']==true){
	        return array('city_ename'=>$city_ename);
	    }
	}
	private function cityfs_cache($Options=array('needreturn'=>false,'needassign'=>true)){
	    include(PLUS_PATH.'cityfs.cache.php');
	    if($Options['needreturn']==true){
	        return array('city_two'=>$city_two,'city_three'=>$city_three);
	    }
	}
	private function jobename_cache($Options=array('needreturn'=>false,'needassign'=>true)){
	    include(PLUS_PATH.'jobename.cache.php');
	    if($Options['needreturn']==true){
	        return array('job_ename'=>$job_ename);
	    }
	}
	private function jobfs_cache($Options=array('needreturn'=>false,'needassign'=>true)){
	    include(PLUS_PATH.'jobfs.cache.php');
	    if($Options['needreturn']==true){
	        return array('job_two'=>$job_two,'job_three'=>$job_three);
	    }
	}
	private function shoptype_cache($Options=array('needreturn'=>false,'needassign'=>true)){
		include(PLUS_PATH.'shoptype.cache.php');
        if($Options['needreturn']==true){
            return array('shoptype_index'=>$shoptype_index,'shoptype_name'=>$shoptype_name);
        }
	}
	/**
	 * 处理城市/职能拼音搜索
	 * @param array $get
	 */
	public function pinYin($get = array(),$cache = array('city_index'=>array(),'job_index'=>array()))
	{
	    $return  =  array();
	    // 处理城市拼音搜索
	    if (!empty($get['ecity']))
	    {
	        $ecityid  =  0;
	        $ecArr     =  $this->GetCache(array('cityename','cityfs'));
	        
	        foreach ($ecArr['city_ename'] as $k=>$v){
	            if ($v == $get['ecity']){
	                $ecityid  =  $k;
	            }
	        }
	        if ($ecityid != 0){
	            
	            if (empty($cache['city_index'])){
	                
	                $cityCache   =  $this->GetCache(array('city'));
	                $city_index  =  $cityCache['city_index']; 
	                
	            }else{
	                
	                $city_index  =  $cache['city_index']; 
	            }
	            
	            if (in_array($ecityid, $city_index)){
	                
	                $return['provinceid']    =  $ecityid;
	                
	            }elseif (in_array($ecityid, $ecArr['city_two'])){
	                
	                $return['cityid']        =  $ecityid;
	                
	            }elseif (in_array($ecityid, $ecArr['city_three'])){
	                
	                $return['three_cityid']  =  $ecityid;
	            }
	        }
	    }
	    // 处理职能拼音搜索
	    if (!empty($get['ejob']))
	    {
	        $ejobid  =  0;
	        $ejArr    =  $this->GetCache(array('jobename','jobfs'));
	        
	        foreach ($ejArr['job_ename'] as $k=>$v){
	            if ($v == $get['ejob']){
	                $ejobid  =  $k;
	            }
	        }
	        if ($ejobid != 0){
	            
	            if (empty($cache['job_index'])){
	                
	                $jobCache   =  $this->GetCache(array('job'));
	                $job_index  =  $jobCache['job_index'];
	                
	            }else{
	                
	                $job_index  =  $cache['job_index'];
	            }
	            
	            if (in_array($ejobid, $job_index)){
	                
	                $return['job1']      =  $ejobid;
	                
	            }elseif (in_array($ejobid, $ejArr['job_two'])){
	                
	                $return['job1_son']  =  $ejobid;
	                
	            }elseif (in_array($ejobid, $ejArr['job_three'])){
	                
	                $return['job_post']  =  $ejobid;
	            }
	        }
	    }
	    
	    return $return;
	}
}
?>