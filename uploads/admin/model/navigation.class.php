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
class navigation_controller extends adminCommon{
	function index_action(){
		$NavigationM	=	$this->MODEL('navigation');
		if ($_GET['type']!=""){
			$where['type']			=	$_GET['type'];
			$urlarr['type']			=	$_GET['type'];
		}
		if($_GET['eject']){
			if($_GET['eject']=='2'){
				$where['eject']		=	'0';
			}else{
				$where['eject']		=	intval($_GET['eject']);
			}
			$urlarr['eject']		=	$_GET['eject'];
		}

		if($_GET['display']){
			if($_GET['display']=='2'){
				$where['display']	=	'0';
			}else{
				$where['display']	=	intval($_GET['display']);
			}
			$urlarr['display']		=	$_GET['display'];
		}
		if ($_GET['nid']!=""){
			$where['nid']			=	$_GET['nid'];
			$urlarr['nid']			=	$_GET['nid'];
		}
		if($_GET['news_search']){

			if (trim($_GET['keyword'])){
				$where['name']		=	array('like',trim($_GET['keyword']));
				$urlarr['keyword']	=	$_GET['keyword'];
			}
			$urlarr['news_search']	=	$_GET['news_search'];
		}
		$urlarr['page']	=	"{{page}}";
		$pageurl		=	Url($_GET['m'],$urlarr,'admin');
		$pageM			=	$this->MODEL('page');
		$pages			=	$pageM->pageList('navigation',$where,$pageurl,$_GET['page']);
		if($pages['total']>0){
			if($_GET['order']){
				$where['orderby']	=	$_GET['t'].','.$_GET['order'];
				$urlarr['order']	=	$_GET['order'];
				$urlarr['t']		=	$_GET['t'];
			}else{

				// $where['orderby']	=	'sort';

				//按id降序
			    $where['orderby'] = 'id';
			}

			$where['limit']			=	$pages['limit'];
			$nav	=	$NavigationM->getNavList($where);
		}
		$navinfo	=	$NavigationM->getNavTypeList();
		$nclass		=	array();
		foreach($navinfo as $key=>$value){
			foreach($nav as $k=>$v){
				if($value['id']==$v['nid']){
					$nav[$k]['typename']	=	$value['typename'];
				}
			}
			$nclass[$value['id']]	=	$value['typename'];
		}
		$this->yunset("nclass",$nclass);
		$this->yunset("get_type", $_GET);
		$this->yunset("nav",$nav);
		$this->yuntpl(array('admin/admin_navigation_list'));
	}

	//导航列表
	function add_action(){
		//类别查询
		$NavigationM	=	$this->MODEL('navigation');
		$type			=	$NavigationM->getNavTypeList();
	    $this->yunset("type",$type);
		//更新操作
		if($_GET['id']){
			$group		=	$NavigationM->getNav(array('id'=>$_GET['id']));
			if($group['url']=='article/'){
			    $this->yunset("msg",'职场资讯设置伪静态地址，需关闭分站<a href="index.php?m=cache&c=news">生成新闻首页</a>后才能设置');
			}
			$this->yunset("types",$group);
			$this->yunset("lasturl",$_SERVER['HTTP_REFERER']);
		}
       $this->yuntpl(array('admin/admin_navigation_add'));
	}

