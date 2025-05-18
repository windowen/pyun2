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
class resume_controller extends user{
	//简历管理
	function index_action(){

		$this->public_action();
		
        $this->member_satic();
        
        $ResumeM        =     $this->MODEL('resume');
        
        $where['uid']   =     $this->uid;
        
        if (!empty($this->config['user_number'])){
            
            $num		=     $ResumeM->getExpectNum($where);
            $maxnum		=  $this->config['user_number'] - $num;
            
            if($maxnum < 0){$maxnum='0';}
        }else{

            $maxnum		=  '';
        }
        $this->yunset("maxnum",$maxnum);
		
        $rows  =  $ResumeM -> getSimpleList($where,array('field'=>'id,name,lastupdate,doc,tmpid,integrity,hits,height_status,statusbody,`topdate`,`top`,`state`,`status`'));
    
        if($rows&&is_array($rows)){
		
            foreach($rows as $key=>$val){
    				
                if($val['topdate']>1){
        				
                $rows[$key]['topdate']=date("Y-m-d",$val['topdate']);
        
                $rows[$key]['topdatetime']=$val['topdate']-time();
        	
                }else{
                  
    				$rows[$key]['topdate']='未设置';
          
    			}
    		}
		}
       
		$this->yunset("rows",$rows);
    
        $row    =    $ResumeM->getResumeInfo($where,array('field'=>'def_job,idcard_status,idcard_pic'));
        
        $rexpectwhere['uid']          =     $this->uid;
        
        $rexpectwhere['defaults']     =     1;
        
        $resume                       =     $ResumeM->getExpect($rexpectwhere,array('field'=>'integrity,statusbody,sex'));
        
        $integrity                    =     $resume['integrity'];
        
        $renumwhere['integrity']      =     array('>',$integrity);
        
        $resumepm                     =     $ResumeM->getExpectNum($renumwhere);
    
		$this->yunset("resumepm",$resumepm);//简历完整度排名
		
		$CacheArr=$this->MODEL('cache')->GetCache(array('user'));
    
        $eduwhere['uid']              =     $this->uid;
        
        $eduwhere['eid']              =     $row['def_job'];
        
        $eduwhere['orderby']          =     array('sdate,desc');
        
        $edu                          =     $ResumeM-> getResumeEdus($eduwhere);    
    		
       
    
        $workwhere['uid']              =     $this->uid;
        
        $workwhere['eid']              =     $row['def_job'];
        
        $workwhere['orderby']          =     array('sdate,desc');
        
        $work                           =     $ResumeM-> getResumeWorks($workwhere);  

		if(is_array($work)){
      
			$whour = 0;
      
            $hour=array();
      
			foreach($work as $value){
        
				if ($value['edate']){
          
					$workTime = ceil(($value['edate']-$value['sdate'])/(30*86400));
          
				}else{
          
					$workTime = ceil((time()-$value['sdate'])/(30*86400));
          
				}
        
				$hour[] = $workTime;
        
				$whour += $workTime;
        
			}
      
			$worknum = count($hour);
		}
		if($whour>24 || $worknum>3){//工作经历二年以上或工作经历三项以上
    
			$this->yunset('heighttwo',2);
      
		}	
		$this->yunset("def_job",$row['def_job']);
		$this->user_tpl('resume');
	}
	function del_action(){
    
        $ResumeM          =       $this->MODEL('resume');
        
        $id               =       $_GET['id'];
        
        $return           =       $ResumeM->delResume($id,array('uid' => $this -> uid));
        
        $this -> layer_msg($return['msg'], $return['errcode'], $return['layertype'], $_SERVER['HTTP_REFERER']);
	}

