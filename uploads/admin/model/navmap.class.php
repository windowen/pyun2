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
class navmap_controller extends adminCommon{
	function set_search(){
		$search_list[]=array("param"=>"type","name"=>'链接类型',"value"=>array("1"=>"站内链接","2"=>"原链接"));
		$search_list[]=array("param"=>"eject","name"=>'弹出窗口',"value"=>array("1"=>"新窗口","2"=>"原窗口"));
		$search_list[]=array("param"=>"display","name"=>'显示状态',"value"=>array("1"=>"是","2"=>"否"));
		$this->yunset("search_list",$search_list);
	}
	function index_action(){
		$NavmapM	=	$this->MODEL('navmap');
		$this->set_search();
		
		if($_GET['eject']){
			if($_GET['eject']=='2'){
				$where['eject']		=	'0';
			}else{
				$where['eject']		=	$_GET['eject'];
			}
			$urlarr['eject']=$_GET['eject'];
		}
		if($_GET['display']){
			if($_GET['display']=='2'){
				$where['display']	=	'0';
			}else{
				$where['display']	=	$_GET['display'];
			}
			$urlarr['display']		=	$_GET['display'];
		}
		if (trim($_GET['keyword'])){
			if($_GET['type']=='2'){
				$where['url']		=	array('like',trim($_GET['keyword']));
			}else{
				$where['name']		=	array('like',trim($_GET['keyword']));
			}
			$urlarr['keyword']		=	$_GET['keyword'];
		}
		$urlarr['page']				=	"{{page}}";
		$pageurl					=	Url($_GET['m'],$urlarr,'admin');
		$pageM						=	$this->MODEL('page');
		$pages						=	$pageM->pageList('navmap',$where,$pageurl,$_GET['page'],$limit);
		if($pages['total']>0){
			$where['orderby']		=	'sort';
			$where['limit']			=	$pages['limit'];
			$nav					=	$NavmapM->getNavMapList($where);
			if(is_array($nav)){
				foreach($nav as $key=>$value){
					foreach($nav as $k=>$v){
						if($value['id']==$v['nid']){
							$nav[$k]['typename']	=	$value['name'];
						}
					}
				}
			}
		}
		$this->yunset("nav",$nav);
		$this->yuntpl(array('admin/admin_navmap'));
	}
	//添加网站地图
	function add_action(){
		$NavmapM	=	$this->MODEL('navmap');
		$type		=	$NavmapM->getNavMapList(1,array('field'=>'`id`,`name`'));
	    $this->yunset("type",$type);
		if($_GET['id']){
			$types	=	$NavmapM->getNavMap(array('id'=>$_GET['id']));
			$this->yunset("types",$types);
			$this->yunset("lasturl",$_SERVER['HTTP_REFERER']);
		}
       $this->yuntpl(array('admin/admin_navmap_add'));
	}

	//更新添加
	function save_action(){
		$NavmapM	=	$this->MODEL('navmap');
		if($_POST['submit']){
			$postData=array(
				'nid'		=>	$_POST['nid'],
				'eject'		=>	$_POST['eject'],
				'display'	=>	$_POST['display'],
				'name'		=>	$_POST['name'],
				'url'		=>	str_replace("amp;","",$_POST['url']),
				'furl'		=>	$_POST['furl'],
				'sort'		=>	$_POST['sort'],
				'type'		=>	$_POST['type'],
			);
			if($_POST['id']){
				$nbid	=	$NavmapM->upNavMap(array('id'=>$_POST['id']),$postData);
				$msg	=	"更新网站地图(ID:".$_POST['id'].")";
			}else{
				$nbid	=	$NavmapM->addNavMap($postData);
				$msg	=	"添加网站地图";
			}
			$this->cache_action();
			isset($nbid)?$this->ACT_layer_msg($msg."成功！",9,"index.php?m=navmap",2,1):$this->ACT_layer_msg($msg."失败！",8,"index.php?m=navmap");
		}
	}
	function del_action(){
		$this->check_token();
		 //批量删除
		$NavmapM	=	$this->MODEL('navmap');
		$return		=	$NavmapM->delNavMap($_GET['del']);
		$this->cache_action();
		$this->layer_msg($return['msg'],$return['errcode'],$return['layertype'],$_SERVER['HTTP_REFERER'],2,1);
	}

	function cache_action(){
		include(LIB_PATH."cache.class.php");
		$cacheclass= new cache(PLUS_PATH,$this->obj);
		$makecache=$cacheclass->navmap_cache("navmap.cache.php");
	}
	function nav_xianshi_action(){
	    $this->check_token();
		$NavmapM	=	$this->MODEL('navmap');
		$nid		=	$NavmapM->upNavMap(array('id'=>intval($_GET['id'])),array(''.$_GET['type'].''=>intval($_GET['rec'])));
		if($_GET['type']=='display'){
	        $this->MODEL('log')->addAdminLog("网站地图是否显示(ID:".$_GET['id'].")设置成功！");
	    }else{
	        $this->MODEL('log')->addAdminLog("网站地图是否新窗口打开(ID:".$_GET['id'].")设置成功！");
	    }
		$this->cache_action();
	    echo $nid?1:0;die;
	}
}


?>