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
class excel_controller extends adminCommon{
	function index_action(){
		$this->yuntpl(array('admin/admin_excel'));
	}
	function resume_action(){
		set_time_limit(0);
		include LIB_PATH."/reader.php";
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('utf-8');

		if($_FILES['excel']['name']!=""){
			$time = time();
			$excel = $time.".xls";
			move_uploaded_file($_FILES[excel][tmp_name],DATA_PATH."upload/excel/".$excel);
		}else{
			$this->ACT_layer_msg("无文件上传！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
		$data->read(DATA_PATH.'upload/excel/'.$excel);
		if($data->sheets[0]['numRows']<1){
			$this->ACT_layer_msg("数据读取失败，请检查excel格式！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
		$user = array(); 
		$cells=count($data->sheets[0]['cells']);
		$cellsnum=count($data->sheets[0]['cells'][1]); 

		for ($i = 2; $i <= $cells; $i++){
			$user[$i]['username'] 	= trim($data->sheets[0]['cells'][$i][1]);
			$user[$i]['name'] 		= trim($data->sheets[0]['cells'][$i][2]); 
			if($user[$i]['name']){ 
				for($j=3;$j<=$cellsnum;$j++){
					$value=trim($data->sheets[0]['cells'][$i][$j]);
					switch($data->sheets[0]['cells'][1][$j]){ 
						case	'性别':
							$user[$i]['sex_n']			=	$value;break;
						case	'年龄':
							$user[$i]['age_n']			=	$value;break;
						case	'婚姻':
							$user[$i]['marriage_n']		=	$value;break;
						case	'籍贯':
							$user[$i]['jiguan_n']		=	$value;break;
						case	'联系电话':
							$user[$i]['telphone']		=	$value;break;
						case	'固定电话':
							$user[$i]['homephone']		=	$value;break;
						case	'邮箱':
							$user[$i]['email']			=	$value;break;
						case	'学历':
							$user[$i]['edu_n']			=	$value;break;
						case	'经验':
							$user[$i]['exp_n']			=	$value;break;
						case	'现居住地':
							$user[$i]['xcity']			=	$value;break;
						case	'详细地址':
							$user[$i]['address']		=	$value;break;
						case	'期望工作地址':
							$user[$i]['jobaddress']		=	$value;break;
						case	'期望工作岗位':
							$user[$i]['job']			=	$value;break;
						case	'期望薪资':
							$user[$i]['salary_n']		=	$value;break;
						case	'教育经历':
							$user[$i]['eduexcel']		=	$value;break;
						case	'工作经历':
							$user[$i]['work_n']			=	$value;break;
						case	'专业擅长':
							$user[$i]['other_n']		=	$value;break;
						case	'个人介绍':
							$user[$i]['description']	=	$value;break;
					}  
				} 
			}			
		}
		if(is_array($user)){
			$resumeM=$this->MODEL('resume');
			$numres = 0; $numuser=0;$numwork=0;$numedu=0;$los=0;
			include PLUS_PATH."/job.cache.php";
			include PLUS_PATH."/user.cache.php";
			include PLUS_PATH."/city.cache.php";
			foreach($user as $key=>$value){
				if($value[name]!=""){
					$salt = substr(uniqid(rand()), -6);
					$pass =array("a","b","c","d","e","f","g","h","i","g","k","l","m","n","o","p","q","r","s","t","u","v","w","x","w","z","1","2","3","4","5","6","7","8","9","0");

					$password='';
					for($i=0;$i<6;$i++){
						$k = rand(0,36);
						$password.=$pass[$k];
					}
					$npass = passCheck($password,$salt);
					$time = time();
					
					$value['username'] = 'py'.date('mdHis').rand(100,999);
					
					if($value['email']!=''){
						$emailuser[] = array('email'=>$value['email'],'username'=>$value[username],'password'=>$password,'name'=>$value['name']);
					}
					$mvalue = array(
						'username'	=>	$value[username],
						'password'	=>	$npass,
						'email'		=>	$value[email],
						'usertype'	=>	'1',
						'address'	=>	$value[address],
						'status'	=>	'1',
						'salt'		=>	$salt,
						'moblie'	=>	$value[telphone],
						'reg_date'	=>	$time,
						'passtext'	=>	$password,
						'source'	=>	'7'
					);
					$uvalue=array(
						'email'		=>	$value[email],
						'telphone'	=>	$value[telphone],
						'r_status'	=>	$this->config['user_state']
					);
					$uid 		=	$this -> MODEL('userinfo') -> addInfo(array('mdata'=>$mvalue,'udata'=>$uvalue));
					if($uid['msg']){
                        $this->ACT_layer_msg($uid['msg'],8,$_SERVER['HTTP_REFERER'],2,1);
                    }
					$jobname	=	$value['job'];
					if(!is_array($uid) && $uid>0){
						$numuser++;
						$sqlval['uid']	=	$uid;
						$sqlval['name']	=	$jobname;

						$job_row		=	$this->get_job_class($jobname);

						if($job_row){
						    $j		=	1;
						    $job1	=	$job1_son	=	$job_post	=	0;
							foreach($job_row as $vs){
							    if($j==1){
							        $job1		=	$vs;
							    }
							    if($j==2){
							        $job1_son	=	$vs;
							    }
							    if($j==3){
							        $job_post	=	$vs;
							    }
							    $j++;
							}
							if($job_post>0){
							    $job_classid = $job_post;
							}elseif ($job1_son>0){
							    $job_classid = $job1_son;
							}elseif ($job1>0){
							    $job_classid = $job1;
							}
							if ($job_classid){
							    $sqlval['job_classid']=$job_classid;
							}
						}



						$pcity	=	$value['jiguan_n'];
						$xcity	=	$value['xcity'];
						$provinceid =	$cityid	=	$three_cityid	=	0;
						$city_row	=	$this->get_city($value['jobaddress']);

						$i=1;
						foreach($city_row as $v){
							if($i==1){
								$provinceid		=	$v;
							}
							if($i==2){
								$cityid			=	$v;
							}
							if($i==3){
								$three_cityid	=	$v;
							}
							$i++;
						}
						$sqlval['provinceid']	=	$provinceid;
						$sqlval['cityid']		=	$cityid;
						$sqlval['three_cityid']	=	$three_cityid;
						$sqlval['lastupdate']	=	time();
						if($three_cityid>0){
						    $city_classid	=	$three_cityid;
						}elseif ($cityid>0){
						    $city_classid	=	$cityid;
						}elseif ($provinceid>0){
						    $city_classid	=	$provinceid;
						}
						if ($city_classid){
						    $sqlval['city_classid']	=	$city_classid;
						}
						$salaryN = @explode('-',trim($value['salary_n'])); 
						$sqlval['minsalary']	=	$salaryN[0];
						$sqlval['maxsalary']	=	$salaryN[1];

						$sqlval['type']			=	$userdata['user_type'][0];
						$sqlval['report']		=	$userdata['user_report'][0];
						$sqlval['source']		=	'7';
						$sqlval['jobstatus']	=	$userdata['user_jobstatus'][0];
						$sqlval['r_status']		=	'1';
						$sqlval['state']		=	$this->config['user_state']==1?$this->config['resume_status']:0;
						$resume   = $resumeM -> addInfo(array('uid'=>$uid,'eData'=>$sqlval));
						$resumeid = $resume['id'];
						$numres++;
						if($resumeid){
							$ressql['def_job']	=	$resumeid;
							$ressql['name']		=	$value['name'];
							$resume_expect_data['uname']= $value['name'];
							if($value['sex_n']=="女"){
								$ressql['sex']					=	'2';
								$resume_expect_data['sex']		=	'2';
							}else{
								$ressql['sex']					=	'1';
								$resume_expect_data['sex']		=	'1';
							}
							if((int)$value['age_n']>0){
								$birthday = date("Y")-$value['age_n']."-".date("m-d");
								$ressql['birthday']				=	$birthday;
								$resume_expect_data['birthday']	=	$birthday;
							}
							
							if($value['marriage_n']!=""){
								$marriage='';
								foreach($userdata['user_marriage'] as $k=>$v){
									if(strpos($userclass_name[$v],$value['marriage_n'])!==false){
										$marriage				=	$v;
									}
								}
								$ressql['marriage']				=	$marriage;
							}
							
							$ressql['telphone']					=	$value['telphone'];
							$ressql['telhome']					=	$value['homephone'];
							$ressql['living']					=	$xcity;
							$ressql['domicile']					=	$pcity;
							$ressql['email']					=	$value['email'];
						

							if($value['edu_n']!=""){
								$edu='';
								foreach($userdata['user_edu'] as $k=>$v){
									if(strpos($userclass_name[$v],$value['edu_n'])!==false){
										$edu = $v;
									}
								}
								$ressql['edu']					=	$edu;
								$resume_expect_data['edu']		=	$edu;
							}
							
							
							if($value['exp_n']!=""){
								$exp='';
								foreach($userdata['user_word'] as $k=>$v){
									if(strpos($userclass_name[$v],$value['exp_n'])!==false){
										$exp = $v;
									}
								}
								$ressql['exp']					=	$exp;
								$resume_expect_data['exp']		=	$exp;
							}	
							
							
							$ressql['address']					=	$value[address];
							$ressql['description']				=	$value[description];
							$resume_expect_data['defaults']		=	'1';
							$resume_expect_data['ctime']		=	time();

							$resumeM -> upResumeInfo(array('uid'=>$uid),array('rData'=>$ressql));
							
							$resumeM -> upInfo(array('uid'=>$uid),array('eData'=>$resume_expect_data));

							$whour		=	0;
							if($resumeid  && $value['work_n']!=""){
							    
							    $work_arr = @explode("【#】",$value['work_n']);
							    
							    
							    if(is_array($work_arr)){
							        foreach($work_arr as $k=>$v){
							            $sonv = @explode("||",$v);
							            for ($i = 0; $i < count($sonv); $i ++) {
							                
							                $workson_arra = @explode("-", $sonv[$i]);
							                $sdate = $this->uparr($workson_arra[0]);
							                $workson = @explode("***", $workson_arra[1]);
							                $edate = $this->uparr($workson[0]);
							                $name = $workson[1];
							                $content = $workson[2];
							                $title = $workson[3];
							                if ($edate) {
							                    $workTime = ceil(($edate - $sdate) / (30 * 86400));
							                } else {
							                    $workTime = ceil((time() - $sdate) / (30 * 86400));
							                }
							                
							                $whour += $workTime;
							                
							                if ($sdate != "" || $edate != "" || $content != "") {
							                    
							                    $resumeM -> addResumeWork(array('uid'=>$uid,'eid'=>$resumeid,'sdate'=>$sdate,'edate'=>$edate,'name'=>$name,'title'=>$title,'content'=>$content));
							                    $numwork ++;
							                }
							            }
							            
							        }
							        
							        $avghour = ceil($whour/$numwork);
							        $resume_time['whour']	 =	$whour;
							        $resume_time['avghour']  =	$avghour;
							    }
							    $resumeM -> upInfo(array('uid'=>$uid),array('eData'=>$resume_time));
							}
							if ($resumeid && $value['eduexcel'] != "") {
							    $edu_arr = @explode("【#】", $value['eduexcel']);
							    
							    if (is_array($edu_arr)) {
							        foreach ($edu_arr as $k => $v) {
							            $sonv = @explode("||", $v);
							            for ($i = 0; $i < count($sonv); $i ++) {
							                $eduson_arra = @explode("-", $sonv[$i]);
							                
							                $sdate = $this->uparr($eduson_arra[0]);
							                $eduson = @explode("***", $eduson_arra[1]);
							                $edate = $this->uparr($eduson[0]);
							                $name = $eduson[1];
							                $specialty = $eduson[2];
							                if ($eduson[3]) {
							                    foreach ($userclass_name as $uk => $uv) {
							                        if ($eduson[3] == $uv) {
							                            $education = $uk;
							                        }
							                    }
							                } else {
							                    $education = '';
							                }
							                if ($eduson[1] != "" || $eduson[2] != "") {
							                    $resumeM -> addResumeEdu(array('uid'=>$uid,'eid'=>$resumeid,'sdate'=>$sdate,'edate'=>$edate,'name'=>$name,'specialty'=>$specialty,'education'=>$education));
							                    $numedu ++;
							                }
							            }
							        }
							    }
							}
							if($resumeid  && $value['other_n']!=""){
								$other_arr = @explode("【#】",$value['other_n']);
								if(is_array($other_arr)){
									foreach($other_arr as $k=>$v){
										$sonv = @explode("|",$v);
										if($sonv[0]!="" || $sonv[1]!=""){
											$resumeM -> addResumeOther(array('uid'=>$uid,'eid'=>$resumeid,'name'=>$sonv[0],'content'=>$sonv[1]));
										}
									}
								}
							}

						}
						$where_data   =   array(
						   
						    'uid' =>  $uid,
						    'eid' =>  $resumeid
						);
						
						$user_data    =   array(
						  
						    'work'    =>  $numwork,
						    'edu'     =>  $numedu
						);
						
						$resumeM -> upUserResume($user_data,$where_data);
					}

				}else{

				}
			}

			$msg = "本次新增个人会员：".$numuser."人，新增简历：".$numres."份;新增工作经历:".$numwork."份;新增教育经历:".$numedu."份.";
			$this->ACT_layer_msg($msg,9,$_SERVER['HTTP_REFERER'],2,1);


		}else{
			$this->ACT_layer_msg("没有找到合适的数据，请查看格式是否规范！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
	}

	function comexcel_action()
	{
		include LIB_PATH."/reader.php";
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('utf-8');

		if($_FILES[excel][name]!="")
		{
			$time = time();
			$excel = $time.".xls";
			move_uploaded_file($_FILES[excel][tmp_name],DATA_PATH."upload/excel/".$excel);
		}

		$data->read(DATA_PATH."upload/excel/".$excel);
		$user = array();
		if($data->sheets[0]['numRows']<1){
			$this->ACT_layer_msg("数据读取失败，请检查excel格式！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
		$cells=count($data->sheets[0]['cells']);
		$cellsnum=count($data->sheets[0]['cells'][1]); 
		for ($i = 2; $i <= $cells; $i++){
			$user[$i]['comname'] 	= trim($data->sheets[0]['cells'][$i][1]); 
			//if($user[$i]['comname']){ 
				for($j=2;$j<=$cellsnum;$j++){
					$value=$data->sheets[0]['cells'][$i][$j];
					switch($data->sheets[0]['cells'][1][$j]){ 
						case	'联系人':
							$user[$i]['linkman']		=	$value;break;   
						case	'联系人职位':
							$user[$i]['linkjob']		=	$value;break;   
						case	'联系电话':
							$user[$i]['linktel']		=	$value;break;   
						case	'籍贯':
							$user[$i]['jiguan_n']		=	$value;break;   
						case	'联系电话':
							$user[$i]['telphone']		=	$value;break; 
						case	'固定电话':
							$user[$i]['linkphone']		=	$value;break; 
						case	'联系QQ':
							$user[$i]['linkqq']			=	$value;break; 
						case	'联系地址':
							$user[$i]['address']		=	$value;break; 
						case	'联系邮箱':
							$user[$i]['email']			=	$value;break; 
						
						case	'企业行业':
							$user[$i]['hy']				=	$value;break; 
						case	'企业规模':
							$user[$i]['mun']			=	$value;break; 
						case	'企业简介':
							$user[$i]['content']		=	$value;break; 
						case	'招聘职位':
							$user[$i]['name']			=	$value;break; 
						
						case	'招聘岗位':
							$user[$i]['jobclass']		=	$value;break; 
						case	'招聘人数':
							$user[$i]['num']			=	$value;break; 
						case	'岗位要求':
							$user[$i]['description']	=	$value;break; 
						
						case	'薪资待遇':
							$user[$i]['salary']			=	$value;break; 
						case	'工作经验':
							$user[$i]['exp']			=	$value;break; 
						case	'学历要求':
							$user[$i]['edu']			=	$value;break; 
						case	'工作地点':
							$user[$i]['city']			=	$value;break; 
					}  
				}
			//}			
		}  
		if(is_array($user)){
			$numjob=0;$numuser=0;$los=0;
			
			include PLUS_PATH."/job.cache.php";
			include PLUS_PATH."/industry.cache.php";
			include PLUS_PATH."/city.cache.php";
			include PLUS_PATH."/com.cache.php";
			foreach($user as $key=>$value){
				$salt	=	substr(uniqid(rand()), -6);
				$pass	=	array("a","b","c","d","e","f","g","h","i","g","k","l","m","n","o","p","q","r","s","t","u","v","w","x","w","z","1","2","3","4","5","6","7","8","9","0");
				$password	=	'';
				$len	=	rand(8,12);
				for($i=0;$i<$len;$i++){
					$k = rand(0,36);
					$password.=$pass[$k];
				}
				$npass	=	passCheck($password,$salt);
				$time	=	time();
				if($value[comname]!=""){
					$comname	=	$value[comname];

					$mvalue		=	array(
						'username'	=>	$value[comname],
						'password'	=>	$npass,
						'email'		=>	$value[email],
						'usertype'	=>	'2',
						'address'	=>	$value[address],
						'status'	=>	'1',
						'salt'		=>	$salt,
						'moblie'	=>	$value[telphone],
						'reg_date'	=>	$time,
						'passtext'	=>	$password,
						'source'	=>	'7'
					);
					$uid	=	$this -> MODEL('userinfo') -> addInfo(array("mdata"=>$mvalue));

					if ($uid && is_numeric($uid)) {
						$numuser++;
						$comval['name']		=	$value[comname];
						$comval['r_status']	=	'1';
						$comval['linkman']	=	$value[linkman];
						$comval['linkjob']	=	$value[linkjob];
						$comval['linktel']	=	$value[linktel];
						$comval['linkphone']=	$value[linkphone];
						$comval['linkqq']	=	$value[linkqq];
						$comval['address']	=	$value[address];
						$comval['linkmail']	=	$value[email];
						$comval['content']	=	$value[content];
						if(is_array($industry_name) && $value[hy]!=""){
							foreach($industry_name as $k=>$v){
								if(strpos($v,$value['hy'])!==false){
									$hy = $k;
								}
							}
							$comval['hy']	=	$hy;
						}

						if($value[mun]){
							$mun = str_replace("人","",$value[mun]);
							$mun = @explode("-",$mun);

							if($mun[1]<20){
								$munval	=	"27";
							}elseif($mun[1]<99){
								$munval	=	"28";
							}
							elseif($mun[1]<499){
								$munval =	"29";
							}elseif($mun[1]<999){
								$munval =	"30";
							}elseif($mun[1]<9999){
								$munval =	"31";
							}else{
								$munval =	"32";
							}

							$comval['mun']	=	$munval;
							$mun			=	$munval;
						}
					}

					$this -> MODEL('company') -> upInfo($uid,'',$comval);
				}

				if ($uid && is_numeric($uid)) {
					$stime = time();
					$etime = $stime+3600*24*30;
					if($value['jobclass']!=""){
						$job1	=	$job1_son	=	$job_post	=	0;
						$jobarr =	explode('*',$value['jobclass']);
						$jobval['uid']			=	$uid;
						$jobval['hy']			=	$hy;
						$jobval['description']	=	$value[description];
						$jobval['name']			=	$value[name];
						$jobval['state']		=	1;
						$jobval['sdate']		=	$stime;
						$jobval['edate']		=	$etime;
						$jobval['lastupdate']	=	$stime;
						$job_post="";
						if($jobarr[0]!=""){
							foreach($job_index as $k=>$v){
								if((strpos($job_name[$v],$jobarr[0])!==false || strpos($jobarr[0],$job_name[$v])!==false)){
									$job1 = $v;
									break;
								}
							}
							if($job1>0 && $jobarr[1]!=""){
								if(is_array($job_type[$job1])){
									foreach($job_type[$job1] as $k=>$v){
										if((strpos($job_name[$v],$jobarr[1])!==false || strpos($jobarr[1],$job_name[$v])!==false)){
											$job1_son = $v;
											break;
										}
									}
									if($job1_son>0 && $jobarr[2]!=""){
										if(is_array($job_type[$job1_son])){
											foreach($job_type[$job1_son] as $k=>$v){
												if((strpos($job_name[$v],$jobarr[2])!==false || strpos($jobarr[2],$job_name[$v])!==false)){
													$job_post = $v;
													break;
												}
											}
										}
									}
								}
							}
						}
						if(is_array($job_name)){
							foreach($job_name as $k=>$v){
								if(strpos($v,$value['jobclass'])){
									$job_post = $k;
									foreach($job_type as $kk=>$vv){
										if($k == $vv){
											$job1_son = $kk;
											foreach($job_index as $kkk=>$vvv){
												if($kk == $vvv){
													$job1 = $kk;
												}
											}
										}
									}
								}
							}
						}
						if($job_post!=""){
							$jobval['job1']		=	$job1;
							$jobval['job1_son']	=	$job1_son;
							$jobval['job_post']	=	$job_post;
						}
						if($value[num]=="若干"){
							$num	=	"40";
						}elseif((int)$value[num]<2){

							$num	=	"41";

						}elseif((int)$value[num]<10){

							$num	=	"42";
						}elseif((int)$value[num]<50){

							$num	=	"43";
						}elseif((int)$value[num]<100){

							$num	=	"44";
						}elseif((int)$value[num]<999){

							$num	=	"45";
						}else{
							$num	=	"";
						}
						if($num!=""){
							$jobval['number']	=	$num;
						}
						if($value[sex]=="女"){
							$jobval['sex']		=	'2';
						}elseif($value[sex]=="男"){
							$jobval['sex']		=	'1';
						}else{
							$jobval['sex']		=	'3';
						}

						if($value['exp']!=""){
							foreach($comdata['job_exp'] as $k=>$v){
								if(strpos($comclass_name[$v],$value['exp'])!==false){
									$exp = $v;
								}
							}
						}
						$jobval['exp']			=	$exp;
						if($value['edu']!=""){
							foreach($comdata['job_edu'] as $k=>$v){
								if(strpos($comclass_name[$v],$value['edu'])!==false){
									$edu = $v;
								}
							}
						}
						$jobval['edu']			=	$edu;
						if($value['city']){
							$provinceid = $cityid = $three_cityid=0;
							$city_row=$this->get_city($value['city']);

							$i=1;
							foreach($city_row as $v){
								if($i==1){
									$provinceid		=	$v;
								}
								if($i==2){
									$cityid			=	$v;
								}
								if($i==3){
									$three_cityid	=	$v;
								}
								$i++;
							}
							$jobval['provinceid']	=	$provinceid;
							$jobval['cityid']		=	$cityid;
							$jobval['three_cityid']	=	$three_cityid;
						}

						$salaryN = explode('-',$value['salary']); 
						$jobval['minsalary']		=	$salaryN[0];
						$jobval['maxsalary']		=	$salaryN[1];
						$jobval['com_name']			=	$comname;
						$jobval['mun']				=	$mun;
						$jobval['source']			=	'7';
						$jobval['r_status']			=	'1';
						$numjob++;

						$this -> MODEL('job') -> addInfo($jobval);
					}
				}
			}
			$msg	=	"本次新增企业会员：".$numuser."人，新增职位：".$numjob."个";
			$this->ACT_layer_msg($msg,9,$_SERVER['HTTP_REFERER'],2,1);

		}else{
			$this->ACT_layer_msg("未读取到合适的数据，请检查格式是否规范！",8,$_SERVER['HTTP_REFERER'],2,1);
		}
	}

	function uparr($arr){
		$arr	=	str_replace("年","-",$arr);
		$arr	=	str_replace("月","-",$arr);
		$arr	=	str_replace("日","-",$arr);
		$arr	=	str_replace(".","-",$arr);
		$arr	=	str_replace("/","-",$arr);

		$narr	=	@explode("-",$arr);

		if($narr[2]==""){
			$narr[2]	=	"01";
		}
		if($narr[1]==""){
			$narr[1]	=	"01";
		}
		if($narr[0]!=""){
			$arr		=	$narr[0]."-".$narr[1]."-".$narr[2];
		}
		$arr	=	strtotime($arr);
		return $arr;
	}

	function locoytostr($arr,$str,$unstr='',$locoy_rate="50"){
		foreach($arr as $key=>$value){
			if($key!=$unstr){
				similar_text($str,$value,$locoy_rate);
				$rows[$locoy_rate]	=	$key;
				$aaa[$locoy_rate]	=	$value;
			}
		}
		krsort($rows);
		foreach($rows as $k =>$v){
			if ($k>=$locoy_rate){
				return array('id'=>$v,'percent'=>$k);
			}else{
				return false;
			}
		}
	}

	function tostring($string){
		$length	=	strlen($string);
		$retstr	=	'';
		for($i=0;$i<$length;$i++) {
			$retstr[]=ord($string[$i])>127?$string[$i].$string[++$i]:$string[$i];
		}
		return $retstr;
	}

	function get_com_type($cat){
		include(PLUS_PATH."com.cache.php");
		foreach($comdata["job_".$cat] as $v){
			$data[$v]	=	$comclass_name[$v];
		}
		return $data;
	}

	function get_all_city($city_type,$data,$k=""){
		if(is_array($data)){
			foreach($data as $v){
				foreach($city_type as $key=>$value){
					$a=$k?$k:$v;
					if(in_array($a,$value)){
						if($key){
							$val=$this->get_all_city($city_type,$data,$key);
						}
						$val[$key]=$a;
					}
				}
			}
		}
		return $val;
	}

	function get_once_city($t,$n,$id){
		$row	=	$n[$id];
		if(is_array($t[$id])){
			foreach($t[$id] as $k=>$v){
				$array[$v]	=	$n[$v];
			}
		}
		$locoystr	=	$this->locoytostr($array,$row);
		$r 			=	array('id'=>$locoystr['id'],'percent'=>$locoystr['percent'],'name'=>$row);
		return $r;
	}
	function get_city($name){
	    include(PLUS_PATH."city.cache.php");
	    $name			=	str_replace(array("省","市"),"/",$name);
	    $city_name_old	=	$city_name;
	    $arr=explode("/",$name);
	    if(is_array($arr)){
	        foreach($arr as $v){
	            $locoystr=	$this->locoytostr($city_name,$v,$locoystr['id']);
	            if($locoystr['id']){
	                foreach($city_type[$locoystr['id']] as $key=>$value){
	                    $city_name_new[$value]	=	$city_name_old[$value];
	                }
	                $city_name	=	$city_name_new;
	            }
	            $data[]	=	$locoystr['id'];
	        }
	    }
	    
	    return $data;
	}
	function get_job_class($name){
		include(PLUS_PATH."job.cache.php");
		$job_name_old	=	$job_name;
		$arr			=	explode(",",$name);
		if(is_array($arr)){
			foreach($arr as $v){
			    $locoystr	=	$this->locoytostr($job_name,$v,$locoystr['id']);
				if($locoystr['id']){
				    foreach($job_type[$locoystr['id']] as $key=>$value){
				        $job_name_new[$value]	=	$job_name_old[$value];
				    }
				    $job_name	=	$job_name_new;
				}
				$data[]	=	$locoystr['id'];
			}
		}
		return $data;
	}
	function get_user_type($cat){
		include(PLUS_PATH."user.cache.php");
		foreach($userdata["user_".$cat] as $v){
			$data[$v]	=	$userclass_name[$v];
		}
		return $data;
	}
}
?>