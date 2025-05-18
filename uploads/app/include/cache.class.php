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
 class cache{
	public $cachedir;
	public $obj;
	public function __construct($cachedir,$obj) {
		$this->cachedir	=	$cachedir;
		$this->obj		=	$obj;
	}
	public function city_cache($dir){
		$cityarr		=	$this->obj->select_all("city_class",array('display'=>'1','orderby'=>'sort,asc'));

		if(is_array($cityarr)){
			foreach($cityarr as $v){
				if($v['keyid']==0){
					$city_index[]				=	$v['id'];
				}
				if($v['keyid']!=0){
					$city_type[$v['keyid']][]	=	$v['id'];
				}
				if($v['e_name']){
					$cityename[$v['id']]		=	$v['e_name'];
				}
				$city_parent[$v['id']]			=	$v['keyid'];
				$cityname[$v['id']]				=	$v['name'];
			}
			
			foreach ($city_type as $k=>$v){
			    if (in_array($k, $city_index)){
			        foreach ($v as $val){
			            $city_two[]=$val;
			        }
			    }
			}
			foreach ($city_type as $k=>$v){
			    if (in_array($k, $city_two)){
			        foreach ($v as $val){
			            $city_three[]=$val;
			        }
			    }
			}
			
		}

        $this->made_web_js($this->cachedir.'city.cache.js',array('ci'=>$city_index,'ct'=>count($city_type)?$city_type:'new Array()','cn'=>$cityname));
        
        $data['city_index']	=	ArrayToString($city_index,false);
		$data['city_type']	=	ArrayToString($city_type);
		$data['city_name']	=	ArrayToString($cityname);
		if($city_two){
		    $fsdata['city_two']=ArrayToString($city_two,false);
		}
		if($city_three){
		    $fsdata['city_three']=ArrayToString($city_three,false);
		}
		if($fsdata){
		    made_web_array($this->cachedir.'cityfs.cache.php',$fsdata);
		}
		if($city_parent){
			$this->made_web_js($this->cachedir.'cityparent.cache.js',array('city_parent'=>$city_parent));
		    $parent['city_parent']	=	ArrayToString($city_parent);
		    made_web_array($this->cachedir.'cityparent.cache.php',$parent);
		}
		
		$ename['city_ename']		=	ArrayToString($cityename);
		made_web_array($this->cachedir.'cityename.cache.php',$ename);
		
		return made_web_array($this->cachedir.$dir,$data);
	}
	//问答类别缓存
	public function ask_cache($dir){
		$askarr	=	$this->obj->select_all("q_class",array('orderby'=>'sort,asc'));

		if(is_array($askarr)){
			foreach($askarr as $v){
				if($v['pid']==0){
					$ask_index[]			=	$v['id'];
				}
				if($v['pid']!=0){
					$ask_type[$v['pid']][]	=	$v['id'];
				}

				$askpic[$v['id']]			=	$v['pic'];
				$askintro[$v['id']]			=	$v['intro'];
				$askname[$v['id']]			=	$v['name'];
			}
		}
        $this->made_web_js($this->cachedir.'ask.cache.js',array('ai'=>$ask_index,'at'=>$ask_type,'an'=>$askname));
		$data['ask_index']	=	ArrayToString($ask_index,false);
		$data['ask_type']	=	ArrayToString($ask_type);
		$data['ask_pic']	=	ArrayToString($askpic);
		$data['ask_intro']	=	ArrayToString($askintro);
		$data['ask_name']	=	ArrayToString($askname);
		return made_web_array($this->cachedir.$dir,$data);
	}
    //生成网站配置
	function made_web_js($dir,$array,$preffix='o'){
		$content	=	'';
		if(is_array($array)){
			foreach($array as $key=>$v){
                if(is_array($v)&&(!is_array(current($v)))){
                    $content	.=	'var '.$key.'=new Array();'."\n";
					$content	.=	$this->made_js_string($v,$key,$preffix);
                }else if(is_array($v)){
                    $content	.=	'var '.$key.'=new Array();'."\n";
					$content	.=	$this->made_js_string($v,$key,$preffix);
				}else{
					$v			=	str_replace("'","\\'",$v);
					$v			=	str_replace("\"","'",$v);
					$v			=	str_replace("\$","",$v);
					$content	.=	"$key='".$v."';";
				}
				$content		.=	" \n";
			}
		}
        $fpindex	=	@fopen($dir,"w+");
        $fw			=	@fwrite($fpindex,$content);
        @fclose($fpindex);
        return $fw;
	}
    function made_js_string($array,$objkey='',$preffix='o'){
		$i 		=	0;
		$string	=	'';
		foreach($array as $key=>$value){
			if(is_array($value)&&(!is_array(current($value)))){
				$ItemList	=	array();
                foreach($value as $v){
                	if(is_numeric($v)){
                        $ItemList[]	=	$v;
                    }else{
                        $ItemList[]	=	'\''.$v.'\'';
                    }
                }
                if(count($ItemList)==1){
                    $ItemList[0]	=	'\''.$ItemList[0].'\'';
                }
                $string				.=	$objkey.'['.(is_numeric($key)?$key:('\''.$key.'\''))."]=new Array(".implode(',',$ItemList).');'."\n";
			}else if(is_array($value)){
				$string				.=	$objkey.'.'.$preffix.$key."=new Array();\n";
                foreach($value as $k=>$v){
                    $ItemList[]		=	$objkey.'.'.$preffix.$key."=new Array();\n".$this->made_js_string($v,$objkey.".".$preffix.$k)."\n";
                }

			}else{
				if(is_numeric($value)){
                    $string			.=	$objkey.'['.(is_numeric($key)?$key:('\''.$key.'\''))."]=".$value.";\n";
                }else{
				    $string			.=	$objkey.'['.(is_numeric($key)?$key:('\''.$key.'\''))."]='".$value."';\n";
                }
			}
			$i++;
		}
		return $string;
	}
	public function cron_cache($dir){
		$rows	=	$this->obj->select_all("cron",array('display'=>'1','orderby'=>'id,asc'));
		if(is_array($rows)){
			foreach($rows as $key=>$value){
				$cron_cache[$key]['id']			=	$value["id"];
				$cron_cache[$key]['dir']		=	$value["dir"];
				$cron_cache[$key]['type']		=	$value["type"];
				$cron_cache[$key]['week']		=	$value["week"];
				$cron_cache[$key]['month']		=	$value["month"];
				$cron_cache[$key]['hour']		=	$value["hour"];
				$cron_cache[$key]['minute']		=	$value["minute"];
				$cron_cache[$key]['nexttime']	=	$value["nexttime"];
			}
		}
		$data['cron']	=	ArrayToString($cron_cache);
		return made_web_array($this->cachedir.$dir,$data);
	}
	
	
	public function job_cache($dir){
		$rows	=	$this->obj->select_all("job_class",array('orderby'=>'sort,asc'));
		if(is_array($rows)){
			foreach($rows as $v){
				if($v['keyid']==0){
					$job_index[]			=	$v['id'];
				}
				if($v['keyid']!=0){
					$jobtype[$v['keyid']][]	=	$v['id'];
				}
				if($v['content']){
					$content[]				=	$v['id'];
				}
				if($v['e_name']){
					$jobename[$v['id']]		=	$v['e_name'];
				}
				$job_parent[$v['id']]		=	$v['keyid'];
				$jobname[$v['id']]			=	$v['name'];

			}
			foreach ($jobtype as $k=>$v){
			    if (in_array($k, $job_index)){
			        foreach ($v as $val){
			            $job_two[]=$val;
			        }
			    }
			}
			foreach ($jobtype as $k=>$v){
			    if (in_array($k, $job_two)){
			        foreach ($v as $val){
			            $job_three[]=$val;
			        }
			    }
			}
			
		}
        $this->made_web_js($this->cachedir.'job.cache.js',array('content'=>$content,'ji'=>$job_index,'jt'=>count($jobtype)?$jobtype:'new Array()','jn'=>$jobname));
		$data['content']	=	ArrayToString($content,false);
		$data['job_index']	=	ArrayToString($job_index,false);
		$data['job_type']	=	ArrayToString($jobtype);
		$data['job_name']	=	ArrayToString($jobname);
		$data['job_key']    =   ArrayToString($jobkey);
		if($job_two){
		    $fsdata['job_two']=ArrayToString($job_two,false);
		}
		if($job_three){
		    $fsdata['job_three']=ArrayToString($job_three,false);
		}
		if($fsdata){
		    made_web_array($this->cachedir.'jobfs.cache.php',$fsdata);
		}
		if ($job_parent){
			$this->made_web_js($this->cachedir.'jobparent.cache.js',array('job_parent'=>$job_parent));
		    $parent['job_parent']		=	ArrayToString($job_parent);
		    made_web_array($this->cachedir.'jobparent.cache.php',$parent);
		}
		
		    $ename['job_ename']		=	ArrayToString($jobename);
		    made_web_array($this->cachedir.'jobename.cache.php',$ename);
		
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function industry_cache($dir){
		$rows	=	$this->obj->select_all("industry",array('orderby'=>'sort,desc'));
		if(is_array($rows)){
			foreach($rows as $v){
				$industry_index[]		=	$v['id'];
				$industryname[$v['id']]	=	$v['name'];
			}
		}
        $this->made_web_js($this->cachedir.'industry.cache.js',array('hi'=>$industry_index,'ht'=>count($industrytype)?$industrytype:'new Array()','hn'=>$industryname));

		$data['industry_index']	=	ArrayToString($industry_index,false);
		$data['industry_name']	=	ArrayToString($industryname);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function introduce_cache($dir){
		$rows	=	$this->obj->select_all("introduce_class",array('orderby'=>'sort,asc'));
		if(is_array($rows)){
			foreach($rows as $v){
				$introduce_index[]			=	$v['id'];
				$introducename[$v['id']]		=	$v['name'];
				$introducecontent[$v['id']]		=	$v['content'];
			}
		}
		$data['introduce_index']	=	ArrayToString($introduce_index,false);
		$data['introduce_content']	=	ArrayToString($introducecontent);
		$data['introduce_name']		=	ArrayToString($introducename);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function redeem_cache($dir){
		$rows	=	$this->obj->select_all("redeem_class",array('orderby'=>'sort,desc'));
		if(is_array($rows)){
			foreach($rows as $v){
				if($v['keyid']==0){
					$redeem_index[]				=	$v['id'];
				}
				if($v['keyid']!=0){
					$redeemtype[$v['keyid']][]	=	$v['id'];
				}
				$redeemname[$v['id']]			=	$v['name'];
			}
		}
		$data['redeem_index']	=	ArrayToString($redeem_index,false);
		$data['redeem_type']	=	ArrayToString($redeemtype);
		$data['redeem_name']	=	ArrayToString($redeemname);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function integralclass_cache($dir){
		$rows	=	$this->obj->select_all("admin_integralclass",array('state'=>'1','orderby'=>'integral,asc'));
		if(is_array($rows)){
			foreach($rows as $v){
				$integralclass_index[]				=	$v['id'];
				$integralclass_name[$v['id']]		=	$v['integral'];
				$integralclass_names[]				=	$v['integral'];
				$integralclass_discount[$v['id']]	=	$v['discount'];
			}
		}
		$data['integralclass_index']	=	ArrayToString($integralclass_index,false);
		$data['integralclass_name']		=	ArrayToString($integralclass_name);
		$data['integralclass_names']	=	ArrayToString($integralclass_names);
		$data['integralclass_discount']	=	ArrayToString($integralclass_discount);
		return made_web_array($this->cachedir.$dir,$data);
	}
	 
	public function user_cache($dir){
	    $rows	=	$this->obj->select_all("userclass",array('orderby'=>'sort,asc'));
	    if(is_array($rows)){
	        foreach($rows as $v){
	            if($v['keyid']!=0){
	                $com_index[$v["keyid"]][]	=	$v["id"];
	            }
	            $jobname[$v['id']]				=	$v['name'];
	        }
	        foreach($rows as $v){
	            if($v['keyid']==0){
	                $data2[$v['variable']]		=	$com_index[$v['id']];
	            }
	        }
	    }
	    //$this->made_web_js($this->cachedir.'user.cache.js',array('useri'=>$data2,'usern'=>$jobname));
	    $data['userdata']		=	ArrayToString($data2);
	    $data['userclass_name']	=	ArrayToString($jobname);
	    return made_web_array($this->cachedir.$dir,$data);
	}
	
    
	public function com_cache($dir){
		$rows	=	$this->obj->select_all("comclass",array('orderby'=>'sort,asc'));
		if(is_array($rows)){
			foreach($rows as $v){
				if($v["keyid"]!=0){
					$com_index[$v["keyid"]][]	=	$v["id"];
				}
				$comname[$v['id']]				=	$v['name'];
			}
			foreach($rows as $v){
				if($v['keyid']==0){
					$data2[$v['variable']]		=	$com_index[$v['id']];
				}
			}
		}
        //$this->made_web_js($this->cachedir.'com.cache.js',array('comd'=>$data2,'comn'=>$comname));
		$data['comdata']		=	ArrayToString($data2);
		$data['comclass_name']	=	ArrayToString($comname);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function part_cache($dir){
		$rows	=	$this->obj->select_all("partclass",array('orderby'=>'sort,asc'));
		if(is_array($rows)){
			foreach($rows as $v){
				if($v["keyid"]!=0){
					$com_index[$v["keyid"]][]	=	$v["id"];
				}
				$comname[$v['id']]				=	$v['name'];
			}
			foreach($rows as $v){
				if($v['keyid']==0){
					$data2[$v['variable']]		=	$com_index[$v['id']];
				}
			}
		}
		$data['partdata']		=	ArrayToString($data2);
		$data['partclass_name']	=	ArrayToString($comname);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function menu_cache($dir){
		$rows	=	$this->obj->select_all("navigation",array('display'=>'1','orderby'=>'sort,asc'));
		if(is_array($rows)){
			foreach($rows as $key=>$v){
				if(!is_array($com_index[$v["nid"]]))
					$a[$v["nid"]]=0;
					$com_index[$v["nid"]][$a[$v["nid"]]]['name']	=	$v['name'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['url']		=	$v['url'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['furl']	=	$v['furl'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['eject']	=	$v['eject'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['type']	=	$v['type'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['color']	=	$v['color'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['model']	=	$v['model'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['bold']	=	$v['bold'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['sort']	=	$v['sort'];
					$com_index[$v["nid"]][$a[$v["nid"]]]['pic']		=	$v['pic'];
					$a[$v["nid"]]++;
			}
		}
		$data['menu_name']	=	ArrayToString($com_index,true,true);
		return made_web_array($this->cachedir.$dir,$data);
	}
	
	//网站地图
	public function navmap_cache($dir){
		$rows	=	$this->obj->select_all("navmap",array('display'=>'1','orderby'=>'sort,desc'));
		if(is_array($rows)){
			foreach($rows as $key=>$v){
				$navmap[$v['nid']][$key]['id']		=	$v['id'];
				$navmap[$v['nid']][$key]['name']	=	$v['name'];
				$navmap[$v['nid']][$key]['url']		=	$v['url'];
				$navmap[$v['nid']][$key]['furl']	=	$v['furl'];
				$navmap[$v['nid']][$key]['eject']	=	$v['eject'];
				$navmap[$v['nid']][$key]['type']	=	$v['type'];
			}
		}
		$data['navmap']	=	ArrayToString($navmap);
		return made_web_array($this->cachedir.$dir,$data);
	}
	//SEO缓存
	public function seo_cache($dir){
		$rows	=	$this->obj->select_all("seo");
		if(is_array($rows)){
			foreach($rows as $key=>$v){
				$seo_index[$v["ident"]][$key]['title']			=	$v["title"];
				$seo_index[$v["ident"]][$key]['keywords']		=	$v["keywords"];
				$seo_index[$v["ident"]][$key]['description']	=	$v["description"];
				$seo_index[$v["ident"]][$key]['did']			=	$v["did"];
				$seo_index[$v["ident"]][$key]['php_url']		=	$v["php_url"];
				$seo_index[$v["ident"]][$key]['rewrite_url']	=	$v["rewrite_url"];
			}
		}
		$data['seo']	=	ArrayToString($seo_index);
		return made_web_array($this->cachedir.$dir,$data);
	}
	//域名缓存
	public function domain_cache($dir){
		$rows	=	$this->obj->select_all("domain",array('type'=>'1'));
		include(PLUS_PATH."/city.cache.php");
		include(PLUS_PATH."/industry.cache.php");
		if(is_array($rows)){
			foreach($rows as $key=>$value){
				if($value["three_cityid"]){
					$site_domain[$key]['cityname']		=	$city_name[$value["three_cityid"]];
					$site_domain[$key]['three_cityid']	=	$value["three_cityid"];
				}elseif($value["cityid"]){
					$site_domain[$key]['cityname']		=	$city_name[$value["cityid"]];
					$site_domain[$key]['cityid']		=	$value["cityid"];
				}else{
					$site_domain[$key]['cityname']		=	$city_name[$value["province"]];
					$site_domain[$key]['province']		=	$value["province"];
				}

				$hyname 						=	$industry_name[$value["hy"]];
				$site_domain[$key]['id']		=	$value["id"];
				$site_domain[$key]['webname']	=	$value["title"];
				$site_domain[$key]['host']		=	$value["domain"];
				$site_domain[$key]['mode']		=	$value["mode"];
				$site_domain[$key]['indexdir']	=	$value["indexdir"];
				$site_domain[$key]['hy']		=	$value["hy"];
				$site_domain[$key]['type']		=	$value["type"];
				$site_domain[$key]['tpl']		=	$value["tpl"];
				$site_domain[$key]['hyname']	=	$hyname;
				$site_domain[$key]['style']		=	$value["style"];
				$site_domain[$key]['fz_type']	=	$value["fz_type"];
				$site_domain[$key]['webtitle']	=	$value["webtitle"];
				$site_domain[$key]['webkeyword']=	$value["webkeyword"];
				$site_domain[$key]['webmeta']	=	$value["webmeta"];
				$site_domain[$key]['weblogo']	=	$value["weblogo"];
			}
		}
		$data['site_domain']	=	ArrayToString($site_domain);
		return made_web_array($this->cachedir.$dir,$data);
	}
	
	public function keyword_cache($dir){
		$rows	=	$this->obj->select_all("hot_key",array('check'=>'1','orderby'=>'num,desc'));

		if(is_array($rows)){
			foreach($rows as $key=>$value){
				$row[$key]['id']		=	$value["id"];
				$row[$key]['key_name']	=	$value["key_name"];
				$row[$key]['num']		=	$value["num"];
				$row[$key]['type']		=	$value["type"];
				$row[$key]['size']		=	$value["size"];
				$row[$key]['color']		=	$value["color"];
				$row[$key]['bold']		=	$value["bold"];
				$row[$key]['tuijian']	=	$value["tuijian"];
			}
		}
		$data['keyword']	=	ArrayToString($row);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function link_cache($dir){
		$rows	=	$this->obj->select_all("admin_link",array('link_state'=>'1','orderby'=>'link_sorting,desc'),"*",'1');
		if(is_array($rows)){
			foreach($rows as $key=>$value){
				$row[$key]['id']		=	$value["id"];
				$row[$key]['link_name']	=	$value["link_name"];
				$row[$key]['link_url']	=	$value["link_url"];
				$row[$key]['img_type']	=	$value["img_type"];
				$row[$key]['pic']		=	$value["pic"];
				$row[$key]['link_type']	=	$value["link_type"];
				$row[$key]['did']		=	$value["did"];
				$row[$key]['tem_type']	=	$value["tem_type"];
			}
		}
		$data['link']	=	ArrayToString($row);
		return made_web_array($this->cachedir.$dir,$data);
	}
	/*生成网站主题*/
	public function indextpl_cache($dir){
		$rows	=	$this->obj->select_all("tplindex");
		if(is_array($rows)){
			foreach($rows as $key=>$value){
				$row[$value["id"]]['id']		=	$value["id"];
				$row[$value["id"]]['name']		=	$value["name"];
				$row[$value["id"]]['height']	=	$value["height"];
				$row[$value["id"]]['se']		=	$value["se"];
				$row[$value["id"]]['stime']		=	$value["stime"];
				$row[$value["id"]]['etime']		=	$value["etime"];
				$row[$value["id"]]['pic']		=	$value["pic"];
				$row[$value["id"]]['status']	=	$value["status"];
			}
		}
		$data['indextpl']	=	ArrayToString($row);
		return made_web_array($this->cachedir.$dir,$data);
	}
	
	public function comrating_cache($dir){
		$rows	=	$this->obj->select_all("company_rating");
		if(is_array($rows)){
			foreach($rows as $key=>$value){
				$row[$key]['id']				=	$value["id"];
				$row[$key]['display']			=	$value["display"];
				$row[$key]['name']				=	$value["name"];
				if($value["com_pic"]!='0'){
					$row[$key]['com_pic']			=	$value["com_pic"];
				}
				$row[$key]['jobrec']			=	$value["jobrec"];
				$row[$key]['category']			=	$value["category"];
				$row[$key]['service_discount']	=	$value["service_discount"];
				if($value["com_color"]){
					$row[$key]['com_color']		=	"#".str_replace("#","",$value["com_color"]);
				}else{
					$row[$key]['com_color']		=	'';
				}
			}
		}
		$data['comrat']	=	ArrayToString($row);
		return made_web_array($this->cachedir.$dir,$data);
	}
    public function reason_cache($dir){
		$rows	=	$this->obj->select_all("reason",array('orderby'=>'id,desc'));
		if(is_array($rows)){
		    foreach($rows as $key=>$value){
		        $row[$key]['id']	=	$value["id"];
		        $row[$key]['name']	=	$value["name"];
		    }
		}
		$data['reason']	=	ArrayToString($row);
		return made_web_array($this->cachedir.$dir,$data);
	}
    public function database_cache($dir){
		global $db;
    	$query	=	$db->query("show TABLES;");
    	while($row=$db->fetch_array($query)){
    		$TableList[]	=	$row;
    	}
    	foreach($TableList as $k=>$v){
    	    //由于$db->fetch_array()改为只返回关联数组,故原有用直接用$v[0],需处理，改为先取出数组中的值
    	    $table 	=	array_values($v);
    	    
    	    $query	=	$db->query("desc `".$table[0]."`;");

    	    while($row=$db->fetch_array($query)){
    	    	$TableStructList[$table[0]][]		=	$row;
    	    }
            $TableStructListNew	=	array();
            foreach($TableStructList[$table[0]] as $key=>$val){
                $TableStructListNew[$val['Field']]	=	$val['Type'];
            }
            $data[$table[0]]	=	ArrayToString($TableStructListNew,true,true);
        }
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function group_cache($dir){
		$rows	=	$this->obj->select_all("news_group",array('orderby'=>'sort,desc'));
		if(is_array($rows)){
			foreach($rows as $v){
				if($v['keyid']==0){
					$group_index[]				=	$v['id'];
				}
				if($v['keyid']!=0){
					$grouptype[$v['keyid']][]	=	$v['id'];
				}
				if($v['rec']=='1'){
					$group_rec[]				=	$v['id'];
				}
				if($v['rec_news']=='1'){
					$group_recnews[]			=	$v['id'];
				}
				$groupname[$v['id']]			=	$v['name'];
			}
		}
		if(!empty($group_rec)){
			$data['group_rec']		=	ArrayToString($group_rec,false);
		}
		if(!empty($group_recnews)){
			$data['group_recnews']	=	ArrayToString($group_recnews,false);
		}
		if(!empty($group_index)){
			$data['group_index']	=	ArrayToString($group_index,false);
		}
		if(!empty($grouptype)){
			$data['group_type']		=	ArrayToString($grouptype);
		}
		if(!empty($groupname)){
			$data['group_name']		=	ArrayToString($groupname);
		}
		return made_web_array($this->cachedir.$dir,$data);
	}
    public function desc_cache($dir){
		$DescClassList	=	$this->obj->select_all('desc_class',array('orderby'=>'sort,asc'));
		$DescList		=	$this->obj->select_all('description',array('is_nav'=>'1','orderby'=>'sort,asc'),'`id`,`nid`,`name`,`url`,`title`,`is_type`');
        foreach($DescList as $k=>$v){
            foreach($DescClassList as $k=>$val){
                if($v['nid']==$val['id']){
                    $DescList[$k]['classname']	=	$val['name'];
                }
            }
        }
        $data['desc_class']	=	ArrayToString($DescClassList,true,true);
        $data['desc_list']	=	ArrayToString($DescList,true,true);
		return made_web_array($this->cachedir.$dir,$data);
	}
	public function emailconfig_cache($dir){
	    include(PLUS_PATH.'config.php');
	    if ($config['sy_email_online']==1){
	        $rows	=	$this->obj->select_all("admin_email");
	        if(is_array($rows)){
	            foreach($rows as $key=>$value){
	                if($value["smtpserver"] && $value["smtpuser"] && $value["smtppass"] && $value["default"]=='1'){
	                    $row[$key]['id']			=	$value["id"];
	                    $row[$key]['smtpserver']	=	$value["smtpserver"];
	                    $row[$key]['smtpuser']		=	$value["smtpuser"];
	                    $row[$key]['smtppass']		=	$value["smtppass"];
	                    $row[$key]['smtpport']		=	$value["smtpport"];
	                    $row[$key]['smtpnick']		=	$value["smtpnick"];
	                }
	                
	            }
	        }
	        if(empty($row)){
	            $row = array();
	            $setVal = '0';
	        }else{
	            $setVal = '1';
	        }
	    }elseif ($config['sy_email_online']==2){
	        if ($config['accesskey'] && $config['accesssecret'] && $config['ali_email'] && $config['ali_name'] && $config['ali_tag']){
	            $setVal = '1';
	        }else{
	            $setVal = '0';
	        }
	    }
		$adminconfig	=	$this->obj->select_num("admin_config",array('name'=>'sy_email_set'));
		if($adminconfig==false){
			$this->obj->insert_into("admin_config",array('name'=>'sy_email_set','config'=>$setVal));
		}else{
			$this->obj->update_once("admin_config",array('config'=>$setVal),array('name'=>'sy_email_set'));
		}
		$adminconfig	=	$this->obj->select_all('admin_config');
		if(is_array($adminconfig)){
		    foreach($adminconfig as $v){
				$configarr[$v['name']]	=	$v['config'];
			}
		}
		made_web(PLUS_PATH.'config.php',ArrayToString($configarr),'config');


		$data['emailconfig']			=	ArrayToString($row);
		return made_web_array($this->cachedir.$dir,$data);
	}
 }
?>