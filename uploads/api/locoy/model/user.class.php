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
class user_controller extends common{
	function add_action(){
		include(APP_PATH."data/api/locoy/locoy_config.php");
		
		if($locoyinfo['locoy_online']!=1){
			echo 4;die;
		}
		if($locoyinfo['locoy_key']!=trim($_GET['key'])){
			echo 5;die;
		}
        if(!$_POST['info_name']){
			echo 2;die;
		}
		$uid=$this->add_user_info($_POST,$locoyinfo);
		$this->add_resume($_POST,$locoyinfo,$uid);
	}

	function add_resume($p,$l,$uid){
			$v['name'] = trim($p['info_classid']);
			$v['lastupdate']=time();
			$v['uid']=$uid;
				$v['r_status']=1;
			
			include(PLUS_PATH."industry.cache.php");
			$v['hy']=$this->locoytostr($industry_name,$p['info_hy'],$l['locoy_rate']);
			if(!$v['hy']){
				$v['hy']=$l['locoy_resume_hy'];
			}
			$city_row=$this->get_city($p['info_city'],$l['locoy_rate']);
			if($city_row){
				$i=1;
				foreach($city_row as $vs){
					if($i==1)$v['provinceid']=$vs;
					if($i==2)$v['cityid']=$vs;
					if($i==3)$v['three_cityid']=$vs;
					$i++;
				}
			}else{
				$v['provinceid']=$l['locoy_resume_province'];
				$v['cityid']=$l['locoy_resume_city'];
				$v['three_cityid']=$l['locoy_resume_three'];
			}
			
      
			$v['city_classid']=$v['cityid'];
			$job_row=$this->get_job_class($p['info_classid'],$l['locoy_rate']);
			if($job_row){
				foreach($job_row as $vs){
					$job_arr[] = $vs;
				}
				$v['job_classid']=pylode(',',$job_arr);
			}
			if(!$v['job_classid']){
				$v['job_classid']=$l['locoy_resume_post'];
			}
			
			$salary=$p['info_salary'];
			$v['salary']=$this->locoytostr($this->get_user_type('salary'),$salary,$l['locoy_rate']);
			if(!$v['salary']){
				$v['salary']=$l['locoy_user_salary'];
			}
			$report=$p['info_report'];
			$v['report']=$this->locoytostr($this->get_user_type('report'),$report,$l['locoy_rate']);
			if(!$v['report']){
				$v['report']=$l['locoy_user_report'];
			}else{
				$v['report']=326;
			}
      
			$type=$p['info_type'];
            $v['type']=$this->locoytostr($this->get_user_type('type'),$type,$l['locoy_rate']);
			
			if($p['info_hits']){
				$v['hits']=trim($p['info_hits']);
			}else{
				$row=explode('-',$locoyinfo['locoy_resume_rand']);
				if(is_array($row)){
					$rand=rand(trim($row[0]),trim($row[1]));
				}else{
					$rand=!trim($row)?0:$row;
				}
				$v['hits']=$rand;
			}
			$v['source'] = 6;
			if($p['jobstatus']){
				$v['jobstatus']=$p['jobstatus'];
			}else{
				$v['jobstatus']=241;
			}
			
			$numresume=55;
			if($p['skill_name'] || $p['skill_skill'] || $p['skill_ing']){
				$numresume=$numresume+10;
			}
			if($p['work_name'] || $p['work_sdate']){
				$numresume=$numresume+7;
			}
			if($p['pro_name']|| $p['pro_sdate']){
				$numresume=$numresume+7;
			}
			if($p['edu_name'] || $p['edu_title']){
				$numresume=$numresume+7;
			}
			if($p['train_name'] || $p['train_title']){
				$numresume=$numresume+7;
			}
			if($p['cert_name'] || $p['cert_title']){
				$numresume=$numresume+7;
			}
			
			$v['integrity']=$numresume;
			$v['defaults']=1;
			$v['edu']=$this->locoytostr($this->get_user_type('edu'),$p['info_edu'],$l['locoy_rate']);
			if(!$v['edu']){
				$v['edu']=$l['locoy_user_edu'];
			}
			$v['exp']=$this->locoytostr($this->get_user_type('word'),$p['info_exp'],$l['locoy_rate']);
			if(!$v['exp']){
				$v['exp']=$l['locoy_user_exp'];
			}
			$v['uname']=trim($p['info_name']);
				if($p['info_sex']=="男" || $p['info_sex']==1){
				$v['sex']=1;
			}elseif($p['info_sex']=="女" || $p['info_sex']==2){
				$v['sex']=2;
			}
			if(!$v['sex']){
				$v['sex']=$l['locoy_user_sex'];
			}
		
			$v['r_status']=1;
            if($p['minsalary']){
    		    $v['minsalary']=$p['minsalary'];
    		}else{
    		    $v['minsalary']=$l['locoy_minsalary'];
    	    }
    		if($p['maxsalary']){
    		    $v['maxsalary']=$p['maxsalary'];
    		}else{
    			$v['maxsalary']=$l['locoy_maxsalary'];
    		}
			$nid=$this->obj->insert_into("resume_expect",$v);
     
			if($nid){ 
				$num=$this->obj->DB_select_once("member_statis","`uid`='".$uid."'");
				if($num['resume_num']==0){
					$this->obj->DB_update_all("resume","`def_job`='".$nid."'","`uid`='".$uid."'");
				}
				$this->obj->DB_insert_once("user_resume","`uid`='".$uid."',`eid`='$nid'");
                $this->obj->DB_insert_once("resume_cityclass","`uid`='".$uid."',`eid`='$nid',`cityid`='".$v['cityid']."',`provinceid`='".$v['provinceid']."',`three_cityid`='".$v['three_cityid']."'");
				$this->obj->DB_update_all("member_statis","`resume_num`=`resume_num`+1","uid='".$uid."'");
				$state_content = "发布了 <a href=\"".$this->config['sy_weburl']."/index.php?m=resume&id=$nid\" target=\"_blank\">新简历</a>。";
				$this->obj->DB_insert_once("friend_state","`uid`='".$uid."',`content`='".$state_content."',`ctime`='".time()."'");
				
				if($p['skill_name'] || $p['skill_skill'] || $p['skill_ing']){
					$skill_v="`uid`='".$uid."',";
					$skill_v.="`eid`='".$nid."',";
					$skill_v.="`name`='".$p['skill_name']."',";
					$skill_v.="`skill`='".$p['skill_skill']."',";
					$skill_v.="`ing`='".$p['skill_ing']."',";
					$skill_v.="`longtime`='".$p['skill_longtime']."'";
					$this->obj->DB_insert_once("resume_skill",$skill_v);
				}
				if($p['work_name'] || $p['work_sdate']){
					$work_v="`uid`='".$uid."',";
					$work_v.="`eid`='".$nid."',";
					$work_v.="`name`='".$p['work_name']."',";

					$p['work_sdate'] = str_replace(array('年','月','日'),'-',$p['work_sdate']);
					$p['work_edate'] = str_replace(array('年','月','日'),'-',$p['work_edate']);
					if(substr($p['work_sdate'], -1)=='-')
					{
						$p['work_sdate'].='01';
					}
					if(substr($p['work_edate'], -1)=='-')
					{
						$p['work_edate'].='01';
					}
					$work_v.="`sdate`='".strtotime($p['work_sdate'])."',";
					$work_v.="`edate`='".strtotime($p['work_edate'])."',";
					$work_v.="`department`='".$p['work_department']."',";
					$work_v.="`content`='".$p['work_content']."',";
					$work_v.="`title`='".$p['work_title']."'";
					$this->obj->DB_insert_once("resume_work",$work_v);
				}
				if($p['work_name1'] || $p['work_sdate1']){    
					$work_v1="`uid`='".$uid."',";
					$work_v1.="`eid`='".$nid."',";
					$work_v1.="`name`='".$p['work_name1']."',";

					$p['work_sdate1'] = str_replace(array('年','月','日'),'-',$p['work_sdate1']);
					$p['work_edate1'] = str_replace(array('年','月','日'),'-',$p['work_edate1']);
					if(substr($p['work_sdate1'], -1)=='-')
					{
						$p['work_sdate1'].='01';
					}
					if(substr($p['work_edate1'], -1)=='-')
					{
						$p['work_edate1'].='01';
					}
					
					$work_v1.="`sdate`='".strtotime($p['work_sdate1'])."',";
					$work_v1.="`edate`='".strtotime($p['work_edate1'])."',";
					$work_v1.="`department`='".$p['work_department1']."',";
					$work_v1.="`content`='".$p['work_content1']."',";
					$work_v1.="`title`='".$p['work_title1']."'";
					$this->obj->DB_insert_once("resume_work",$work_v1);
				}
				if($p['work_name2'] || $p['work_sdate2']){    
					$work_v2="`uid`='".$uid."',";
					$work_v2.="`eid`='".$nid."',";
					$work_v2.="`name`='".$p['work_name2']."',";

					$p['work_sdate2'] = str_replace(array('年','月','日'),'-',$p['work_sdate2']);
					$p['work_edate2'] = str_replace(array('年','月','日'),'-',$p['work_edate2']);
					if(substr($p['work_sdate2'], -1)=='-')
					{
						$p['work_sdate2'].='01';
					}
					if(substr($p['work_edate2'], -1)=='-')
					{
						$p['work_edate2'].='01';
					}
					
					$work_v2.="`sdate`='".strtotime($p['work_sdate2'])."',";
					$work_v2.="`edate`='".strtotime($p['work_edate2'])."',";
					$work_v2.="`department`='".$p['work_department2']."',";
					$work_v2.="`content`='".$p['work_content2']."',";
					$work_v2.="`title`='".$p['work_title2']."'";
					$this->obj->DB_insert_once("resume_work",$work_v2);
				}
				if($p['work_name3'] || $p['work_sdate3']){    
					$work_v3="`uid`='".$uid."',";
					$work_v3.="`eid`='".$nid."',";
					$work_v3.="`name`='".$p['work_name3']."',";

					$p['work_sdate3'] = str_replace(array('年','月','日'),'-',$p['work_sdate3']);
					$p['work_edate3'] = str_replace(array('年','月','日'),'-',$p['work_edate3']);
					if(substr($p['work_sdate3'], -1)=='-')
					{
						$p['work_sdate3'].='01';
					}
					if(substr($p['work_edate3'], -1)=='-')
					{
						$p['work_edate3'].='01';
					}
					
					$work_v3.="`sdate`='".strtotime($p['work_sdate3'])."',";
					$work_v3.="`edate`='".strtotime($p['work_edate3'])."',";
					$work_v3.="`department`='".$p['work_department3']."',";
					$work_v3.="`content`='".$p['work_content3']."',";
					$work_v3.="`title`='".$p['work_title3']."'";
					$this->obj->DB_insert_once("resume_work",$work_v3);
				}
				if($p['work_name4'] || $p['work_sdate4']){    
					$work_v4="`uid`='".$uid."',";
					$work_v4.="`eid`='".$nid."',";
					$work_v4.="`name`='".$p['work_name4']."',";

					$p['work_sdate4'] = str_replace(array('年','月','日'),'-',$p['work_sdate4']);
					$p['work_edate4'] = str_replace(array('年','月','日'),'-',$p['work_edate4']);
					if(substr($p['work_sdate4'], -1)=='-')
					{
						$p['work_sdate4'].='01';
					}
					if(substr($p['work_edate4'], -1)=='-')
					{
						$p['work_edate4'].='01';
					}
					
					$work_v4.="`sdate`='".strtotime($p['work_sdate4'])."',";
					$work_v4.="`edate`='".strtotime($p['work_edate4'])."',";
					$work_v4.="`department`='".$p['work_department4']."',";
					$work_v4.="`content`='".$p['work_content4']."',";
					$work_v4.="`title`='".$p['work_title4']."'";
					$this->obj->DB_insert_once("resume_work",$work_v4);
				}
				
				if($p['pro_name']|| $p['pro_sdate']){
					$pro_v="`uid`='".$uid."',";
					$pro_v.="`eid`='".$nid."',";
					$pro_v.="`name`='".$p['pro_name']."',";
					$p['pro_sdate'] = str_replace(array('年','月','日'),'-',$p['pro_sdate']);
					$p['pro_edate'] = str_replace(array('年','月','日'),'-',$p['pro_edate']);
					if(substr($p['pro_sdate'], -1)=='-')
					{
						$p['pro_sdate'].='01';
					}
					if(substr($p['pro_edate'], -1)=='-')
					{
						$p['pro_edate'].='01';
					}
					$pro_v.="`sdate`='".strtotime($p['pro_sdate'])."',";
					$pro_v.="`edate`='".strtotime($p['pro_edate'])."',";
					$pro_v.="`sys`='".$p['pro_sys']."',";
					$pro_v.="`content`='".$p['pro_content']."',";
					$pro_v.="`title`='".$p['pro_title']."'";
					$this->obj->DB_insert_once("resume_project",$pro_v);
				}
				if($p['edu_name'] || $p['edu_title']){
					$edu_v="`uid`='".$uid."',";
					$edu_v.="`eid`='".$nid."',";
					$edu_v.="`name`='".$p['edu_name']."',";
					$p['edu_sdate'] = str_replace(array('年','月','日'),'-',$p['edu_sdate']);
					$p['edu_edate'] = str_replace(array('年','月','日'),'-',$p['edu_edate']);
					if(substr($p['edu_sdate'], -1)=='-')
					{
						$p['edu_sdate'].='01';
					}
					if(substr($p['edu_edate'], -1)=='-')
					{
						$p['edu_edate'].='01';
					}
					$H['edu']=$this->locoytostr($this->get_user_type('edu'),$p['edu_title'],$l['locoy_rate']);
					$edu_v.="`sdate`='".strtotime($p['edu_sdate'])."',";
					$edu_v.="`edate`='".strtotime($p['edu_edate'])."',";
					$edu_v.="`specialty`='".$p['edu_specialty']."',";
					$edu_v.="`content`='".$p['edu_content']."',";
					$edu_v.="`education`='".$H['edu']."'";
					$this->obj->DB_insert_once("resume_edu",$edu_v);
				}
				if($p['edu_name1'] || $p['edu_title1']){       
					$edu_v1="`uid`='".$uid."',";
					$edu_v1.="`eid`='".$nid."',";
					$edu_v1.="`name`='".$p['edu_name1']."',";
					$p['edu_sdate1'] = str_replace(array('年','月','日'),'-',$p['edu_sdate1']);
					$p['edu_edate1'] = str_replace(array('年','月','日'),'-',$p['edu_edate1']);
					if(substr($p['edu_sdate1'], -1)=='-')
					{
						$p['edu_sdate1'].='01';
					}
					if(substr($p['edu_edate1'], -1)=='-')
					{
						$p['edu_edate1'].='01';
					}
					$edu_v1.="`sdate`='".strtotime($p['edu_sdate1'])."',";
					$edu_v1.="`edate`='".strtotime($p['edu_edate1'])."',";
					$edu_v1.="`specialty`='".$p['edu_specialty1']."',";
					$edu_v1.="`content`='".$p['edu_content1']."',";
					$edu_v1.="`title`='".$p['edu_title1']."'";
					$this->obj->DB_insert_once("resume_edu",$edu_v1);
				}
				if($p['edu_name2'] || $p['edu_title2']){       
					$edu_v2="`uid`='".$uid."',";
					$edu_v2.="`eid`='".$nid."',";
					$edu_v2.="`name`='".$p['edu_name2']."',";
					$p['edu_sdate2'] = str_replace(array('年','月','日'),'-',$p['edu_sdate2']);
					$p['edu_edate2'] = str_replace(array('年','月','日'),'-',$p['edu_edate2']);
					if(substr($p['edu_sdate2'], -1)=='-')
					{
						$p['edu_sdate2'].='01';
					}
					if(substr($p['edu_edate2'], -1)=='-')
					{
						$p['edu_edate2'].='01';
					}
					$edu_v2.="`sdate`='".strtotime($p['edu_sdate2'])."',";
					$edu_v2.="`edate`='".strtotime($p['edu_edate2'])."',";
					$edu_v2.="`specialty`='".$p['edu_specialty2']."',";
					$edu_v2.="`content`='".$p['edu_content2']."',";
					$edu_v2.="`title`='".$p['edu_title2']."'";
					$this->obj->DB_insert_once("resume_edu",$edu_v2);
				}
				if($p['cert_name'] || $p['cert_title']){
					$cert_v="`uid`='".$uid."',";
					$cert_v.="`eid`='".$nid."',";
					$cert_v.="`name`='".$p['cert_name']."',";
					$p['cert_sdate'] = str_replace(array('年','月','日'),'-',$p['edu_edate']);
					if(substr($p['cert_sdate'], -1)=='-')
					{
						$p['cert_sdate'].='01';
					}
					$cert_v.="`sdate`='".strtotime($p['cert_sdate'])."',";
					$cert_v.="`content`='".$p['cert_content']."',";
					$cert_v.="`title`='".$p['cert_title']."'";
					$this->obj->DB_insert_once("resume_cert",$cert_v);
				}
				if($p['other_content'] || $p['other_title']){
					$other_v="`uid`='".$uid."',";
					$other_v.="`eid`='".$nid."',";
					$other_v.="`content`='".$p['other_content']."',";
					$other_v.="`name`='".$p['other_name']."'";
					$this->obj->DB_insert_once("resume_other",$other_v);
				}
				if($p['train_name'] || $p['train_title']){
					$train_v="`uid`='".$uid."',";
					$train_v.="`eid`='".$nid."',";
					$train_v.="`name`='".$p['train_name']."',";
					$p['train_sdate'] = str_replace(array('年','月','日'),'-',$p['train_sdate']);
					$p['train_edate'] = str_replace(array('年','月','日'),'-',$p['train_edate']);
					if(substr($p['train_sdate'], -1)=='-')
					{
						$p['train_sdate'].='01';
					}
					if(substr($p['train_edate'], -1)=='-')
					{
						$p['train_edate'].='01';
					}
					$train_v.="`sdate`='".strtotime($p['train_sdate'])."',";
					$train_v.="`edate`='".strtotime($p['train_edate'])."',";
					$train_v.="`content`='".$p['train_content']."',";
					$train_v.="`title`='".$p['train_title']."'";
					$this->obj->DB_insert_once("resume_training",$train_v);
				}
				if($p['train_name1'] || $p['train_title1']){    
					$train_v1="`uid`='".$uid."',";
					$train_v1.="`eid`='".$nid."',";
					$train_v1.="`name`='".$p['train_name1']."',";
					$p['train_sdate1'] = str_replace(array('年','月','日'),'-',$p['train_sdate1']);
					$p['train_edate1'] = str_replace(array('年','月','日'),'-',$p['train_edate1']);
					if(substr($p['train_sdate1'], -1)=='-')
					{
						$p['train_sdate1'].='01';
					}
					if(substr($p['train_edate1'], -1)=='-')
					{
						$p['train_edat1'].='01';
					}
					$train_v1.="`sdate`='".strtotime($p['train_sdate1'])."',";
					$train_v1.="`edate`='".strtotime($p['train_edate1'])."',";
					$train_v1.="`content`='".$p['train_content1']."',";
					$train_v1.="`title`='".$p['train_title1']."'";
					$this->obj->DB_insert_once("resume_training",$train_v1);
				}
				echo 1;die;
			}
	}
	
