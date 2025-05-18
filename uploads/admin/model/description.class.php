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
class description_controller extends adminCommon{
	function index_action(){
		if($_GET['order']){
			if($_GET['order']=="desc"){
				$where['orderby']	=	$_GET['t'].",desc";
			}else{
				$where['orderby']	=	$_GET['t'].",asc";
			}
		}else{
			$where['orderby']	=	"id,desc";
		} 
		if ($_GET['is_type']!=""){
		    $where['is_type']	=	$_GET['is_type'];
		    $urlarr['is_type']	=	$_GET['is_type'];
		}
		if (trim($_GET['keyword'])){
		    $where['name']		=	array('like',trim($_GET['keyword']));
		    $urlarr['keyword']	=	$_GET['keyword'];
		}

		$urlarr['page']	=	'{{page}}';
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		//提取分页
		$pageM			=	$this  -> MODEL('page');
		$pages			=	$pageM -> pageList('description',$where,$pageurl,$_GET['page']);

		if($pages['total'] > 0){
		    //limit order 只有在列表查询时才需要
		    if($_GET['order']){
				if($_GET['order']=="desc"){
					$where['orderby']	=	$_GET['t'].",desc";
				}else{
					$where['orderby']	=	$_GET['t'].",asc";
				}
			}else{
				$where['orderby']	=	"id,desc";
			}
		    $where['limit']				=	$pages['limit'];
		    $descM	=	$this 	-> MODEL('description');
		    $List	=	$descM	-> getDesList($where);
		    
		    $this -> yunset('descrows',$List);
		}

		$is_type	=	array('0'=>'外部链接','1'=>'自定义页面','2'=>'站内链接');
		$this	->	yunset('is_type',$is_type);
		$this	->	yuntpl(array('admin/admin_description'));
	}
	//添加
	function add_action(){
		$descM	=	$this 	-> MODEL('description');
		if($_GET['id']){
			$descrow			=	$descM	->	getDes(array('id'=>$_GET['id']));
		}else{
			$descrow['sort']	=	rand(1,50);
		}
		$this	->	yunset('lasturl',$_SERVER['HTTP_REFERER']);
		$this	->	yunset("descrow",$descrow);
		$class	=	$descM	->	getDesClassList();
		$this	->	yunset("class",$class);
		$this	->	yuntpl(array('admin/admin_description_add'));
	}
	//保存
	function save_action(){
	    $_POST=$this->post_trim($_POST);
		$addData['name']	=	$_POST['name'];
		if($_POST['url'] && $_POST['is_type']==1){
			//验证URL
			$url 		=	stripslashes($_POST['url']);
			$url 		=	str_replace("../","",$url);
			$url 		=	str_replace("./","",$url);
			$p_delfiles =	path_tidy($url);
			if($p_delfiles!=$url){
				$this	->	ACT_layer_msg("无效的文件名！",8,$_SERVER['HTTP_REFERER']);
			}
			$urlArr	=	explode('/',$url);
			foreach($urlArr as $v){
				if(!preg_match("/^[".chr(0xa1)."-".chr(0xff)." |a-z|0-9|A-Z|\@\.\_\]\[\!]+$/",$v) && $v!='') {

					$this	->	ACT_layer_msg("无效的文件名！",8,$_SERVER['HTTP_REFERER']);
				}
			}
			$urlarr	=	explode(".",$url);
			if(end($urlarr)!="html"){
				$this	->	ACT_layer_msg("请正确填写静态网页名称！",8,$_SERVER['HTTP_REFERER']);
			}
			if(substr($url,0,1)=="/"){
				$url	=	substr($url,1);
			}
		}else{
			$url = $_POST['url'];
		}
		$addData['nid']				=	$_POST['nid'];
		$addData['url']				=	$url;
		$addData['title']			=	$_POST['title'];
		$addData['keyword']			=	$_POST['keyword'];
		$addData['descs']			=	$_POST['description'];
		$addData['top_tpl']			=	$_POST['top_tpl'];
		$addData['top_tpl_dir']		=	$_POST['top_tpl_dir'];
		$addData['footer_tpl']		=	$_POST['footer_tpl'];
		$addData['footer_tpl_dir']	=	$_POST['footer_tpl_dir'];
		$addData['ctime']			=	time();
		$addData['sort']			=	$_POST['sort'];
		$addData['is_nav']			=	$_POST['is_nav'];
		$addData['is_type']			=	$_POST['is_type'];
		$addData['content'] 		=	str_replace(array("&amp;","background-color:#ffffff","background-color:#fff","white-space:nowrap;","<img "),array("&",'','','','<img style="max-width:100%" '),$_POST["content"]);
		$descM	=	$this 	-> MODEL('description');

		if(!$_POST['id']){
			$descid	=	$descM	->	addDes($addData);
			$ids=$descid;
			$alert="添加";
		}else{
			$row	=	$descM	->	getDes(array('id'=>$_POST['id']));
			if($row['is_menu']=="1"){
				$url			=	str_replace("amp;","",$url);
				$addNdata['url']	=	$url;
				$addNdata['furl']	=	$url;
				$addNdata['name']	=	$_POST['name'];
				$navM	=	$this 	-> MODEL('navigation');
				$navM	->	upNav($addNData,array('desc'=>$_POST['id']));
				$this	->	menu_cache_action();
			}
			$descid	=	$descM	->	upDes($addData,array('id'=>$_POST['id']));
			$ids=$_POST['id'];
			$alert="更新";
		}

		if($descid){
			$this	->	cache_action();
			if($_POST['is_type']==1){
                $this	->	descriptionshow($ids,$url);
			}
			$this	->	ACT_layer_msg("单页面(ID:".$ids.")".$alert."成功！",9,"index.php?m=description",2,1);
		}else{
			$this	->	ACT_layer_msg($alert."失败！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	//删除
	function del_action(){
		if(is_array($_POST['del'])){
			$linkid			=	$_POST['del'];
			$data['type']	=	'all';
		}else{
			$this	->	check_token();
			$linkid			=	$_GET["id"];
			$data['type']	=	'one';
		}
		$descM	=	$this 	-> MODEL('description');
		$navM	=	$this 	-> MODEL('navigation');
		$return	=	$descM	->	delDes($linkid,$data);
		$navM	->	delNav(array('desc'=>array('in',pylode(',',$linkid))));

		$this	->	menu_cache_action();//更新导航缓存
		$this	->	cache_action();
		$this	->	layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER']);
	}
	//生成
	function make_action(){
		if($_GET['id']){
			$where['id']		=	$_GET['id'];
		}else{
			$where['is_type']	=	1;
		}
		$this	->	cache_action();
		$descM	=	$this 	-> MODEL('description');
		$rows	=	$descM	->	getDesList($where);
		if(is_array($rows)){
			foreach($rows as $row){
                $fw	=	$this	->	descriptionshow($row['id'],$row["url"]);
			}
		}
 		if($fw){
 			$this	->	layer_msg("单页面生成成功！",9,0,$_SERVER['HTTP_REFERER']);
 		}else{
 			$this	->	layer_msg("生成失败！",8,0,$_SERVER['HTTP_REFERER']);
 		}
	}
	
	
	function menu_cache_action(){//导航缓存
		include_once(LIB_PATH."cache.class.php");
		$cacheclass	=	new cache(PLUS_PATH,$this->obj);
		$makecache	=	$cacheclass	->	menu_cache("menu.cache.php");
	}
    //通过smarty缓存直接生成静态文件
    function descriptionshow($id,$path){
		
		$M		=	$this	->	MODEL('description');
		$row	=	$M	->	getDes(array("id"=>$id));
		$top	="";
		$footer	="";
		if($row['top_tpl']==1){
            $top	=	APP_PATH."/app/template/".$this->config['style']."/header.htm";
		}else if($row['top_tpl']==3){
            $top	=	APP_PATH."/app/template/".$row['top_tpl_dir'].".htm";
		}
		if($row['footer_tpl']==1){
            $footer	=	APP_PATH."/app/template/".$this->config['style']."/footer.htm";
		}else if($row['footer_tpl']==3){
            $footer	=	APP_PATH."/app/template/".$row['footer_tpl_dir'].".htm";
		}
		$seo['title']		=	$row['name'];
		$seo['keywords']	=	$row['keyword'];
		$seo['description']	=	$row['descs'];
		$this	->	yunset("seo",$seo);
		$this	->	yunset("name",$row['name']);
		$this	->	yunset("content",$row['content']);

		$this	->	header_desc($row['name']."-".$this->config['sy_webname'],$row['keyword'],$row['descs']);

		$make		=	APP_PATH."/app/template/".$this->config['style']."/make.htm";
		$make_top	=	APP_PATH."/app/template/".$this->config['style']."/make_top.htm";

        global $phpyun;
        //必须传参数$cache_id,否则多个文件的内容会重复

        if($make_top){
            $contect	=	$phpyun	->	fetch($make_top,$id);
        }
		
        if($top){
            $contect .=	$phpyun	->	fetch($top,$id);
        }
        if($make){
            $contect .= $phpyun	->	fetch($make,$id);
        }
        if($footer){
            $contect .= $phpyun	->	fetch($footer,$id);
        }
        $DirList	=	explode('/',$path);
        foreach($DirList as $k=>$v){
            $Dir.=$v.'/';
            if(!is_dir(APP_PATH.$Dir)&&!strstr($Dir,'.html')){
                mkdir(APP_PATH.$Dir,0777);
            }
        }
        $fp	=	fopen(APP_PATH.$path, "w");
        $fw	=	fwrite($fp, $contect);
        fclose($fp);
		
		return $fw;
	}
	function cache_action(){

		include_once(LIB_PATH."cache.class.php");
		$cacheclass	= new cache(PLUS_PATH,$this->obj);

		$makecache	=	$cacheclass	->	desc_cache("desc.cache.php");
	}
}
?>