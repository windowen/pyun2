<?php
class Smarty_Internal_Compile_Joblist extends Smarty_Internal_CompileBase{
    public $required_attributes = array('item');
    public $optional_attributes = array('name', 'key', 'limit', 'comlen', 'namelen', 'urgent', 'ispage', 'rec','report', 'hy', 'job1', 'job1_son', 'job_post', 'jobids', 'pr', 'mun', 'provinceid', 'cityid', 'ltype', 'three_cityid', 'type', 'edu', 'exp', 'sex', 'minsalary','maxsalary','keyword', 'sdate', 'cert', 'sdate', 'uptime', 'order', 'orderby', 'uid', 'noid', 'nouid', 'jobwhere', 'bid', 'state','isshow','jobin','cityin','islt','noids','is_graduate','mun','welfare', 'com_id');
    public $shorttag_order = array('from', 'item', 'key', 'name');
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        $from = $_attr['from'];
        $item = $_attr['item'];
        $name = $_attr['item'];
        $name=str_replace('\'','',$name);
        $name=$name?$name:'List';$name='$'.$name;
        if (!strncmp("\$_smarty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
            $compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }

        //自定义标签 START
        $OutputStr='global $db,$db_config,$config;
		$time = time();
		
		
		//可以做缓存
        $paramer='.ArrayToString($_attr,true).';
		$ParamerArr = GetSmarty($paramer,$_GET,$_smarty_tpl);
		$paramer = $ParamerArr[arr];
        $Purl =  $ParamerArr[purl];
        global $ModuleName;
        if(!$Purl["m"]){
            $Purl["m"]=$ModuleName;
        }
		include_once  PLUS_PATH."/comrating.cache.php";
		include(CONFIG_PATH."db.data.php"); 
		if($config[sy_web_site]=="1"){
			if($config[province]>0 && $config[province]!=""){
				$paramer[provinceid] = $config[province];
			}
			if($config[cityid]>0 && $config[cityid]!=""){
				$paramer[cityid] = $config[cityid];
			}
			if($config[three_cityid]>0 && $config[three_cityid]!=""){
				$paramer[three_cityid] = $config[three_cityid];
			}
			if($config[hyclass]>0 && $config[hyclass]!=""){
				$paramer[hy]=$config[hyclass];
			}
		}

		
		if($paramer[sdate]){
			$where = "`sdate`>".strtotime("-".intval($paramer[sdate])." day",time())." and `state`=1";
		}else{
			$where = "`state`=1";
		}
		
		//按照UID来查询（按公司地址查询可用GET[id]获取当前公司ID）
		if($paramer[uid]){
			$where .= " AND `uid` = \'$paramer[uid]\'";
		}
		if($paramer[com_id]){
			$where .= " AND `uid` = \'$paramer[com_id]\'";
		}

		//是否推荐职位
		if($paramer[rec]){
			
			$where.=" AND `rec_time`>=".time();
			
		}
		//企业认证条件
		if($paramer[\'cert\']){
			$job_uid=array();
			$company=$db->select_all("company","`yyzz_status`=1","`uid`");
			if(is_array($company)){
				foreach($company as $v){
					$job_uid[]=$v[\'uid\'];
				}
			}
			$where.=" and `uid` in (".@implode(",",$job_uid).")";
		}
		//取不包含当前企业的职位
		if($paramer[nouid]){
			$where.= " and `uid`<>$paramer[nouid]";
		}
		//取不包含当前id的职位
		if($paramer[noid]){
			$where.= " and `id`<>$paramer[noid]";
		}
		//是否被锁定
		if($paramer[r_status]){
			$where.= " and `r_status`=2";
		}else{
			$where.= " and `r_status`=1";
		}
		//是否下架职位
		if($paramer[status]){
			$where.= " and `status`=\'1\'";
		}else{
			$where.= " and `status`=\'0\'";
		}
		//公司体制
		if($paramer[pr]){
			$where .= " AND `pr` =$paramer[pr]";
		}
		//公司行业分类
		if($paramer[\'hy\']){
			$where .= " AND `hy` = $paramer[hy]";
		} 
		//职位大类
		if($paramer[job1]){
			$where .= " AND `job1` = $paramer[job1]";
		}
		//职位子类
		if($paramer[job1_son]){
			$where .= " AND `job1_son` = $paramer[job1_son]";
		}
		//职位三级分类
		if($paramer[job_post]){
			$where .= " AND (`job_post` IN ($paramer[job_post]))";
		}
		//您可能感兴趣的职位--个人会员中心
		if($paramer[\'jobwhere\']){
			$where .=" and ".$paramer[\'jobwhere\'];
		}
		//职位分类综合查询
		if($paramer[\'jobids\']){
			$where.= " AND (`job1` = \'$paramer[jobids]\' OR `job1_son`= \'$paramer[jobids]\' OR `job_post`=\'$paramer[jobids]\')";
		}
		//职位分类区间,不建议执行该查询
		if($paramer[\'jobin\']){
			$where .= " AND (`job1` IN ($paramer[jobin]) OR `job1_son` IN ($paramer[jobin]) OR `job_post` IN ($paramer[jobin]))";
		}
		//城市大类
		if($paramer[provinceid]){
			$where .= " AND `provinceid` = $paramer[provinceid]";
		}
		//城市子类
		if($paramer[\'cityid\']){
			$where .= " AND (`cityid` IN ($paramer[cityid]))";
		}
		//城市三级子类
		if($paramer[\'three_cityid\']){
			$where .= " AND (`three_cityid` IN ($paramer[three_cityid]))";
		}
		if($paramer[\'cityin\']){
			$where .= " AND `three_cityid` IN ($paramer[cityin])";
		}
		//学历
		if($paramer[edu]){
			$where .= " AND `edu` = $paramer[edu]";
		}
		//工作经验
		if($paramer[exp]){
			$where .= " AND `exp` = $paramer[exp]";
		}
		//到岗时间
		if($paramer[report]){
			$where .= " AND `report` = $paramer[report]";
		}
		//职位性质
		if($paramer[type]){
			$where .= " AND `type` = $paramer[type]";
		}
		//性别
		if($paramer[sex]){
			$where .= " AND `sex` = $paramer[sex]";
		}
		//应届生
		if($paramer[is_graduate]){
			$where .= " AND `is_graduate` = $paramer[is_graduate]";
		}
		//公司规模
		if($paramer[mun]){
			$where .= " AND `mun` = $paramer[mun]";
		}
		 
		if($paramer[minsalary] && $paramer[maxsalary]){
			$where.= " AND (`minsalary`>=".intval($paramer[minsalary])." and `minsalary`<=".intval($paramer[maxsalary])." and `maxsalary`<=".intval($paramer[maxsalary]).") ";
		}elseif($paramer[minsalary]&&!$paramer[maxsalary]){
			$where.= " AND (`minsalary`>=".intval($paramer[minsalary]).") ";
		}elseif(!$paramer[minsalary]&&$paramer[maxsalary]){
			$where.= " AND (`minsalary`<=".intval($paramer[maxsalary])." and `maxsalary`<=".intval($paramer[maxsalary]).") ";
		}

	    //福利待遇
	    $cache_array = $db->cacheget();
		$comclass_name = $cache_array["comclass_name"];
		if($paramer[welfare]){
			$welfarename=$comclass_name[$paramer[welfare]];
            $where .=" AND `welfare` LIKE \'%".$welfarename."%\' ";
		}
		
		//城市区间,不建议执行该查询
		if($paramer[cityin]){
			$where .= " AND (`provinceid` IN ($paramer[cityin]) OR `cityid` IN ($paramer[cityin]) OR `three_cityid` IN ($paramer[cityin]))";
		}
		//紧急招聘urgent
		if($paramer[urgent]){
			$where.=" AND `urgent_time`>".time();
		}
		//更新时间区间
		if($paramer[uptime]){
			if($paramer[uptime]==1){
				$beginToday=mktime(0,0,0,date(\'m\'),date(\'d\'),date(\'Y\'));
				$where.=" AND lastupdate>$beginToday";
			}else{
				$time=time();
				$uptime = $time-($paramer[uptime]*86400);
				$where.=" AND lastupdate>$uptime";
			}
		}
		//按类似公司名称,不建议进行大数据量操作
		if($paramer[comname]){
			$where.=" AND `com_name` LIKE \'%".$paramer[comname]."%\'";
		}
		//按公司归属地,只适合查询一级城市分类
		if($paramer[com_pro]){
			$where.=" AND `com_provinceid` =\'".$paramer[com_pro]."\'";
		}
		//按照职位名称匹配
		if($paramer[keyword]){
			$where1[]="`name` LIKE \'%".$paramer[keyword]."%\'";
			$where1[]="`com_name` LIKE \'%".$paramer[keyword]."%\'";
			include  PLUS_PATH."/city.cache.php";
			foreach($city_name as $k=>$v){
				if(strpos($v,$paramer[keyword])!==false){
					$cityid[]=$k;
				}
			}
			if(is_array($cityid)){
				foreach($cityid as $value){
					$class[]= "(provinceid = \'".$value."\' or cityid = \'".$value."\')";
				}
				$where1[]=@implode(" or ",$class);
			}
			$where.=" AND (".@implode(" or ",$where1).")";
		}

		//多选职位
		if($paramer["job"]){
			$where.=" AND `job_post` in ($paramer[job])";
		}
		//置顶招聘
		if($paramer[bid]){
			if($config[joblist_top]!=1){
				$paramer[limit] = 5;
			}
			$where.="  and `xsdate`>\'".time()."\'";
		} 
		
		//自定义查询条件，默认取代上面任何参数直接使用该语句
		if($paramer[where]){
			$where = $paramer[where];
		}

		//查询条数
		$limit = \'\';
		if($paramer[limit]){

			$limit = " limit ".$paramer[limit];
		}
		if($paramer[ispage]){
			$limit = PageNav($paramer,$_GET,"company_job",$where,$Purl,"",$paramer[islt]?$paramer[islt]:"6",$_smarty_tpl);        
		}

		//排序字段默认为更新时间
		//置顶设置为随机5条时，随机查询
		if($paramer[bid] && $paramer[limit]){
			$order = " ORDER BY rand() ";
		}else{
			if($paramer[order] && $paramer[order]!="lastdate"){
				$order = " ORDER BY ".str_replace("\'","",$paramer[order])."  ";
			}else{
				$order = " ORDER BY `lastupdate` ";
			}
		}
		//排序规则 默认为倒序
		if($paramer[sort]){
			$sort = $paramer[sort];
		}else{
			$sort = " DESC";
		} 
		$where.=$order.$sort;
		 
		'.$name.' = $db->select_all("company_job",$where.$limit);

		if(is_array('.$name.') && !empty('.$name.')){
			//处理类别字段
			$cache_array = $db->cacheget();
			$comuid=$jobid=array();
			foreach('.$name.' as $key=>$value){
				if(in_array($value[\'uid\'],$comuid)==false){$comuid[] = $value[\'uid\'];}
				if(in_array($value[\'id\'],$jobid)==false){$jobid[] = $value[\'id\'];} 
			}
			$comuids = @implode(\',\',$comuid);
			$jobids = @implode(\',\',$jobid);
			
			if($comuids){
				$r_uids=$db->select_all("company","`uid` IN (".$comuids.")","`uid`,`yyzz_status`,`logo`,`logo_status`,`pr`,`hy`,`mun`,`shortname`,`welfare`,`hotstart`,`hottime`");
				if(is_array($r_uids)){
					foreach($r_uids as $key=>$value){
						if($value[shortname]){
    						$value[\'shortname_n\'] = $value[shortname];
    					}
						if(!$value[\'logo\'] || $value[\'logo_status\']!=0){
							$value[\'logo\'] = checkpic("",$config[\'sy_unit_icon\']);
						}else{
							$value[\'logo\']= checkpic($value[\'logo\']);
						}
						$value[\'pr_n\'] = $cache_array[\'comclass_name\'][$value[pr]];
						$value[\'hy_n\'] = $cache_array[\'industry_name\'][$value[hy]];
						$value[\'mun_n\'] = $cache_array[\'comclass_name\'][$value[mun]];
						if($value[\'hotstart\']<=time() && $value[\'hottime\']>=time()){
							$value[\'hotlogo\'] = 1;
						}
						$r_uid[$value[\'uid\']] = $value;
					}
				}
			}
			
 			if($paramer[bid]){
				$noids=array();
			}	
			foreach('.$name.' as $key=>$value){

				if($paramer[bid]){
					$noids[] = $value[id];
				}
				//筛除重复
				if($paramer[noids]==1 && !empty($noids) && in_array($value[\'id\'],$noids)){
					unset('.$name.'[$key]);
					continue;
				}else{
					'.$name.'[$key] = $db->array_action($value,$cache_array);
					'.$name.'[$key][stime] = date("Y-m-d",$value[sdate]);
					'.$name.'[$key][etime] = date("Y-m-d",$value[edate]);
					if($arr_data[\'sex\'][$value[\'sex\']]){
						'.$name.'[$key][sex_n]=$arr_data[\'sex\'][$value[\'sex\']];
					}
					'.$name.'[$key][lastupdate] = date("Y-m-d",$value[lastupdate]);
					if($value[minsalary]&&$value[maxsalary]){
						if($config[\'resume_salarytype\']==1){
								'.$name.'[$key][job_salary] =$value[minsalary]."-".$value[maxsalary]."元";
						}else{
							if($value[maxsalary]<1000){
								if($config[\'resume_salarytype\']==2){
									'.$name.'[$key][job_salary] = "1千以下";
								}elseif($config[\'resume_salarytype\']==3){
								'.$name.'[$key][job_salary] = "1K以下";
								}elseif($config[\'resume_salarytype\']==4){
								'.$name.'[$key][job_salary] = "1k以下";
								}
							}else if($value[minsalary]<1000){
								'.$name.'[$key][job_salary] =changeSalary($value[maxsalary])."以下";
							}else{
								'.$name.'[$key][job_salary] =changeSalary($value[minsalary])."-".changeSalary($value[maxsalary]);
							}
						}
					}elseif($value[minsalary]){
						if($config[\'resume_salarytype\']==1){
								'.$name.'[$key][job_salary] =$value[minsalary]."以上";
						}else{
							'.$name.'[$key][job_salary] =changeSalary($value[minsalary])."以上";
						}
					}else{
						'.$name.'[$key][job_salary] ="面议";
					}
					if($r_uid[$value[\'uid\']][shortname]){
						'.$name.'[$key][com_name] =$r_uid[$value[\'uid\']][shortname];
					}
					'.$name.'[$key][yyzz_status] =$r_uid[$value[\'uid\']][yyzz_status];
					'.$name.'[$key][logo] =$r_uid[$value[\'uid\']][logo];
					'.$name.'[$key][pr_n] =$r_uid[$value[\'uid\']][pr_n];
					'.$name.'[$key][hy_n] =$r_uid[$value[\'uid\']][hy_n];
					'.$name.'[$key][mun_n] =$r_uid[$value[\'uid\']][mun_n];
					'.$name.'[$key][hotlogo] =$r_uid[$value[\'uid\']][hotlogo];
					$time=$value[\'lastupdate\'];
					//今天开始时间戳
					$beginToday=mktime(0,0,0,date(\'m\'),date(\'d\'),date(\'Y\'));
					//昨天开始时间戳
					$beginYesterday=mktime(0,0,0,date(\'m\'),date(\'d\')-1,date(\'Y\'));
					//一周内时间戳
					$week=strtotime(date("Y-m-d",strtotime("-1 week")));
					if($time>$week && $time<$beginYesterday){
						'.$name.'[$key][\'time\'] ="一周内";
					}elseif($time>$beginYesterday && $time<$beginToday){
						'.$name.'[$key][\'time\'] ="昨天";
					}elseif($time>$beginToday){	
						'.$name.'[$key][\'time\'] = date("H:i",$value[\'lastupdate\']);
						'.$name.'[$key][\'redtime\'] =1;
					}else{
						'.$name.'[$key][\'time\'] = date("Y-m-d",$value[\'lastupdate\']);
					}
    
                     // 前天
    				$beforeYesterday=mktime(0,0,0,date(\'m\'),date(\'d\')-2,date(\'Y\'));

					if($value[\'sdate\']>$beforeYesterday){
						'.$name.'[$key][\'newtime\'] =1;
					}
					//获得福利待遇名称
					if($value[welfare]){
						$welfareList = @explode(\',\',$value[welfare]);

						if(!empty($welfareList)){
							'.$name.'[$key][welfarename] =$welfareList;
						}
					}
					//截取公司名称
					if($paramer[comlen]){
						if($r_uid[$value[\'uid\']][shortname]){
							'.$name.'[$key][com_n] = mb_substr($r_uid[$value[\'uid\']][shortname],0,$paramer[comlen],"utf-8");
						}else{
							'.$name.'[$key][com_n] = mb_substr($value[\'com_name\'],0,$paramer[comlen],"utf-8");
						}
					}
					//截取职位名称
					if($paramer[namelen]){
						if($value[\'rec_time\']>time()){
							'.$name.'[$key][name_n] = "<font color=\'red\'>".mb_substr($value[\'name\'],0,$paramer[namelen],"utf-8")."</font>";
						}else{
							'.$name.'[$key][name_n] = mb_substr($value[\'name\'],0,$paramer[namelen],"utf-8");
						}
					}else{
						if($value[\'rec_time\']>time()){
							'.$name.'[$key][\'name_n\'] = "<font color=\'red\'>".$value[\'name\']."</font>";
						}
					}
					//构建职位伪静态URL
					'.$name.'[$key][job_url] = Url("job",array("c"=>"comapply","id"=>$value[id]),"1");
					//构建企业伪静态URL
					'.$name.'[$key][com_url] = Url("company",array("c"=>"show","id"=>$value[uid]));
					
					foreach($comrat as $k=>$v){
						if($value[rating]==$v[id]){
							'.$name.'[$key][color] = str_replace("#","",$v[com_color]);
							if($v[com_pic]){
								'.$name.'[$key][ratlogo] = checkpic($v[com_pic]);
							}
							'.$name.'[$key][ratname] = $v[name];
						}
					}
					if($paramer[keyword]){
						'.$name.'[$key][name]=str_replace($paramer[keyword],"<font color=#FF6600 >".$paramer[keyword]."</font>",$value[name]);
						'.$name.'[$key][com_name]=str_replace($paramer[keyword],"<font color=#FF6600 >".$paramer[keyword]."</font>",$value[com_name]);
						'.$name.'[$key][name_n]=str_replace($paramer[keyword],"<font color=#FF6600 >".$paramer[keyword]."</font>",'.$name.'[$key][name_n]);
						'.$name.'[$key][com_n]=str_replace($paramer[keyword],"<font color=#FF6600 >".$paramer[keyword]."</font>",'.$name.'[$key][com_n]);
						'.$name.'[$key][job_city_one]=str_replace($paramer[keyword],"<font color=#FF6600 >".$paramer[keyword]."</font>",$city_name[$value[provinceid]]);
						'.$name.'[$key][job_city_two]=str_replace($paramer[keyword],"<font color=#FF6600 >".$paramer[keyword]."</font>",$city_name[$value[cityid]]);
					}
				}
			}
			if(is_array('.$name.')){
				if($paramer[keyword]!=""&&!empty('.$name.')){
					addkeywords(\'3\',$paramer[keyword]);
				}
			}
		}';
        //global $DiyTagOutputStr;
        //$DiyTagOutputStr[]=$OutputStr;
        return SmartyOutputStr($this,$compiler,$_attr,'joblist',$name,$OutputStr,$name);
    }
}
class Smarty_Internal_Compile_Joblistelse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('joblist'));
        $this->openTag($compiler, 'joblistelse', array('joblistelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Joblistclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('joblist', 'joblistelse'));

        return "<?php } ?>";
    }
}