	function add_user_info($p,$l){
		$row=$this->obj->DB_select_once("resume","`name`='".$p['info_name']."'");
		if(is_array($row)){
			return $row['uid'];
		}else{
			$userid=$this->add_user($p,$l);
			$where['uid']=$userid;
			$data['name']=trim($p['info_name']);
			$data['address']=trim($p['info_address']);
			$data['height']=trim($p['info_height']);
			$data['weight']=trim($p['info_weight']);
			$data['birthday']=$p['info_birthday'];
			$data['telphone']=$p['info_telphone'];
			$data['homepage']=$p['info_homepage'];
			$info_description=strip_tags(html_entity_decode($p['info_description']),"<p> <br>");
			$data['description']=$info_description;
			$data['living']=$p['info_living'];
			$data['domicile']=$p['info_domicile'];
			$data['email']=$p['info_email'];
            $data['qq']=$p['info_qq'];
			$data['resume_photo']=$p['info_photo'];
			 	$data['photo']=$p['info_photo'];                
				
			
			if($p['info_sex']=="男" || $p['info_sex']==1){
				$data['sex']=1;
			}elseif($p['info_sex']=="女" || $p['info_sex']==2){
				$data['sex']=2;
			}
			if(!$data['sex']){
				$data['sex']=$l['locoy_user_sex'];
			}
			
			$data['marriage']=$this->locoytostr($this->get_user_type('marriage'),$p['info_marriage'],$l['locoy_rate']);
			if(!$data['marriage']){
				$data['marriage']=$l['locoy_user_marriage'];
			}
			$data['edu']=$this->locoytostr($this->get_user_type('edu'),$p['info_edu'],$l['locoy_rate']);
			if(!$data['edu']){
				$data['edu']=$l['locoy_user_edu'];
			}
			$data['exp']=$this->locoytostr($this->get_user_type('word'),$p['info_exp'],$l['locoy_rate']);
			if(!$data['exp']){
				$data['exp']=$l['locoy_user_exp'];
			}
			if(!$p['nationality']){
				$data['nationality']=$l['locoy_user_nationality'];
			}else{
				$data['nationality']=$p['nationality'];
			}
			$nid=$this->obj->update_once("resume",$data,$where);
			return $userid;
		}
	}
	