	function publicdel_action()
    {
        $ResumeM = $this->MODEL('resume');
        
        $uid = $this->uid;
        
        if ($_POST['id'] && $_POST['table']) {
            
            $tables = array("skill","work","project","edu","training","cert","other");
            
            if (in_array($_POST['table'], $tables)) {
                
                $table  =  "resume_" . $_POST['table'];
                $eid    =  (int) $_POST['eid'];
                $id     =  (int) $_POST['id'];
                $url    =  $_POST['table'];
                
                $nid  =  $ResumeM->delFb($_POST['table'], array('id'=>$id,'uid'=>$uid,'eid'=>$eid), array('utype' => 'user'));
                
                $resume     =  $ResumeM->getUserResumeInfo(array('eid'=>$eid,'uid'=>$this->uid));
                $numresume=20;
	            
	            if($resume['expect'] > 0){
	                $numresume  =  $numresume + 35;
	            }
	            if($resume['work'] > 0){
	                $numresume  =  $numresume + 10;
	            }
	            if($resume['edu'] > 0){
	                $numresume  =  $numresume + 10;
	            }
	            if($resume['skill'] > 0){
	                $numresume  =  $numresume + 10;
	            }
	            if($resume['project'] > 0){
	                $numresume  =  $numresume + 8;
	            }
	            if($resume['training'] > 0){
	                $numresume  =  $numresume + 7;
	            }
				$integrity = $numresume;
                if ($nid) {
                    if ($table == 'resume_work') {
                        // 计算
                        $workList  =  $ResumeM->getResumeWorks(array('eid'=>$eid,'uid'=>$uid), 'sdate,edate');
                        
                        $whour  =  0;
                        $hour   =  array();
                        
                        foreach ($workList as $value) {
                            
                            // 计算每份工作时长(按月)
                            
                            if ($value['edate']) {
                                
                                $workTime  =  ceil(($value['edate'] - $value['sdate']) / (30 * 86400));
                            } else {
                                
                                $workTime  =  ceil((time() - $value['sdate']) / (30 * 86400));
                            }
                            
                            $hour[]  =  $workTime;
                            
                            $whour  +=  $workTime;
                        }
                        // 更新当前简历时长字段
                        $avghour  =  ceil($whour / count($hour));
                        
                        $texpectdata  =  array('whour' => $whour,'avghour' => $avghour);
                        
                        $ResumeM->upInfo(array('id'=>$eid,'uid'=>$uid), array('eData' => $texpectdata));
                    }
                    
                    $data  =  array(
                        'num'        =>  $resume[$url],
                        'integrity'  =>  $integrity
                    );
                    
                    echo json_encode($data);die();
                } else {
                    echo 0;die();
                }
            } else {
                echo 0;die();
            }
        }
    }
	 
	/**
	 * 单个简历刷新
	 */
	function refresh_action()
	{
    
        $resumeM  =  $this->MODEL('resume');
        
        $data     =  array('lastupdate'=>time());
        
        $nid      =  $resumeM -> upInfo(array('id'=>(int)$_GET['id'],'uid'=>$this->uid),array('eData'=>$data));
        
        $resumeM -> upResumeInfo(array('uid'=>$this->uid),array('rData'=>$data));
            
        $nid?$this->layer_msg('刷新成功！',9,0):$this->layer_msg('刷新失败！',8,0);
    
 	}
	
	/**
	 * 会员中心首页提醒弹出框刷新
	 */
	function resumerefresh_action()
	{
	    $resumeM  =  $this->MODEL('resume');
	    
	    $data     =  array(
	        'lastupdate'  =>  time(),
	        'jobstatus'   =>  $_POST['jobstatus']
	    );
	    
	    $nid  =  $resumeM -> upInfo(array('id'=>(int)$_POST['id'],'uid'=>$this->uid),array('eData'=>$data));
    		
        if($nid){
            
            $resumeM -> upResumeInfo(array('uid'=>$this->uid),array('rData'=>array('lastupdate'=>time())));
    			
            echo 1;die;
        }
    }
 	
 	
 	
	//设置默认简历，TODO:仅个人会员中心
    function defaultresume_action()
    {
     
        $resumeM  =  $this->MODEL('resume');
        
        $resumeM -> defaults(array('id'=>(int)$_GET['id'],'uid'=>$this->uid));
    
		$this->layer_msg('操作成功！',9,0,"index.php?c=resume");
	}
}
?>