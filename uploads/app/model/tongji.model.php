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

class tongji_model extends model{
  
    public  function tjtotal($table,$whereData = array(), $data = array()){
    
        $data['field']  =	empty($data['field']) ? '*' : $data['field'];
       
        $List           =   $this -> select_all($table, $whereData, $data['field']); 
        
        return $List;  
    
    }

 	 public  function tjtonce($table, $whereData = array(),$data = array()){
    
		$data['field']  =	empty($data['field']) ? '*' : $data['field'];
		
		$List           =   $this -> select_once($table, $whereData, $data['field']); 
		
		return $List;  
 
	}
	

	function getTj($Tablename,$Get,$Field,$Where=array(),$CountField='count(*) as count'){
		
		$TimeDate = $this->TimeDate($Field,$CountField);
		
		$Stats =$this->select_all($Tablename,array_merge($Where,$TimeDate['FieldWhere']),$TimeDate['FieldFormat']);
		
		return $this->TjInfo($Stats,$TimeDate);
	}


	 function TjInfo($Stats,$TimeDate){
	
		if(is_array($Stats))
		{
			$AllNum = 0;$Date=array();
			foreach($Stats as $key=>$value)
			{
				$AllNum +=$value['count'];
				
				$Date[$value['tjtime']] = $value['count'];
			}
			foreach($TimeDate['DateList'] as $key=>$value){
				if($Date[$value['onday']]<1){
					$Date[$value['onday']] = 0;
				}
				$List[$value['onday']] = array('tjtime'=>$value['tjtime'],'count'=>$Date[$value['onday']],'date'=>$value['date']);
			}
		}
		ksort($List);
		return array('allnum'=>$AllNum,'list'=>$List,'timedate'=>$TimeDate);
	}


	//时间线获取
	function TimeDate($field, $countfield = 'count(*) as count')
    {
        if (! $_GET['days'] && ! $_GET['date']) {

            $_GET['days']   =   1;
        }
        
        $DateList   =   $DateWhere  =   $FieldWhere =   array();
        
        if ($_GET['days'] == '-1') { // 昨天 以24小时计算

            $days   =   24;
            $sdate  =   strtotime('-1 days');
            $edate  =   strtotime(date('Y-m-d'));

            $FieldFormat = "FROM_UNIXTIME(`$field`,'%k') as tjtime,$countfield";

            for ($i = 0; $i <= $days; $i ++) {

                $DateList[$i]['tjtime'] =   $i;
                $DateList[$i]['date']   =   $i.":00";
                $DateList[$i]['onday']  =   $i;
            }
        } elseif ($_GET['days'] == '1') { // 今天 以24小时计算
            
            $days   =   24;
            $sdate  =   strtotime(date('Y-m-d'));
            $edate  =   time();
            
            $FieldFormat = "FROM_UNIXTIME(`$field`,'%k') as tjtime,$countfield";
            
            for ($i = 0; $i <= $days; $i ++) {

                $DateList[$i]['tjtime'] =   $i;
                $DateList[$i]['date']   =   $i.":00";
                $DateList[$i]['onday']  =   $i;
            }
            
        } elseif ((int) $_GET['days'] > 1) { // 多日期 以具体日期数计算
        
            $days   =   (int) $_GET['days'];
            $sdate  =   strtotime(date('Y-m-d', (time() - $days * 86400)));
            $edate  =   time();
            
            $FieldFormat = "FROM_UNIXTIME(`$field`,'%Y%m%d') as tjtime,$countfield";

            for ($i = 0; $i <= $days; $i ++) {
                
                $onday  =   date("Ymd", strtotime(' -' . $i . 'day'));
                $tjtime =   date('m-d', strtotime(' -' . $i . 'day'));
                $date   =   date('Y-m-d', strtotime(' -' . $i . 'day'));

                $DateList[$i]['tjtime'] =   $tjtime;
                $DateList[$i]['date']   =   $date;
                $DateList[$i]['onday']  =   $onday;
            }
            
        } elseif ($_GET['date']) {

            $dates  =   @explode('~', $_GET['date']);
            
            $edate  =   strtotime($dates[1]."23:59:59");
            $sdate  =   strtotime($dates[0]."00:00:00");
            
            $days   =   ceil(abs($edate - $sdate) / 86400);

            $FieldFormat    =   "FROM_UNIXTIME(`$field`,'%Y%m%d') as tjtime,$countfield";
            
            for ($i = 0; $i <= $days; $i ++) {
                
                $t      =   $edate - $i * 86400;
                $onday  =   date("Ymd", $t);
                $tjtime =   date('m-d', $t);
                $date   =   date('Y-m-d', $t);
                
                $DateList[$t]['tjtime'] =   $tjtime;
                $DateList[$t]['date']   =   $date;
                $DateList[$t]['onday']  =   $onday;
            }
        }
 
        $DateWhere[$field][]    =   array('>=', $sdate);
        $DateWhere[$field][]    =   array('<=', $edate);
        
        $FieldWhere['groupby']  =   'tjtime';
        $FieldWhere['orderby']  =   'tjtime,desc';

        return array(
            'days'          =>  $days,
            'FieldWhere'    =>  array_merge($DateWhere, $FieldWhere),
            'FieldFormat'   =>  $FieldFormat,
            'DateList'      =>  $DateList,
            'DateWhere'     =>  $DateWhere
        );
    }
	