	//导航更新添加
	function save_action(){
		$NavigationM	=	$this->MODEL('navigation');
		//更新
		$postData	=	array(
			'nid'		=>	$_POST['nid'],
			'eject'		=>	$_POST['eject'],
			'display'	=>	$_POST['display'],
			'name'		=>	$_POST['name'],
			'url'		=>	str_replace("amp;","",$_POST['url']),
			'furl'		=>	$_POST['furl'],
			'sort'		=>	$_POST['sort'],
			'color'		=>	$_POST['color'],
			'model'		=>	$_POST['model'],
			'bold'		=>	$_POST['bold'],
			'type'		=>	$_POST['type'],
		);
		$lasturl	=	str_replace("&amp;","&",$_POST['lasturl']);
		if ($_FILES['file']) {

		    $postData['file']  =  $_FILES['file'];
		}
		if($_POST['update']){
			$nbid	=	$NavigationM->upNav($postData,array('id'=>$_POST['id']));
			// 图片上传失败提示
			if (isset($nbid['msg'])){
			    $this->ACT_layer_msg($nbid['msg'],8,$lasturl);
			}
			$this->cache_action();
			
			isset($nbid)?$this->ACT_layer_msg( "网站导航(ID:".$_POST['id'].")更新成功！",9,$lasturl,2,1):$this->ACT_layer_msg( "更新失败！",8,$lasturl);
		}
		//添加
	    if($_POST['add']){
			$nav	=	$NavigationM->getNav(array('name'=>$_POST['name'],'nid'=>$_POST['nid']));
			if($nav){
				$this->ACT_layer_msg( "已经存在此导航！",8,$lasturl);
			}else{
				$nbid	=	$NavigationM->addNav($postData);
				// 图片上传失败提示
				if (isset($nbid['msg'])){
				    $this->ACT_layer_msg($nbid['msg'],8,$lasturl);
				}
				$this->cache_action();
				isset($nbid)?$this->ACT_layer_msg( "网站导航(ID:".$nbid.")添加成功！",9,$lasturl,2,1):$this->ACT_layer_msg( "添加失败！",8,$lasturl);
			}
		}
	}

