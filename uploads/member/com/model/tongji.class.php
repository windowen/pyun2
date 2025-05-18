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
class tongji_controller extends company{
  
	function index_action(){
    
        $jobM                     =   $this -> MODEL('job');
    
        $resumeM                  =   $this -> MODEL('resume');
    
		$this->company_satic();
    
		$this->public_action();
    
		$tjtype	=	array("1"=>"区域","2"=>"学历","3"=>"薪资","4"=>"工作经验");
    
		$this->yunset("tjtype",$tjtype);
		
		$uid	=	$this->uid;
        $cwhere['com_id']	=   $uid;
    
        $ListA		=     $jobM->getList($cwhere,array('field'=>'id,name'));
		
		$JobList	=	$ListA['list'];
     
        if(is_array($JobList) && $_GET['jobid']){
          
          foreach($JobList as $v){
            
            if($_GET['jobid']==$v['id']){
              
              $this->yunset("jobname",$v['name']);
              
            }
          }
        }
    
        $this->yunset("JobList",$JobList);
    
        $lwhere['com_id']    =   $where['com_id']  =  $uid;
		
        $mwhere['fid']       =  $uid;
    
		if($_GET['jobid']){
      
            $jobid             =   intval($_GET['jobid']);
      
            $where['job_id']   =   $jobid;
      
            $lwhere['jobid']   =   $jobid;
      
            $mwhere['jobid']   =   $jobid;

            $tdwhere['job_id']  =   $jobid;
      
		}
		if($_GET['sedate']){
      
			$sedate=str_replace(" ","" ,$_GET['sedate']);
      
			$arr = explode('~', $sedate);
      
			$sdate = $arr[0];
      
  			$days = ceil(abs(time() - strtotime($sdate))/86400);
        
			$edate = $arr[1];
      
 			$days = ceil(abs(strtotime($edate) - strtotime($sdate))/86400);
      
			$edatetime=strtotime($edate);
      
		}else{
      
			$days = 30;
      
			$sdate = date('Y-m-d',(time()-$days*86400));
      
			$edate = date('Y-m-d');
      
			$edatetime=time();
      
		}
    	
        $where['datetime'][]    =   array('>=',strtotime($sdate));
    
        $where['datetime'][]    =   array('<=',$edatetime,'AND');
    
        $lwhere['datetime'][]   =   array('>=',strtotime($sdate));
    
        $lwhere['datetime'][]   =   array('<=',$edatetime,'AND');
    
        $mwhere['datetime'][]   =   array('>=',strtotime($sdate));
    
        $mwhere['datetime'][]   =   array('<=',$edatetime,'AND');

        $tdwhere['datetime'][]  =   array('>=',strtotime($sdate));
    
        $tdwhere['datetime'][]  =   array('<=',$edatetime,'AND');
      
		$this->yunset("sdate",$sdate);
    
		$this->yunset("edate",$edate);
    
		$this->yunset("days",$days);
        //------邀请面试成功率
        
        $useridmsg             =     $jobM->getYqmsNum($mwhere);
    
        $mwhere['is_browse']    =     3;    
    
        $cgmsg                  =     $jobM->getYqmsNum($mwhere);
		
		if ($useridmsg&&$cgmsg){
      
		    $cgl=number_format($cgmsg/$useridmsg*100, 0, ',', ' ');
        
		}else{
      
		    $cgl=0;
        
		}
		$this->yunset("useridmsg",$useridmsg);
    
		$this->yunset("cgl",$cgl);
    
		//------查询投递的职位
        $tdwhere['com_id']    =  $uid;  

        $tdnum                =     $jobM->getSqJobNum($tdwhere);      
        
		$this->yunset("tdnum",$tdnum);
    
		$List=$this->tj("userid_job",$where,$days);
    
		$this->yunset("list",$List);
    
		//------职位被浏览统计

		$LookList=$this->tj("look_job",$lwhere,$days);
    
		$this->yunset("looklist",$LookList);
    
		//---------以下是统计类型

        $useridjob      =     $jobM->getSqJobList($where,array('field'=>'job_id,eid'));
		if(is_array($useridjob)){
      
			foreach($useridjob as $v){
        
                $job_id[]=$v['job_id'];
        
                $eid[]=$v['eid'];
			}
		}
		if($_GET['type']=="1"){
      
			include PLUS_PATH."city.cache.php";
      
			foreach($city_index as $k=>$v){
        
				$dateArr[] = $city_name[$v];
        
			}
      
            $cwhere['id']		=   array('in',pylode(',',$job_id));
            $cwhere['groupby']	=  'provinceid';
      
			$listA	=   $jobM->getList($cwhere,array('field'=>'count(*) as num,provinceid'));
			
			$list	=	$listA['list'];
			if(is_array($list)){
        
				foreach($list as $key=>$value){
          
					$list[$key]['fields'] = $city_name[$value['provinceid']];
          
				}
        
			}
      
			$this->yunset("piename","区域简历投递统计");
      
		}elseif($_GET['type']=="2"){
      
			include PLUS_PATH."user.cache.php";
      
			foreach($userdata['user_edu'] as $k=>$v){
        
				$dateArr[] = $userclass_name[$v];
        
			}
      
      
            $expectwhere['id']        =   array('in',pylode(',',$eid));
      
            $expectwhere['groupby']    =  'edu';
      
			$list       =     $resumeM->getSimpleList($expectwhere,array('field'=>"count(*) as num,edu"));
      
			if(is_array($list)){
        
				foreach($list as $key=>$value){
          
					$list[$key]['fields'] = $userclass_name[$value['edu']];
          
				}
        
			}
			$this->yunset("piename","学历简历投递统计");
      
		}elseif($_GET['type']=="3"){
      
			$TongJi=$this->MODEL('tongji');
      
			$list = $TongJi->DataTj('resume_expect',"`id` in (".@implode(",",$eid).") ",'resume_expect',"id");
      
			$list=$list['salary'];
      
			if(is_array($list)){
        
				foreach($list as $key=>$value){
          
					$list[$key]['num'] = $value['count'];
          
					$list[$key]['fields'] = $value['name'];
          
				}
        
			}
      
			$this->yunset("piename","薪资简历投递统计");

		}elseif($_GET['type']=="4"){
      
			include PLUS_PATH."user.cache.php";
      
			foreach($userdata['user_exp'] as $k=>$v){
        
				$dateArr[] = $userclass_name[$v];
        
			}
      
            $rexpectwhere['id']        =   array('in',pylode(',',$eid));
      
            $rexpectwhere['groupby']    =  'exp';
      
			$list       =     $resumeM->getSimpleList($rexpectwhere,array('field'=>"count(*) as num,exp"));
      
			if(is_array($list)){
        
				foreach($list as $key=>$value){
          
					$list[$key]['fields'] = $userclass_name[$value['exp']];
          
				}
        
			}
			$this->yunset("piename","工作经验简历投递统计");
      
		}
		$this->yunset("pielist",$list);
        
		$this->com_tpl('tongji');
	}
	function tj($table,$where,$days){
     
        $tongjiM                  =   $this -> MODEL('tongji');
    
        $where['groupby']         =   'td';
    
        $where['orderby']         =   array('td,desc');
    
    
		$joblist                  =$tongjiM->tjtotal($table,$where,array('field'=>'FROM_UNIXTIME(`datetime`,"%Y%m%d") as td,count(*) as cnt'));
		//整合广告赚钱折线图
		if(is_array($joblist)){
			$AllNum = 0;
      
			foreach($joblist as $key=>$value){
        
				$AllNum +=$value['cnt'];
        
				$Date[$value['td']] = $value;
        
			}
			if($days>0){
        
				for($i=0;$i<=$days;$i++){
          
					$onday = date("Ymd", strtotime(' -'. $i . 'day'));
          
					$td    = date('m-d', strtotime(' -'. $i . 'day'));
          
					$date    = date('Y-m-d', strtotime(' -'. $i . 'day'));
          
					if(!empty($Date[$onday])){
            
						$Date[$onday]['td'] = $td;
            
						$Date[$onday]['date'] = $date;
            
						$List[$onday] = $Date[$onday];
            
					}else{
            
						$List[$onday] = array('td'=>$td,'cnt'=>0,'date'=>$date);
            
					}
          
				}
        
			}
      
		}
    
		ksort($List);
    
		return $List;
	}

}
?>