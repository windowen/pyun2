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
class special_model extends model{
	/**
     * @desc   引用company类，查询company列表信息   
     */
    
    private function getComList($whereData = array(), $data = array()) {

        require_once ('company.model.php');
        
        $CompanyM = new company_model($this->db, $this->def);
        
        return  $CompanyM   ->  getList($whereData , $data);
        
    }
	/**
     * @desc   获取缓存数据
     *
     * @param   array $options
     * @return  array $cache
     */
    private function getClass($options)
    {
             
        include_once ('cache.model.php');
        
        $cacheM     =   new cache_model($this->db, $this->def);
        
        $cache      =   $cacheM -> GetCache($options);
        
        return $cache;
     
    }
    function getSpecial($whereData=array(),$data=array()){
		$ListNew		=	array();
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('special',$whereData,$data['field']);
		
		if(!empty( $List )){
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
    }
	
	function getSpecialOne($whereData=array(),$data=array()){
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List			=	$this -> select_once('special',$whereData,$data['field']);
			if(!empty($List)){
				$List['ctime_n']=date('Y年-m月-d日',$List['ctime']);
				$List['etime_n']=date('Y年-m月-d日',$List['etime']);
				$List['intro']=str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;"),array("&",'','',''),$List["intro"]);
				if($List['pic']){
					$List['pic']		=	checkpic($List['pic']);
				}
				if($List['background']){
					$List['background']	=	checkpic($List['background']);
				}
				if($List['limit']>$List['num']){
					$List['apply']		=	'1';
				}
			}
		}
		return $List;
    }
	
    function addSpecial($setData=array()){
		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('special',$setData);
			
		}

