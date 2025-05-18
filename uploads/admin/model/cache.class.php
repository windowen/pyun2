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
class cache_controller extends adminCommon{
	function index_action(){
		$configM=$this->MODEL('config');
		if($_POST["madeall"]){ 
			if($this->config['sy_web_site']==1){  
				$index='../index.html';
				if(file_exists($index)){
					@unlink($index);
				}
				$this->ACT_layer_msg("分站已开启，不支持生成首页静态！",8);
			}else{
				$fw=$this->webindex($_POST['make_index_url']);
				
				$configM -> setConfig(array('make_index_url'=>$_POST['make_index_url']));
				
				$this->web_config();
				
				$fw?$this->ACT_layer_msg( "生成成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "生成失败！",8,$_SERVER['HTTP_REFERER']);
			}
		}
		$this->yunset("type","index");
		$this->yuntpl(array('admin/admin_makenews'));
	}
	function cache_action(){
		$configM=$this->MODEL('config');
		
		include_once(CONFIG_PATH."db.data.php");
		
		$this->yunset("type",$arr_data['cache']);
		
		if($_POST["madeall"]){
			/*生成四位JS，css识别码*/
			
			$cachecode=rand(1000,9999);
			
			$configM -> setConfig(array('cachecode'=>$cachecode));
			
			$this->web_config();
			
			$this->makecache_action();
		}
		$this->yuntpl(array('admin/admin_cache'));
	}
	function once_action(){
		$descM=$this -> MODEL('description');
		set_time_limit(200);
		if($_POST['make']){
			$where['is_type']	=	'1';
			if($_POST['desc']>0){
				$where['id']	=	$_POST['desc'];
			}
			$rows	=	$descM -> getDesList($where);

			if(@is_array($rows)){
				foreach($rows as $row){
                    $this->descriptionshow($row[id],$row['url']);
				}
			}
			echo 1;die;
		}
		$rows=$descM -> getDesList($where,array('field'=>"`id`,`name`"));
		$this->yunset("rows",$rows);
		$this->yunset("type","once");
		$this->yuntpl(array('admin/admin_makenews'));
	}
	function news_action(){
		$configM=$this->MODEL('config');
		set_time_limit(200);
		if($_POST["madeall"]){
			if($this->config['sy_web_site']==1){
				$index='../news.html';
				if(file_exists($index)){
					@unlink($index);
				}
				$this->ACT_layer_msg("分站已开启，不支持生成文件！",8);
			}else{
        
        $fw=$this->articleindex($_POST["make_new_url"]);
				
				$configM -> setConfig(array('make_new_url'=>$_POST['make_new_url']));
				
				$this->web_config();
				
				$fw?$this->ACT_layer_msg( "新闻生成(ID:$fw)成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "新闻生成(ID:$fw)失败！",8,$_SERVER['HTTP_REFERER']);	
			}
		}
		$this->yunset("type","news");
		$this->yuntpl(array('admin/admin_makenews'));
	}
	function newsclass_action(){
		set_time_limit(200);
		if($_POST['action']=="makeclass"){
			$val=$this->mk_newsclass();
			if(is_array($val)){
				foreach($val as $va){
					if($name==""){
						$name=$va;
					}
				}
				$this->get_return("class",$val,"正在生成新闻类别--".$name);
			}else{
				$this->get_return("ok",0,"全部生成完成");
			}
		}
		$rows=$this -> MODEL('article') -> getClass();
		$this->yunset("rows",$rows['list']);
		foreach($rows as $v){
			$classid[]=$v["id"];
		}
		$this->yunset("classid",@implode(',',$classid));
		$this->yunset("type","newsclass");
		$this->yuntpl(array('admin/admin_makenews'));
	}
	function archive_action(){
		set_time_limit(200);
		if($_POST['action']=="makearchive"){
			$pagesize	=	$_POST['limit'];
			$page		=	$this -> mk_archive($pagesize);
			if($page){
				if($page!=1){
					$npage	=	$page;
					$page	=	$page-1;
					$spage	=	$page*$pagesize;
					$topage	=	$spage+$pagesize;
				}else{
					$npage	=	$page;
					$spage	=	$page;
					$topage	=	$pagesize;
				}
				$name		=	$spage."-".$topage;
				$this -> get_return("archive",$npage,"正在生成".$name."新闻");
			}else{
				$this -> get_return("ok",0,"全部生成完成");
			}
		}
		$rows=$this -> MODEL('article') -> getClass();
		$this->yunset("rows",$rows['list']);
		$this->yunset("type","archive");
		$this->yuntpl(array('admin/admin_makenews'));
	}
	function all_action(){
		$configM=$this->MODEL('config');
		set_time_limit(200);
		if($_POST['action']=="makeall"){
			if($this->config['sy_web_site']==1){
				if($_POST['type']=="cache"){
					include_once(LIB_PATH."cache.class.php");
					$cacheclass= new cache(PLUS_PATH,$this->obj);
					include_once(CONFIG_PATH."db.data.php");
					$value=$_POST['value']+1;
				
					if($value==1){
						$makecache=$cacheclass->city_cache("city.cache.php");
					}
					if($value==2){
						$makecache=$cacheclass->industry_cache("industry.cache.php");
					}
					if($value==3){
						$makecache=$cacheclass->job_cache("job.cache.php");
					}
					if($value==4){
						$makecache=$cacheclass->user_cache("user.cache.php");
					}
					if($value==5){
						$makecache=$cacheclass->com_cache("com.cache.php");
					}
					if($value==6){
						$makecache=$cacheclass->menu_cache("menu.cache.php");
					}
					if($value==7){
						$cache=$this->del_dir("../data/templates_c",1);
						$cache=$this->del_dir("../data/cache",1);
					}
					if($value==8){
						$makecache=$cacheclass->seo_cache("seo.cache.php");
					}
					if($value==9){
						$makecache=$cacheclass->domain_cache("domain_cache.php");
					}
					if($value==10){
						$makecache=$cacheclass->keyword_cache("keyword.cache.php");
					}
					if($value==11){
						$makecache=$cacheclass->link_cache("link.cache.php");
					}
					if($value==12){
						$makecache=$cacheclass->group_cache("group.cache.php");
					}
					if($value==13){
						$makecache=$cacheclass->desc_cache("desc.cache.php");
					}
					if($value==14){
						$adM = $this->MODEL('ad');
						$makecache=$adM->model_ad_arr();
					}
					if($value==15){
						$makecache=$cacheclass->part_cache("part.cache.php");
					}
					if($value==16){
						$makecache=$cacheclass->redeem_cache("redeem.cache.php");
					}
					if($value==17){
						$makecache=$cacheclass->database_cache("dbstruct.cache.php");
					}
					if($value==18){
						$makecache=$cacheclass->emailconfig_cache("emailconfig.cache.php");
					}
					if($value==19){
						$makecache=$cacheclass->navmap_cache("navmap.cache.php");
					}
					if($value==20){
						$makecache=$cacheclass->ask_cache("ask.cache.php");
					}
					if($value==21){
						
					}
				 
					if($value==23){
						$makecache=$cacheclass->moblienav_cache("moblienav.cache.php");
					}
					if($value==24){
						$makecache=$cacheclass->introduce_cache("introduce.cache.php");
					}
					if($value<=24){
						$v=$value+1;
						$this->get_return("cache",$value,"正在生成".$arr_data['cache'][$v]);
					}
					$index='../index.html';
					$news='../news.html';
					if(file_exists($index)||file_exists($news)){
						@unlink($index);
						@unlink($news);
					}
					$this->get_return("ok",0,"全部生成完成");
					echo json_encode($data);die;
				}
			}else{
				if($_POST['type']=="cache"){
					include_once(LIB_PATH."cache.class.php");
					$cacheclass= new cache(PLUS_PATH,$this->obj);
					include_once(CONFIG_PATH."db.data.php");
					$value=$_POST['value']+1;
				
					if($value==1){
						$makecache=$cacheclass->city_cache("city.cache.php");
					}
					if($value==2){
						$makecache=$cacheclass->industry_cache("industry.cache.php");
					}
					if($value==3){
						$makecache=$cacheclass->job_cache("job.cache.php");
					}
					if($value==4){
						$makecache=$cacheclass->user_cache("user.cache.php");
					}
					if($value==5){
						$makecache=$cacheclass->com_cache("com.cache.php");
					}
					if($value==6){
						$makecache=$cacheclass->menu_cache("menu.cache.php");
					}
					if($value==7){
						$cache=$this->del_dir("../data/templates_c",1);
						$cache=$this->del_dir("../data/cache",1);
					}
					if($value==8){
						$makecache=$cacheclass->seo_cache("seo.cache.php");
					}
					if($value==9){
						$makecache=$cacheclass->domain_cache("domain_cache.php");
					}
					if($value==10){
						$makecache=$cacheclass->keyword_cache("keyword.cache.php");
					}
					if($value==11){
						$makecache=$cacheclass->link_cache("link.cache.php");
					}
					if($value==12){
						$makecache=$cacheclass->group_cache("group.cache.php");
					}
					if($value==13){
						$makecache=$cacheclass->desc_cache("desc.cache.php");
					}
					if($value==14){
						$adM = $this->MODEL('ad');
						$makecache=$adM->model_ad_arr();
					}
					if($value==15){
						$makecache=$cacheclass->part_cache("part.cache.php");
					}
					if($value==16){
						$makecache=$cacheclass->redeem_cache("redeem.cache.php");
					}
					if($value==17){
						$makecache=$cacheclass->database_cache("dbstruct.cache.php");
					}
					if($value==18){
						$makecache=$cacheclass->emailconfig_cache("emailconfig.cache.php");
					}
					if($value==19){
						$makecache=$cacheclass->navmap_cache("navmap.cache.php");
					}
					if($value==20){
						$makecache=$cacheclass->ask_cache("ask.cache.php");
					}
					if($value==21){
					}
					 
					if($value==23){
						$makecache=$cacheclass->moblienav_cache("moblienav.cache.php");
					}
					if($value==24){
						$makecache=$cacheclass->introduce_cache("introduce.cache.php");
					}
					if($value<=24){
						$v=$value+1;
						$this->get_return("cache",$value,"正在生成".$arr_data['cache'][$v]);
					}
					$fw=$this->webindex($_POST['make_index_url']);
					
					$configM -> setConfig(array('make_index_url'=>$_POST['make_index_url']));
					
					$this->web_config();
					$this->get_return("index","index","正在生成首页");					
					echo json_encode($data);die;
				}
				if($_POST['type']=="index"){
					if($_POST['value']=="make_index_url"){
							
						$fw=$this->webindex($_POST['make_index_url']);
						
						$configM -> setConfig(array('make_index_url'=>$_POST['make_index_url']));
						
						$this->web_config();
							
						$this->get_return("index","news","正在生成新闻首页");
					}else{
						 
						$this->articleindex($_POST["make_new_url"]);
						
						$configM -> setConfig(array('make_new_url'=>$_POST['make_new_url']));
						
						$this->web_config();
							
						$this->get_return("class",0,"正在获取新闻类别数目");
					}
					echo json_encode($data);die;
				}
				if($_POST['type']=="class"){
					$val=$this->mk_newsclass();
					if(is_array($val)){
						foreach($val as $va){
							if($name==""){
								$name=$va;
							}
						}
						$this -> get_return("class",$val,"正在生成新闻类别--".$name);
					}else{
						$this -> get_return("archive",0,"正在获取新闻详细页数目");
					}
				}
				if($_POST['type']=="archive"){
					$pagesize="20";
					$page=$this->mk_archive($pagesize);
					if($page){
						if($page!=1){
							$npage	=	$page;
							$page	=	$page-1;
							$spage	=	$page*$pagesize;
							$topage	=	$spage+$pagesize;
						}else{
							$npage	=	$page;
							$spage	=	$page;
							$topage	=	$pagesize;
						}
						$name		=	$spage."-".$topage;
						$this -> get_return("archive",$npage,"正在生成".$name."新闻");
					}else{
						$this -> get_return("ok",0,"全部生成完成");
					}
				}
			}
		}
		$this->yunset("type","all");
		$this->yuntpl(array('admin/admin_makenews'));
	}
	function makecache_action(){
		set_time_limit(200);
		$cache	=	$_POST['cache'];
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);

		if(@in_array("1",$cache)){
			$makecache=$cacheclass->city_cache("city.cache.php");
		}
		if(@in_array("2",$cache)){
			$makecache=$cacheclass->industry_cache("industry.cache.php");
		}
		if(@in_array("3",$cache)){
			$makecache=$cacheclass->job_cache("job.cache.php");
		}
		if(@in_array("4",$cache)){
			$makecache=$cacheclass->user_cache("user.cache.php");
		}
		if(@in_array("5",$cache)){
			$makecache=$cacheclass->com_cache("com.cache.php");
		}
		if(@in_array("6",$cache)){
			$makecache=$cacheclass->menu_cache("menu.cache.php");
		}
		if(@in_array("7",$cache)){
			$makecache=$this->del_dir("../data/templates_c",1);
			$makecache=$this->del_dir("../data/cache",1);
		}
		if(@in_array("8",$cache)){
			$makecache=$cacheclass->seo_cache("seo.cache.php");
		}
		if(@in_array("9",$cache)){
			$makecache=$cacheclass->domain_cache("domain_cache.php");
		}
		if(@in_array("10",$cache)){
			$makecache=$cacheclass->keyword_cache("keyword.cache.php");
		}
		if(@in_array("11",$cache)){
			$makecache=$cacheclass->link_cache("link.cache.php");
		}
		if(@in_array("12",$cache)){
			$makecache=$cacheclass->group_cache("group.cache.php");
		}
        if(@in_array('13',$cache)){
            $makecache=$cacheclass->desc_cache("desc.cache.php");
        }
		if(@in_array('14',$cache)){
			$adM = $this->MODEL('ad');
			$makecache=$adM->model_ad_arr();
        }
		if(@in_array('15',$cache)){
		    $makecache=$cacheclass->part_cache("part.cache.php");
		}
		if(@in_array('16',$cache)){
			$makecache=$cacheclass->redeem_cache("redeem.cache.php");
		}
		if(@in_array('17',$cache)){
			$makecache=$cacheclass->database_cache("dbstruct.cache.php");
		}
		if(@in_array('18',$cache)){
			$makecache=$cacheclass->emailconfig_cache("emailconfig.cache.php");
		}

		if(@in_array('19',$cache)){
			$makecache=$cacheclass->navmap_cache("navmap.cache.php");
		}
		if(@in_array('20',$cache)){
			$makecache=$cacheclass->ask_cache("ask.cache.php");
		}
		if(@in_array('21',$cache)){
			$makecache=$cacheclass->lt_cache("lt.cache.php");
		}
	 
		if(@in_array("23",$cache)){
			$makecache=$cacheclass->moblienav_cache("moblienav.cache.php");
		}
		if(@in_array("24",$cache)){
			$makecache=$cacheclass->introduce_cache("introduce.cache.php");
		}
		if($makecache){
			$this->ACT_layer_msg( "生成(ID:$makecache)成功！",9,"index.php?m=cache&c=cache",2,1);
		}else{
			$this->ACT_layer_msg( "生成(ID:$makecache)失败！",8,"index.php?m=cache&c=cache");
		}
       
        
	}
	function mk_newsclass(){
		if($_POST['value']==0){
			if($_POST['group']!="all" && $_POST['group']){
				$where['id']	=	$_POST['group'];
			}
			$rows=$this -> MODEL('article') -> getClass($where);
			if(is_array($rows['list'])){
				foreach($rows['list'] as $v){
					$val[$v['id']]=$v['name'];
				}
			}
		}else{
			$rows=$_POST['value'];
			if(is_array($rows)){
				foreach($rows as $k=>$va){
					if($nid==""){
						$nid=$k;
					}else{
						$val[$k]=$va;
					}
				}
			}
			$this->makenewsclass($nid);
		}
		return $val;
	}
	function mk_archive($pagesize){
		$articleM=$this->MODEL('article');
		if($_POST['value']==0){
			$stime=strtotime($_POST['stime']);
			$etime=strtotime($_POST['etime']);
			if($stime&&preg_match("/^\d*$/",$stime)){
				
				$where['datetime'][]	=	array('>=',$stime);
			}
			if($etime&&preg_match("/^\d*$/",$etime)){
				
				$where['datetime'][]	=	array('<=',$etime);
			}
			if($_POST['group']>0){
				
				$where['nid']			=	$_POST['group'];
			}
			if($_POST['startid']>0){
				
				$where['id'][]			=	array('>=',$_POST['startid']);
			}
			if($_POST['endid']>0){
				
				$where['id'][]			=	array('<=',$_POST['endid']);
			}
			
			$nlist=$articleM -> getList($where,array('field'=>"`id`,`datetime`"));

			$news_list	=	$nlist['list'];
			$allnum		=	count($news_list);
			$allpage	=	ceil(($allnum)/$pagesize);
			$i=1;
			foreach($news_list as $v){
				if(count($val[$i])<=$pagesize){
					$val[$i][$v['id']]=$v['datetime'];
				}else{
					$i++;
					$val[$i][$v['id']]=$v['datetime'];
				}
			}
			if($val&&is_array($val)){
				made_web("../data/plus/news.cache.php",ArrayToString($val),"newscache");
				$page=1;
			}
		}else{
			$page=$_POST['value'];
			include_once(PLUS_PATH."news.cache.php");
			if(is_array($newscache)){
				foreach($newscache as $k=>$va){
					if($k==$page){
                        $index=0;
						foreach($va as $key=>$value){
                            $NewsIDList[]=$key;
                        }
					}elseif($k>$page){
						$val[$k]=$va;
					}
				}
			}
			$where['id']		=	array('in',implode(',',$NewsIDList));
			
			$where['orderby']	=	array('id,desc');
			
			$nlist=$articleM -> getList($where,array('content'=>'1'));
			
            $news_list			=	$nlist['list'];
			
            foreach($news_list as $k1=>$v1){
                $this->articleshow($v1['id'],$v1['datetime'],$v1,$news_list[$k1+1],$news_list[$k1-1]);
            }
			$page=$page+1;
			if(!is_array($val)){$page=0;unlink("../data/plus/news.cache.php");}
		}
		return $page;
	}
	function get_return($type,$value,$msg){
		$data['type']	=	$type;
		$data['value']	=	$value;
		$data['msg']	=	$msg;
		echo json_encode($data);die;
	}
	function makenewsclass($nid){
		include_once(PLUS_PATH."group.cache.php");
		if($group_type[$nid]){
			$nids			=	$group_type[$nid];
			
			$nids[]			=	$nid;
			
			$where['nid']	=	array('in',pylode(',',$nids));
			
		}else{
			
			$where['nid']	=	$nid;
			
		}
		$newsnum=$this -> MODEL('article') -> getNum($where);
		$allpage=ceil($newsnum/20);
		$this->articleclass($nid,"../news/".$nid."/"."index.html",'index');
		for($i=1;$i<=$allpage;$i++){
			if($allpage>=$i){
                $fw=$this->articleclass($nid,"../news/".$nid."/"."$i.html",$i);
			}
		}
	}
    function webindex($path){
        global $phpyun;
		if($this->config['sy_jobdir']!=""){
			$jobclassurl=$this->config['sy_weburl']."/job/index.php?c=search&";
		}else{
			$jobclassurl=$this->config['sy_weburl']."/index.php?m=job&c=search&";
		}
		global $ModuleName;
		$ModuleName = 'index';
		$this->yunset("jobclassurl",$jobclassurl);
		$this->yunset("ishtml",'1');
		$this->yunset("tplindex",'1');
		$this->yunset("admincache",'1');
        $CacheM=$this->MODEL('cache');
        $CacheList=$CacheM->GetCache(array('job','city','com','user','hy'));
        $this->yunset($CacheList);
        $this->seo("index");
        //必须传参数$cache_id,否则多个文件的内容会重复
        $contect = $phpyun->fetch(TPL_PATH.$this->config['style'].'/index/index.htm','abc');

        $fp = fopen($path, "w");
        $fw=fwrite($fp, $contect);
        fclose($fp);
        return $fw;
    }
    //通过smarty缓存直接生成静态文件
    function articleshow($id,$datetime,$news,$news_next,$news_last){
		
		$M=$this->MODEL('article');

		if(!empty($news_last)){
			if($this->config['sy_news_rewrite']=="2"){
				$news_last["url"]=$this->config['sy_weburl']."/news/".date("Ymd",$news_last["datetime"])."/".$news_last['id'].".html";
			}else{
				$news_last["url"]= Url('article',array('c'=>'show',"id"=>$news_last['id']),"1");
			}
		}
		if(!empty($news_next)){
			if($this->config['sy_news_rewrite']=="2"){
				$news_next["url"]=$this->config['sy_weburl']."/news/".date("Ymd",$news_next["datetime"])."/".$news_next['id'].".html";
			}else{
				$news_next["url"]= Url('article',array('c'=>'show',"id"=>$news_next['id']),"1");
			}
		}
		$class=$M->getGroup(array("id"=>$news['nid']));
		//相关文章,按照关键字获取
		if($news["keyword"]!=""){

			$keyarr = @explode(",",$news["keyword"]);
			if(is_array($keyarr) && !empty($keyarr)){
				$where['PHPYUNBTWSTART_A']	=	'' ;
				foreach($keyarr as $key=>$value){
					$where['keyword'][]		=	array('like',$value,'OR') ;
				}
				$where['PHPYUNBTWEND_A']	=	'' ;
				$where['id']				=	array('<>',$id);
				$where['orderby']			=	'id,desc';
				$where['limit']				=	6;
				
				$aboutlist	=	$M->getList($where);//相关文章
				$about		=	$aboutlist['list'];
				
				if(is_array($about)){
					foreach($about as $k=>$v){
						if($this->config['sy_news_rewrite']=="2"){
							
							$about[$k]["url"]	=	$this->config['sy_weburl']."/news/".date("Ymd",$v["datetime"])."/".$v['id'].".html";
							
						}else{
							$about[$k]["url"]	= 	Url('article',array('c'=>'show',"id"=>$v['id']),"1"); 
						}
					}
				}
			}
		}
		$info					=	$news;
		$data['news_title']		=	$news['title'];//新闻名称
		$data['news_keyword']	=	$news['keyword'];//描述
		$data['news_class']		=	$class['name'];//新闻类别
		$data['news_desc']		=	$this->GET_content_desc($news['description']);//描述
		$this->data				=	$data;
		$info["news_class"]		=	$class['name'];
		$info["last"]			=	$news_last;
		$info["next"]			=	$news_next;
		$info["like"]			=	$about;
		$info['content']		=	htmlspecialchars_decode($info['content']);
		$this->yunset("Info",$info);
		$this->yunset("ishtml",'1');
		$this->seo("news_article");
        global $phpyun;
        //必须传参数$cache_id,否则多个文件的内容会重复
		if(!is_dir(APP_PATH.'news/'.date('Ymd',$news['datetime']).'/'))@mkdir(APP_PATH.'news/'.date('Ymd',$news['datetime']).'/');
		@chmod(APP_PATH.'news/'.date('Ymd',$news['datetime']).'/',0777);
        $contect = $phpyun->fetch(TPL_PATH.$this->config['style'].'/article/show.htm',$id);
        $fp = fopen(APP_PATH.'news/'.date('Ymd',$news['datetime']).'/'.$id.'.html', "w");
        fwrite($fp, $contect);
        fclose($fp);
	}
    //通过smarty缓存直接生成静态文件
    function descriptionshow($id,$path){
		
		$M=$this->MODEL('description');
		$row=$M->getDes(array("id"=>$id));
		$top="";$footer="";
		if($row['top_tpl']==1){
            $top=APP_PATH."/app/template/".$this->config['style']."/header.htm";
		}else if($row['top_tpl']==3){
            $top=APP_PATH."/app/template/".$row['top_tpl_dir'].'.htm';
		}
		if($row['footer_tpl']==1){
            $footer=APP_PATH."/app/template/".$this->config['style']."/footer.htm";
		}else if($row['footer_tpl']==3){
            $footer=APP_PATH."/app/template/".$row['footer_tpl_dir'].'.htm';
		}
		$seo['title']		=	$row['title'];
		$seo['keywords']	=	$row['keyword'];
		$seo['description']	=	$row['descs'];
		$this->yunset("seo",$seo);
		$this->yunset("name",$row['name']);
		$this->yunset("content",$row['content']);
		$this->header_desc($row['title'],$row['keyword'],$row['descs']);
		$make=APP_PATH."/app/template/".$this->config['style']."/make.htm";
		$make_top=APP_PATH."/app/template/".$this->config['style']."/make_top.htm";
		//$this->yuntpl(array($make_top,$top,$make,$footer));

        global $phpyun;
        //必须传参数$cache_id,否则多个文件的内容会重复
        if($make_top){
            $contect = $phpyun->fetch($make_top,$id);
        }
        if($top){
            $contect .= $phpyun->fetch($top,$id);
        }
        if($make){
            $contect .= $phpyun->fetch($make,$id);
        }
        if($footer){
            $contect .= $phpyun->fetch($footer,$id);
        }
        $DirList=explode('/',$path);
        foreach($DirList as $k=>$v){
            $Dir.=$v.'/';
            if(!is_dir(APP_PATH.$Dir)&&!strstr($Dir,'.html')){
                mkdir(APP_PATH.$Dir,0777);
            }
        }
        $fp = fopen(APP_PATH.$path, "w");
        fwrite($fp, $contect);
        fclose($fp);
	}
  function articleindex($path){
	$this->seo("news");
		
	global $phpyun;//必须传参数$cache_id,否则多个文件的内容会重复
	$this->yunset("ishtml",'1');
    $contect = $phpyun->fetch(APP_PATH."/app/template/".$this->config['style']."/article/index.htm");
    $path=$this->format_url($path);
    $DirList=explode('/',$path);
    foreach($DirList as $k=>$v){
      $Dir.=$v.'/';
      if(!is_dir(APP_PATH.$Dir)&&!strstr($Dir,'.html')){
        mkdir(APP_PATH.$Dir,0777);
      }
    }
    $fp = fopen(APP_PATH.$path, "w");
    $fw=fwrite($fp, $contect);
    fclose($fp);
    return $fw;
	}
  function articleclass($id,$path,$page){
		global $phpyun;
        $_GET['nid']	=	$id;
        $_GET['page']	=	$page;
        $_GET['cache']	=	'1';
        $M=$this -> MODEL('article');
        $class=$M -> getGroup(array('id'=>(int)$_GET['nid']),array('field'=>"`name`"));
        $this->yunset("classname",$class['name']);
        $data['news_class']=$class['name'];
        $this->data=$data;
        $this->seo("newslist");
		$this->yunset("nid",$id);
		$this->yunset("ishtml",'1');
        
        //必须传参数$cache_id,否则多个文件的内容会重复
        $contect = $phpyun->fetch(APP_PATH."/app/template/".$this->config['style']."/article/list.htm",'articleclass-nid'.$id.'-page'.$page);
        $path=$this->format_url($path);
        $DirList=explode('/',$path);
        foreach($DirList as $k=>$v){
          $Dir.=$v.'/';
          if(!is_dir(APP_PATH.$Dir)&&!strstr($Dir,'.html')){
            mkdir(APP_PATH.$Dir,0777);
          }
        }
        $fp = fopen(APP_PATH.$path, "w");
        $fw=fwrite($fp, $contect);
        fclose($fp);
        return $fw;
	}
  function articlelist($id,$path){
    
    $_GET['nid']=$id;
		$M=$this->MODEL('article');
    $group=$M->GetNewsGroupList(array('keyid'=>'0'),array('field'=>"`id`,`name`"));
    if(is_array($group)){
      foreach($group as $k=>$v){
        if($this->config[sy_news_rewrite]=="2"){
					$group[$k]['url']=$this->config['sy_weburl']."/news/".$v['id']."/";
				}else{
					$group[$k]['url']= Url("article",array('c'=>'list',"id"=>$v[id]),"1");
				}
      }
    }
    $this->yunset("group",$group);
    $this->seo("newslist");
	$this->yunset("ishtml",'1');
    global $phpyun;//必须传参数$cache_id,否则多个文件的内容会重复
    $contect = $phpyun->fetch(APP_PATH."/app/template/".$this->config['style']."/article/list.htm");
    $path=$this->format_url($path);
    $DirList=explode('/',$path);
    foreach($DirList as $k=>$v){
      $Dir.=$v.'/';
      if(!is_dir(APP_PATH.$Dir)&&!strstr($Dir,'.html')){
        mkdir(APP_PATH.$Dir,0777);
      }
    }
    $fp = fopen(APP_PATH.$path, "w");
    $fw=fwrite($fp, $contect);
    fclose($fp);
    return $fw;
	}
  function format_url($srcurl, $baseurl='') {
    $SplitList=explode('/',$srcurl);
    foreach($SplitList as $v){
      switch($v){
        case '..':$URL.='';break;
        case '.':break;
        default:$URL.='/'.$v;break;
      }
    }
    return $URL;
        /*$srcinfo = parse_url($srcurl);
        if(isset($srcinfo['scheme'])) {
            return $srcurl;
        }
        $baseinfo = parse_url($baseurl);
        $url = $baseinfo['scheme'].'://'.$baseinfo['host'];
        if(substr($srcinfo['path'], 0, 1) == '/') {
            $path = $srcinfo['path'];
        }else{
            $path = dirname($baseinfo['path']).'/'.$srcinfo['path'];
        }
        $rst = array();
        $path_array = explode('/', $path);
        if(!$path_array[0]) {
            $rst[] = '';
        }
        foreach ($path_array AS $key => $dir) {
            if ($dir == '..') {
                if (end($rst) == '..') {
                    $rst[] = '..';
                }elseif(!array_pop($rst)) {
                    $rst[] = '..';
                }
            }elseif($dir && $dir != '.') {
                $rst[] = $dir;
            }
        }
        if(!end($path_array)) {
            $rst[] = '';
        }
        $url .= implode('/', $rst);
        return str_replace('\\', '/', $url);  */
    }
}
?>