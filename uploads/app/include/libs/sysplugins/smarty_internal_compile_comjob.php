<?php
class Smarty_Internal_Compile_Comjob extends Smarty_Internal_CompileBase{
    public $required_attributes = array('item');
    public $optional_attributes = array('name', 'key', 'limit','hy', 'rec', 'comlen', 'joblen', 'jobnum', 'urgent');
    public $shorttag_order = array('from', 'item', 'key', 'name');
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        $from = $_attr['from'];
        $item = $_attr['item'];
        if (!strncmp("\$_smarty_tpl->tpl_vars[$item]", $from, strlen($item) + 24)) {
            $compiler->trigger_template_error("item variable {$item} may not be the same variable as at 'from'", $compiler->lex->taglineno);
        }
        $OutputStr='global $db,$db_config,$config;$paramer='.ArrayToString($_attr,true).';
		$ParamerArr = GetSmarty($paramer,$_GET,$_smarty_tpl);
		$paramer = $ParamerArr[arr];extract($paramer);
		$Purl =  $ParamerArr[purl];
		if($config[\'sy_web_site\']=="1"){
			if($config[\'province\']>0 && $config[\'province\']!=""){
				$provinceid=$config[\'province\'];
			}
			if($config[\'cityid\']>0 && $config[\'cityid\']!=""){
				$cityid=$config[\'cityid\'];
			}
			if($config[\'three_cityid\']>0 && $config[\'three_cityid\']!=""){
				$three_cityid = $config[\'three_cityid\'];
			}
			if($config[\'hyclass\']>0 && $config[\'hyclass\']!=""){
				$hy=$config[\'hyclass\'];
			}
		}
		$time = time();
		$where = "`sdate`<$time AND  `state`=\'1\' and `r_status`=\'1\' and `status`=\'0\'";
		if($paramer[urgent]){
			$where.=" AND `urgent_time`>$time";
		}
		if($hy){
			$where.=\' AND `hy`=\'.$hy;
		}
		if($provinceid){
			$where.=\' AND `provinceid`=\'.$provinceid;
		}
		if($cityid){
			$where.=\' AND `cityid`=\'.$cityid;
		}
		if($three_cityid){
			$where.=" AND `three_cityid`=$three_cityid";
		}
		if($paramer[rec]){
			$where.=" AND `rec_time`>$time";
		}
		if($paramer[\'limit\']){
			$limit=" limit ".$paramer[\'limit\'];
		}
		include  PLUS_PATH."/city.cache.php";
		include  PLUS_PATH."/comrating.cache.php";
		$query = $db->query("select count(*) as num,uid,provinceid,cityid,three_cityid,lastupdate from (select * from $db_config[def]company_job where $where ORDER BY lastupdate desc) as a where  $where GROUP BY uid ORDER BY lastupdate desc $limit");
		$uids=array();$ComList=array();
        while($rs = $db->fetch_array($query)){
			if($citylen){
				$one_city[$rs[\'uid\']]	= mb_substr($city_name[$rs[\'cityid\']],0,$citylen);
				$two_city[$rs[\'uid\']] = mb_substr($city_name[$rs[\'three_cityid\']],0,$citylen);
			}else{
				$one_city[$rs[\'uid\']]	= $city_name[$rs[\'cityid\']];
				$two_city[$rs[\'uid\']] = $city_name[$rs[\'three_cityid\']];
			}
			if($one_city[$rs[\'uid\']]==\'\'){
				$one_city[$rs[\'uid\']]=$city_name[$rs[\'provinceid\']];
			}
			if(!$lasttime[$rs[\'uid\']]){
				$lasttime[$rs[\'uid\']] = date(\'Y-m-d\',$rs[\'lastupdate\']);
			}	
			
			$uids[] = $rs[\'uid\'];
		}
		if(!empty($uids)){
			$comids = @implode(\',\',$uids);
			$joblist = $db->select_all("company_job","$where AND `uid` IN ($comids) ORDER BY lastupdate desc","`id`,`uid`,`name`,`com_name`");

			$comlist = $db->select_all("company","`uid` IN ($comids) ORDER BY lastupdate desc","`uid`,`yyzz_status`,`shortname`");
			$job_list=array();
			foreach($joblist as $value){
				if(!$jobnum || count($job_list[$value[\'uid\']])<$jobnum){
					if($joblen){
						$value[\'name_n\'] = mb_substr($value[\'name\'],0,$joblen,\'utf-8\');
					}
					$value[\'url\'] = Url("job",array("c"=>"comapply","id"=>$value[\'id\']),"1");
					$job_list[$value[\'uid\']][] = $value;
				}
				$comname[$value[\'uid\']] = $value[\'com_name\'];
			}

			foreach($comlist as $value){
				
				$yyzz[$value[\'uid\']] = $value[\'yyzz_status\'];
        		$shortname[$value[\'uid\']] = $value[\'shortname\'];
			}
			$statis = $db->select_all("company_statis","`uid` in (".@implode(",",$uids).")","`uid`,`rating`");
			foreach($uids as $key=>$value){
				foreach($statis as $v){
					foreach($comrat as $val){
						if($value==$v[uid] && $val[id]==$v[rating])
						{
							$ComList[$key][color]=$val[com_color];
							$ComList[$key][ratlogo]=checkpic($val[com_pic]);
						}
					}
				}
				$ComList[$key][\'curl\']     =  Url("company",array("c"=>"show","id"=>$value));
				$ComList[$key][\'uid\']     = $value;
        		
        		if($shortname[$value]){
    				$ComList[$key][\'name\']	  = $shortname[$value];
    			}else{
    				$ComList[$key][\'name\']	  = $comname[$value];
    			}
				
				$ComList[$key][\'yyzz_status\']	  = $yyzz[$value];
				$ComList[$key][\'one_city\']	  = $one_city[$value];
				$ComList[$key][\'two_city\']	  = $two_city[$value];
				$ComList[$key][\'lasttime\']     = $lasttime[$value];
				if($comlen){
					if($shortname[$value]){
        				$ComList[$key][\'name_n\'] = mb_substr($shortname[$value],0,$comlen,\'utf-8\');
    				}else{
    					$ComList[$key][\'name_n\'] = mb_substr($comname[$value],0,$comlen,\'utf-8\');
    				}
				}
				$ComList[$key][\'joblist\'] =$job_list[$value];
				$ComList[$key][\'jobcount\'] =count($job_list[$value]);
			}
			
		}';

        return SmartyOutputStr($this,$compiler,$_attr,'comjob','$ComList',$OutputStr,'$ComList');
    }
}
class Smarty_Internal_Compile_Comjobelse extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);

        list($openTag, $nocache, $item, $key) = $this->closeTag($compiler, array('comjob'));
        $this->openTag($compiler, 'comjobelse', array('comjobelse', $nocache, $item, $key));

        return "<?php }\nif (!\$_smarty_tpl->tpl_vars[$item]->_loop) {\n?>";
    }
}
class Smarty_Internal_Compile_Comjobclose extends Smarty_Internal_CompileBase{
    public function compile($args, $compiler, $parameter){
        $_attr = $this->getAttributes($compiler, $args);
        if ($compiler->nocache) {
            $compiler->tag_nocache = true;
        }

        list($openTag, $compiler->nocache, $item, $key) = $this->closeTag($compiler, array('comjob', 'comjobelse'));

        return "<?php } ?>";
    }
}
