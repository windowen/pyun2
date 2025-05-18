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
class email_controller extends adminCommon{
	
	function index_action(){
		$emailM		=	$this -> MODEL('email');
		
	    $birthday	=	$emailM -> getEmsgOnce(array('title'=>'生日提醒','orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $anniversary=	$emailM -> getEmsgOnce(array('title'=>'网站周年','orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $todaydue	=	$emailM -> getEmsgOnce(array('title'=>array('like','会员套餐还有1天将过期'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
		$sevenduew['title'][]	=	array('like','会员套餐还有');
		
		$sevenduew['title'][]	=	array('like','天将过期');
		
		$sevenduew['orderby']	=	'ctime,desc';
		
	    $sevendue	=	$emailM -> getEmsgOnce($sevenduew,array('field'=>'`ctime`'));
		
	    $useradd	=	$emailM -> getEmsgOnce(array('title'=>array('like','未发布简历' ),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $userup		=	$emailM -> getEmsgOnce(array('title'=>array('like','今天简历刷新'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $addjob		=	$emailM -> getEmsgOnce(array('title'=>array('like','未发布职位'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $upjob		=	$emailM -> getEmsgOnce(array('title'=>array('like','未刷新职位'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $this->yunset(array('birthday'=>$birthday,'anniversary'=>$anniversary,'todaydue'=>$todaydue,'sevendue'=>$sevendue,'useradd'=>$useradd,'userup'=>$userup,'addjob'=>$addjob,'upjob'=>$upjob));
		
		$this->yuntpl(array('admin/admin_tuiguang'));
	}

	function msgtg_action(){
		$mobliemsgM	=	$this -> MODEL('mobliemsg');
		
	    $birthday	=	$mobliemsgM -> getInfo(array('content'=>array('like','生日'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $anniversary=	$mobliemsgM -> getInfo(array('content'=>array('like','周年庆'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $todaydue	=	$mobliemsgM -> getInfo(array('content'=>array('like','将于1天后到期'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $sevendue	=	$mobliemsgM -> getInfo(array('content'=>array('like','将于7天后到期'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $useradd	=	$mobliemsgM -> getInfo(array('content'=>array('like','未发布简历'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $userup		=	$mobliemsgM -> getInfo(array('content'=>array('like','未刷新简历'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $addjob		=	$mobliemsgM -> getInfo(array('content'=>array('like','未发布职位'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $upjob		=	$mobliemsgM -> getInfo(array('content'=>array('like','未刷新职位'),'orderby'=>'ctime,desc'),array('field'=>'`ctime`'));
		
	    $this->yunset(array('birthday'=>$birthday,'anniversary'=>$anniversary,'todaydue'=>$todaydue,'useradd'=>$useradd,'userup'=>$userup,'addjob'=>$addjob,'upjob'=>$upjob));
		
		$this->yuntpl(array('admin/admin_msgtg'));
	}

	function getBirthday_action(){
		$jobM		=	$this->MODEL('job');
		
		$statisM	=	$this->MODEL('statis');
		
		$resumeM	=	$this->MODEL('resume');
		
		$companyM	=	$this->MODEL('company');
		
		$userinfoM	=	$this->MODEL('userinfo');
		
		$todayduewh['vip_etime'][]	=	array('>',time());
		
		$todayduewh['vip_etime'][]	=	array('<',strtotime('+1 day'));
		
	    $todaydue	=	$statisM->getList($todayduewh,array('field'=>'uid','usertype'=>'2'));
	    foreach ($todaydue as $v){
	        $todayuid[]=$v['uid'];
	    }
		$sevenduewh['vip_etime'][]	=	array('>',time());
		
		$sevenduewh['vip_etime'][]	=	array('<',strtotime('+7 day'));
		
	    $sevendue	=	$statisM->getList($sevenduewh,array('field'=>'uid','usertype'=>'2'));
	    foreach ($sevendue as $v){
	        $sevenuid[]=$v['uid'];
	    }
	    $regs		=	$userinfoM->getList(array('reg_date'=>array('>',strtotime('-7 day')),'usertype'=>'1'),array('field'=>'uid'));
	    foreach($regs as $k=>$v){
	        $uids[]=$v['uid'];
	    }

	    $upjobA		=	$jobM->getList(array('lastupdate'=>array('<',strtotime('-7 day')),'r_status'=>array('<>','2'),'groupby'=>'`uid`','orderby'=>'lastupdate,desc'),array('field'=>'`uid`'));
        $upjob		=	$upjobA['list'];
		$upuid		=	array();
	    foreach ($upjob as $v){
	        $upuid[$v['uid']]	=	$v['uid'];
	    }
		
		$upjobArr	=	$jobM->getList(array('lastupdate'=>array('>=',strtotime('-7 day')),'uid' => array('in', pylode(',', $upuid))),array('field'=>'`uid`'));
        $upjobs		=	$upjobArr['list'];
		$upuids		=	array();
	    foreach ($upjobs as $uv){
	        $upuids[$uv['uid']]	=	$uv['uid'];
	    }
		$newUpuids	=	array_diff($upuid, $upuids);

	    if ($_GET['type']=='email'){
	        $num['birthday_e']   =	$resumeM->getResumeNum(array('birthday'=>array('dateformat','%m%d'),'email'=>array('<>',''), 'email_status'=>'1'));
			
	        $num['anniversary_e']=	$userinfoM->getMemberNum(array('email'=>array('<>',''),'status'=>'1'));
			
	        $num['todaydue_e']   =	$companyM->getCompanyNum(array('email_status'=>'1','linkmail'=>array('<>',''),'r_status'=>array('<>','2'),'uid'=>array('in',pylode(',',$todayuid))));
			
	        $num['sevendue_e']   =	$companyM->getCompanyNum(array('email_status'=>'1','linkmail'=>array('<>',''),'r_status'=>array('<>','2'),'uid'=>array('in',pylode(',',$sevenuid))));
			
	        $num['useradd_e']    =	$resumeM->getResumeNum(array('email_status'=>'1','r_status'=>1,'def_job'=>'0','resumetime'=>array('isnull'),'email'=>array('<>',''),'uid'=>array('in',pylode(',',$uids))));
			
	        $num['userup_e']     =	$resumeM->getResumeNum(array('email_status'=>'1','def_job'=>array('>','0'),'r_status'=>1,'email'=>array('<>',''),'lastupdate'=>array('<',strtotime('-7 day'))));
			
			$addjob_ewh['email_status']	=	'1';
          
			$addjob_ewh['jobtime'][]	=	array('>','1');
          
			$addjob_ewh['jobtime'][]	=	array('<',strtotime('-7 day'));
          
			$addjob_ewh['r_status']		=	array('<>','2');
          
			$addjob_ewh['linkmail']		=	array('<>','');
			
	        $num['addjob_e']     =	$companyM->getCompanyNum($addjob_ewh);
			
	        $num['upjob_e']      =	$companyM->getCompanyNum(array('email_status'=>'1','linkmail'=>array('<>',''),'r_status'=>array('<>','2'),'uid'=>array('in',pylode(',',$newUpuids))));
	    }else{

	        $num['birthday_m']   =	$resumeM->getResumeNum(array('birthday'=>array('dateformat','%m%d'),'telphone'=>array('<>',''), 'moblie_status'=>'1'));
			
	        $num['anniversary_m']=	$userinfoM->getMemberNum(array('moblie'=>array('<>',''),'status'=>'1'));
			
	        $num['todaydue_m']   =	$companyM->getCompanyNum(array('moblie_status'=>'1','linktel'=>array('<>',''),'r_status'=>array('<>','2'),'uid'=>array('in',pylode(',',$todayuid))));
			
	        $num['sevendue_m']   =	$companyM->getCompanyNum(array('moblie_status'=>'1','linktel'=>array('<>',''),'r_status'=>array('<>','2'),'uid'=>array('in',pylode(',',$sevenuid))));
			
	        $num['useradd_m']    =	$resumeM->getResumeNum(array('moblie_status'=>'1','r_status'=>1,'def_job'=>'0','resumetime'=>array('isnull'),'telphone'=>array('<>',''),'uid'=>array('in',pylode(',',$uids))));
			
	        $num['userup_m']     =	$resumeM->getResumeNum(array('moblie_status'=>'1','def_job'=>array('>','0'),'r_status'=>1,'telphone'=>array('<>',''),'lastupdate'=>array('<',strtotime('-7 day'))));
			
			$addjob_mwh['moblie_status']	=	'1';
			
			$addjob_mwh['jobtime'][]		=	array('>','1');
			
			$addjob_mwh['jobtime'][]		=	array('<',strtotime('-7 day'));
			
			$addjob_mwh['r_status']			=	array('<>','2');
			
			$addjob_mwh['linktel']			=	array('<>','');
			
	        $num['addjob_m']     =	$companyM->getCompanyNum($addjob_mwh);
			
	        $num['upjob_m']      =	$companyM->getCompanyNum(array('moblie_status'=>'1','linktel'=>array('<>',''),'r_status'=>array('<>','2'),'uid'=>array('in',pylode(',',$newUpuids))));
	    }
	    echo json_encode($num);die;
	}

	function sendPromotion_action(){
		$jobM		=	$this->MODEL('job');
		
		$statisM	=	$this->MODEL('statis');
		
		$resumeM	=	$this->MODEL('resume');
		
		$companyM	=	$this->MODEL('company');
		
		$userinfoM	=	$this->MODEL('userinfo');
		
	    $type		=	intval($_POST['type']);
	    $emailtype	=	intval($_POST['emailtype']);
	    $emailtpl	=	intval($_POST['emailtpl']);
	    $dayslimit	=	intval($_POST['dayslimit']);
		$sort		=	intval($_POST['sort']);
	    
		if($sort){
	        if($this->config['sy_email_set']!="1"){
	            $arr['status']	=	0;
	            $arr['msg']		=	'还没有配置邮箱，请联系管理员！';
	            
	            echo json_encode($arr);die;
	        }
	    }else{
	        if(!checkMsgOpen($this -> config)){
	            $arr['status']	=	0;
	            $arr['msg']		=	'还没有配置短信，请联系管理员！';
	            
	            echo json_encode($arr);die;
	        }
	    }

	    $emailarr=$user=$com=$userinfo=array();
	    $members=$users=$companys=$uids=$useremail=$comemail=$tpls=array();
	    
		if($emailtype=='1'){
			
		    if(($this->config['sy_email_birthday']!=1 && $sort == '1') || ($this->config['sy_msg_birthday']!=1 && !$sort)){
				$arr['status']	=	0;
	            $arr['msg']		=	'请先开启生日提醒！';
	            
	            echo json_encode($arr);die;
			} 
			
	        if($type=='1'){
	            if($sort){
	                $users	=	$resumeM->getResumeList(array('birthday'=>array('dateformat','%m%d'),'email'=>array('<>',''), 'email_status'=>'1'),array('field'=>'`email`,`uid`,`birthday`,`name`'));
	            }else{
	                $users	=	$resumeM->getResumeList(array('birthday'=>array('dateformat','%m%d'),'telphone'=>array('<>',''), 'moblie_status'=>'1'),array('field'=>'`uid`,`birthday`,`name`,`telphone` as moblie'));
	            }

				
	            if($users&&is_array($users)){
	                foreach($users as $k=>$v){
	                    $userinfo[$v['uid']]=$v;
	                }
	            }
	        }
	    }else if($emailtype=='2'){
		    if(($this->config['sy_email_webbirthday']!=1 && $sort == '1') || ($this->config['sy_msg_webbirthday']!=1 && !$sort)){
				$arr['status']	=	0;
	            $arr['msg']		=	'请先开启周年提醒！';
	            
	            echo json_encode($arr);die;
			}
	        if($type=='2'){
	            if($sort){
	                $members	=	$userinfoM->getList(array('email'=>array('<>',''),'status'=>'1'),array('field'=>'`uid`,`username`,`email`'));
	            }else{
	                $members	=	$userinfoM->getList(array('moblie'=>array('<>',''),'status'=>'1'),array('field'=>'`uid`,`username`,`moblie`'));
	            }
	            if($members&&is_array($members)){
	                foreach($members as $k=>$v){
	                    $userinfo[$v['uid']]=$v;
	                }
	            }
	        }
	    }else if($emailtype=='3'){
		    if(($this->config['sy_email_vipmr']!=1 && $sort == '1') || ($this->config['sy_msg_vipmr']!=1 && !$sort)){
			    $arr['status']	=	0;
	            $arr['msg']		=	'请先开启会员到期提醒！';
	            
	            echo json_encode($arr);die;
			}
			$statiswh['vip_etime'][]	=	array('>',time());
			
			$statiswh['vip_etime'][]	=	array('<',strtotime('+'.$dayslimit.' day'));
			
	        $comstatis	=	$statisM->getList($statiswh,array('field'=>'`uid`,`vip_etime`,`rating_name`','usertype'=>'2'));
	        if(is_array($comstatis)){
				foreach($comstatis as $key=>$value){
					$uid[] = $value['uid'];
				}
				$where['uid']	=	array('in',pylode(',',$uid));
				
				$where['name']	=	array('<>','');
				
				if($sort){
					$where['linkmail']		=	array('<>','');
					
					$where['email_status']	=	'1';
					
					$companys	=	$companyM->getChCompanyList($where,array('field'=>'`uid`,`name`,`linkmail` as email'));
				}else{
					$where['linktel']		=	array('<>','');
					
					$where['moblie_status']	=	'1';
					
					$companys	=	$companyM->getChCompanyList($where,array('field'=>'`uid`,`name`,`linktel` as moblie'));
				}
				foreach($companys as $key=>$value){
					foreach ($comstatis as $k=>$v){
						if($value['uid']==$v['uid']){
							$companys[$key]['vip_etime']	=	$v['vip_etime'];
							$companys[$key]['rating_name']	=	$v['rating_name'];
						}
					}
				}
			}
	        if($companys&&is_array($companys)){
	            foreach($companys as $k=>$v){
					$v['day'] = $dayslimit;
	                $userinfo[$v['uid']]=$v;
	            }
	        }
	    }else if($emailtype=='4'){
	        if(($this->config['sy_email_useradd']!=1 && $sort == '1') || ($this->config['sy_msg_useradd']!=1 && !$sort)){
				$arr['status']	=	0;
	            $arr['msg']		=	'请先开启未发布简历提醒！';
	            
	            echo json_encode($arr);die;
			}
	        if($type=='1'){
	            $regs	=	$userinfoM->getList(array('reg_date'=>array('>',strtotime('-'.$dayslimit.' day')),'usertype'=>'1'),array('field'=>'uid,reg_date,username'));
	            $regdate=$user=array();
				foreach($regs as $k=>$v){
	                $uids[]	=	$v['uid'];
					$regdate[$v['uid']] =	$v['reg_date'];
					$user[$v['uid']] 	=	$v['username'];
	            }
				$where['r_status']	=	1;
				
				$where['def_job']	=	'0';
				
				$where['resumetime']=	array('isnull');
				
				$where['uid']		=	array('in',pylode(',',$uids));
				
	            if($sort){
					$where['email']			=	array('<>','');
					
					$where['email_status']	=	'1';
					
	                $users	=	$resumeM->getResumeList($where,array('field'=>'`uid`,`name`,`email`,`resumetime`'));
	            }else{
					$where['telphone']		=	array('<>','');
					
					$where['moblie_status']	=	'1';
					
	                $users	=	$resumeM->getResumeList($where,array('field'=>'`uid`,`name`,`resumetime`,`telphone` as `moblie`'));
	            }
	            foreach($users as $k=>$v){
					if($v['name']){
						
						$v['name']	=	$user[$v['uid']];
					}
	                $userinfo[$v['uid']]			=	$v;
	                $userinfo[$v['uid']]['day']		=	$dayslimit;
					$userinfo[$v['uid']]['reg_date']=	$regdate[$v['uid']];
	            }
	        }
	    }else if($emailtype=='5'){
	        if(($this->config['sy_email_userup']!=1 && $sort == '1') || ($this->config['sy_msg_userup']!=1 && !$sort)){
				$arr['status']	=	0;
	            $arr['msg']		=	'请先开启未刷新简历提醒！';
	            
	            echo json_encode($arr);die;
			}
			
	        if($type=='1'){
				$where['def_job']	=	array('>','0');
				
				$where['r_status']	=	1;
				
				$where['lastupdate']=	array('<',strtotime('-7 day'));
				
	            if($sort){
					$where['email']			=	array('<>','');
					
					$where['email_status']	=	'1';
					
	                $resumes	=	$resumeM->getResumeList($where,array('field'=>'distinct `uid`,`name`,`email`,`lastupdate`'));
	            }else{
					$where['telphone']		=	array('<>','');
					
					$where['moblie_status']	=	'1';
					
	                $resumes	=	$resumeM->getResumeList($where,array('field'=>'distinct `uid`,`name`,`lastupdate`,`telphone` as `moblie`'));
	            }
				foreach($resumes as $k=>$v){
	            	
	                $userinfo[$v['uid']]		=	$v;
	                $userinfo[$v['uid']]['day']	=	$dayslimit;
	            }
	            }
	            
	    }else if($emailtype=='6'){
	        if(($this->config['sy_email_addjob']!=1 && $sort == '1') || ($this->config['sy_msg_addjob']!=1 && !$sort)){
				$arr['status']	=	0;
	            $arr['msg']		=	'请先开启未发布职位提醒！';
	            
	            echo json_encode($arr);die;
			}
			
	        if($type=='2'){
				$where['jobtime'][]	=	array('>','1');
				
				$where['jobtime'][]	=	array('<',strtotime('-7 day'));
				
				$where['r_status']	=	array('<>','2');
				
	            if($sort){
					$where['linkmail']		=	array('<>','');
					
					$where['email_status']	=	'1';
					
	                $companys	=	$companyM->getChCompanyList($where,array('field'=>'`uid`,`name`,`jobtime`,`linkmail` as `email`,`linktel` as `moblie`'));
	            }else{
					$where['linktel']		=	array('<>','');
					
					$where['moblie_status']	=	'1';
					
	                $companys	=	$companyM->getChCompanyList($where,array('field'=>'`uid`,`name`,`jobtime`,`linktel` as `moblie`'));
	            }
	            foreach($companys as $k=>$v){
	                $userinfo[$v['uid']]		=	$v;
	                $userinfo[$v['uid']]['day']	=	$dayslimit;
	            }
	        }
	    }else if($emailtype=='7'){

	        if(($this->config['sy_email_upjob']!=1 && $sort == '1') || ($this->config['sy_msg_upjob']!=1 && !$sort)){
				$arr['status']	=	0;
	            $arr['msg']		=	'请先开启未刷新职位提醒！';
	            
	            echo json_encode($arr);die;
			}
	        if($type=='2'){

	            $comjobsA	=	$jobM->getList(array('lastupdate'=>array('<',strtotime('-7 day')),'r_status'=>array('<>','2'),'groupby'=>'uid','orderby'=>'lastupdate,desc'),array('field'=>'`uid`,`lastupdate`'));
				$comjobs	=	$comjobsA['list'];
				$comids		=	array();
	            foreach($comjobs as $k=>$v){
	                $comids[$v['uid']]=$v['uid'];
	            }
				
				$comjobsArr	=	$jobM->getList(array('lastupdate'=>array('>=',strtotime('-7 day')),'uid' => array('in', pylode(',', $comids))),array('field'=>'`uid`,`lastupdate`'));
				$comJobs	=	$comjobsArr['list'];
				$comidss	=	array();
	            foreach($comJobs as $ck=>$cv){
	                $comidss[$cv['uid']]	=	$cv['uid'];
	            }
				
				$newComids	=	array_diff($comids, $comidss);
				
				$where['name']	=	array('<>','');
				
				$where['uid']	=	array('in',pylode(',',$newComids));
				
	            if($sort){
					$where['linkmail']		=	array('<>','');
					
					$where['email_status']	=	'1';
					
	                $companys	=	$companyM->getChCompanyList($where,array('field'=>'`uid`,`name`,`linkmail` as `email`'));
	            }else{
					$where['linktel']		=	array('<>','');
					
					$where['moblie_status']	=	'1';
					
	                $companys	=	$companyM->getChCompanyList($where,array('field'=>'`uid`,`name`,`linktel` as `moblie`'));
	            }
	            foreach($companys as $k=>$v){
	                foreach ($comjobs as $val){
	                    if ($v['uid']==$val['uid']){
	                        $companys[$k]['lastupdate']=$val['lastupdate'];
	                    }
	                }
	            }
	            foreach($companys as $k=>$v){
	                $userinfo[$v['uid']]		=	$v;
	                $userinfo[$v['uid']]['day']	=	$dayslimit;
	            }
	        }
	    }

	    if($emailtpl=='1'){
	        $useremail	=	array_unique($useremail);
	        $comemail	=	array_unique($comemail);
	        set_time_limit(1000);
	        if(count($userinfo)>500){
	            $arr['status']=0;
				if($sort){
					$arr['msg']='数量过多，第三方发送服务器将会影响，部分邮件无法发送。建议找专业的群发软件！';
				}else{
					$arr['msg']='数量过多，第三方发送服务器将会影响，部分短信无法发送。建议找专业的群发软件！';
				}
	            echo json_encode($arr);die;
	        }
		}

		foreach($userinfo as $key=>$value){
			$data[] = $this->shjobmsg($value,$emailtype,$sort);
		}
		
 		if($data!=""){
			if($_POST['action']=='sendEmailMsg'){
				
				$pagesize	=	intval($_POST['pagelimit']);
				$sendok		=	intval($_POST['sendok']);
				$sendno		=	intval($_POST['sendno']);

				$result		=	$this->sendEmailMsg($pagesize,$sendok,$sendno,$data);
				if($result){
					
					$toSize		=	$pagesize * $result['page'];

					if(count($data) > $toSize){
						$npage	=	$result['page'];
						$spage	=	$npage*$pagesize+1;
						$topage	=	($npage+1)*$pagesize;

						$name	=	$spage."-".$topage;
 						$this->get_return("3",$npage,"正在发送".$name."份数据",$result['sendok'],$result['sendno']);
					}else{
						
 						$this->get_return("1",0,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
					}
				}
			}
		}
	}

	function shjobmsg($info,$type,$sort){
	    $data=array();
	    $tpltype=array(
	        '1'	=>	'birthday',
	        '2'	=>	'webbirthday',
	        '3'	=>	'vipmr',
	        '4'	=>	'useradd',
	        '5'	=>	'userup',
	        '6'	=>	'addjob',
	        '7'	=>	'upjob'
	    );
	    $data['type']	=	$tpltype[$type];
	    if($data['type']!=""){
	        if($type=='1'){
                $data['uid']		=	$info['uid'];
                $data['name']		=	$info['name'];
                if($sort){
                    $data['email']	=	$info['email'];
                }else{
                    $data['moblie']	=	$info['moblie'];
                }
                $data['username']	=	$info['name'];
                $data['date']		=	$info['birthday'];
                $data['year']		=	date("Y")-date("Y",strtotime($info['birthday']));
	        }elseif ($type=='2'){
	            $data['uid']		=	$info['uid'];
	            $data['name']		=	$info['username'];
	            $data['username']	=	$info['username'];
	            if($sort){
	                $data['email']	=	$info['email'];
	            }else{
	                $data['moblie']	=	$info['moblie'];
	            }
	        }elseif ($type=='3'){
 	            $data['uid']		=	$info['uid'];
	            $data['name']		=	$info['name'];
	            if($sort){
	                $data['email']	=	$info['email'];
	            }else{
	                $data['moblie']	=	$info['moblie'];
	            }
	            $data['ratingname']	=	$info['rating_name'];
				$data['date']		=	date("Y-m-d",$info['vip_etime']);//会员到期日期
				$data['day']		=	ceil(($info['vip_etime'] - time()) / (60 * 60 * 24));//剩余几天到期（向下取整）
	        }elseif ($type=='4'){
	            if($info['username_n']==''){
 	                $data['username']	=	$userarr['username'];
	            }else{
	                $data['username']	=	$info['username_n'];
	            }
	            $data['uid']			=	$info['uid'];
	            $data['name']			=	$info['name_n'];
	            if($sort){
                    $data['email']		=	$info['email'];
                }else{
                    $data['moblie']		=	$info['moblie'];
                }
	            $data['date']			=	date("Y-m-d",$info['reg_date']);
	            $data['day']			=	$info['day'];
	        }elseif ($type=='5'){
	            if($info['username_n']==''){
	                $userarr   =   $this->MODEL('userinfo')->getInfo(array('uid'=>$info['uid']),array('field'=>'`username`'));
	                $data['username']	=	$userarr['username'];
	            }else{
	                $data['username']	=	$info['username_n'];
	            }
	            $data['uid']			=	$info['uid'];
	            $data['name']			=	$info['name_n'];
	            if($sort){
                    $data['email']		=	$info['email'];
                }else{
                    $data['moblie']		=	$info['moblie'];
                }
	            $data['date']			=	date("Y-m-d",$info['lastupdate']);
	            $data['day']			=	$info['day'];
	        }elseif ($type=='6'){
	            $data['uid']			=	$info['uid'];
	            $data['name']			=	$info['name'];
	            if($sort){
                    $data['email']		=	$info['email'];
                }else{
                    $data['moblie']		=	$info['moblie'];
                }
	            if($info['name']){
	                $data['username']	=	$info['name'];
	            }else{
	                $data['username']	=	$info['username'];
	            }
	            $data['date']			=	date("Y-m-d",$info['jobtime']);
	            $data['day']			=	$info['day'];
	        }elseif ($type=='7'){
	            $data['uid']			=	$info['uid'];
	            $data['name']			=	$info['name'];
	            if($sort){
                    $data['email']		=	$info['email'];
                }else{
                    $data['moblie']		=	$info['moblie'];
                }
	            if($info['name']){
	                $data['username']	=	$info['name'];
	            }else{
	                $data['username']	=	$info['username'];
	            }
	            $data['date']			=	date("Y-m-d",$info['lastupdate']);
	            $data['day']			=	$info['day'];
	        }
	        return $data;
	    }
	}

	function sendEmailMsg($pagesize,$sendok,$sendno,$data){
		
 		$errorMsg	=	'';
		$notice		=	$this->MODEL('notice');

		if($_POST['value']=='0'){

			foreach($data as $key=>$value){

				if($key < $pagesize){
					if($value['email']){
						$retval = $notice->sendEmailType($value);
					}
					if($value['moblie']){
						$value['port']	=	'5';
						$retval = $notice->sendSMSType($value);
					}

					if($retval['status'] != -1){
						$sendok ++;	
 					}else{
						$sendno ++;
					}
				}
			}

			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{

			$page=$_POST['value'];
			$start = $page*$pagesize;
			$end = ($page+1)*$pagesize;

			foreach($data as $key=>$value){

				if(  $key >= $start && $key < $end){
					if($value['email']){
						$retval = $notice->sendEmailType($value);
					}
					if($value['moblie']){
						$value['port']	=	'5';
						$retval = $notice->sendSMSType($value);
					}
					if($retval['status'] != -1){
						$sendok ++;	
					}else{
						$sendno ++;
					}
				}
			}
			$page = $page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
		}

		return $result;
	}

	//简历推广

	function tgresume_action(){
	    $this->yuntpl(array('admin/admin_tgresume'));
	}

	function getcom_action(){
		$jobM		=	$this->MODEL('job');
		
		$statisM	=	$this->MODEL('statis');
		
		$companyM	=	$this->MODEL('company');
		
		$userinfoM	=	$this->MODEL('userinfo');
		
		$com	=	(int)$_POST['com'];
		$time	=	time();
	    if ($com==1){ 
	        $row	=	$statisM->getList(array('vip_etime'=>array('>=',strtotime('today'))),array('field'=>'uid','usertype'=>'2'));
	    }elseif ($com==2){ 
			$rowA	=	$jobM->getList(array('lastupdate'=>array('>',strtotime('-7 day',$time)),'r_status'=>array('<>','2')),array('field'=>'`uid`'));
			$row	=	$rowA['list'];
	    }elseif ($com==3){  
	        $row	=	$userinfoM->getList(array('reg_date'=>array('>',strtotime('-3 day',$time)),'usertype'=>'2'),array('field'=>'uid'));
		}
		$uids = array();
		foreach($row as $r){
			$uids [] = $r['uid'];
		}
		$uids = array_unique($uids);

		$num = 0;
		if(count($uids) > 0){
			$where['uid']	=	array('in',pylode(',', $uids));
			
			$where['name']	=	array('<>','');
			
			if($_POST['msgType'] == 1){
				$where['linktel']		=	array('<>','');
				
				$where['moblie_status']	=	'1';
			}
			else{
				$where['linkmail']		=	array('<>','');
				
				$where['email_status']	=	'1';
			}
			$num	=	$companyM->getCompanyNum($where);
		}
	    echo $num;die;
	}

	function sendresume_action(){

		extract($_POST);
	    $stype	=	intval($stype);
	    
		if($stype==1){

	        $company	=	$this->gsresume($resume,$com,$sendnum,$num);
			if($company&&is_array($company)){
				
				$pagesize	=	intval($_POST['pagelimit']);
				$sendok		=	intval($_POST['sendok']);
				$sendno		=	intval($_POST['sendno']?$_POST['sendno']:0);
				$value		=	intval($_POST['value']);
				
				$result = $this->sendResumeETG($company,$email_title,$pagesize,$value,$sendok,$sendno);
				
		
				if($result){
					
					$toSize = $pagesize * $result['page'];

					if(count($company) > $toSize){
						$npage	=	$result['page'];
						$spage	=	$npage*$pagesize+1;
						$topage	=	($npage+1)*$pagesize;
						$name	=	$spage."-".$topage;

 						$this->get_return("3",$npage,"正在发送".$name."份邮件",$result['sendok'],$result['sendno']);
					}else{
						$this->get_return("1",0,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
					}
				}

	        } 
	        
	    }else{

			if(!checkMsgOpen($this -> config)){
				$arr['msg']="还没有配置短信！";
				$arr['status']=2;
				echo json_encode($arr);die;
	        }
			$company=$this->getsendcom($com,$sendnum,2);
			
			if($company&&is_array($company)){
				
				$pagesize	=	intval($pagelimit);
				$sendok		=	intval($sendok);
				$sendno		=	intval($sendno?$sendno:0);
				$value		=	intval($value);

				$result = $this->sendResumeMTG($company,$content,$pagesize,$value,$sendok,$sendno);

				if($result){
					
					$toSize = $pagesize * $result['page'];

					if(count($company) > $toSize){
						$npage	=	$result['page'];
						$spage	=	$npage*$pagesize+1;
						$topage	=	($npage+1)*$pagesize;
						$name	=	$spage."-".$topage;
 						
						$this->get_return("3",$npage,"正在发送".$name."条信息",$result['sendok'],$result['sendno']);
					}else{
						$this->get_return("1",0,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
					}
				}

			}
			

 	    }
	}

	function sendResumeETG($company,$title,$pagesize,$value,$sendok,$sendno){

		$notice = $this->MODEL('notice');

		if($value==0){
			foreach($company as $key=>$val){
				 
				if($key < $pagesize){
					if($val['html']!=''){
						$emailData['email']		=	$val['linkmail'];
						$emailData['subject']	=	$title;
						$emailData['content']	=	$val['html'];
						$emailData['uid']		=	$val['uid'];
						$emailData['name']		=	$val['name'];
						$emailData['cname']		=	"admin";
						$sendid	=	$notice->sendEmail($emailData);
						
						if($sendid['status'] != -1){
							$sendok++;
						}else{
							$sendno++;
						}
					}
				}
			}

			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{

			$page	=	$_POST['value'];
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			foreach($company as $key=>$val){

				if($key >= $start && $key < $end){
					if($val['html']!=''){
						$emailData['email']		=	$val['linkmail'];
						$emailData['subject']	=	$title;
						$emailData['content']	=	$val['html'];
						$emailData['uid']		=	$val['uid'];
						$emailData['name']		=	$val['name'];
						$emailData['cname']		=	"admin";
						$sendid	=	$notice->sendEmail($emailData);

						if($sendid['status'] != -1){
							$sendok++;
						}else{
							$sendno++;
						}
					}
				}
			}
			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
		}

		return $result;
	}

	function sendResumeMTG($company,$content,$pagesize,$value,$sendok,$sendno){

		$notice = $this->MODEL('notice');

		if($value==0){
			foreach($company as $key=>$value){
				if($key < $pagesize){
					$msgData['moblie']	=	$val['linktel'];
					$msgData['content']	=	$content;
					$msgData['uid']		=	$val['uid'];
					$msgData['name']	=	$val['name'];
					$msgData['cname']	=	"admin";
					$msgData['port']	=	'5';
					$sendid	=	$notice->sendSMS($msgData);
					if($sendid['status'] != -1){
						$sendok++;
					}else{
						$sendno++;
					}
				}
			}

			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{

			$page	=	$_POST['value'];
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			foreach($company as $key=>$value){

				if($key >= $start && $key < $end){
					$msgData['moblie']	=	$val['linktel'];
					$msgData['content']	=	$content;
					$msgData['uid']		=	$val['uid'];
					$msgData['name']	=	$val['name'];
					$msgData['cname']	=	"admin";
					$msgData['port']	=	'5';
					$sendid	=	$notice->sendSMS($msgData);
					if($sendid['status'] != -1){
						$sendok++;
					}else{
						$sendno++;
					}
				}
			}
			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
		}

		return $result;
	}

	function gsresume($resume,$com,$sendnum,$num){
        $resumeM	=	$this->MODEL('resume');
        $company	=	$this->getsendcom($com, $sendnum);
        $cacheM		=	$this->MODEL('cache');
	    $cacheList	=	$cacheM->GetCache('user');
		if($company && is_array($company)){
	        foreach ($company as $v){
	            $hyid[$v['uid']]['hy']		=	$v['hy'];
	            $hyid[$v['uid']]['cityid']	=	$v['cityid'];
	        }
	    }
	    if($hyid && is_array($hyid)){
	        include(CONFIG_PATH."db.data.php");
	        include PLUS_PATH."/user.cache.php";
	        foreach ($hyid as $k=>$v){
				$where['hy']			=	$v['hy'];
				
				$where['status']		=	array('<>','2');

				$where['r_status']		=	1;
				
				$where['job_classid']	=	array('<>','');
				
				$where['defaults']		=	'1';
				
				$where['orderby']		=	'lastupdate,desc';
				
				$where['limit']			=	$num;
				
	           if ($resume==2){
	                $expect	=	$resumeM->getSimpleList($where);
	            }elseif ($resume==3){
					$where['whour']		=	array('>','12');
					
					$where['exp']		=	array('>','18');
					
	                $expect	=	$resumeM->getSimpleList($where);
	            }
	   
	            if($expect && is_array($expect)){

	                $html='<table width="800"border="0" style="border:1px solid #ddd" cellpadding="5" cellspacing="0" id="rtable">';
	                $html.='<tr ><td colspan="6"><div style="float:left;padding:10px;"><img src="'.$this->config['sy_weburl'].'/'.$this->config['sy_logo'].'"></div><div style="float:left; padding-left:100px; line-height: 100px;"> 网站联系电话:'.$this->config['sy_freewebtel'].'</div> <div style=" float:right; padding-right:10px;    text-align: center;"><div><img src="'.$this->config['sy_weburl'].'/'.$this->config['sy_wx_qcode'].'" width="80"height="80"></div>微信公众号二维码</div></td></tr>';
	                $html.='<tr style="background:#f8f8f8; font-weight:bold"><td height="26">姓名</td><td>年龄</td><td>学历</td><td>工作经验</td> <td>性别</td> <td width="80" align="center">操作</td></tr>';
	                

	                foreach ($expect as $k => $v){
	                	
	                    $a=date('Y',strtotime($v['birthday']));
	                    $age=date("Y")-$a;
	                    
	                     $url=Url('resume',array('c'=>'show','id'=>$v['id']));
	                    
	                    $html.='<tr><td height="26" style="border-bottom:1px solid #ddd"><b><font color="#0033FF">'.$v['uname'].'</font></b></td><td style="border-bottom:1px solid #ddd">'.$age.'</td><td style="border-bottom:1px solid #ddd">'.$userclass_name[$v['edu']].'</td><td style="border-bottom:1px solid #ddd">'.$userclass_name[$v['exp']].'</td> <td style="border-bottom:1px solid #ddd">'.$cacheList['user_sex'][$v['sex']].'</td><td align="center" style="border-bottom:1px solid #ddd"><a href="'.$url.'" style="background:#f60; padding:2px 14px;color:#fff; display:inline-block">查看</a></td></tr>';
	                }
	                $html.='</table>';
	                $table[$k]=$html;
	            }
	        }
	        foreach ($company as $k=>$v){
	        	
	            foreach ($table as $key=>$val){
	                $company[$k]['html']=$val;
	            }
	        }

	        return $company;
	    }
	}

	function getsendcom($com,$sendnum,$type=1){
        $jobM		=	$this->MODEL('job');
		
        $statisM	=	$this->MODEL('statis');
		
        $companyM	=	$this->MODEL('company');
		
        $userinfoM	=	$this->MODEL('userinfo');
		
	    if ($com==1){ 
	        $scom	=	$statisM->getList(array('vip_etime'=>array('>=',strtotime('today'))),array('field'=>'uid','usertype'=>'2'));
	    }elseif ($com==2){  
	        $scomA	=	$jobM->getList(array('lastupdate'=>array('>',strtotime('-7 day')),'r_status'=>array('<>','2')),array('field'=>"distinct `uid`"));
			$scom	=	$scomA['list'];
	    }elseif ($com==3){  
	        $scom	=	$userinfoM->getList(array('reg_date'=>array('>',strtotime('-3 day')),'usertype'=>'2'),array('field'=>'uid'));
	    }
	    if($scom && is_array($scom)){
	        foreach($scom as $v){
	            $comid[]=$v['uid'];
	        }
	        
			$where['r_status']	=	array('<>','2');
			
			$where['uid']		=	array('in',pylode(',',$comid));
			
			$where['orderby']	=	'lastupdate,desc';
			
	        if($sendnum){
	            $where['limit']	=	$sendnum;
	        }
	        if($type==1){
				$where['linkmail']		=	array('<>','');
				
				$where['email_status']	=	'1';
				
	            $company	=	$companyM->getChCompanyList($where,array('field'=>'uid,name,hy,linkmail,cityid'));
	        }else{
				$where['linktel']		=	array('<>','');
				
				$where['moblie_status']	=	'1';
				
	            $company	=	$companyM->getChCompanyList($where,array('field'=>'uid,name,hy,linktel,cityid'));
	        }
	    }
	    return $company;
	}

	//职位推广
	function tgjob_action(){
	    $this->yuntpl(array('admin/admin_tgjob'));
	}

	function getuser_action(){
		$resumeM	=	$this->MODEL('resume');
		
		$userinfoM	=	$this->MODEL('userinfo');
		
	    $user		=	(int)$_POST['user'];
	    if ($user==1){
	        $row	=	$resumeM->getSimpleList(array('lastupdate'=>array('>',strtotime('-7 day')),'status'=>1,'r_status'=>1,'defaults'=>'1'),array('field'=>'`uid`'));
	    }elseif ($user==2){
	        $row	=	$userinfoM->getList(array('reg_date'=>array('>',strtotime('-3 day')),'usertype'=>'1'),array('field'=>'`uid`'));
	    }
	    $uids = array();
	    foreach($row as $v){
	        $uids [] = $v['uid'];
	    }
	    
	    $num = 0;
	    if(count($uids) > 0){
	        $where['uid']	=	array('in',pylode(',',$uids));
	        if($_POST['msgType'] == 1){
	            $where['email']			=	array('<>','');
	            $where['email_status']	=	'1';
	        }
	        else{
	            $where['telphone']		=	array('<>','');
	            $where['moblie_status']	=	'1';
	        }
	        $num	=	$resumeM->getResumeNum($where);
	    }
	    echo $num;die;
	}

	function getjob_action(){
		
		$jobM	=	$this -> MODEL('job');
		
	    $job	=	(int)$_POST['job'];
		
	    if ($job==2){
			
	        $num	=	$jobM -> getJobNum(array('rec_time'=>array('>',time()),'state'=>'1'));
			
	    }elseif ($job==3){
			
	        $num	=	$jobM -> getJobNum(array('urgent_time'=>array('>',time()),'state'=>'1'));
			
	    }
	    echo $num;die;
	}

	function sendjob_action(){
	    extract($_POST);
	    $stype=intval($stype);

	    if($stype==1){
	        
			$resume=$this->gsjob($job,$user,$sendnum,$num);
 	        
			if($resume&&is_array($resume)){

				$pagesize	=	intval($_POST['pagelimit']);
				$sendok		=	intval($_POST['sendok']);
				$sendno		=	intval($_POST['sendno']?$_POST['sendno']:0);
				$value		=	intval($_POST['value']);
				
				$result		=	$this->sendJobETG($resume,$email_title,$pagesize,$value,$sendok,$sendno);

				if($result){
					
					$toSize	=	$pagesize * $result['page'];

					if(count($resume) > $toSize){
						$npage	=	$result['page'];
						$spage	=	$npage*$pagesize+1;
						$topage	=	($npage+1)*$pagesize;
						$name	=	$spage."-".$topage;

 						$this->get_return("3",$npage,"正在发送".$name."份邮件",$result['sendok'],$result['sendno']);
					}else{
						$this->get_return("1",0,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
					}
				}
	        }

	    }else{
	        
			if(!checkMsgOpen($this -> config)){
				$arr['msg']		=	"还没有配置短信！";
				$arr['status']	=	2;
				echo json_encode($arr);die;
	        }
			$resume=$this->getsenduser($_POST['user'], $_POST['sendnum'],2);
			
			if($resume&&is_array($resume)){
				$pagesize	=	intval($pagelimit);
				$sendok		=	intval($sendok);
				$sendno		=	intval($sendno?$sendno:0);
				$value		=	intval($value);

				$result		=	$this->sendJobMTG($company,$content,$pagesize,$value,$sendok,$sendno);

				if($result){
					
					$toSize =	$pagesize * $result['page'];

					if(count($resume) > $toSize){
						$npage	=	$result['page'];
						$spage	=	$npage*$pagesize+1;
						$topage	=	($npage+1)*$pagesize;
						$name	=	$spage."-".$topage;
 						
						$this->get_return("3",$npage,"正在发送".$name."条信息",$result['sendok'],$result['sendno']);
					}else{
						$this->get_return("1",0,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
					}
				}
			}
 	    }
	}

	function gsjob($job,$user,$sendnum,$num){
		$jobM		=	$this->MODEL('job');
		$resumeM	=	$this->MODEL('resume');
	    $resume		=	$this->getsenduser($user, $sendnum);
	    $cacheM		=	$this->MODEL('cache');
	    $cacheList	=	$cacheM->GetCache('user');
	    if($resume&&is_array($resume)){
	        foreach ($resume as $v){
	            $uid[]=$v['uid'];
	        }
	        $expect	=	$resumeM->getSimpleList(array('uid'=>array('in',pylode(',', $uid)),'defaults'=>'1'),array('field'=>'uid,hy,city_classid'));
	        foreach ($expect as $v){
	            $hyid[$v['uid']]['hy']		=	$v['hy'];
	            $hyid[$v['uid']]['cityid']	=	$v['city_classid'];
	        }
	    }
	    if($hyid&&is_array($hyid)){
	        include(CONFIG_PATH."db.data.php");
	        include PLUS_PATH."/com.cache.php";
	        include PLUS_PATH."/city.cache.php";
	        foreach ($hyid as $k=>$v){
				$where['hy']		=	$v['hy'];
				
				$where['cityid']	=	array('in',$v['cityid']);
				
				$where['state']		=	'1';
				
				$where['orderby']	=	'lastupdate,desc';
				
				$where['limit']		=	$num;
				
	            if ($job==1){ 
	                $comjobA	=	$jobM->getList($where);
					$comjob		=	$comjobA['list'];
	            }elseif ($job==2){
	                $where['rec_time']		=	array('>',time());
					
					$comjobA	=	$jobM->getList($where);
					$comjob		=	$comjobA['list'];
	            }elseif ($job==3){
	                $where['urgent_time']	=	array('>',time());
					
	                $comjobA	=	$jobM->getList($where);
					$comjob		=	$comjobA['list'];
	            }
	            if($comjob&&is_array($comjob)){
	                $html='<table width="800"border="0" style="border:1px solid #ddd" cellpadding="5" cellspacing="0" id="rtable">';
	                $html.='<tr ><td colspan="6"><div style="float:left;padding:10px;"><img src="'.$this->config['sy_weburl'].'/'.$this->config['sy_logo'].'"></div><div style="float:left; padding-left:100px; line-height: 100px;"> 网站联系电话:'.$this->config['sy_freewebtel'].'</div> <div style=" float:right; padding-right:10px;    text-align: center;"><div><img src="'.$this->config['sy_weburl'].'/'.$this->config['sy_wx_qcode'].'" width="80"height="80"></div>微信公众号二维码</div></td></tr>';
	                $html.='<tr style="background:#f8f8f8; font-weight:bold"><td height="26">职位</td><td>工作地点</td><td>薪资</td><td>学历要求</td><td>工作经验</td> <td>性别</td> <td width="80" align="center">操作</td></tr>';
	                foreach ($comjob as $v){
	                    $url=Url('job',array('c'=>'comapply','id'=>$v['id']));
	                    if ($v['minsalary']&&$v['maxsalary']){
	                        $salary='￥'.$v['minsalary'].'-'.$v['maxsalary'];
	                    }elseif ($v['minsalary']&&!$v['maxsalary']){
	                        $salary='￥'.$v['minsalary'].'起';
	                    }elseif (!$v['minsalary']&&!$v['maxsalary']){
	                        $salary='面议';
	                    }
	                    $html.='<tr><td height="26" style="border-bottom:1px solid #ddd"><b><font color="#0033FF">'.mb_substr($v['name'],"0","12","utf-8").'</font></b></td><td style="border-bottom:1px solid #ddd">'.$city_name[$v['cityid']].'</td><td style="border-bottom:1px solid #ddd">'.$salary.'</td><td style="border-bottom:1px solid #ddd">'.$comclass_name[$v['edu']].'</td><td style="border-bottom:1px solid #ddd">'.$comclass_name[$v['exp']].'</td> <td style="border-bottom:1px solid #ddd">'.$cacheList['user_sex'][$v['sex']].'</td><td align="center" style="border-bottom:1px solid #ddd"><a href="'.$url.'" style="background:#f60; padding:2px 14px;color:#fff; display:inline-block">查看</a></td></tr>';
	                }
	                $html.='</table>';
	                $table[$k]=$html;
	            }
	        }

	        foreach ($resume as $k=>$v){
	            foreach ($table as $key=>$val){
	                if ($v['uid']==$key){
	                    $resume[$k]['html']=$val;
	                }
	            }
	        }

	        return $resume;
	    }
	}

	function getsenduser($user,$sendnum,$type=1){
		$resumeM	=	$this->MODEL('resume');
		
		$userinfoM	=	$this->MODEL('userinfo');
	    if ($user==1){
	        $suser	=	$resumeM->getSimpleList(array('lastupdate'=>array('>',strtotime('-7 day')),'status'=>array('<>','2'),'r_status'=>array('<>','2'),'job_classid'=>array('<>',''),'defaults'=>'1'),array('field'=>'`uid`'));
	    }elseif ($user==2){ 
	        $suser	=	$userinfoM->getList(array('reg_date'=>array('>',strtotime('-3 day')),'usertype'=>'1'),array('field'=>'`uid`'));
	    }
	    if($suser&&is_array($suser)){
	        foreach ($suser as $v){
	            $userid[]=$v['uid'];
	        }
	        
			$where['r_status']	=	1;
			
			$where['uid']		=	array('in',pylode(',',$userid));
			
			$where['orderby']	=	'lastupdate,desc';
			
	        if ($sendnum){
	            $where['limit']=$sendnum;
	        }
	        if ($type==1){
				$where['email']			=	array('<>','');
				
				$where['email_status']	=	'1';
				
	            $resume	=	$resumeM->getResumeList($where,array('field'=>"uid,name,email"));
	        }else{
				$where['telphone']		=	array('<>','');
				
				$where['moblie_status']	=	'1';
				
	            $resume	=	$resumeM->getResumeList($where,array('filed' => "uid,name,telphone"));
	        }
	    }
	    return $resume;
	}

	function sendJobETG($resume,$title,$pagesize,$value,$sendok,$sendno){

		$notice = $this->MODEL('notice');

		if($value==0){
			foreach($resume as $key=>$val){
				 
				if($key < $pagesize){
					if($val['html']!=''){
						$emailData['email']		=	$val['email'];
						$emailData['subject']	=	$title;
						$emailData['content']	=	$val['html'];
						$emailData['uid']		=	$val['uid'];
						$emailData['name']		=	$val['name'];
						$emailData['cname']		=	"admin";
						$sendid	=	$notice->sendEmail($emailData);
						if($sendid['status'] != -1){
							$sendok++;
						}else{
							$sendno++;
						}
					}
				}
			}

			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{

			$page	=	$_POST['value'];
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			foreach($resume as $key=>$val){

				if($key >= $start && $key < $end){
					if($val['html']!=''){
						$emailData['email']		=	$val['email'];
						$emailData['subject']	=	$title;
						$emailData['content']	=	$val['html'];
						$emailData['uid']		=	$val['uid'];
						$emailData['name']		=	$val['name'];
						$emailData['cname']		=	"admin";
						$sendid	=	$notice->sendEmail($emailData);

						if($sendid['status'] != -1){
							$sendok++;
						}else{
							$sendno++;
						}
					}
				}
			}
			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
		}

		return $result;
	}

	function sendJobMTG($resume,$content,$pagesize,$value,$sendok,$sendno){

		$notice = $this->MODEL('notice');

		if($value==0){
			foreach($resume as $key=>$value){
				if($key < $pagesize){
					$msgData['moblie']	=	$value['telphone'];
					$msgData['content']	=	$content;
					$msgData['uid']		=	$value['uid'];
					$msgData['name']	=	$value['name'];
					$msgData['cname']	=	"admin";
					$msgData['port']	=	'5';
					$sendid	=	$notice->sendSMS($msgData);
					if($sendid['status'] != -1){
						$sendok++;
					}else{
						$sendno++;
					}
				}
			}

			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{

			$page	=	$_POST['value'];
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			foreach($resume as $key=>$value){

				if($key >= $start && $key < $end){
					$msgData['moblie']	=	$value['telphone'];
					$msgData['content']	=	$content;
					$msgData['uid']		=	$value['uid'];
					$msgData['name']	=	$value['name'];
					$msgData['cname']	=	"admin";
					$msgData['port']	=	'5';
					$sendid	=	$notice->sendSMS($msgData);
					if($sendid['status'] != -1){
						$sendok++;
					}else{
						$sendno++;
					}
				}
			}
			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
		}

		return $result;
	}
	
	//自定义邮件
	function email_action(){  
		$this->yuntpl(array('admin/admin_send_email'));  
	}
	
	function send_action(){ 
	
		$UserInfoM = $this->MODEL('userinfo');

		if($_POST['email_title']==''||$_POST['content']==''){
			$arr['msg']		=	"邮件标题均不能为空！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
 		} 
		
		$emailarr=$user=$com=$userinfo=array();
 		
		if($_POST['utype']!='5'){
		    //自定义用户
			$userrows	=	$UserInfoM->getList(array('usertype'=>$_POST['utype']),array('field'=>'email,`uid`,`usertype`'));
			
		}else if($_POST['utype']=='5'){
		    
			$email_user	=	@explode(',',$_POST['email_user']); 
			$email_user	=	array_filter($email_user);
			foreach($email_user as $v){
			    if(CheckRegEmail($v)){
			        $earr[]=$v;
			    }
			}
			if(!empty($earr)){
				$where['email']=array('in', "\"".@implode('","',$earr)."\"");
				$userrows	=	$UserInfoM->getList($where,array('field'=>'`email`,`uid`,`usertype`'));  
			}
		}
		if(is_array($userrows)&&$userrows){
			foreach($userrows as $v){
				
				if($v['usertype']=='1'){	$user[]	=	$v['uid'];}
				if($v['usertype']=='2'){	$com[]	=	$v['uid'];}
				
				$emailarr[$v['uid']]=$v["email"];
			}
 			
			if($user&&is_array($user)){
				$where['uid']	=	array('in',pylode(',',$user));
			}
			if($com&&is_array($com)){
				$where['uid']	=	array('in',pylode(',',$com));
			}
			
			
			$List	=	$UserInfoM->getUserList($where);
			
			foreach($List as $v){
				$userinfo[$v['uid']]=$v['name'];
			}
		} 
			
		if(!count($emailarr)){ 
			$arr['msg']		=	"没有符合条件的邮箱，请先检查！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}else{
			set_time_limit(10000);

			$pagesize	=	intval($_POST['pagelimit']);
			$sendok		=	intval($_POST['sendok']);
			$sendno		=	intval($_POST['sendno']?$_POST['sendno']:0);
			$value		=	intval($_POST['value']);

			//分批次发送
			$result		=	$this->send_email($emailarr,$_POST['email_title'],$_POST['content'],$userinfo,$pagesize,$sendok,$sendno,$value);
			
			if($result){
				$toSize = $pagesize * $result['page'];

				if(count($emailarr) > $toSize){
					$npage	=	$result['page'];
					$spage	=	$npage*$pagesize+1;
					$topage	=	($npage+1)*$pagesize;
					$name	=	$spage."-".$topage;
					
					$this->get_return("3",$npage,"正在发送".$name."封邮件",$result['sendok'],$result['sendno']);
				 
				}else{
					$this->get_return("1",$npage,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
 				}
			}
		}
 	}

	function send_email($email=array(),$emailtitle="",$emailcoment="",$userinfo=array(),$pagesize,$snedok,$snedno,$value){
		
		$notice = $this->MODEL('notice');
		$sendok = intval($sendok);
		$sendno = intval($sendno);

		if($value=='0'){
			$i = 1;
			foreach($email as $key => $v){
					
				if($i <=$pagesize){
					$emailData['email']		=	$v;
					$emailData['subject']	=	$emailtitle;
					$emailData['content']	=	stripslashes($emailcoment);
					$emailData['uid']		=	$key;
					$emailData['name']		=	$userinfo[$key];
					$emailData['cname']		=	"系统";
					if($v){
						$sendid = $notice->sendEmail($emailData);
					}
					if($sendid['status'] != -1){
						$state=1;
						$sendok++;
					}else{
						$state=0;
						$sendno++;
					}
				}
				$i++;
			}
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{
			$page	=	$value;
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			$i=1;

			foreach($email as $key=>$v){

				if($i > $start && $i <= $end){
					
					$emailData['email']		=	$v;
					$emailData['subject']	=	$emailtitle;
					$emailData['content']	=	stripslashes($emailcoment);
					$emailData['uid']		=	$key;
					$emailData['name']		=	$userinfo[$key]['name'];
					$emailData['cname']		=	"系统";
						
					if($v){
						$sendid = $notice->sendEmail($emailData);
					}
					if($sendid['status'] != -1){
						$state=1;
						$sendok++;
					}else{
						$state=0;
						$sendno++;
					}
				}
				$i++;
			}

			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
			 
		}
		return $result;
	}

	//自定义短信
	function msg_action(){
		$this->yuntpl(array('admin/information'));
	}
	
	function msgsave_action(){
		
		$userinfoM	=	$this->MODEL('userinfo');
		
		if(!checkMsgOpen($this -> config)){
			$arr['msg']		=	"还没有配置短信！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}
		if(trim($_POST['content'])==''){
			$arr['msg']		=	"请输入短信内容！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}
		if($_POST['userarr']=='' && $_POST['utype']=='5'){
			$arr['msg']		=	"手机号码不能为空！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}
		$uidarr=array();
		if($_POST['utype']==5){
			$mobliesarr	=	@explode(',',$_POST['userarr']);
			$userrows	=	$userinfoM->getList(array('moblie'=>array('in',$_POST['userarr'])),array('field'=>'`moblie`,`uid`,`usertype`'));		 
			$moblies	=	array();
			foreach($userrows as $v){
				$moblies[]=$v['moblie'];
			}  
		}else{
			$userrows	=	$userinfoM->getList(array('usertype'=>$_POST['utype']),array('field'=>'`moblie`,`uid`,`usertype`'));
		}

		if(is_array($userrows)&&$userrows){
			$user=$com=$userinfo=array();
			foreach($userrows as $v){
				
				if($v['usertype']=='1'){	$user[]	=	$v['uid'];}
				if($v['usertype']=='2'){	$com[]	=	$v['uid'];}
				
				$uidarr[$v['uid']]	=	$v["moblie"];
			}
			if($user&&is_array($user)){
				$where['uid']	=	array('in',pylode(',',$user));
			}
			if($com&&is_array($com)){
				$where['uid']	=	array('in',pylode(',',$com));
			}
			
			$List	=	$userinfoM->getUserList($where);
			
			foreach($List as $v){
				$userinfo[$v['uid']]=$v['name'];
			}
		}
		if($_POST['utype']==5){
			foreach($mobliesarr as $v){
				if(in_array($v,$moblies)==false&&CheckMoblie($v)){
					$uidarr[]=$v;
				}
			}
		}
 
		if(!count($uidarr)){ 
			$arr['msg']		=	"没有符合条件号码，请先检查！";
			$arr['status']	=	2;
			echo json_encode($arr);die;
		}else{
			set_time_limit(10000);

			$pagesize	=	intval($_POST['pagelimit']);
			$sendok		=	intval($_POST['sendok']);
			$sendno		=	intval($_POST['sendno']?$_POST['sendno']:0);
			$value		=	intval($_POST['value']);

			//分批次发送
			$result		=	$this->sendDivMsg($uidarr,$_POST['content'],$userinfo,$pagesize,$sendok,$sendno,$value);
			
			if($result){
				$toSize = $pagesize * $result['page'];

				if(count($uidarr) > $toSize){
					$npage	=	$result['page'];
					$spage	=	$npage*$pagesize+1;
					$topage	=	($npage+1)*$pagesize;
					$name	=	$spage."-".$topage;
					
					$this->get_return("3",$npage,"正在发送".$name."条短信",$result['sendok'],$result['sendno']);
				}else{
					$this->get_return("1",$npage,"发送成功:".$result['sendok'].",失败:".$result['sendno']);
				}
			}
			
 			
		}
	}

	function sendDivMsg($uidarr=array(),$content="",$userinfo=array(),$pagesize,$snedok,$snedno,$value){

		$notice = $this->MODEL('notice');
		$sendok = intval($sendok);
		$sendno = intval($sendno);
	   
		if($value=='0'){
			$i = 1;
			foreach($uidarr as $key => $v){
					
				if($i <= $pagesize){
					$msgData['mobile']	=	$v;
 					$msgData['content']	=	$content;
					$msgData['uid']		=	$key;
					$msgData['name']	=	$userinfo[$key];
					$msgData['cname']	=	"系统";
					$msgData['port']	=	'5';
					if($v){
						$sendid = $notice->sendSMS($msgData);
 					}
					if($sendid['status'] != -1){
						$sendok++;
					}else{
						$sendno++;
					}
				}
				$i++;
			}
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	1;

		}else{
			$page	=	$value;
			$start	=	$page*$pagesize;
			$end	=	($page+1)*$pagesize;

			$i=1;

			foreach($uidarr as $key=>$v){

				if($i > $start && $i <= $end){
					
					$msgData['moblie']	=	$v;
 					$msgData['content'] =	$content;
					$msgData['uid']		=	$key;
 					$msgData['name']	=	$userinfo[$key]['name'];
					$msgData['cname']	=	"系统";
					$msgData['port']	=	'5';	
					if($v){
						$sendid = $notice->sendSMS($msgData);
					}
					if($sendid['status'] != -1){
 						$sendok++;
					}else{
 						$sendno++;
					}
				}
				$i++;
			}

			$page	=	$page + 1;
			$result['sendok']	=	$sendok;
			$result['sendno']	=	$sendno;
			$result['page']		=	$page;
			 
		}
		return $result;
	}
	
	//Other
	function get_return($status,$value,$msg,$sendok,$sendno){
		$data['status']	=	$status;
		$data['value']	=	$value;
		$data['msg']	=	$msg;
		$data['sendok']	=	$sendok;	
		$data['sendno']	=	$sendno;
		echo json_encode($data);die;
	}
	
	function sendcom_action(){
		$emailM	=	$this->MODEL('email');
		
		$data	=	$emailM->setSendCom($_POST);
		
		echo json_encode($data);die;
	}
	function senduser_action(){
		$emailM	=	$this->MODEL('email');
		
		$data	=	$emailM->setSendUser($_POST);
		
		echo json_encode($data);die;
	}
}

?>