		return $nid;
    }
	
    function upSpecial($whereData=array(),$data=array()){
		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('special',$data,$whereData);
			
		}
		return $nid;
    }
	
	function addSpecialCom($setData=array()){
		if(!empty($setData)){
			
			$nid	=	$this -> insert_into('special_com',$setData);
			
		}

		return $nid;
    }
	
	function upSpecialCom($whereData=array(),$data=array()){
		if(!empty($whereData)){
			
			$nid	=	$this -> update_once('special_com',$data,$whereData);
			
		}
		return $nid;
    }
	
	function getSpecialComOne($whereData=array(),$data=array()){
		if($whereData){
			$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
			$List			=	$this -> select_once('special_com',$whereData,$data['field']);
		}

		return $List;
    }
	
	//删除专题
	function delSpecial($whereData,$data){
		
		if($data['type']=='one'){
			$limit		=	'limit 1';
		}
		if($data['type']=='all'){
			$limit		=	'';
		}

		$special	=	$this->getSpecialList(array('id'=>$whereData['id']),array('field'=>'`pic`,`background`'));
		

		$result		=	$this -> delete_all('special',$whereData,$limit);

		if(is_array($special['list']) && $result){
			
			$this -> delSpecialCom(array('sid'=>$whereData['id']),$data['type']);
		}
		
		
		return	$result;
		
    }
	
	//删除专题商家
    function delSpecialCom($whereData,$data){
		
		if($data['type']=='one'){
			$limit	=	'limit 1';
		}
		
		if($data['type']=='all'){
			$limit	=	'';
		}
		if($data['uid']){
		
			$getWhere	=	array('uid'=>$data['uid'],'id'=>$whereData['id'],'status'=>0);
		}else{
			
			$getWhere	=	array('id'=>$whereData['id'],'status'=>0);
		}

		$rows	=	$this->getSpecialComList($getWhere,array('field'=>'`uid`,`integral`'));
		
		if(is_array($rows['list'])){
			
			require_once ('integral.model.php');
        
			$IntegralM 	= 	new integral_model($this->db, $this->def);
			
			foreach($rows['list'] as $val){
				
				if($val['integral']>0){
					
					$IntegralM->company_invtal($val['uid'],2,$val['integral'],true,"取消专题招聘报名，退还".$this->config['integral_pricename'],true,2,'integral');
				}
			
			}
		}
		
		$result	=	$this	->	delete_all('special_com',$whereData,$limit);
		
		return	$result;
		
    }
	
	//获取专题商家列表
	function getSpecialComList($whereData=array(),$data=array()){
		$ListNew		=	array();
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		$List			=	$this -> select_all('special_com',$whereData,$data['field']);
		$cache     	 	=   $this -> getClass(array('hy','com'));
		$jobinfo=array();
		if(!empty( $List )){
			foreach($List as $val){
				
				if ($val['uid']) {
				    $uid[]			=	$val['uid'];
				}
				if ($val['sid']) {
				    $sid[]			=	$val['sid'];
				}
			}
			if (!empty($uid)){
			    $comWhere['uid']	=	array('in',pylode(',',$uid));
			    $company			=	$this->getComList($comWhere,array('field'=>'`uid`,`name`,`hy`,`logo`'));
			    $job				=	$this->select_all("company_job",array('uid'=>array('in',pylode(',',$uid)),'sdate'=>array('<',time()),'state'=>1,'orderby'=>'lastupdate,desc'),'`id`,`uid`,`name`,`minsalary`,`maxsalary`,`exp`,`edu`');
			}
			if (!empty($sid)){
			    $special			=	$this->getSpecial(array('id'=>array('in',pylode(',',$sid))),array('field'=>'id,title,intro'));
			}
			if($job&&is_array($job)){
				foreach($job as $k=>$v){
					$v['edu_n']=$cache['comclass_name'][$v['edu']];
					$v['exp_n']=$cache['comclass_name'][$v['exp']];
					if($v['minsalary']&&$v['maxsalary']){
						if($this ->config['resume_salarytype']==1){
							$v['job_salary'] =$v['minsalary']."-".$v['maxsalary']."元";
						}else{
							if($v['maxsalary']<1000){
								if($this->config['resume_salarytype']==2){
		                            $v['job_salary'] = "1千以下";
		                        }elseif($this->config['resume_salarytype']==3){
		                            $v['job_salary'] = "1K以下";
		                        }elseif($this->config['resume_salarytype']==4){
		                            $v['job_salary'] = "1k以下";
		                        }
							}else{
								$v['job_salary'] = changeSalary($v['minsalary'])."-".changeSalary($v['maxsalary']);
							}
						}
					}elseif($v['minsalary']){
						if($this ->config['resume_salarytype']==1){
							$v['job_salary'] =$v['minsalary']."以上元";
						}else{
							$v['job_salary'] =changeSalary($v['minsalary'])."以上";
						}
					}else{
						$v['job_salary'] ="面议";
					}
					$jobinfo[$v['uid']][]=$v;
				}
			}
			foreach($List as $key=>$val){
				if ($data['utype']=='admin') {
					
					foreach($company['list'] as $v){
						if($val['uid']==$v['uid']){
							$List[$key]['name']		=	$v['name'];
						}
					}
					
				}
				foreach($special['list'] as $v){
					if($val['sid']==$v['id']){
						$List[$key]['title']=$v['title'];
						$List[$key]['intro']=$v['intro'];
					}
				}
				$List[$key]['jobs']=$jobinfo[$val['uid']];
				$List[$key]['spetime_n']	=	date('Y-m-d',$val['time']);
			}
			
			$ListNew['list']	=	$List;
		}

		return	$ListNew;
    }
	
	
	//获取专题列表
	function getSpecialList($whereData=array(),$data=array()){
		$ListNew	=	array();
		$List		=	$this -> getSpecial($whereData,$data['field']);
		
		if(!empty( $List )){
			
			foreach($List['list'] as $key=>$val){
				$zid[]	=	$val['id'];
				$List['list'][$key]['ctime_n']	=	date('Y-m-d',$val['ctime']);
			}
			
			if ($data['utype']=='admin') {
				$oneWhere['sid']		=	array('in',pylode(",",$zid));
				$oneWhere['groupby']	=	'sid';
				$all					=	$this->getSpecialComList($oneWhere,array('field'=>'`sid`,count(id) as num'));
				
				$twoWhere['sid']		=	array('in',pylode(",",$zid));
				$twoWhere['status']		=	'0';
				$twoWhere['groupby']	=	'sid';
				$status					=	$this->getSpecialComList($twoWhere,array('field'=>'`sid`,count(id) as num'));
				foreach($List['list'] as $key=>$v){
					foreach($all['list'] as $val){
						if($v['id']	==	$val['sid']){
							$List['list'][$key]['comnum']	=	$val['num']>=0	?	$val['num']:0;
						}
					}
					foreach($status['list'] as $val){
						if($v['id']	==	$val['sid']){
							$List['list'][$key]['booking']	=	$val['num']>=0	?	$val['num']:0;
						}
					}
				}
           
		   }
			
			$ListNew['list']	=	$List['list'];
		}
		
		return	$ListNew;
		
	}
	
	 
	public function getSpecialComNum($Where=array()){
		
        return $this->select_num('special_com',$Where);
    }
	
	public function addSpecialComInfo($data=array()){
		
		$id			=	(int)$data['id'];
		
		if($data['uid']&&$data['usertype']=='2'){
			
			$info	=	$this->getSpecialOne(array("id"=>$id,"display"=>1));
			
			if($info['com_bm']!='1'){
				return array('msg'=>'该专题禁止报名！','errcode'=>8);
			}
			require_once ('statis.model.php');
			$statisM	= new statis_model($this->db, $this->def);
			$statis		=	$statisM->getInfo($data['uid'],array("usertype"=>'2','field'=>'integral,`rating`'));
			
			$isapply	=	$this->getSpecialComNum(array("uid"=>$data['uid'],"sid"=>$id));
			$applynum 	=	$this->getSpecialComNum(array("sid"=>$id));
			

			if($isapply){
				return array('msg'=>'您已报名该专题，请等待管理员审核！','errcode'=>8);
			}
			
			if($info['rating']){
				$rating	=	@explode(',',$info['rating']);
			}
			
			$jobnum		=	$this->select_num('company_job',array("uid"=>$data['uid'],"state"=>'1','sdate'=>array('<',time())));
			
			if($info['limit']<=$applynum){
				return array('msg'=>'报名已满，请下次提前报名！','errcode'=>8);
			}
			if($jobnum<1){
				return array('msg'=>'您暂无公开且合适职位！','errcode'=>8);
			}  
			if($rating&&is_array($rating)){ 
				
				if(!in_array($statis['rating'],$rating)){
					require_once ('rating.model.php');
					$ratingM		= 	new rating_model($this->db, $this->def);
					
					$ratings		=	$ratingM->getList(array("display"=>1,'category'=>1,'id'=>array('in',$info['rating'])),array("field"=>"`id`,`name`"));
					
					$rname			=	array();
					foreach($ratings as $val){
						$rname[]	=	$val['name'];
					}
					return array('msg'=>'只有'.@implode('、',$rname).'才能报名该专题！','errcode'=>8);
				}
			}
			if($statis['integral']<$info['integral']){
				return array('msg'=>$this->config['integral_pricename'].'不足，请先充值！','errcode'=>8);
			}
			require_once ('integral.model.php');
			$integralM		= 	new integral_model($this->db, $this->def);
					
			$nid	=	$integralM->company_invtal($data['uid'],2,$info['integral'],false,"报名专题招聘",true,2,'integral',9);
			if($nid){
				
				$this->insert_into('special_com',array("sid"=>$id,"uid"=>$data['uid'],'integral'=>$info['integral'],'status'=>'0','time'=>time()));
				return array('msg'=>'报名成功，请耐心等我们工作人员审核！','errcode'=>9,'url'=>$_SERVER['HTTP_REFERER']);
			}else{
				return array('msg'=>'报名失败，请稍后重试！','errcode'=>8,'url'=>$_SERVER['HTTP_REFERER']);
			}
		}else{
			return array('msg'=>'只有企业用户才能报名！','errcode'=>8);
		}
    }	
}
?>