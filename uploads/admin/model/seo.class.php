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
class seo_controller extends adminCommon{
	function index_action(){
		$SeoM		=	$this->MODEL('seo');
		include (CONFIG_PATH."/db.data.php");
		$this->yunset("arr_data",$arr_data);
		$action		=	$_GET[action]?$_GET[action]:"index";
		$seolist	=	$SeoM->getSeoList(array('seomodel'=>$action));
		$this->yunset("get_type",$_GET);
		$this->yunset("seolist",$seolist);
		$this->yuntpl(array('admin/admin_list_seo'));
	}
	function SeoAdd_action(){
		$SeoM		=	$this->MODEL('seo');
		include(CONFIG_PATH."db.data.php"); 
		$this->yunset("arr_data",$arr_data);
		//提取分站内容
	    $cacheM  =	$this -> MODEL('cache');
	    $domain  =	$cacheM	-> GetCache('domain');
	    $this -> yunset('Dname', $domain['Dname']);
		if($_GET['id']){
			$info	=	$SeoM->getSeoInfo(array('id'=>$_GET['id']));
		    $this->yunset("info",$info);
		}
		$this->yuntpl(array('admin/admin_add_seo'));
	}
	function Save_action(){
		$SeoM		=	$this->MODEL('seo');
		$postData	=	array(
			'seoname'		=>	$_POST['seoname'],
			'ident'			=>	$_POST['ident'],
			'seomodel'		=>	$_POST['seomodel'],
			'title'			=>	$_POST['title'],
			'keywords'		=>	$_POST['keywords'],
			'php_url'		=>	$_POST['php_url'],
			'rewrite_url'	=>	$_POST['rewrite_url'],
			'description'	=>	$_POST['description'],
			'did'			=>	$_POST['did'],
		);
		if($_POST['update']){
			$SeoM->upSeo(array('id'=>$_POST['id']),$postData);
			$this->cache_action();
			$msg	=	"SEO 修改成功！";
		}elseif($_POST['add']){
			$postData['time']	=	time();
			$SeoM->addSeo($postData);
			$this->cache_action();
			$msg	=	"SEO 添加成功！";
		}
		$this->ACT_layer_msg( $msg,9,"index.php?m=seo&action=".$seomodel,2,1);
		$this->yuntpl(array('admin/admin_add_seo'));
	}

	function getseo_action(){
		include(PLUS_PATH."seo.cache.php");
		//$this->get_apache_url($seo);
		//$this->yuntpl(array('admin/admin_get_seo'));
	}
	function del_action(){
		$SeoM	=	$this->MODEL('seo');
		if($_GET['del']){
			$this->check_token();
			$id	=	$SeoM->delSeo(array('id'=>$_GET['del']));
			if($id){
				$this->cache_action();
				$this->layer_msg('SEO(ID:'.$del.')删除成功！',9,$layer_status,$_SERVER['HTTP_REFERER']);
			}else{
				$this->layer_msg('SEO(ID:'.$del.')删除失败！',8,$layer_status,$_SERVER['HTTP_REFERER']);
			}
		}

	}
	function cache_action(){
		include(LIB_PATH."cache.class.php");
		$cacheclass	=	new cache(PLUS_PATH,$this->obj);
		$makecache	=	$cacheclass->seo_cache("seo.cache.php");
	}

}