	//统计 前十排行
	function TopTen($Tablename,$Where,$Field,$Type,$Limit='10',$CountField="count(*) AS count"){
		
		$topwhere['groupby']	=	$Field;
		
		$topwhere['orderby']	=	array('count,desc');
		
		$topwhere['limit']		=	$Limit;

		$List = $this->select_all($Tablename,array_merge($Where,$topwhere),$CountField.",".$Field);
		
		foreach($List as $key=>$value){
			
			$Fields[] = $value[$Field];
			$Count[$value[$Field]] = $value['count'];
		}
		
		if($Type=='job'){//统计职位信息
			
			$jobwhere['id']=array('in',pylode(',',$Fields));
			
			$FieldList = $this->select_all("company_job",$jobwhere,"`id`,`uid`,`name`");
			
			foreach($FieldList as $key=>$value){
			
				$value['count'] = $Count[$value['id']];
				$NewList[$value['id']] = $value;
			}

			foreach($List as $key=>$value){
			
				$TopList[$key] = $NewList[$value[$Field]];
			}
		}elseif($Type=='company'){
			
			$comwhere['uid']=array('in',pylode(',',$Fields));
			
			$FieldList = $this->select_all("company",$comwhere,"`uid`,`name`");

			
			foreach($FieldList as $key=>$value){
			
				$value['count'] = $Count[$value['uid']];
				$NewList[$value['uid']] = $value;
			}

			foreach($List as $key=>$value){
				if(!empty($NewList[$value[$Field]])){
					$TopList[$key] = $NewList[$value[$Field]];
				}	
				
			}
		}elseif($Type=='expect'){
			
			$ewhere['id']=array('in',pylode(',',$Fields));
			
			$FieldList = $this->select_all("resume_expect",$ewhere,"`id`,`uid`,`uname`,`job_classid`,`provinceid`,`cityid`,`three_cityid`");
			
			foreach($FieldList as $key=>$value){
			
				$value['count'] = $Count[$value['id']];
				$NewList[$value['id']] = $value;
			}

			foreach($List as $key=>$value){
				if(!empty($NewList[$value[$Field]])){
					$TopList[$key] = $NewList[$value[$Field]];
				}	
				
			}
		}elseif($Type=='resume'){
			
			$rwhere['uid']=array('in',pylode(',',$Fields));
			
			$FieldList = $this->select_all("resume",$rwhere,"`uid`,`name`,`sex`,`exp`,`edu`");
			
			$ExpectList = $this->select_all("resume_expect",$rwhere,"`id`,`uid`,`defaults`");
			
			foreach($FieldList as $key=>$value){
			
				$value['count'] = $Count[$value['uid']];
				$NewList[$value['uid']] = $value;
			}
			foreach($List as $key=>$value){
				if(!empty($NewList[$value[$Field]])){
					$TopList[$key] = $NewList[$value[$Field]];
				}
				foreach($ExpectList as $val){
					if($val['uid']==$value['uid']&&$val['defaults']==1){
						$TopList[$key]['eid']=$val['id'];
					}
				}
			}
		}elseif($Type=='order'){
			
			$mwhere['uid']=array('in',pylode(',',$Fields));
			
			$FieldList = $this->select_all("member",$mwhere,"`uid`,`username`,`usertype`");
			
			foreach($FieldList as $key=>$value){
			
				$value['count'] = $Count[$value['uid']];
				$NewList[$value['uid']] = $value;
			}

			foreach($List as $key=>$value){
				if(!empty($NewList[$value[$Field]])){
					$TopList[$key] = $NewList[$value[$Field]];
				}	
				
			}
		}if($Type=='ad'){//统计广告点击量
			
			$adclickwhere['aid']=array('in',pylode(',',$Fields));
			
			$FieldList = $this->select_all("adclick",$adclickwhere,"`aid`");
			
			$adwhere['id']=array('in',pylode(',',$Fields));
			
			$adList = $this->select_all("ad",$adwhere);
			foreach($adList as $value){
				$adLists[$value['id']] = $value['ad_name'];
			}
			foreach($FieldList as $key=>$value){
			
				$value['count'] = $Count[$value['aid']];
				$value['name'] = $adLists[$value['aid']];
				$NewList[$value['aid']] = $value;
			}

			foreach($List as $key=>$value){
			
				$TopList[$key] = $NewList[$value[$Field]];
			}
		}
		return $TopList;
	}
	//完整数据分析，获取行业、薪资区间、区域等数据进行统一分析
	function DataTj($Type,$Where,$Tablename,$Field){
		
		include PLUS_PATH.'city.cache.php';
		include PLUS_PATH.'industry.cache.php';
		include PLUS_PATH.'job.cache.php';
		include PLUS_PATH.'com.cache.php';
		include PLUS_PATH.'user.cache.php';
		
        include_once ('cache.model.php');
            
        $cacheM     =   new cache_model($this->db, $this->def);
        
        $cache      =   $cacheM -> GetCache('com');	
		
		
		$List = $this->select_all($Tablename,$Where,$Field);
		
		if(is_array($List)){
			foreach($List as $key=>$value){
				
				$Fields[] = $value[$Field];
			}
			if($Type=='resume_expect'){

				//查询职位信息
				$ewhere['id']		=	array('in',pylode(',',$Fields));
				
				$citywhere['eid']	=	array('in',pylode(',',$Fields));
				
				$ResumeList		= $this->select_all("resume_expect",$ewhere,"`id`,`sex`,`edu`,`exp`,`job_classid`,`provinceid`,`cityid`,`three_cityid`,`maxsalary`,`minsalary`");
				
				$ResumeCityList = $this->select_all("resume_cityclass",$citywhere,"`eid`,`provinceid`,`cityid`,`three_cityid`");
				$jobClassEnd = array();
				foreach($job_index as $val){
					
					if(is_array($job_type[$val])){
						$jobClassAll[$val][] = $val;
						foreach($job_type[$val] as $v){
							
							$jobClassAll[$val] = array_merge($jobClassAll[$val], $job_type[$val]);
							
							if(is_array($job_type[$v])){
										
								$jobClassAll[$val] = array_merge($jobClassAll[$val], $job_type[$v]);
								
							}
						}
					}
				}
				foreach($jobClassAll as $key=>$value){
					if(is_array($value)){
						foreach($value as $v){
							$jobClassEnd[$v] = $key;
						}
					}
				}
			
				foreach($ResumeList as $key=>$value){
					
					$Count['sex'][$value['sex']]['count']++;					
					$Count['sex'][$value['sex']]['name'] =$cache['com_sex'][$value['sex']];
										
					$Count['edu'][$value['edu']]['count']++;
					$Count['edu'][$value['edu']]['name'] = $userclass_name[$value['edu']];

					$Count['exp'][$value['exp']]['count']++;
					$Count['exp'][$value['exp']]['name'] = $userclass_name[$value['exp']];
					if($value['job_classid']){
						$jobclassid = @explode(',',$value['job_classid']);
						foreach($jobclassid as $k=>$v){
							$classid = $jobClassEnd[$v];
							if($classid){
								$Count['job1'][$classid]['count']++;
								$Count['job1'][$classid]['name'] = $job_name[$classid];
							}
						}
					}
					
					foreach($ResumeCityList as $val){
						if($val['eid']==$value['id']){
							$Count['provinceid'][$val['provinceid']]['count']++;
							$Count['provinceid'][$val['provinceid']]['name'] = $city_name[$val['provinceid']];
							
							$Count['cityid'][$val['cityid']]['count']++;
							$Count['cityid'][$val['cityid']]['name'] = $city_name[$val['cityid']];

							$Count['three_cityid'][$val['three_cityid']]['count']++;
							$Count['three_cityid'][$val['three_cityid']]['name'] = $city_name[$val['three_cityid']];
						}
					}
					
					if($value['maxsalary']>0){
						if($value['maxsalary']<=2000){
							$Count['salary']['2000以下']['count']++;
							$Count['salary']['2000以下']['name'] = '2000以下';
						}
						if($value['maxsalary']>2000&&$value['maxsalary']<=4000){
							$Count['salary']['2000-4000']['count']++;
							$Count['salary']['2000-4000']['name'] = '2000-4000';
						}
						if($value['maxsalary']>4000&&$value['maxsalary']<=6000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['maxsalary']>6000&&$value['maxsalary']<=8000){
							$Count['salary']['6000-8000']['count']++;
							$Count['salary']['6000-8000']['name'] = '6000-8000';
						}
						if($value['maxsalary']>8000&&$value['maxsalary']<=10000){
							$Count['salary']['8000-10000']['count']++;
							$Count['salary']['8000-10000']['name'] = '8000-10000';
						}
						if($value['maxsalary']>10000){
							$Count['salary']['10000以上']['count']++;
							$Count['salary']['10000以上']['name'] = '10000以上';
						}
					}else{
						if($value['minsalary']<=2000){
							$Count['salary']['2000以下']['count']++;
							$Count['salary']['2000以下']['name'] = '2000以下';
						}
						if($value['minsalary']>2000&&$value['minsalary']<=4000){
							$Count['salary']['2000-4000']['count']++;
							$Count['salary']['2000-4000']['name'] = '2000-4000';
						}
						if($value['minsalary']>4000&&$value['minsalary']<=6000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['minsalary']>6000&&$value['minsalary']<=8000){
							$Count['salary']['6000-8000']['count']++;
							$Count['salary']['6000-8000']['name'] = '6000-8000';
						}
						if($value['minsalary']>8000&&$value['minsalary']<=10000){
							$Count['salary']['8000-10000']['count']++;
							$Count['salary']['8000-10000']['name'] = '8000-10000';
						}
						if($value['minsalary']>10000){
							$Count['salary']['10000以上']['count']++;
							$Count['salary']['10000以上']['name'] = '10000以上';
						}
					
					}
				}
				
			}elseif($Type=='job'){
				
				//查询职位信息
				$jobwhere['id']=array('in',pylode(',',$Fields));
				
				$JobList = $this->select_all("company_job",$jobwhere,"`edu`,`exp`,`job1`,`job1_son`,`job_post`,`provinceid`,`cityid`,`three_cityid`,`minsalary`,`maxsalary`");
				
				foreach($JobList as $key=>$value){
					
					$Count['edu'][$value['edu']]['count']++;
					$Count['edu'][$value['edu']]['name'] = $comclass_name[$value['edu']];

					$Count['exp'][$value['exp']]['count']++;
					$Count['exp'][$value['exp']]['name'] = $comclass_name[$value['exp']];

					$Count['job1'][$value['job1']]['count']++;
					$Count['job1'][$value['job1']]['name'] = $job_name[$value['job1']];

					$Count['job1_son'][$value['job1_son']]['count']++;
					$Count['job1_son'][$value['job1_son']]['name'] = $job_name[$value['job1_son']];

					$Count['job_post'][$value['job_post']]['count']++;
					$Count['job_post'][$value['job_post']]['name'] = $job_name[$value['job_post']];

					$Count['provinceid'][$value['provinceid']]['count']++;
					$Count['provinceid'][$value['provinceid']]['name'] = $city_name[$value['provinceid']];
					
					$Count['cityid'][$value['cityid']]['count']++;
					$Count['cityid'][$value['cityid']]['name'] = $city_name[$value['cityid']];

					$Count['three_cityid'][$value['three_cityid']]['count']++;
					$Count['three_cityid'][$value['three_cityid']]['name'] = $city_name[$value['three_cityid']];

					if($value['maxsalary']>0){
						if($value['maxsalary']<=2000){
							$Count['salary']['2000以下']['count']++;
							$Count['salary']['2000以下']['name'] = '2000以下';
						}
						if($value['maxsalary']>2000&&$value['maxsalary']<=4000){
							$Count['salary']['2000-4000']['count']++;
							$Count['salary']['2000-4000']['name'] = '2000-4000';
						}
						if($value['maxsalary']>4000&&$value['maxsalary']<=6000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['maxsalary']>6000&&$value['maxsalary']<=8000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['maxsalary']>8000&&$value['maxsalary']<=10000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['maxsalary']>10000){
							$Count['salary']['10000以上']['count']++;
							$Count['salary']['10000以上']['name'] = '10000以上';
						}
					}else{
						if($value['minsalary']<=2000){
							$Count['salary']['2000以下']['count']++;
							$Count['salary']['2000以下']['name'] = '2000以下';
						}
						if($value['minsalary']>2000&&$value['minsalary']<=4000){
							$Count['salary']['2000-4000']['count']++;
							$Count['salary']['2000-4000']['name'] = '2000-4000';
						}
						if($value['minsalary']>4000&&$value['minsalary']<=6000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['minsalary']>6000&&$value['minsalary']<=8000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['minsalary']>8000&&$value['minsalary']<=10000){
							$Count['salary']['4000-6000']['count']++;
							$Count['salary']['4000-6000']['name'] = '4000-6000';
						}
						if($value['minsalary']>10000){
							$Count['salary']['10000以上']['count']++;
							$Count['salary']['10000以上']['name'] = '10000以上';
						}
					
					}
					
				}
			}elseif($Type=='reg'){
				
				

				//查询注册信息
				$mwhere['uid']		=	array('in',pylode(',',$Fields));
				
				$mwhere['groupby']	=	'source';
				
				$RegList = $this->select_all("member",$mwhere,"count(*) as count,source");
				
				foreach($RegList as $key=>$value){
					
					$Count['source'][$value['source']]['count'] = $value['count'];
					switch($value['source']){
						case '1':
							$Count['source'][$value['source']]['name'] = 'PC网页';
						break;
						case '2':
							$Count['source'][$value['source']]['name'] = '手机WAP';
						break;
						
						case '4':
							$Count['source'][$value['source']]['name'] = '微信';
						break;
						
						case '6':
							$Count['source'][$value['source']]['name'] = '采集';
						break;
						case '7':
							$Count['source'][$value['source']]['name'] = 'EXCEL导入';
						break;
						case '8':
							$Count['source'][$value['source']]['name'] = 'QQ登录';
						break;
						case '9':
							$Count['source'][$value['source']]['name'] = '微信扫一扫';
						break;
						case '10':
							$Count['source'][$value['source']]['name'] = '微博登录';
						break;
						case '11':
							$Count['source'][$value['source']]['name'] = 'PC快速投递';
						break;
						case '12':
							$Count['source'][$value['source']]['name'] = 'WAP快速投递';
						break;
						case '13':
							$Count['source'][$value['source']]['name'] = '微信小程序';
						break;
						case '14':
							$Count['source'][$value['source']]['name'] = '微信小程序快速投递';
						break;
						


					}
				}
			}elseif($Type=='order'){
				
				//查询职位信息
				$owhere['id']=array('in',pylode(',',$Fields));
				
				$OrderList = $this->select_all("company_order",$owhere,"`id`,`order_type`,`type`");
				
				$TypeName = array(0 => '其他',1 => '会员充值（购买会员）',2 => $this->config['integral_pricename'].'充值',3 => '银行转帐',4 => '金额充值',5 => '购买增值包',10 => '职位置顶',11 => '职位紧急',12 => '职位推荐',13 => '自动刷新',14 => '简历置顶',16 => '刷新职位',17 => '刷新兼职',19 => '下载简历',20 => '发布职位',21 => '发布兼职',23 => '面试邀请',24 => '兼职推荐',25 => '店铺招聘',26 => '购买广告位');

				$OrderTypeName = array('adminpay'=>'管理员充值','admincut'=>'管理员扣款','alipay'=>'支付宝','wapalipay'=>'手机支付宝','bank'=>'银行汇款','tenpay'=>'财付通','0'=>'其他');

				foreach($OrderList as $key=>$value){	
					if(!$value['order_type']){
						$value['order_type'] = 0;
					}
					$Count['ordertype'][$value['order_type']]['count']++;
					$Count['ordertype'][$value['order_type']]['name'] = $OrderTypeName[$value['order_type']];

					if(!$value['type']){
						$value['type'] = 0;
					}
					$Count['type'][$value['type']]['count']++;
					$Count['type'][$value['type']]['name'] = $TypeName[$value['type']];
				}
			}elseif($Type=='company'){
				$rating = $this->select_all('company_rating',array(),'`id`,`name`');
				foreach($rating as $value){
					$ratingList[$value['id']] = $value['name'];
				}
				
				//查询企业资料
				$comwhere['uid']=array('in',pylode(',',$Fields));
				
				$CompanyList = $this->select_all("company",$comwhere,"`uid`,`hy`,`provinceid`,`jobtime`");

				$CompanyRating = $this->select_all("company_statis",$comwhere,"`uid`,`rating`");
				foreach($CompanyRating as $value){

					$Count['rating'][$value['rating']]['count']++;
					$Count['rating'][$value['rating']]['name'] = $ratingList[$value['rating']];

				}
				
				foreach($CompanyList as $key=>$value){	
					if(!$value['hy']){
						$Count['hy'][0]['count']++;
						$Count['hy'][0]['name'] = '其他';
						$Count['is'][0]['count']++;//完善企业资料数量
					}else{
						$Count['is'][1]['count']++;//完善企业资料数量
						$Count['hy'][$value['hy']]['count']++;
						$Count['hy'][$value['hy']]['name'] = $industry_name[$value['hy']];
					}
					
					
					$Count['is'][0]['name']='信息不全';

					$Count['is'][1]['name']='信息完善';

				}
			
			}elseif($Type=='ad'){
				
				
				//查询企业资料
				$comwhere['uid']=array('in',pylode(',',$Fields));
				
				$CompanyList = $this->select_all("company",$comwhere,"`uid`,`hy`,`provinceid`,`jobtime`");


				$CompanyRating = $this->select_all("company_statis",$comwhere,"`uid`,`rating`");
				foreach($CompanyRating as $value){

					$Count['rating'][$value['rating']]['count']++;
					$Count['rating'][$value['rating']]['name'] = $ratingList[$value['rating']];

				}
				
				foreach($CompanyList as $key=>$value){	
					if(!$value['hy']){
						$Count['hy'][0]['count']++;
						$Count['hy'][0]['name'] = '其他';
						$Count['is'][0]['count']++;//完善企业资料数量
					}else{
						$Count['is'][1]['count']++;//完善企业资料数量
						$Count['hy'][$value['hy']]['count']++;
						$Count['hy'][$value['hy']]['name'] = $industry_name[$value['hy']];
					}
					
					
					$Count['is'][0]['name']='信息不全';

					$Count['is'][1]['name']='信息完善';

				}
			
			}
		}
		return $Count;
	}
}
?>