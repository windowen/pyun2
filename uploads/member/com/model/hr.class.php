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
class hr_controller extends company{
    
    function index_action(){
		

         
        $this -> public_action();
       
        $JobM       =   $this -> MODEL('job');
 
        $ResumeM    =   $this -> MODEL('resume');
		
        $CacheM     =   $this -> MODEL('cache');
        $cache      =   $CacheM -> GetCache(array('user','uptime'));
        $this->yunset($cache);
        
        $where      =   array();
        
        $where['com_id']	=   $this -> uid;
        $where['type']		=     array('<>',3);
        
        if($_GET['jobid']){
            
            $jobid              =   @explode('-', $_GET['jobid']);
            $jobid['1']         =   !array_key_exists('1', $jobid) ? 1 : $jobid['1'];
            
            $where['job_id']    =	$jobid['0'];
            $where['type']		=	$jobid['1'];
            $urlarr['jobid']	=	$_GET['jobid'];
        }
        /* 查询简历eid */
        if($_GET['resumetype'] || $_GET['exp'] || $_GET['edu'] || $_GET['sex'] || $_GET['uptime'] || $_GET['keyword']){
            
            $sqjob      =   $JobM ->getSqJobList ($where,array('field'=>'eid'));
            
            if(!empty($sqjob) && is_array($sqjob)){
                
                foreach($sqjob as $v){
                    
                    $sqeid[]    =   $v['eid'];
                }
            }
            if($_GET['resumetype']){
                
                $resumeType                 =   intval($_GET['resumetype']);
                $rWhere['height_status']    =    0;
                $urlarr['resumetype']       =	$resumeType;
            }
            
            if($_GET['exp']){
                
                $resumeExp                  =   intval($_GET['exp']);
                $rWhere['exp']              =   $resumeExp;
                $urlarr['exp']              =	$resumeExp;
            }
            if($_GET['edu']){
                
                $resumeEdu                  =   intval($_GET['edu']);
                $rWhere['edu']              =   $resumeEdu;
                $urlarr['edu']              =	$resumeEdu;
                
            }
            
            if($_GET['sex']){
                
                $resumeSex                  =   intval($_GET['sex']);
                $rWhere['sex']              =   $resumeSex;
                $urlarr['sex']              =	$resumeSex;
            }
            
            if($_GET['uptime']){
                
                $resumeUptime               =   intval($_GET['uptime']);
                
                if($resumeUptime == 1){
                    
                    $rWhere['lastupdate']   =   array('>' , strtotime('today'));
                }else{
                    $rWhere['lastupdate']   =   array('>',strtotime('-'.$resumeUptime.' day'));
                }
                $urlarr['uptime']           =	$resumeUptime;
            }
            
            if (!empty($rWhere)) {
                
                $rWhere['id']               =   array('in', pylode(',', $sqeid));
                $rWhere['r_status']         =   1;
				
                $resumeA                    =   $ResumeM -> getList($rWhere, array('field'=>'`id`'));
                $resumeList                 =   $resumeA['list'];
				
                if(!empty($resumeList) && is_array($resumeList)){
                    
                    foreach($resumeList as $v){
                        $reid[]  =   $v['id'];
                    }
                }
                $where['eid']   =   array('in', pylode(',', $reid));
            }
        }
        if(trim($_GET['keyword'])){
            
            $resume =   $ResumeM -> getResumeList(array('name' => array('like', trim($_GET['keyword'])), 'r_status' => 1 ), array('field'=>"`uid`"));
            
            if(!empty($resume) && is_array($resume)){
                
                foreach($resume as $v){
                    
                    $uid[]      =	$v['uid'];
                }
            }
            $where['uid']		=	array('in', pylode(',' , $uid));
            $urlarr['keyword']	=	trim($_GET['keyword']);
        }
        if($_GET['state']){
			$where['is_browse']	=	intval($_GET['state']);
			$urlarr['state']	=	$_GET['state'];
		}
		
		//分页链接
		$urlarr['c']	=	$_GET['c'];
        $urlarr['page']	=	'{{page}}';
        
        $pageurl		=	Url('member',$urlarr);
        
        //提取分页
        $pageM			=	$this  -> MODEL('page');
        $pages			=	$pageM -> pageList('userid_job', $where, $pageurl,$_GET['page']);
        
        //分页数大于0的情况下 执行列表查询
        if($pages['total'] > 0){
            
            //limit order 只有在列表查询时才需要
            if($_GET['order']){
                
                $where['orderby']  =  $_GET['t'].','.$_GET['order'];
                $urlarr['order']   =  $_GET['order'];
                $urlarr['t']	   =  $_GET['t'];
                
            }else{
                $where['orderby']  =  'datetime';
            }
            $where['limit']		   =  $pages['limit'];
            
            $rows                  =  $JobM -> getSqJobList($where, array('uid' => $this -> uid, 'usertype' => $this -> usertype, 'is_link' => 'yes')); // s_num 申请状态数目
            
        }
		
		$uid		=   $this->uid;
		 
		$job		=	$this->yqmsInfo();
		
		foreach ($job as $key => $val){
			$job[$key]['type']		=	1;
		}
		$JobList	=	$job;
 		
		if($JobList && is_array($JobList) && $jobid['0']){
			foreach($JobList as $val){
				if($jobid['0']==$val['id'] && $jobid['1']==$val['type']){
					$current		=	$val;
				}
			}
		}
		
		$dclNum   =   $JobM -> getSqJobNum(array('com_id' => $this -> uid, 'is_browse' => 1,'type'=>array('<>',3)));
		$yckNum   =   $JobM -> getSqJobNum(array('com_id' => $this -> uid, 'is_browse' => 2,'type'=>array('<>',3)));
		$bhsNum   =   $JobM -> getSqJobNum(array('com_id' => $this -> uid, 'is_browse' => 4,'type'=>array('<>',3)));
		$wjtNum   =   $JobM -> getSqJobNum(array('com_id' => $this -> uid, 'is_browse' => 5,'type'=>array('<>',3)));
		$dtzNum   =   $JobM -> getSqJobNum(array('com_id' => $this -> uid, 'is_browse' => 3,'type'=>array('<>',3)));
		
		$this -> yunset(array('current' => $current,'rows' => $rows,'JobList' => $JobList,'StateList' => array(array('id' => 1,'name' => '待处理', 'num' => $dclNum),array('id' => 2,'name' => '已查看', 'num' => $yckNum),array('id' => 4,'name' => '不合适', 'num' => $bhsNum),array('id' => 5,'name' => '未接通', 'num' => $wjtNum),array('id' => 3,'name' => '等通知', 'num' => $dtzNum))));
		$this -> yunset('com_look', @explode(',', $this->config['com_look']));
		
		
		$this -> com_tpl('hr');
		
	}
	
	
	function hrset_action(){
        
		if($_POST['ajax']==1 && $_POST['ids']){

		    //批量阅读
			$JobM    =   $this -> MODEL('job');
			$arr     =   $JobM -> ReadSqJob($_POST['ids'],array('uid'=>$this->uid,'usertype'=>$this->usertype));
			echo json_encode($arr);die;
		}else if($_POST['delid']||$_GET['delid']){
			
			//删除
			$JobM    =   $this -> MODEL('job');
			if(is_array($_POST['delid'])){
			    $id  =   $_POST['delid'];
			}else{
			    $id  =   intval($_GET['delid']);
			}
			$arr     =   $JobM -> delSqJob($id,array('utype'=>'com','uid'=>$this->uid,'usertype'=>$this->usertype));
			$this ->  layer_msg($arr['msg'], $arr['errcode'], $arr['layertype'],$_SERVER['HTTP_REFERER']);
		}else if($_POST['browse']){
		    
			//简历状态设置
			$JobM    =   $this -> MODEL('job');
			$id      =   intval($_POST['id']);
			$data=array('uid'=>$this->uid,'usertype'=>$this->usertype,'username'=>$this->username,'browse'=>intval($_POST['browse']),'port'=>'1');
			$arr     =   $JobM -> BrowseSqJob($id,$data);
			echo $arr;die;
		}
	}
}
?>