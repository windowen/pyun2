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
class index_controller extends user{
	//首页
	function index_action(){
		$atnM			=	$this -> MODEL('atn');
		$JobM			=	$this -> MODEL('job');
		$cacheM			=	$this -> MODEL('cache');
		$resumeM		=	$this -> MODEL('resume');
		$userinfoM		=	$this -> MODEL('userinfo');
		$lookresumeM	=	$this -> MODEL('lookresume');
		$this -> public_action();
		$this -> member_satic();
		
		$this -> yunset($cacheM -> GetCache(array('user','com','job')));
		
		//面试通知数
		$yqnum		=	$JobM -> getYqmsNum(array('uid'=>$this->uid));
		$this -> yunset("yqnum",$yqnum); 
		
		//未查看面试邀请 
		$msgnum		=	$JobM -> getYqmsNum(array('uid'=>$this->uid,'is_browse'=>1));
		$this -> yunset("msgnum", $msgnum);
		
		//谁看了我的简历
		$lookNum	=	$lookresumeM -> getLookNum(array('uid'=>$this->uid,'status'=>array('<>',1)));
		$this -> yunset("lookNum",$lookNum);
		//我的关注
		$where['uid']				=	$this->uid;
		$where['sc_usertype']		=	'2';
		$atncomnum		=	$atnM -> getAtnNum($where);
		$atnltnum		=	$atnM -> getAtnNum(array('uid'=>$this->uid,'sc_usertype'=>'3'));
		$atnnum			=	$atncomnum+$atnltnum;
		$this->yunset('atnnum',$atnnum);
		//判断今天是否已刷新简历
		$time		=	strtotime(date("Y-m-d 00:00:00"));
		$this -> yunset("time",$time);
		
		$where    =   array();
		
		$where['uid']				=	$this->uid;
		$where['sc_usertype']		=	'2';
		$where['groupby']           =   'sc_uid';
		
		$auids    =   $atnM -> getatnList($where,array('field'=>'uid,sc_uid','utype'=>'company'));
		$this -> yunset('ainfo',$auids);
		
		//判断我是否有简历
		$eData    =   array(
		    'field'   => '`lastupdate`,`jobstatus`,`id`,`name`'
		);
		
		$rlist			=	$resumeM -> getExpect(array('uid'=>$this->uid,'defaults'=>1),$eData);
		if(!$rlist){
		    
		    $rlist		=	$resumeM -> getExpect(array('uid'=>$this->uid,'orderby'=>'id,desc'),$eData);
		}
		$this -> yunset('rlist',$rlist);
		
		if($this -> config['resume_sx']==1){//登录自动简历刷新,在后台配置
			//$resumedef		=	$resumeM -> getInfo(array('uid'=>$this->uid),array('field'=>'`def_job`'));
		    if($rlist['id']){
		        
		        $resumeM -> upInfo(array('id'=>$rlist['id'],'uid'=>$this->uid),array('eData'=>array('lastupdate'=>time())));
		        
		        $resumeM -> upResumeInfo(array('uid'=>$this->uid),array('rData'=>array('lastupdate'=>time())));	
		    }
		}
		//当日首次进入会员中心 如未刷新进行提醒
		$this -> cookie -> SetCookie("exprefresh",'1',time() + 86400);
		
		//微信绑定判断
		$member		=	$userinfoM -> getInfo(array('uid'=>$this->uid),array('field'=>'`wxopenid`,`wxid`'));
		$this -> yunset("member",$member);
		if($member['wxid']=='' && $member['wxopenid']=='' && $this->config['wx_author']=='1'){
		    
			$this -> yunset("wxbindshow",1);
			$this -> cookie -> SetCookie("wxbd",'1',time() + 86400);
		}
		
		//问好语
		$hours		=	date('H');
        if($hours<12){
			$wenhou	=	'上午好~';
		}elseif($hours<24){
			$wenhou	=	'下午好~';
		}
		$this -> yunset('wenhou',$wenhou);
		
		$this -> user_tpl('index');
	}

