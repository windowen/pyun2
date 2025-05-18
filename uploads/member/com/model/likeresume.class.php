<?php
/* *
* $Author ：PHPYUN开发团队
*
* 官网: http://www.phpyun.com
*
* 版权所有 2009-2019 宿迁鑫潮信息技术有限公司，并保留所有权利。
*
* 软件声明：未经授权前提下，不得用于商业运营、二次开发以及任何形式的再次发布。
*/
class likeresume_controller extends company{
	//相似简历
	function index_action(){
		$JobM	=	$this->MODEL("job");
		$resumeM	=	$this->MODEL("resume");
		if($_GET['jobid']){
			
			$id		=	(int)$_GET['jobid'];
			$job	=	$JobM->getInfo(array('id' => $id),array('com'=>'yes'));
			$this->yunset("job",$job);

			$msg	=	$JobM -> getYqmsList(array('fid'=>$this->uid,'jobid'=>$id,'groupby'=>'uid'));
			
			$rData['field']		="`id`,`uid`,`city_classid`,`job_classid`,`report`,`minsalary`,`maxsalary`,`edu`,`sex`,`exp`";
			$where['defaults']	=	1;
			
			if($msg&&is_array($msg)){
				$uids			=	array();
				foreach($msg as $val){
					$uids[]		=	$val['uid'];
				}
				$where['uid']	=	array('notin',pylode(',',$uids));
			}
			$where['PHPYUNBTWSTART_A']		=	'' ;
			$where['job_classid'][]			=	array('findin',$job['job_post']);
			$where['job_classid'][]			=	array('findin',$job['job1'],'or');
			$where['job_classid'][]			=	array('findin',$job['job1_son'],'or');
			$where['PHPYUNBTWEND_A']		=	'' ;
			
			$where['PHPYUNBTWSTART_B']		=	'' ;
			$where['city_classid'][]		=	array('findin',$job['provinceid']);
			$where['city_classid'][]		=	array('findin',$job['cityid'],'or');
			$where['city_classid'][]		=	array('findin',$job['three_cityid'],'or');
			$where['PHPYUNBTWEND_B']		=	'' ;
			
			
			$where['orderby']		=	'lastupdate,desc';
			$where['limit']			=	15;
			$resume		=	$resumeM->getList($where,$rData);
			$resume		=	$resume['list'];
			if(is_array($resume)){ 
				foreach ($resume as $v){
					$uids[]	=	$v['uid'];
				}
				if ($uids){
					$user 	= 	$resumeM->getResumeList(array('uid'=>array('in',pylode(',',$uids))),array('field'=>'`uid`,`name`,`nametype`,`sex`,`marriage`,`def_job`'));
				}
				foreach($resume as $k=>$v){
					foreach ($user as $val){
						if ($v['uid']==$val['uid']){
							$resume[$k]['name']	=	$val['name_n'];
						}
					}
					if ($v['sex']=='152'){
						$resume[$k]['sex']		=	'女';
					}elseif ($v['sex']=='153'){
						$resume[$k]['sex']		=	'男';
					}else {
						$resume[$k]['sex']		=	$v['sex_n'];
					}
					
					$resume[$k]['jobname']		=	$v['job_classname'];
					$resume[$k]['cityname']		=	$v['city_classname'];
					
					$pre				=	60;
					 
					if($job['job_edu']==$v['edu_n'] || $job['job_edu']=="不限"){
						$pre			=	$pre+5;
					}
					if($job['job_sex']==$v['sex_n']){
						$pre			=	$pre+5;
					}
					foreach ($user as $val){
						if ($v['uid']==$val['uid']){
							if($job['job_marriage']==$val['marriage_n'] || $job['job_marriage']=="不限"){
								$pre	=	$pre+5;
							}
						}
					}
					if($job['job_report']==$v['report_n'] || $job['job_report']=="不限"){
						$pre			=	$pre+5;
					}
					if($job['job_exp']==$v['exp_n']|| $job['job_exp']=="不限"){
						$pre			=	$pre+5;
					}
					$resume[$k]['pre']	=	$pre;
				}
				$sort = array(
					'direction' => 'SORT_DESC',
					'field'     => 'lastupdate',    
				);
				$arrSort 						= 	array();
				foreach($resume AS $uniqid => $row){
				    foreach($row AS $key=>$value){
				        $arrSort[$key][$uniqid]	=	$value;
				    }
				}
				if($sort['direction']){
				    array_multisort($arrSort[$sort['field']], constant($sort['direction']), $resume);
				}
				
				$this->yunset("resume",$resume);
			}
		}
		
		$this->yqmsInfo();
		$this->public_action();
		$this->company_satic();
		
		$this->com_tpl('likeresume');
	}
}
?>