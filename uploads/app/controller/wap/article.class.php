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
class article_controller extends common{
	function index_action(){
		$this->get_moblie();
		
		if ($_COOKIE['newc']){
		    $newc	=	explode(',', $_COOKIE['newc']);
		    $this->yunset('newc',$newc);
		    $this->yunset($this->MODEL('cache')->GetCache(array('group')));
		}
		$this->yunset("headertitle","职场资讯");
		$this->yunset('backurl','index.php');
		$this->seo("news");
		$this->yuntpl(array('wap/article'));
	}
	function show_action(){
		$this->get_moblie();
        $articleM	=	$this->MODEL('article');
		$id			=	(int)$_GET[id];
		$nwhere['id']=	$id;
		if($this->config['did']>0){
			$nwhere['PHPYUNBTWSTART_A']	=	'';
			$nwhere['did'][]	=	array('=',$this->config['did']);
			$nwhere['did'][]	=	array('=','-1','OR');
			$nwhere['PHPYUNBTWEND_A']	=	'';
		}
        $info		= 	$articleM->getInfo($nwhere,array('iscon'=>1));
		$this->yunset("Info",$info);
		$class		=	$articleM->getGroup(array("id"=>$info['nid']));
		if($info["keyword"]!=""){
			//分割关键字
			$keyarr 	= 	@explode(",",$info["keyword"]);
			if(is_array($keyarr) && !empty($keyarr)){
				$where['PHPYUNBTWSTART_A']	=	'' ;
				foreach($keyarr as $key=>$value){
					$where['keyword'][]		=	array('like',$value,'OR') ;
				}
				$where['PHPYUNBTWEND_A']	=	'' ;
				$where['id']				=	array('<>',$id);
				$where['orderby']			=	'id,desc';
				if($this->config['did']>0){
					$where['PHPYUNBTWSTART_B']	=	'';
					$where['did'][]	=	array('=',$this->config['did']);
					$where['did'][]	=	array('=','-1','OR');
					$where['PHPYUNBTWEND_B']	=	'';
				}
				$where['limit']				=	6;
				
				$aboutlist	=	$articleM->getList($where);//相关文章
				$about		=	$aboutlist['list'];
				
				if(is_array($about)){
					foreach($about as $k=>$v){
						if($this->config['sy_news_rewrite']=="2"){
							
							$about[$k]["url"]=$this->config['sy_weburl']."/article/".date("Ymd",$v["datetime"])."/".$v['id'].".html";
							
						}else{
							$about[$k]["url"]= Url("wap",array('c'=>'article','a'=>'show',"id"=>$v[id]),"1"); 
						}
					}
				}
			}
		}
		$this->yunset("about",$about);
		
		$data['news_class']		=	$class['name'];//新闻类别
		$data['news_title']		=	$info['title'];//新闻名称
		$data['news_keyword']	=	$info['keyword'];//描述  
		$description			=	$info['description']?$info['description']:$info['content'];
		$data['news_desc']		=	$this->GET_content_desc($description);//描述 
		$this->data				=	$data; 
		$this->seo("news_article");
		
		$this->yunset("headertitle","职场资讯");
		
		$this->yuntpl(array('wap/article_show'));
	}
	function channels_action(){
	    if ($_COOKIE['newc'] && $_COOKIE['oldc']){
		    $newc	=	explode(',', $_COOKIE['newc']);
		    $oldc	=	explode(',', $_COOKIE['oldc']);
		    $this->yunset('newc',$newc);
		    $this->yunset('oldc',$oldc);
		    $this->yunset($this->MODEL('cache')->GetCache(array('group')));
		}
	    $this->seo("news");
		
	    $this->yunset("headertitle","频道管理");
	    $this->yuntpl(array('wap/article_channels'));
	}
	function editchannels_action(){
	    if ($_POST['newc']){
	        $oldc	=	@pylode(',', $_POST['oldc']);
	        $newc	=	@pylode(',', $_POST['newc']);
	        $this->cookie->setcookie('oldc',$oldc,time()+86400);
	        $this->cookie->setcookie('newc',$newc,time()+86400);
	        echo Url('wap',array('c'=>'article'));die;
	    }else{
	        echo 1;die();
	    }
	}
	function GetHits_action() {
	    if($_GET['id']){
	        $articleM	=	$this->MODEL('article');
	        $articleM->upBase(array('id'=>(int)$_GET['id']),array('hits'=>array('+',1)));
			
	        $hits		=	$articleM->getInfo(array('id'=>(int)$_GET['id']),array('field'=>'hits'));
	       echo 'document.write('.$hits['hits'].')';
	    }
	}
}
?>