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
class model_config_controller extends adminCommon{
	function index_action(){
		$ConfigM		=	$this->MODEL('config');
		include(CONFIG_PATH."db.data.php");
		$modelconfig 	=	$arr_data['modelconfig'];
		$config			=	$ConfigM->getList();
        foreach($config['list'] as $v){
            $config_new[$v['name']]		=	$v['config'];
        }
		foreach($modelconfig as $key=>$value){
			$newModel[$key]['value'] 	= 	$value;
			$newModel[$key]['web']		= 	$config_new['sy_'.$key.'_web'];
			$newModel[$key]['ssl'] 		= 	$config_new['sy_'.$key.'ssl'];
			$newModel[$key]['domain'] 	= 	$config_new['sy_'.$key.'domain'];
			$newModel[$key]['dir'] 		= 	$config_new['sy_'.$key.'dir'];
		}
        $this->yunset('newModel',$newModel);
		$this->yuntpl(array('admin/admin_model_config'));
	}
	function save_action(){
		$NavigationM	=	$this -> MODEL('navigation');
		$ConfigM		=	$this -> MODEL('config');
 		if($_POST["config"]){

		    unset($_POST["config"]);
			include(CONFIG_PATH."db.data.php");
			$modelKey	=	array_keys($arr_data['modelconfig']);
			foreach($modelKey as $key=>$value){
				if($_POST['sy_'.$value.'_web']=='1'){
					$setSql['display']	=	'1';
				}else{
					$setSql['display']	=	'0';
				}
				$NavigationM->upNav($setSql,array('config'=>$value));
				if(!$_POST['sy_'.$value.'ssl'] || $_POST['sy_'.$value.'domain']==''){
					$_POST['sy_'.$value.'ssl']	=	'0';
				}
			}
		    foreach($_POST as $key=>$v){
				$config	=	$ConfigM -> getNum(array('name'=>$key));
			    if($config>0){
				    $ConfigM -> upInfo(array('name'=>$key),array('config'=>$v));
			  	}else{
					$ConfigM -> addInfo(array('name'=>$key,'config'=>$v));
				}
			}
			$this -> navcache();
			$this -> web_config();
			$this->ACT_layer_msg("模块设置修改成功！",9,"index.php?m=model_config",2,1);
		}
	}
	function setnav_action(){
		$NavigationM	=	$this -> MODEL('navigation');
 		if($_GET["config"]){
			$type	=	$NavigationM -> getNavTypeList();
			$nav	=	$NavigationM -> getNav(array('config'=>$_GET['config']));
			if(!$nav){
				$nav	=	array('name'=>$_GET['name'],'config'=>$_GET["config"],'nid'=>'1');
			}
			$this->yunset("type",$type);
			$this->yunset('types',$nav);
			$this->yuntpl(array('admin/admin_model_config_nav'));
		}
		if($_POST['config']){
			$postData	=	array(
				'nid'		=>	$_POST['nid'],
				'eject'		=>	$_POST['eject'],
				'display'	=>	$_POST['display'],
				'name'		=>	$_POST['name'],
				'url'		=>	$this->config['sy_'.$_POST['config'].'dir'],
				'sort'		=>	$_POST['sort'],
				'model'		=>	$_POST['model'],
				'bold'		=>	$_POST['bold'],
				'type'		=>	'1',
				'config'	=>	$_POST['config'],
			);
			if($_POST['id']){
				$nbid	=	$NavigationM -> upNav($postData,array('id'=>$_POST['id']));
				$this->navcache();
			}else{
				$nbid	=	$NavigationM -> addNav($postData);
				$this -> navcache();
			}
			$this->layer_msg('导航设置成功！',9);
			
		}
	}
	function setseo_action(){
		$SeoM	=	$this -> MODEL('seo');
 		if($_GET["config"]){
			include(CONFIG_PATH."db.data.php"); 
			$this->yunset("arr_data",$arr_data);
			//提取分站内容
	        $cacheM	=	$this -> MODEL('cache');
	        $domain	=	$cacheM	-> GetCache('domain');
	        $this->yunset('Dname', $domain['Dname']);
			$seo	=	$SeoM->getSeoList(array('seomodel'=>$_GET['config']));
			$this->yunset('seo',$seo);
			$this->yuntpl(array('admin/admin_model_config_seo'));
		}
		if($_POST['id']){
			$postData	=	array(
				'seoname'		=>	$_POST['seoname'],
				'ident'			=>	$_POST['ident'],
				'did'			=>	$_POST['did'],
				'title'			=>	$_POST['title'],
				'keywords'		=>	$_POST['keywords'],
				'description'	=>	$_POST['description'],
				'php_url'		=>	$_POST['php_url'],
				'rewrite_url'	=>	$_POST['rewrite_url'],
			);
			$nbid	=	$SeoM -> upSeo(array('id'=>$_POST['id']),$postData);
			$this -> seocache();
			
			$this -> layer_msg('SEO设置成功！',9);
		}
	}
	function getseo_action(){
		$SeoM	=	$this -> MODEL('seo');
		if($_POST['id']){
			$seo	=	$SeoM -> getSeoInfo(array('id'=>$_POST['id']));
			$data['seoname']		=	$seo['seoname'];
			$data['ident']			= 	$seo['ident'];
			$data['rewrite_url'] 	= 	$seo['rewrite_url'];
			$data['php_url'] 		= 	$seo['php_url'];
			$data['title'] 			= 	$seo['title'];
			$data['keywords'] 		= 	$seo['keywords'];
			$data['description'] 	= 	$seo['description'];
			$data['did'] 			= 	$seo['did'];
			
			echo json_encode($data);
		}
	}
	function  navcache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass	=	new cache(PLUS_PATH,$this->obj);
		$makecache	=	$cacheclass -> menu_cache("menu.cache.php");
	}
	function  seocache(){
		include(LIB_PATH."cache.class.php");
		$cacheclass	=	new cache(PLUS_PATH,$this->obj);
		$makecache	=	$cacheclass -> seo_cache("seo.cache.php");
	}
}

?>