	/* 会员中心首页，简历信息载入 */
	function resumeajax_action(){

    	$resumeM	=	$this -> MODEL('resume');
	
		if($_GET['rand']){
		
			$eData  =  array(
			    'field'      =>  'id,name,job_classid,cityid,hits,jobstatus,integrity,minsalary,maxsalary,doc,tmpid,r_status,topdate,lastupdate,status,state',
			    'needCache'  =>  1  
			);

			if($_GET['id']){
				
				$data		=	$resumeM -> getExpect(array('uid'=>$this->uid,'id'=>intval($_GET['id'])),$eData);
			}else{
				
				$data		=	$resumeM -> getExpect(array('uid'=>$this->uid,'defaults'=>1),$eData);
				
				if(!$data){
					$data	=	$resumeM -> getExpect(array('uid'=>$this->uid,'orderby'=>'id,desc'),$eData);
				}
			}
			$data['name']	=	mb_substr($data['name'],0,10,'utf8');
			
			if($data['job_classid']){
				$jobname	=	explode(',',$data['job_classname']);
				$data['jobname']=	$jobname[0];
			}
			$data['lastupdate']	=	date("Y-m-d H:i:s",$data['lastupdate']);
			
			$user_resume		=	$resumeM->getUserResumeInfo(array('eid'=>$data['id'],'uid'=>$this->uid));
			
			$data['skill']		=	$user_resume['skill'];
			$data['work']		=	$user_resume['work'];
			$data['project']	=	$user_resume['project'];
			$data['edu']		=	$user_resume['edu'];
			$data['training']	=	$user_resume['training'];
			$data['cert']		=	$user_resume['cert'];
			$data['other']		=	$user_resume['other'];
			
			$resume				=	$resumeM->getResumeInfo(array('uid'=>$this->uid),array('field'=>'`description`'));
			$data['description']		=	$resume['description'];
			if($data['topdate']>1){
				$data['topdatetime']	=	$data['topdate']-time();
				$data['topdate']		=	date("Y-m-d",$data['topdate']);
			}else{
				$data['topdate']		=	'未设置';
			}
			$eDatas['field']	=	'id,name,job_classid,cityid,hits,jobstatus,integrity,minsalary,maxsalary,doc,tmpid,r_status,topdate,lastupdate,status,state';
			$data['url']		=	Url('resume',array('c'=>'show','id'=>$data['id']));
			$rlist	=	$resumeM->getList(array('uid'=>$this->uid,'orderby'=>'defaults,desc'),$eDatas);
			$resumelist	=	"";
			foreach($rlist['list'] as $v){
				
				$resumelist	.=	"<li><a href=\"javascript:showresumelist('".$v['id']."');\">".$v['name']."</a></li>";
			}
			$html	=	'<span>'.$data['name'].'</span><div class="index_resume_my_n_list" id="resume_expect'.$data['id'].'" style="display:none;"><ul>'.$resumelist.'</ul></div>';
			$data['resumelist']	=	$html;
			$data['num']		=	count($rlist);
			$data['uid']		=	$this->uid;
			echo json_encode($data);
		}
	}
	function jobajax_action(){
		$resumeM  =  $this -> MODEL('resume');
		$eData    =  array(
		    'field'      =>  '`job_classid`,`city_classid`',
		    'needCache'  =>  1
		);
	    $resume			=	$resumeM -> getExpect(array('uid'=>$this->uid,'defaults'=>1),$eData);
	    if(!$resume){
	        $resume		=	$resumeM -> getExpect(array('uid'=>$this->uid,'orderby'=>'id,desc'),$eData);
	    }

		$nwhere['sdate']				=	$where['sdate']		=	array('<',time());
		$nwhere['r_status']				=	$where['r_status']	=	1;
		$nwhere['status']				=	$where['status']	=	0;
		$nwhere['state']				=	$where['state']		=	1;
	    if($resume['job_classid']!=""){
			$where['PHPYUNBTWSTART_A']	=	'';
			$where['job_post']			=	array('in',$resume['job_classid']);
			$where['job1_son']			=	array('in',$resume['job_classid'],'or');
			$where['PHPYUNBTWEND_A']	=	'';
	    }
	    if($resume['city_classid']!=""){
			$where['PHPYUNBTWSTART_B']	=	'';
			$where['provinceid']		=	array('in',$resume['city_classid']);
			$where['cityid']			=	array('in',$resume['city_classid'],'or');
			$where['three_cityid']		=	array('in',$resume['city_classid'],'or');
			$where['PHPYUNBTWEND_B']	=	'';
	    }
		$nwhere['orderby']				=	$where['orderby']	=	'id,desc';
		$nwhere['limit']				=	$where['limit']		=	12;
		$cdata['field']					=	'id,uid,name,com_name,cityid,edu,exp,minsalary,maxsalary';
		$cdata['isurl']					=	'yes';
		$jobM		=	$this -> MODEL('job');

	    $job		=	$jobM->getList($where,$cdata);
	    if(empty($job)){

	    	$job	=	$jobM->getList($nwhere,$cdata);
			$data['isnew']	=	1;
	    }
	    $list=array();
	    if ($job['list']){
	        foreach ($job['list'] as $k=>$v){
	            $list[$k]['joburl']		= $v['joburl'];
	            $list[$k]['comurl']		= $v['comurl'];
	            $list[$k]['name']		= mb_substr($v['name'], 0, 10 ,"UTF-8");
	            $list[$k]['com_name']	= mb_substr($v['com_name'], 0, 10 ,"UTF-8");
				$list[$k]['jobsalary']	= $v['job_salary'];
				$list[$k]['citytwo']	= $v['job_city_two'];
				$list[$k]['edu_n']		= $v['job_edu']?$v['job_edu']:'不限';
				$list[$k]['exp_n']		= $v['job_exp']?$v['job_exp']:'不限';
	        }
	    }
	    $data['list']=$list;
	    echo json_encode($data);die;
	}
}
?>