	//查询类别
	function group_action(){
		$NavigationM	=	$this->MODEL('navigation');
		$type			=	$NavigationM->getNavTypeList(array('orderby'=>'id'));
		$this->yunset("type",$type);
		//调用模板
		$this->yuntpl(array('admin/admin_navigation_type'));
	}
	//添加类别
	function addtype_action(){
		//添加
		$NavigationM	=	$this->MODEL('navigation');
        if($_POST['sub']){
			if($_POST['typename']!=""){
				$navtype	=	$NavigationM->getNavType(array('typename'=>$_POST['typename']));
				if($navtype){
					$this->ACT_layer_msg( "已经存在此类别！",8,$_SERVER['HTTP_REFERER']);
				}else{
					$nbid	=	$NavigationM->addNavType(array('typename'=>$_POST['typename']));
					$this->cache_action();
					isset($nbid)?$this->ACT_layer_msg( "导航类别(ID:".$nbid.")添加成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "添加失败！",9,$_SERVER['HTTP_REFERER']);
				}
			}else{
				$this->ACT_layer_msg( "请正确填写你的类别！",8,$_SERVER['HTTP_REFERER']);
		    }
        }

	    //更新
	    if($_POST['update']){
			$update		=	$NavigationM->upNavType(array('id'=>$_POST['id']),array('typename'=>$_POST['typename']));
		    $this->cache_action();
		    isset($update)?$this->ACT_layer_msg( "导航类别(ID:".$_POST['id'].")更新成功！",9,$_SERVER['HTTP_REFERER'],2,1):$this->ACT_layer_msg( "更新失败！",8,$_SERVER['HTTP_REFERER']);
	    }
	}

	//删除导航
	function del_action(){
		$this->check_token();
		$NavigationM						=	$this->MODEL('navigation');
		$DescriptionM						=	$this->MODEL('description');
		$ArticleM							=	$this->MODEL('article');
		 //批量删除
	    if($_GET['del']){
	    	$del=$_GET['del'];
	    	if(is_array($del)){
				//更新单页面和新闻类别
				$where							=	array();
				$where['id']					=	array('in', pylode(',', $del));
				$where['PHPYUNBTWSTART']		=	'';
				$where['desc']					=	array('<>','');
				$where['news']					=	array('<>','','OR');
				$where['PHPYUNBTWEND']			=	'';
				$rows							=	$NavigationM->getNavList($where);
				if(is_array($rows)){
					foreach($rows as $v){
						if($v['desc']!=""){
							$desc[]				=	$v['desc'];
						}
						if($v['news']!=""){
							$news[]				=	$v['news'];
						}
						$DescriptionM->upDes(array('is_menu'=>'0'),array('id' => array('in', pylode(',', $desc))));
						$ArticleM->updGroup(array('id' => array('in', pylode(',', $news))),array('is_menu'=>'0'));
					}
				}
				$NavigationM->delNav(array('id' => array('in', pylode(',', $del))));
				$this->cache_action();
				$this->layer_msg( "导航(ID:".@implode(',',$_GET['del']).")删除成功！",9,1,$_SERVER['HTTP_REFERER']);
	    	}else{
				$this->layer_msg( "请选择您要删除的信息！",8,1,$_SERVER['HTTP_REFERER']);
	    	}
	    }
		//删除
	    if(isset($_GET['id'])){
			//更新单页面和新闻类别
			$row	=	$NavigationM->getNav(array('id'=>$_GET['id']));
			if($row['desc']!="")
			{
				$DescriptionM->upDes(array('is_menu'=>'0'),array('id'=>$row['desc']));
			}
			if($row['news']!="")
			{
				$ArticleM->updGroup(array('id'=>$row['news']),array('is_menu'=>'0'));
			}
			$result	=	$NavigationM->delNav(array('id'=>$_GET['id']));
			$this->cache_action();
			isset($result)?$this->layer_msg('导航(ID:'.$_GET['id'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		}
	}

    //删除导航类别
	function deltype_action(){
		$this->check_token();
		$NavigationM						=	$this->MODEL('navigation');
		$DescriptionM						=	$this->MODEL('description');
		$ArticleM							=	$this->MODEL('article');
		if(isset($_GET['id'])){
			$result							=	$NavigationM->delNavType(array('id'=>$_GET['id']));
			$where							=	array();
			$where['nid']					=	$_GET['id'];
			$where['PHPYUNBTWSTART']		=	'';
			$where['desc']					=	array('<>','');
			$where['news']					=	array('<>','','OR');
			$where['PHPYUNBTWEND']			=	'';
			$rows							=	$NavigationM->getNavList($where);
			if(is_array($rows)){
				foreach($rows as $v){
					if($v['desc']!=""){
						$desc[]				=	$v['desc'];
					}
					if($v['news']!=""){
						$news[]				=	$v['news'];
					}
				}
				$DescriptionM->upDes(array('is_menu'=>'0'),array('id' => array('in', pylode(',', $desc))));
				$ArticleM->updGroup(array('id' => array('in', pylode(',', $news))),array('is_menu'=>'0'));
			}
			$NavigationM->delNav(array('nid'=>$_GET['id']));//删除导航
			$this->cache_action();
			isset($result)?$this->layer_msg('导航类别(ID:'.$_GET['id'].')删除成功！',9,0,$_SERVER['HTTP_REFERER']):$this->layer_msg('删除失败！',8,0,$_SERVER['HTTP_REFERER']);
		}else{
			$this->ACT_layer_msg("非法操作！",8,$_SERVER['HTTP_REFERER']);
		}
	}
	function cache_action(){
		include(LIB_PATH."cache.class.php");
		$cacheclass		=	new cache(PLUS_PATH,$this->obj);
		$makecache		=	$cacheclass->menu_cache("menu.cache.php");
	}
	function ajax_action(){
		$NavigationM	=	$this->MODEL('navigation');
		if($_POST['name']){
			$NavigationM->upNavType(array('id'=>$_POST['id']),array('typename'=>$_POST['name']));
			$this->MODEL('log')->addAdminLog("导航类别(ID:".$_POST['id'].")修改成功");
		}
		$this->cache_action();
		echo '1';die;
	}
	function nav_xianshi_action(){
	    $this->check_token();
		$NavigationM	=	$this->MODEL('navigation');
		$nid	=	$NavigationM->upNav(array(''.$_GET['type'].''=>intval($_GET['rec'])),array('id'=>intval($_GET['id'])));
	    if ($_GET['type']=='display'){
	        $this->MODEL('log')->addAdminLog("导航是否显示(ID:".$_GET['id'].")设置成功！");
	    }else{
	        $this->MODEL('log')->addAdminLog("导航是否新窗口打开(ID:".$_GET['id'].")设置成功！");
	    }
		$this->cache_action();
	    echo $nid?1:0;die;
	}
}
?>