	function add_user($p,$l){
		$salt = substr(uniqid(rand()),-6);
		$pass = md5(md5($l['locoy_pwd']).$salt);
		$ip = fun_ip_get();
		$time = time();
		$username=$this->get_username($l);
		if($l['locoy_user_status']==1){
			$satus=1;
		}
		$userid=$this->obj->DB_insert_once("member","`username`='".$username."',`password`='$pass',`moblie`='".$p['info_telphone']."',`email`='".$p['info_email']."',`usertype`='1',`status`='$satus',`salt`='$salt',`reg_date`='$time',`reg_ip`='$ip',`source`='6'");
		$value="`uid`='$userid'";
		$this->obj->DB_insert_once("resume",$value);
		$this->obj->DB_insert_once("member_statis",$value);
		return $userid;
	}
	
	function get_username($l){
		$row = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7","8","9");
		$va="";
		for($i=0;$i<$l['locoy_length'];$i++){
			$rand=rand(0,61);
			$va.=$row[$rand];
		}
		$data=$l['locoy_name'].$va;
		return $data;
	}
	
	function get_city($name,$locoy_rate){
		include(PLUS_PATH."city.cache.php");
		$name = str_replace(array('/','-',','),'',trim($name));
		$name=str_replace(array("省","市","县","区"),"/",$name);
		$arr=explode("/",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($city_name,$v,$locoy_rate);
			}
		}
		$city_type[0]=$city_index;
		$val=$this->get_all_city($city_type,$data);
		if(count($val)==1){
			$val[]=$this->get_once_city($city_type,$city_name,$val[0],$locoy_rate);
		}
		return $val;
	}
	
	function get_job_class($name,$locoy_rate){
		include(PLUS_PATH."job.cache.php");
		$arr=explode(",",$name);
		if(is_array($arr)){
			foreach($arr as $v){
				$data[]=$this->locoytostr($job_name,$v,$locoy_rate);
			}
		}
		return $data;
	}
	
	function get_all_city($city_type,$data,$locoy_rate,$k=""){
		if(is_array($data)){
			foreach($data as $v){
				foreach($city_type as $key=>$value){
					$a=$k?$k:$v;
					if(in_array($a,$value)){
						if($key){
							$val=$this->get_all_city($city_type,$data,$locoy_rate,$key);
						}
						$val[$key]=$a;
					}
				}
			}
		}
		return $val;
	}
	
	function get_once_city($t,$n,$id,$locoy_rate){
		$row=$n[$id];
		if(is_array($t[$id])){
			foreach($t[$id] as $k=>$v){
				$array[$v]=$n[$v];
			}
		}
		$r=$this->locoytostr($array,$row,$locoy_rate);
		return $r;
	}
	
	function get_user_type($cat){
		include(PLUS_PATH."user.cache.php");
		foreach($userdata["user_".$cat] as $v){
			$data[$v]=$userclass_name[$v];
		}
		return $data;
	}
	
	function locoytostr($arr,$str,$locoy_rate="60"){
			$str_array=$this->tostring($str);
			foreach($arr as $key =>$list){
				$h=0;
				foreach($str_array as $val){
					if(substr_count($list,$val))$h++;
				}
				$categoryname_array=$this->tostring($list);
				$j=round($h/count($categoryname_array)*100,2);
				$rows[$j]=$key;
			}
			krsort($rows);
			foreach($rows as $k =>$v){			 
				if ($k>=$locoy_rate){
					return $v;
				}else{
					return false;
				}					
			}
	}
	
	function tostring($string){ 
		$length=strlen($string); 
		$retstr=''; 
		for($i=0;$i<$length;$i++) { 
			$retstr[]=ord($string[$i])>127?$string[$i].$string[++$i]:$string[$i]; 
		} 
		return $retstr; 
	}
}
?>